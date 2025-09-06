<?php
// exam_routine.php
// Full file with: generate routine, clear routine (per course/day/time), search by student id,
// and delete ALL routines (with confirm). Keeps all previous safety and fixes.

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // <-- set your DB password
define('DB_NAME', 'skst_university'); // <-- set your DB name

// connect
$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($con->connect_errno) {
    die("DB Connection failed: " . $con->connect_error);
}
$con->set_charset('utf8mb4');

// helper to convert numeric set_no (1..25) -> A1..E5
function set_label_from_no($n) {
    $n = intval($n);
    if ($n < 1) return '';
    $groupIndex = intval(floor(($n - 1) / 5)); // 0..4 => A..E
    $num = (($n - 1) % 5) + 1;
    $letter = chr(ord('A') + $groupIndex);
    return $letter . $num;
}

// -------------------------------
// Courses with at least one 'enrolled' enrollment
// -------------------------------
$courses = [];
$sqlCourses = "SELECT DISTINCT c.course_id, c.course_code, c.course_name
               FROM enrollments e
               JOIN course c ON e.course_id = c.course_id
               WHERE e.status = 'enrolled'
               ORDER BY c.course_code";
if ($res = $con->query($sqlCourses)) {
    while ($row = $res->fetch_assoc()) $courses[] = $row;
    $res->close();
}

// -------------------------------
// Helper functions (store_result + free_result used)
// -------------------------------
function get_used_sets($con, $roomStr, $exam_date, $time) {
    $used = [];
    $sql = "SELECT set_no FROM exm_routine WHERE room_no = ? AND exam_date = ? AND time = ? ORDER BY set_no ASC";
    if ($cstmt = $con->prepare($sql)) {
        $cstmt->bind_param('sss', $roomStr, $exam_date, $time);
        $cstmt->execute();
        $cstmt->store_result();
        $used_set = null;
        $cstmt->bind_result($used_set);
        while ($cstmt->fetch()) $used[] = (int)$used_set;
        $cstmt->free_result();
        $cstmt->close();
    }
    return $used;
}

function find_latest_partial_room($con, $exam_date, $time, $capacity_per_room = 25) {
    $sql = "SELECT room_no, COUNT(*) as used_count
            FROM exm_routine
            WHERE exam_date = ? AND time = ?
            GROUP BY room_no
            HAVING used_count < ?
            ORDER BY room_no+0 DESC
            LIMIT 1";
    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param('ssi', $exam_date, $time, $capacity_per_room);
        $stmt->execute();
        $stmt->store_result();
        $room_no = null;
        $used_count = null;
        $stmt->bind_result($room_no, $used_count);
        if ($stmt->fetch()) {
            $stmt->free_result();
            $stmt->close();
            return ['room_no' => (string)$room_no, 'used' => (int)$used_count];
        }
        $stmt->free_result();
        $stmt->close();
    }
    return null;
}

// -------------------------------
// POST handlers
// -------------------------------
$errors = [];
$success = null;
$start_info = null;

