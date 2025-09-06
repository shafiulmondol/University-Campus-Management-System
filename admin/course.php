<?php
ob_start();
session_start();
require_once '../library/notice.php';

// Database connection
$mysqli = new mysqli('localhost', 'root', '', 'skst_university');
if ($mysqli->connect_error) die("Connection failed: " . $mysqli->connect_error);

// Handle dashboard back button
if (isset($_POST['dashboard'])) {
    header("Location: admin.php");
    exit();
}

// Search input
$search_code = $_POST['search_code'] ?? '';

// Display success/error messages
$success_msg = '';
$error_msg = '';

// Submit Course
if (isset($_POST['submit_course'])) {
    // First check if course_id already exists
    $check_stmt = $mysqli->prepare("SELECT course_id FROM course WHERE course_id = ?");
    $check_stmt->bind_param("s", $_POST['course_id']);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $error_msg = "A course with ID " . $_POST['course_id'] . " already exists. Please use a different ID.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO course (course_id, course_code, course_name, credit_hours, department, semester, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssiss", $_POST['course_id'], $_POST['course_code'], $_POST['course_name'], $_POST['credit_hours'], $_POST['department'], $_POST['semester']);
        if ($stmt->execute()) {
            $success_msg = "Course added successfully!";
        } else {
            $error_msg = "Failed to add course: " . $mysqli->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Add Instructor
if (isset($_POST['add_instructor'])) {
    $check = $mysqli->prepare("SELECT * FROM course_instructor WHERE course_id=? AND class_day=? AND class_time=? AND room_number=?");
    $check->bind_param("isss", $_POST['course_id'], $_POST['class_day'], $_POST['class_time'], $_POST['room_number']);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        $error_msg = "This course already has an instructor at this time and room.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO course_instructor (faculty_id, course_id, class_day, class_time, room_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $_POST['faculty_id'], $_POST['course_id'], $_POST['class_day'], $_POST['class_time'], $_POST['room_number']);
        if ($stmt->execute()) {
            $success_msg = "Instructor assigned successfully!";
        } else {
            $error_msg = "Error: ".$mysqli->error;
        }
        $stmt->close();
    }
    $check->close();
}

// Handle Enrolment Actions
if (isset($_POST['enrol_action'])) {
    $action = $_POST['action'];
    $enrollment_id = $_POST['enrollment_id'];
    
    if ($action == 'edit') {
        // Update enrolment details
        $stmt = $mysqli->prepare("UPDATE enrollments SET student_id=?, course_id=?, faculty_id=?, status=? WHERE enrollment_id=?");
        $stmt->bind_param("iiisi", $_POST['student_id'], $_POST['course_id'], $_POST['faculty_id'], $_POST['status'], $enrollment_id);
        if ($stmt->execute()) {
            $success_msg = "Enrollment updated successfully!";
        } else {
            $error_msg = "Failed to update enrollment: " . $mysqli->error;
        }
        $stmt->close();
    } elseif ($action == 'drop') {
        // Drop enrolment
        $stmt = $mysqli->prepare("UPDATE enrollments SET status='dropped' WHERE enrollment_id=?");
        $stmt->bind_param("i", $enrollment_id);
        if ($stmt->execute()) {
            $success_msg = "Enrollment dropped successfully!";
        } else {
            $error_msg = "Failed to drop enrollment: " . $mysqli->error;
        }
        $stmt->close();
    }
}

// Display Courses
$query = "SELECT course_id, course_code, course_name, credit_hours, department, semester, created_at, updated_at FROM course WHERE 1";
if (!empty($search_code)) {
    $query .= " AND course_code LIKE '%" . $mysqli->real_escape_string($search_code) . "%'";
}
$query .= " ORDER BY created_at DESC";
$result = $mysqli->query($query);

// For instructor display
if (isset($_POST['show_instructors'])) {
    $instructor_query = "SELECT ci.faculty_id, f.name AS faculty_name, ci.course_id, c.course_name, ci.class_day, ci.class_time, ci.room_number
              FROM course_instructor ci
              JOIN faculty f ON ci.faculty_id=f.faculty_id
              JOIN course c ON ci.course_id=c.course_id
              ORDER BY ci.class_day, ci.class_time";
    $instructor_result = $mysqli->query($instructor_query);
}

// For add instructor form
if (isset($_POST['show_add_instructor_form'])) {
    $faculty_result = $mysqli->query("SELECT faculty_id, name FROM faculty");
    $course_result = $mysqli->query("SELECT course_id, course_name FROM course");
}

