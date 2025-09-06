<?php
// student_exam_routine.php
// This file contains only the search functionality and exam routine display

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
// POST handlers for search
// -------------------------------
$errors = [];
$success = null;
$search_results = [];
$student_biodata = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_student'])) {
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
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Student Exam Routine Search</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    min-height: 100vh;
    padding: 20px;
}
.container {
    max-width: 1200px;
}
.search-card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    margin-bottom: 30px;
}
.card-header {
    background: linear-gradient(90deg, #3f51b5, #2196f3);
    color: white;
    padding: 20px;
}
.table-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.table thead {
    background: linear-gradient(90deg, #3f51b5, #2196f3);
    color: white;
}
.table th {
    padding: 15px;
    font-weight: 600;
    text-align: center;
    vertical-align: middle;
}
.table td {
    padding: 12px 15px;
    vertical-align: middle;
    text-align: center;
}
.btn-back {
    background: linear-gradient(90deg, #6c757d, #5a6268);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 25px;
    font-weight: 600;
    margin-bottom: 25px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}
.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}
.badge-room {
    background: #e3f2fd;
    color: #0d6efd;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-set {
    background: #fff2e5;
    color: #fd7e14;
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 600;
}
.search-form {
    padding: 25px;
    background: #f8f9fa;
    border-radius: 0 0 15px 15px;
}
.student-info {
    background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
.empty-state {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}
.empty-state i {
    font-size: 5rem;
    margin-bottom: 20px;
    color: #dee2e6;
}
.highlight {
    background-color: #fff8e1 !important;
}
.search-btn {
    background: linear-gradient(90deg, #3f51b5, #2196f3);
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
}
.student-biodata {
    background-color: #f8f9fa; 
    border-radius: 8px; 
    padding: 20px; 
    margin-bottom: 20px;
}
.biodata-header {
    border-bottom: 2px solid #3f51b5; 
    padding-bottom: 10px; 
    margin-bottom: 15px;
}
.biodata-row {
    display: flex; 
    margin-bottom: 8px;
}
.biodata-label {
    font-weight: 600; 
    min-width: 150px; 
    color: #495057;
}
.biodata-value {
    color: #212529;
}
</style>
</head>
<body>
<div class="container">
    <!-- Back Button -->
    <button class="btn btn-back" onclick="window.history.back()">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </button>

    <!-- Search Card -->
    <div class="card search-card">
        <div class="card-header">
            <h3 class="mb-0"><i class="fas fa-search me-2"></i>Student Exam Routine Search</h3>
            <p class="mb-0 mt-2">Enter a student ID to view their exam schedule</p>
        </div>
        <div class="search-form">
            <form method="post" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Student ID</label>
                    <input type="number" name="student_search" class="form-control" placeholder="Enter Student ID" required>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" name="search_student" class="btn search-btn text-white">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $er) echo "<div>" . htmlspecialchars($er) . "</div>"; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- If student biodata exists, show it -->
    <?php if ($student_biodata): ?>
        <div class="student-biodata">
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
        <div class="table-container">
            <h5 class="p-3 mb-0 bg-primary text-white text-center">Exam Routine for Student</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Faculty</th>
                            <th>Room</th>
                            <th>Set</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $si = 1; foreach ($search_results as $sr): ?>
                            <tr class="<?php echo ($si % 2 == 1) ? 'highlight' : ''; ?>">
                                <td><?php echo $si++; ?></td>
                                <td><?php echo htmlspecialchars($sr['exam_date']); ?></td>
                                <td><?php echo htmlspecialchars($sr['day']); ?></td>
                                <td><?php echo htmlspecialchars(date('H:i', strtotime($sr['time']))); ?></td>
                                <td><?php echo htmlspecialchars($sr['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($sr['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($sr['faculty_name']); ?></td>
                                <td><span class="badge-room"><?php echo htmlspecialchars($sr['room_no']); ?></span></td>
                                <td><span class="badge-set"><?php echo htmlspecialchars(set_label_from_no($sr['set_no'])); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Empty state -->
        <div class="table-container empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Exam Routine Found</h3>
            <p>Search for a student ID to view their exam schedule.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
// Set focus on search input when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('input[name="student_search"]').focus();
});
</script>
</body>
</html>