// search results holder
$search_results = [];
$student_biodata = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Generate routine (existing behavior)
    if (isset($_POST['generate_routine'])) {
        $course_id = intval($_POST['course_id'] ?? 0);
        $exam_date = $_POST['exam_date'] ?? '';
        $time = $_POST['time'] ?? '';

        if (!$course_id || !$exam_date || !$time) {
            $errors[] = "Please select course, exam date and time.";
        } else {
            // Convert date to day of week
            $day = date('l', strtotime($exam_date));
            
            // latest enrollment date for this course where status='enrolled'
            $latestDateForCourse = null;
            $q = "SELECT DATE(MAX(enrollment_date)) FROM enrollments WHERE course_id = ? AND status = 'enrolled'";
            if ($stmt = $con->prepare($q)) {
                $stmt->bind_param('i', $course_id);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($latestDateForCourse);
                $stmt->fetch();
                $stmt->free_result();
                $stmt->close();
            }

            if (!$latestDateForCourse) {
                $errors[] = "No enrolled students found for the selected course.";
            } else {
                // fetch students for that course/date
                $students = [];
                $sql = "SELECT student_id, faculty_id FROM enrollments 
                        WHERE course_id = ? AND DATE(enrollment_date) = ? AND status = 'enrolled'
                        ORDER BY student_id";
                if ($stmt = $con->prepare($sql)) {
                    $stmt->bind_param('is', $course_id, $latestDateForCourse);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($student_id, $faculty_id);
                    while ($stmt->fetch()) $students[] = ['student_id'=>$student_id, 'faculty_id'=>$faculty_id];
                    $stmt->free_result();
                    $stmt->close();
                }

                if (count($students) === 0) {
                    $errors[] = "No enrolled students found for this course on {$latestDateForCourse}.";
                } else {
                    $capacity_per_room = 25;
                    $start_room = 100;
                    $max_room_limit = $start_room + 2000;

                    $latest_partial = find_latest_partial_room($con, $exam_date, $time, $capacity_per_room);
                    if ($latest_partial) {
                        $room = (int)$latest_partial['room_no'];
                        $used_sets = get_used_sets($con, (string)$room, $exam_date, $time);
                        $first_free_set = null;
                        for ($k = 1; $k <= $capacity_per_room; $k++) {
                            if (!in_array($k, $used_sets, true)) { $first_free_set = $k; break; }
                        }
                        if ($first_free_set === null) {
                            $room++;
                            $first_free_set = 1;
                        }
                        $start_info = "Starting allocation at latest partially-filled room <strong>{$room}</strong>, next set: <strong>" . htmlspecialchars(set_label_from_no($first_free_set)) . " (set_no={$first_free_set})</strong>.";
                    } else {
                        $room = $start_room;
                        $first_free_set = 1;
                        $start_info = "No partially-filled room found for selected date/time. Starting allocation at room <strong>{$room}</strong>, set <strong>" . htmlspecialchars(set_label_from_no($first_free_set)) . " (set_no={$first_free_set})</strong>.";
                    }

                    // prepare statements
                    $checkStudentStmt = $con->prepare("SELECT COUNT(*) FROM exm_routine WHERE student_id = ? AND exam_date = ? AND time = ?");
                    $ins = $con->prepare("INSERT INTO exm_routine (student_id, course_id, faculty_id, day, exam_date, time, room_no, set_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                    if (!$checkStudentStmt || !$ins) {
                        $errors[] = "Database prepare failed: " . $con->error;
                    } else {
                        $inserted = 0;
                        $skipped = [];
                        $local_assigned_ids = [];

                        $con->begin_transaction();
                        try {
                            $current_room = $room;
                            $local_used = get_used_sets($con, (string)$current_room, $exam_date, $time);

                            foreach ($students as $s) {
                                $sid = intval($s['student_id']);

                                // check if student already has an exam at the same date/time
                                $count = 0;
                                $checkStudentStmt->bind_param('iss', $sid, $exam_date, $time);
                                $checkStudentStmt->execute();
                                $checkStudentStmt->store_result();
                                $checkStudentStmt->bind_result($count);
                                $checkStudentStmt->fetch();
                                $checkStudentStmt->free_result();

                                if ($count > 0 || in_array($sid, $local_assigned_ids, true)) {
                                    $skipped[] = $sid;
                                    continue;
                                }

                                $assigned = false;
                                $attempts = 0;
                                while (!$assigned) {
                                    if ($attempts++ > 5000) throw new Exception("Too many attempts to allocate a seat - aborting.");
                                    if ($current_room > $max_room_limit) throw new Exception("Exceeded maximum room search limit.");

                                    $set_no = null;
                                    for ($k = 1; $k <= $capacity_per_room; $k++) {
                                        if (!in_array($k, $local_used, true)) { $set_no = $k; break; }
                                    }

                                    if ($set_no === null) {
                                        $current_room++;
                                        $local_used = get_used_sets($con, (string)$current_room, $exam_date, $time);
                                        continue;
                                    }

                                    $roomStr = (string)$current_room;
                                    $ins->bind_param('iiissssi', $s['student_id'], $course_id, $s['faculty_id'], $day, $exam_date, $time, $roomStr, $set_no);

                                    if (@$ins->execute()) {
                                        $inserted++;
                                        $assigned = true;
                                        $local_used[] = (int)$set_no;
                                        $local_assigned_ids[] = $sid;
                                    } else {
                                        $errno = $con->errno;
                                        if ($errno === 1062) {
                                            $local_used = get_used_sets($con, (string)$current_room, $exam_date, $time);
                                            continue;
                                        } else {
                                            throw new Exception("Insert failed: (" . $con->errno . ") " . $con->error);
                                        }
                                    }
                                }
                            }

                            $con->commit();
                            $ins->close();
                            $checkStudentStmt->close();

                            $success = "Generated routine for course id {$course_id}. Inserted {$inserted} rows. Date: {$exam_date}, Time: {$time}.";
                            if (!empty($skipped)) {
                                $success .= " Skipped " . count($skipped) . " student(s) already scheduled at the same date/time: " . implode(', ', $skipped) . ".";
                            }
                            if ($start_info) $success .= " Start info: " . strip_tags($start_info);
                            $success .= " (Used enrollments from date: {$latestDateForCourse})";
                        } catch (Exception $e) {
                            if (isset($checkStudentStmt) && $checkStudentStmt instanceof mysqli_stmt) {
                                @$checkStudentStmt->free_result();
                                @$checkStudentStmt->close();
                            }
                            if (isset($ins) && $ins instanceof mysqli_stmt) {
                                @$ins->close();
                            }
                            $con->rollback();
                            $errors[] = "Failed to create routine: " . $e->getMessage();
                        }
                    }
                }
            }
        }
    }

    // 2) Clear routine for course+date+time (existing)
    if (isset($_POST['clear_routine'])) {
        $course_id = intval($_POST['course_id'] ?? 0);
        $exam_date = $_POST['exam_date'] ?? '';
        $time = $_POST['time'] ?? '';

        if (!$course_id || !$exam_date || !$time) {
            $errors[] = "To clear, select course, exam date and time.";
        } else {
            $delSql = "DELETE FROM exm_routine WHERE course_id = ? AND exam_date = ? AND time = ?";
            if ($stmt = $con->prepare($delSql)) {
                $stmt->bind_param('iss', $course_id, $exam_date, $time);
                if ($stmt->execute()) {
                    $affected = $stmt->affected_rows;
                    $success = "Cleared {$affected} rows for Course ID {$course_id} on {$exam_date} at {$time}.";
                } else {
                    $errors[] = "Clear failed: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors[] = "Clear prepare failed: " . $con->error;
            }
        }
    }

    // 3) Search individual routine by student id
    if (isset($_POST['search_student'])) {
        $search_id = intval($_POST['student_search'] ?? 0);
        if (!$search_id) {
            $errors[] = "Enter a valid Student ID to search.";
        } else {
            // First get student biodata
            $student_biodata = null;
            $q_biodata = "SELECT id, first_name, last_name, father_name, mother_name, date_of_birth, 
                                 student_phone, email, department, gender, blood_group, nationality, religion
                          FROM student_registration 
                          WHERE id = ?";
            if ($stmt = $con->prepare($q_biodata)) {
                $stmt->bind_param('i', $search_id);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $first_name, $last_name, $father_name, $mother_name, $date_of_birth, 
                                  $student_phone, $email, $department, $gender, $blood_group, $nationality, $religion);
                if ($stmt->fetch()) {
                    $student_biodata = [
                        'id' => $id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'father_name' => $father_name,
                        'mother_name' => $mother_name,
                        'date_of_birth' => $date_of_birth,
                        'student_phone' => $student_phone,
                        'email' => $email,
                        'department' => $department,
                        'gender' => $gender,
                        'blood_group' => $blood_group,
                        'nationality' => $nationality,
                        'religion' => $religion
                    ];
                }
                $stmt->free_result();
                $stmt->close();
            }

            // Then get exam routine
            $search_results = [];
            $q = "SELECT r.day, r.exam_date, r.time, r.room_no, r.set_no, r.course_id, COALESCE(c.course_code,'') AS course_code, COALESCE(c.course_name,'') AS course_name, COALESCE(f.name,'') AS faculty_name
                  FROM exm_routine r
                  LEFT JOIN course c ON r.course_id = c.course_id
                  LEFT JOIN faculty f ON r.faculty_id = f.faculty_id
                  WHERE r.student_id = ?
                  ORDER BY r.exam_date, r.time, r.room_no+0, r.set_no";
            if ($stmt = $con->prepare($q)) {
                $stmt->bind_param('i', $search_id);
                $stmt->execute();
                $stmt->store_result();
                $day = $exam_date = $time = $room_no = $set_no = $course_id_r = $course_code = $course_name = $faculty_name = null;
                $stmt->bind_result($day, $exam_date, $time, $room_no, $set_no, $course_id_r, $course_code, $course_name, $faculty_name);
                while ($stmt->fetch()) {
                    $search_results[] = [
                        'day'=>$day,
                        'exam_date'=>$exam_date,
                        'time'=>$time,
                        'room_no'=>$room_no,
                        'set_no'=>$set_no,
                        'course_id'=>$course_id_r,
                        'course_code'=>$course_code,
                        'course_name'=>$course_name,
                        'faculty_name'=>$faculty_name
                    ];
                }
                $stmt->free_result();
                $stmt->close();

                if (empty($search_results) && $student_biodata) {
                    $success = "No exam routine entries found for student ID {$search_id}.";
                } else if ($student_biodata) {
                    $success = "Found " . count($search_results) . " routine entry(ies) for student ID {$search_id}.";
                } else {
                    $errors[] = "No student found with ID {$search_id}.";
                }
            } else {
                $errors[] = "Search prepare failed: " . $con->error;
            }
        }
    }

    // 4) Delete ALL routines (with client confirmation + server-side check)
    if (isset($_POST['delete_all'])) {
        // require explicit confirm flag
        $confirm_all = $_POST['confirm_delete_all'] ?? '';
        if ($confirm_all !== '1') {
            $errors[] = "Delete all not confirmed.";
        } else {
            try {
                $con->begin_transaction();
                // Use DELETE so foreign keys (if any) behave predictably; can use TRUNCATE if desired
                if (!$con->query("DELETE FROM exm_routine")) {
                    throw new Exception("Delete failed: " . $con->error);
                }
                $con->commit();
                $success = "All routine rows have been deleted.";
            } catch (Exception $e) {
                $con->rollback();
                $errors[] = "Failed to delete all routines: " . $e->getMessage();
            }
        }
    }
}