// For enrolment status
if (isset($_POST['show_enrollment_status'])) {
    // Build enrollment query with search filters
    $enrollment_search = $_POST['enrollment_search'] ?? '';
    $enrollment_query = "SELECT e.enrollment_id, e.student_id, s.first_name, s.last_name, 
                        e.course_id, c.course_name, c.course_code, f.name as faculty_name,
                        e.enrollment_date, e.status
                        FROM enrollments e
                        JOIN student_registration s ON e.student_id = s.id
                        JOIN course c ON e.course_id = c.course_id
                        LEFT JOIN faculty f ON e.faculty_id = f.faculty_id
                        WHERE 1";
    
    if (!empty($enrollment_search)) {
        $enrollment_query .= " AND (c.course_name LIKE '%" . $mysqli->real_escape_string($enrollment_search) . "%' 
                            OR c.course_code LIKE '%" . $mysqli->real_escape_string($enrollment_search) . "%'
                            OR e.student_id LIKE '%" . $mysqli->real_escape_string($enrollment_search) . "%'
                            OR s.first_name LIKE '%" . $mysqli->real_escape_string($enrollment_search) . "%'
                            OR s.last_name LIKE '%" . $mysqli->real_escape_string($enrollment_search) . "%')";
    }
    
    $enrollment_query .= " ORDER BY e.enrollment_date DESC";
    $enrollment_result = $mysqli->query($enrollment_query);
}

// For suggested courses
if (isset($_POST['show_suggested_courses'])) {
    $student_search = $_POST['student_search'] ?? '';

    $students_query = "SELECT st_id, MAX(semister) AS max_semester 
                       FROM student_result 
                       GROUP BY st_id";
    if (!empty($student_search)) {
        $students_query = "SELECT st_id, MAX(semister) AS max_semester 
                           FROM student_result 
                           WHERE st_id LIKE '%" . $mysqli->real_escape_string($student_search) . "%'
                           GROUP BY st_id";
    }
    $students_result = $mysqli->query($students_query);
}

// For completed courses
if (isset($_POST['show_completed_courses'])) {
    $completed_student_search = $_POST['completed_student_search'] ?? '';

    // Get all students who have completed courses
    $completed_students_query = "SELECT DISTINCT st_id FROM student_result";
    if (!empty($completed_student_search)) {
        $completed_students_query = "SELECT DISTINCT st_id FROM student_result 
                                     WHERE st_id LIKE '%" . $mysqli->real_escape_string($completed_student_search) . "%'";
    }
    $completed_students_result = $mysqli->query($completed_students_query);
}

// For editing enrollment
if (isset($_POST['edit_enrollment'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $edit_enrollment_query = "SELECT e.*, s.first_name, s.last_name, c.course_name, c.course_code
                            FROM enrollments e
                            JOIN student_registration s ON e.student_id = s.id
                            JOIN course c ON e.course_id = c.course_id
                            WHERE e.enrollment_id = $enrollment_id";
    $edit_enrollment_result = $mysqli->query($edit_enrollment_query);
    $edit_enrollment = $edit_enrollment_result->fetch_assoc();
    
    // Get all courses for dropdown
    $all_courses = $mysqli->query("SELECT course_id, course_code, course_name FROM course");
    // Get all faculty for dropdown
    $all_faculty = $mysqli->query("SELECT faculty_id, name FROM faculty");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --secondary: #2ecc71;
            --secondary-dark: #27ae60;
            --danger: #e74c3c;
            --danger-dark: #c0392b;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
            --light-gray: #f5f7fa;
            --border-radius: 8px;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light) 0%, #dfe6e9 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: var(--dark);
        }
        
        .page-container {
            max-width: 1300px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .header {
            background: var(--dark);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-success {
            background: var(--secondary);
            color: white;
        }
        
        .btn-success:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: var(--danger-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn-warning:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--light);
            color: white;
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .search-container {
            background: var(--light-gray);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            background: var(--light-gray);
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 20px;
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: var(--primary);
            color: white;
            text-align: left;
            padding: 12px 15px;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .data-table tr:hover {
            background-color: #f1f9ff;
        }
        
        .form-container {
            background: var(--light-gray);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-top: 20px;
        }
        
        .form-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus, .form-select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-enrolled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-dropped {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .action-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .action-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .action-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .action-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .student-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--dark);
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
                justify-content: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="page-container">
    <div class="header">
        <h1><i class="fas fa-book"></i> Course Management System</h1>
        <div class="header-actions">
            <form method="post">
                <button type="submit" name="dashboard" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</button>
            </form>
        </div>
    </div>
    
    <div class="content">
        <div class="search-container">
            <form method="post" class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search_code" placeholder="Search by Course Code" class="search-input" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" style="display: none;">Search</button>
            </form>
        </div>

        <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <div class="action-buttons">
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_instructors" class="btn btn-primary"><i class="fas fa-chalkboard-teacher"></i> Show Instructors</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_add_instructor_form" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Instructor</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_enrollment_status" class="btn btn-warning"><i class="fas fa-user-graduate"></i> Enrollment Status</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_suggested_courses" class="btn btn-primary"><i class="fas fa-lightbulb"></i> Suggested Courses</button>
            </form>
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_completed_courses" class="btn btn-success"><i class="fas fa-check-circle"></i> Completed Courses</button>
            </form>
            <?php if (!isset($_POST['show_add_course_form'])): ?>
            <form method="post" style="display: inline;">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <button type="submit" name="show_add_course_form" class="btn btn-success"><i class="fas fa-plus"></i> Add Course</button>
            </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-list"></i> Course List</span>
                <span>Total: <?php echo $result->num_rows; ?> courses</span>
            </div>
            <div class="card-body">
                <?php if ($result && $result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Credit Hours</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php foreach ($row as $cell): ?>
                            <td><?php echo htmlspecialchars($cell); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Courses Found</h3>
                    <p>There are no courses to display at this time.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_POST['show_add_course_form'])): ?>
        <div class="form-container">
            <h3 class="form-title"><i class="fas fa-plus-circle"></i> Add New Course</h3>
            <form method="post">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <div class="form-grid">
                    <?php
                    $fields = [
                        'course_id' => ['label' => 'Course ID', 'type' => 'text'],
                        'course_code' => ['label' => 'Course Code', 'type' => 'text'],
                        'course_name' => ['label' => 'Course Name', 'type' => 'text'],
                        'credit_hours' => ['label' => 'Credit Hours', 'type' => 'number'],
                        'department' => ['label' => 'Department', 'type' => 'text'],
                        'semester' => ['label' => 'Semester', 'type' => 'text']
                    ];
                    
                    foreach ($fields as $name => $field): 
                    ?>
                    <div class="form-group">
                        <label class="form-label"><?php echo $field['label']; ?> *</label>
                        <input type="<?php echo $field['type']; ?>" name="<?php echo $name; ?>" class="form-input" required>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-actions">
                    <button type="submit" name="submit_course" class="btn btn-success"><i class="fas fa-save"></i> Add Course</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['show_instructors'])): ?>
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-chalkboard-teacher"></i> Course Instructors</span>
            </div>
            <div class="card-body">
                <?php if ($instructor_result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Course</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $instructor_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $r['faculty_name'] . ' (ID: ' . $r['faculty_id'] . ')'; ?></td>
                            <td><?php echo $r['course_name'] . ' (ID: ' . $r['course_id'] . ')'; ?></td>
                            <td><?php echo $r['class_day']; ?></td>
                            <td><?php echo $r['class_time']; ?></td>
                            <td><?php echo $r['room_number']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-times"></i>
                    <h3>No Instructors Assigned</h3>
                    <p>There are no instructors assigned to courses yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['show_add_instructor_form'])): ?>
        <div class="form-container">
            <h3 class="form-title"><i class="fas fa-user-plus"></i> Assign Instructor to Course</h3>
            <form method="post">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Faculty *</label>
                        <select name="faculty_id" class="form-select" required>
                            <?php while ($f = $faculty_result->fetch_assoc()): ?>
                            <option value="<?php echo $f['faculty_id']; ?>"><?php echo $f['name'] . ' (ID: ' . $f['faculty_id'] . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-select" required>
                            <?php while ($c = $course_result->fetch_assoc()): ?>
                            <option value="<?php echo $c['course_id']; ?>"><?php echo $c['course_name'] . ' (ID: ' . $c['course_id'] . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Class Day *</label>
                        <select name="class_day" class="form-select" required>
                            <?php foreach (['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day): ?>
                            <option><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Class Time *</label>
                        <select name="class_time" class="form-select" required>
                            <?php foreach (['8:30-9:30','9:35-10:35','10:40-11:40','11:45-12:45','1:10-2:10','2:15-3:15','4:20-5:20'] as $time): ?>
                            <option><?php echo $time; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Room Number *</label>
                        <input type="text" name="room_number" class="form-input" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_instructor" class="btn btn-success"><i class="fas fa-save"></i> Assign Instructor</button>
                    <button type="submit" name="show_instructors" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['show_enrollment_status'])): ?>
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-user-graduate"></i> Enrollment Status</span>
            </div>
            <div class="card-body">
                <div class="search-container">
                    <form method="post" class="search-box">
                        <input type="hidden" name="show_enrollment_status" value="1">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="enrollment_search" placeholder="Search by Course Name, Course ID, Student ID, or Semester" class="search-input" value="<?php echo htmlspecialchars($_POST['enrollment_search'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
                
                <?php if ($enrollment_result && $enrollment_result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Enrollment ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Faculty</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($enrol = $enrollment_result->fetch_assoc()): 
                            $status_class = 'status-' . $enrol['status'];
                        ?>
                        <tr>
                            <td><?php echo $enrol['enrollment_id']; ?></td>
                            <td><?php echo $enrol['first_name'] . ' ' . $enrol['last_name'] . ' (ID: ' . $enrol['student_id'] . ')'; ?></td>
                            <td><?php echo $enrol['course_name'] . ' (' . $enrol['course_code'] . ')'; ?></td>
                            <td><?php echo $enrol['faculty_name'] ?? 'Not assigned'; ?></td>
                            <td><?php echo $enrol['enrollment_date']; ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($enrol['status']); ?></span></td>
                            <td>
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrol['enrollment_id']; ?>">
                                    <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                                    <button type="submit" name="edit_enrollment" class="btn btn-success btn-sm"><i class="fas fa-edit"></i> Edit</button>
                                </form>
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $enrol['enrollment_id']; ?>">
                                    <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                                    <input type="hidden" name="action" value="drop">
                                    <button type="submit" name="enrol_action" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to drop this enrollment?')"><i class="fas fa-trash"></i> Drop</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Enrollments Found</h3>
                    <p>There are no enrollments to display at this time.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($edit_enrollment)): ?>
        <div class="form-container">
            <h3 class="form-title"><i class="fas fa-edit"></i> Edit Enrollment</h3>
            <form method="post">
                <input type="hidden" name="enrollment_id" value="<?php echo $edit_enrollment['enrollment_id']; ?>">
                <input type="hidden" name="search_code" value="<?php echo htmlspecialchars($search_code); ?>">
                <input type="hidden" name="action" value="edit">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Student</label>
                        <input type="text" class="form-input" value="<?php echo $edit_enrollment['first_name'] . ' ' . $edit_enrollment['last_name'] . ' (ID: ' . $edit_enrollment['student_id'] . ')'; ?>" disabled>
                        <input type="hidden" name="student_id" value="<?php echo $edit_enrollment['student_id']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Course *</label>
                        <select name="course_id" class="form-select" required>
                            <?php while ($course = $all_courses->fetch_assoc()): 
                                $selected = ($course['course_id'] == $edit_enrollment['course_id']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $course['course_id']; ?>" <?php echo $selected; ?>>
                                <?php echo $course['course_name'] . ' (' . $course['course_code'] . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Faculty *</label>
                        <select name="faculty_id" class="form-select" required>
                            <option value="">Select Faculty</option>
                            <?php while ($faculty = $all_faculty->fetch_assoc()): 
                                $selected = ($faculty['faculty_id'] == $edit_enrollment['faculty_id']) ? 'selected' : '';
                            ?>
                            <option value="<?php echo $faculty['faculty_id']; ?>" <?php echo $selected; ?>>
                                <?php echo $faculty['name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="enrolled" <?php echo ($edit_enrollment['status'] == 'enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                            <option value="completed" <?php echo ($edit_enrollment['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="dropped" <?php echo ($edit_enrollment['status'] == 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="enrol_action" class="btn btn-success"><i class="fas fa-save"></i> Update Enrollment</button>
                    <button type="submit" name="show_enrollment_status" class="btn btn-danger"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['show_suggested_courses'])): ?>
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-lightbulb"></i> Suggested Courses</span>
            </div>
            <div class="card-body">
                <!-- Search Bar for Student -->
                <div class="search-container">
                    <form method="post" class="search-box">
                        <input type="hidden" name="show_suggested_courses" value="1">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="student_search" placeholder="Search by Student ID" class="search-input" value="<?php echo htmlspecialchars($_POST['student_search'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>

                <?php if ($students_result && $students_result->num_rows > 0): ?>
                    <?php while ($stud = $students_result->fetch_assoc()): 
                        $student_id = $stud['st_id'];
                        $max_semester = $stud['max_semester'] ?? 1;
                        $suggested_semester = $max_semester + 1;

                        // Student info
                        $student_query = "SELECT first_name, last_name FROM student_registration WHERE id = $student_id";
                        $student_result = $mysqli->query($student_query);
                        $student = $student_result->fetch_assoc();

                        // Suggested courses
                        $suggested_query = "SELECT c.course_code, c.course_name, c.department, c.semester
                                            FROM course c
                                            WHERE c.semester = $suggested_semester
                                            ORDER BY c.course_code";
                        $suggested_result = $mysqli->query($suggested_query);
                    ?>
                    <div class="card" style="margin-top:20px;">
                        <div class="card-header">
                            <strong>Student:</strong> <?php echo $student['first_name'] . ' ' . $student['last_name']; ?> (ID: <?php echo $student_id; ?>)
                        </div>
                        <div class="card-body">
                            <p><strong>Current Progress:</strong> Completed up to Semester <?php echo $max_semester; ?></p>
                            <p><strong>Suggested Semester:</strong> Semester <?php echo $suggested_semester; ?></p>

                            <?php if ($suggested_result && $suggested_result->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Department</th>
                                        <th>Semester</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($course = $suggested_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $course['course_code']; ?></td>
                                        <td><?php echo $course['course_name']; ?></td>
                                        <td><?php echo $course['department']; ?></td>
                                        <td><?php echo $course['semester']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-lightbulb"></i>
                                <h3>No Suggested Courses</h3>
                                <p>No courses suggested for Semester <?php echo $suggested_semester; ?>.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Students Found</h3>
                    <p>No student records available in student_result table.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($_POST['show_completed_courses'])): ?>
        <div class="card">
            <div class="card-header">
                <span><i class="fas fa-check-circle"></i> Completed Courses</span>
            </div>
            <div class="card-body">
                <!-- Search Bar for Student -->
                <div class="search-container">
                    <form method="post" class="search-box">
                        <input type="hidden" name="show_completed_courses" value="1">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="completed_student_search" placeholder="Search by Student ID" class="search-input" value="<?php echo htmlspecialchars($_POST['completed_student_search'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>

                <?php if ($completed_students_result && $completed_students_result->num_rows > 0): ?>
                    <?php while ($student_row = $completed_students_result->fetch_assoc()): 
                        $student_id = $student_row['st_id'];
                        
                        // Student info
                        $student_query = "SELECT first_name, last_name, department FROM student_registration WHERE id = $student_id";
                        $student_result = $mysqli->query($student_query);
                        $student = $student_result->fetch_assoc();
                        
                        // Completed courses
                        $completed_query = "SELECT sr.course, c.course_name, c.credit_hours, sr.grade, sr.marks, sr.semister 
                                           FROM student_result sr
                                           JOIN course c ON sr.course = c.course_code
                                           WHERE sr.st_id = $student_id
                                           ORDER BY sr.semister";
                        $completed_result = $mysqli->query($completed_query);
                        
                        // Calculate CGPA
                        $cgpa_query = "SELECT AVG(marks) as avg_marks FROM student_result WHERE st_id = $student_id";
                        $cgpa_result = $mysqli->query($cgpa_query);
                        $cgpa_data = $cgpa_result->fetch_assoc();
                        $cgpa = number_format(($cgpa_data['avg_marks'] / 25), 2); // Simple conversion from marks to CGPA
                    ?>
                    <div class="card" style="margin-top:20px;">
                        <div class="card-header">
                            <strong>Student:</strong> <?php echo $student['first_name'] . ' ' . $student['last_name']; ?> (ID: <?php echo $student_id; ?>)
                        </div>
                        <div class="card-body">
                            <div class="student-info">
                                <div class="info-item">
                                    <span class="info-label">DEPARTMENT</span>
                                    <span class="info-value"><?php echo $student['department']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">CGPA</span>
                                    <span class="info-value"><?php echo $cgpa; ?></span>
                                </div>
                            </div>

                            <?php if ($completed_result && $completed_result->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Credit Hours</th>
                                        <th>Grade</th>
                                        <th>Marks</th>
                                        <th>Semester</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($course = $completed_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $course['course']; ?></td>
                                        <td><?php echo $course['course_name']; ?></td>
                                        <td><?php echo $course['credit_hours']; ?></td>
                                        <td><?php echo $course['grade']; ?></td>
                                        <td><?php echo $course['marks']; ?></td>
                                        <td><?php echo $course['semister']; ?></td>
                                        <td><span class="status-badge status-completed">Completed</span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-book"></i>
                                <h3>No Completed Courses</h3>
                                <p>This student hasn't completed any courses yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Students Found</h3>
                    <p>No student records available with completed courses.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$mysqli->close();
ob_end_flush();
?>
</body>
</html>