// -------------------------------
// Fetch existing routines to display
// -------------------------------
$routines = [];
$sql = "SELECT r.student_id,
               COALESCE(s.first_name, '') AS first_name, COALESCE(s.last_name, '') AS last_name,
               r.course_id, COALESCE(c.course_code, '') AS course_code, COALESCE(c.course_name, '') AS course_name,
               r.faculty_id, COALESCE(f.name, '') AS faculty_name, r.day, r.exam_date, r.time, r.room_no, r.set_no
        FROM exm_routine r
        LEFT JOIN student_registration s ON r.student_id = s.id
        LEFT JOIN course c ON r.course_id = c.course_id
        LEFT JOIN faculty f ON r.faculty_id = f.faculty_id
        ORDER BY r.exam_date, r.time, r.room_no+0, r.set_no";
if ($res = $con->query($sql)) {
    while ($row = $res->fetch_assoc()) $routines[] = $row;
    $res->close();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Exam Routine Manager (with search & delete all)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #7209b7;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
            --info: #4895ef;
            --background: #f0f7ff;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --gradient: linear-gradient(120deg, var(--primary), var(--secondary));
        }

        body {
            background: var(--background);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #2d3748;
            line-height: 1.6;
            padding-bottom: 2rem;
        }

        .navbar {
            background: var(--gradient);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .main-container {
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: var(--gradient);
            color: white;
            border-radius: 0;
            padding: 1.5rem;
            border: none;
        }

        .card-header h3 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .card-header h3 i {
            margin-right: 12px;
            font-size: 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            font-size: 1.4rem;
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-danger {
            background: linear-gradient(120deg, #e63946, #c1121f);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.3);
        }

        .btn-outline-danger {
            border: 2px solid #e63946;
            color: #e63946;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-danger:hover {
            background: #e63946;
            color: white;
            transform: translateY(-3px);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: linear-gradient(120deg, #4ade80, #16a34a);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(120deg, #e63946, #c1121f);
            color: white;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background: var(--gradient);
            color: white;
            border: none;
            padding: 1.2rem;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 1.2rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .badge {
            padding: 0.6rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .badge-room {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .badge-set {
            background: rgba(247, 127, 0, 0.1);
            color: #fca311;
        }

        .student-biodata {
            background: linear-gradient(120deg, #f0f7ff, #e6f2ff);
            border-radius: 14px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        .biodata-header {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
        }

        .biodata-header i {
            margin-right: 10px;
            font-size: 1.4rem;
        }

        .biodata-row {
            display: flex;
            margin-bottom: 1rem;
        }

        .biodata-label {
            font-weight: 600;
            min-width: 160px;
            color: var(--dark);
        }

        .biodata-value {
            color: #4a5568;
        }

        .progress {
            height: 14px;
            border-radius: 12px;
            background: #e9ecef;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            border-radius: 12px;
            background: var(--gradient);
        }

        .search-box {
            background: white;
            border-radius: 14px;
            padding: 2rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .highlight {
            background-color: rgba(76, 201, 240, 0.1) !important;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .info-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .room-utilization {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .room-item {
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .biodata-row {
                flex-direction: column;
                margin-bottom: 1.2rem;
            }
            
            .biodata-label {
                min-width: unset;
                margin-bottom: 0.4rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
<!-- Navbar with back button -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-calendar-alt me-2"></i>Exam Routine Manager
        </a>
        <button class="btn btn-light ms-auto" onclick="window.history.back()">
            <i class="fas fa-arrow-left me-1"></i> Back
        </button>
    </div>
</nav>

<div class="container py-4">
    <div class="card mb-4">
        <div class="header">
            <h3 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Exam Routine: Create & Manage</h3>
            <div class="small-muted mt-1">Courses shown have at least one enrollment with <strong>status = 'enrolled'</strong>.</div>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $er) echo "<div>" . htmlspecialchars($er) . "</div>"; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">Course (uses latest enrollment date for that course)</label>
                    <select name="course_id" id="courseSelect" class="form-select" required>
                        <option value="">-- choose course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo (int)$c['course_id']; ?>"><?php echo htmlspecialchars($c['course_code'] . ' - ' . $c['course_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="small-muted mt-1">Students included are those with <code>status='enrolled'</code> whose enrollment <strong>date</strong> equals the latest date for that course.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Exam Date</label>
                    <input type="date" name="exam_date" id="examDate" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Time</label>
                    <select name="time" id="timeSelect" class="form-select" required>
                        <option value="">-- time --</option>
                        <option value="09:00:00">09:00 - 12:00</option>
                        <option value="13:00:00">13:00 - 15:00</option>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <button type="submit" name="generate_routine" class="btn btn-primary"><i class="fas fa-play me-1"></i> Generate</button>
                </div>

                <div class="col-12 small-muted">
                    Allocation continues from the latest partially-filled room for the same date+time. Students already scheduled at the same date+time are skipped.
                </div>

                <div class="col-md-3 d-grid">
                    <button type="submit" name="clear_routine" class="btn btn-outline-danger"><i class="fas fa-trash me-1"></i> Clear Routine</button>
                </div>
            </form>

            <!-- Search student routine + Delete All -->
            <div class="row mb-3 g-3">
                <div class="col-md-6">
                    <form method="post" class="d-flex gap-2">
                        <input type="number" name="student_search" class="form-control" placeholder="Enter Student ID to search" required>
                        <button type="submit" name="search_student" class="btn btn-outline-primary"><i class="fas fa-search me-1"></i> Search</button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <form id="deleteAllForm" method="post" onsubmit="return confirmDeleteAll();">
                        <input type="hidden" name="confirm_delete_all" id="confirm_delete_all" value="0">
                        <button type="submit" name="delete_all" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Delete All Routines</button>
                    </form>
                </div>
            </div>

            <!-- If student biodata exists, show it -->
            <?php if ($student_biodata): ?>
                <div class="student-biodata mb-4">
                    <h5 class="biodata-header">Student Biodata</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="biodata-row">
                                <span class="biodata-label">Student ID:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['id']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Name:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['first_name'] . ' ' . $student_biodata['last_name']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Father's Name:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['father_name']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Mother's Name:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['mother_name']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Date of Birth:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['date_of_birth']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Phone:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['student_phone']); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="biodata-row">
                                <span class="biodata-label">Email:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['email']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Department:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['department']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Gender:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['gender']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Blood Group:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['blood_group']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Nationality:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['nationality']); ?></span>
                            </div>
                            <div class="biodata-row">
                                <span class="biodata-label">Religion:</span>
                                <span class="biodata-value"><?php echo htmlspecialchars($student_biodata['religion']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- If search results exist, show them -->
            <?php if (!empty($search_results)): ?>
                <div class="mb-3">
                    <h5>Exam Routine for Student</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Faculty</th>
                                <th>Room</th>
                                <th>Set</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $si = 1; foreach ($search_results as $sr): ?>
                                <tr>
                                    <td><?php echo $si++; ?></td>
                                    <td><?php echo htmlspecialchars($sr['exam_date']); ?></td>
                                    <td><?php echo htmlspecialchars($sr['day']); ?></td>
                                    <td><?php echo htmlspecialchars(date('H:i', strtotime($sr['time']))); ?></td>
                                    <td><?php echo htmlspecialchars(($sr['course_code'] ?: $sr['course_id']) . ' - ' . $sr['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sr['faculty_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sr['room_no']); ?></td>
                                    <td><?php echo htmlspecialchars(set_label_from_no($sr['set_no'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Existing routines -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>All Exam Routines</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Student ID</th>
                            <th>Course</th>
                            <th>Course ID</th>
                            <th>Faculty</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Set</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($routines)): ?>
                        <tr><td colspan="11" class="text-center small-muted">No routine entries yet.</td></tr>
                    <?php else: $i=1; foreach ($routines as $r): ?>
                        <tr class="<?php echo ($i <= 10 ? 'highlight' : ''); ?>">
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars(trim($r['first_name'].' '.$r['last_name'])); ?></td>
                            <td><?php echo (int)$r['student_id']; ?></td>
                            <td><?php echo htmlspecialchars($r['course_code'].' - '.$r['course_name']); ?></td>
                            <td><?php echo (int)$r['course_id']; ?></td>
                            <td><?php echo htmlspecialchars($r['faculty_name']); ?></td>
                            <td><?php echo htmlspecialchars($r['exam_date']); ?></td>
                            <td><?php echo htmlspecialchars($r['day']); ?></td>
                            <td><?php echo htmlspecialchars(date('H:i', strtotime($r['time']))); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($r['room_no']); ?></span></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars(set_label_from_no($r['set_no'])); ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="small-muted mt-3">
                Note: Students are selected only if their enrollment <code>status='enrolled'</code> and their enrollment <strong>date</strong> equals the latest enrollment date for that course. Double-booking at the same date+time is prevented.
            </div>
        </div>
    </div>

    <!-- Room utilization -->
    <div class="card">
        <div class="card-body">
            <h5>Room Utilization Snapshot</h5>
            <?php
            $summarySql = "SELECT room_no, COUNT(*) AS used FROM exm_routine GROUP BY room_no ORDER BY room_no+0 LIMIT 10";
            if ($res = $con->query($summarySql)) {
                while ($row = $res->fetch_assoc()) {
                    $used = intval($row['used']);
                    $percent = min(100, round($used / 25 * 100));
                    echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                    echo '<div><strong>Room ' . htmlspecialchars($row['room_no']) . '</strong></div>';
                    echo '<div><span class="badge bg-light text-dark">' . $used . '/25</span></div>';
                    echo '</div>';
                    echo '<div class="progress mb-3" style="height:14px"><div class="progress-bar" role="progressbar" style="width:' . $percent . '%" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100"></div></div>';
                }
                $res->close();
            } else {
                echo '<div class="small-muted">No data</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- icons + bootstrap -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
// Confirm delete all - sets hidden confirm field to '1' when confirmed
function confirmDeleteAll() {
    if (confirm('Are you sure you want to DELETE ALL exam routine rows? This action cannot be undone.')) {
        document.getElementById('confirm_delete_all').value = '1';
        return true;
    }
    return false;
}

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const yyyy = today.getFullYear();
    let mm = today.getMonth() + 1;
    let dd = today.getDate();
    
    if (dd < 10) dd = '0' + dd;
    if (mm < 10) mm = '0' + mm;
    
    const formattedToday = `${yyyy}-${mm}-${dd}`;
    document.getElementById('examDate').value = formattedToday;
});
</script>
</body>
</html>