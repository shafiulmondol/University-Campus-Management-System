<?php
include 'config.php';
checkFacultyLogin();

$faculty_id = $_SESSION['faculty_id'];

// Get courses taught by this faculty
$courses_sql = "SELECT c.course_id, c.course_code, c.course_name 
                FROM course c
                JOIN course_instructor ci ON c.course_id = ci.course_id
                WHERE ci.faculty_id = ?";
$stmt = $mysqli->prepare($courses_sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$selected_course = null;
$students = [];
$attendance_date = date('Y-m-d');
$attendance_statuses = []; // To store existing attendance status

// If a course is selected, get the students
if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    
    // Get course details
    foreach ($courses as $course) {
        if ($course['course_id'] == $course_id) {
            $selected_course = $course;
            break;
        }
    }
    
    // Get students enrolled in this course
    $students_sql = "SELECT s.id, s.first_name, s.last_name 
                     FROM student_registration s
                     JOIN enrollments e ON s.id = e.student_id
                     WHERE e.course_id = ? AND e.faculty_id = ?";
    $stmt = $mysqli->prepare($students_sql);
    $stmt->bind_param("ii", $course_id, $faculty_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get existing attendance for selected date if provided
    if (isset($_GET['attendance_date']) && !empty($_GET['attendance_date'])) {
        $attendance_date = $_GET['attendance_date'];
    }
    
    // Precompute existing attendance status for each student
    if ($selected_course && count($students) > 0) {
        foreach ($students as $student) {
            $check_sql = "SELECT status FROM attendance WHERE student_id = ? AND course_code = ? AND date = ?";
            $check_stmt = $mysqli->prepare($check_sql);
            $check_stmt->bind_param("iss", $student['id'], $selected_course['course_id'], $attendance_date);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            $existing_status = '';
            if ($check_result->num_rows > 0) {
                $existing_status = $check_result->fetch_assoc()['status'];
            }
            
            $attendance_statuses[$student['id']] = $existing_status;
            $check_stmt->close();
        }
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_attendance'])) {
    $course_id = $_POST['course_id'];
    $attendance_date = $_POST['attendance_date'];
    
    foreach ($_POST['attendance'] as $student_id => $status) {
        // Check if attendance record already exists
        $check_sql = "SELECT id FROM attendance WHERE student_id = ? AND course_code = ? AND date = ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("iss", $student_id, $course_id, $attendance_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing record
            $update_sql = "UPDATE attendance SET status = ? WHERE student_id = ? AND course_code = ? AND date = ?";
            $update_stmt = $mysqli->prepare($update_sql);
            $update_stmt->bind_param("siss", $status, $student_id, $course_id, $attendance_date);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_sql = "INSERT INTO attendance (faculty_id, course_code, student_id, status, date) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_sql);
            $insert_stmt->bind_param("isiss", $faculty_id, $course_id, $student_id, $status, $attendance_date);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
    
    $success_message = "Attendance recorded successfully for " . date('F j, Y', strtotime($attendance_date));
    
    // Refresh attendance statuses after submission
    if ($selected_course && count($students) > 0) {
        $attendance_statuses = [];
        foreach ($students as $student) {
            $check_sql = "SELECT status FROM attendance WHERE student_id = ? AND course_code = ? AND date = ?";
            $check_stmt = $mysqli->prepare($check_sql);
            $check_stmt->bind_param("iss", $student['id'], $selected_course['course_id'], $attendance_date);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            $existing_status = '';
            if ($check_result->num_rows > 0) {
                $existing_status = $check_result->fetch_assoc()['status'];
            }
            
            $attendance_statuses[$student['id']] = $existing_status;
            $check_stmt->close();
        }
    }
}

// Close connection only after all database operations are complete
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... (keep all existing styles) ... */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        /* ================ Navbar Styles ============ */
        .navbar {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background: white;
            padding: 5px;
        }
        
        .logo h1 {
            font-size: 22px;
            font-weight: 600;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .nav-buttons button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .nav-buttons button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* ================ Main Layout ============ */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        /* ================ Sidebar Styles ============ */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 90;
            padding: 25px 0;
        }
        
        .sidebar-menu {
            list-style: none;
            width: 100%;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            color: #4e4376;
            text-decoration: none;
            padding: 12px 25px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .sidebar-menu a:hover, 
        .sidebar-menu a.active, 
        .sidebar-menu button:hover {
            background-color: #f0f5ff;
            color: #2b5876;
            border-right: 4px solid #2b5876;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        /* ================ Content Area ============ */
        .content-area {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            height: calc(100vh - 80px);
        }
        
        .page-header {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-radius: 10px;
        }
        
        .page-title {
            color: #2b5876;
            font-size: 30px;
            font-weight: 600;
            margin: 0;
        }
        
        /* ================ Attendance Cards ============ */
        .attendance-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .attendance-header {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .form-group select, .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group select:focus, .form-group input:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* ================ Students Table ============ */
        .students-table-container {
            margin-top: 30px;
            overflow-x: auto;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .students-table th {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 600;
        }
        
        .students-table td {
            padding: 16px;
            border-bottom: 1px solid #eee;
        }
        
        .students-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .students-table tr:hover {
            background-color: #f0f5ff;
        }
        
        .status-select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            width: 100%;
            background: white;
            cursor: pointer;
        }
        
        .status-select:focus {
            outline: none;
            border-color: #2b5876;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        /* ================ Notifications ============ */
        .notification {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .notification-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .notification-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* ================ Responsive Design ============ */
        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            
            .content-area {
                height: auto;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .students-table {
                font-size: 14px;
            }
            
            .students-table th, 
            .students-table td {
                padding: 12px 8px;
            }
        }
        
        /* ================ Empty State ============ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .empty-icon {
            font-size: 60px;
            color: #c3cfe2;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #2b5876;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 20px;
            font-size: 16px;
        }
        
        /* ================ Connection Status ============ */
        .connection-status {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .connected {
            background-color: #d4edda;
            color: #155724;
        }
        
        .disconnected {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center;">
                <span style="font-weight: bold; color: #2b5876;">SKST</span>
            </div>
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
            <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
            <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="faculty1.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="attendance.php" class="active"><i class="fas fa-user-check"></i> Attendance</a></li>
                <li><a href="materials.php"><i class="fas fa-file-alt"></i> Materials</a></li>
                <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
            </ul>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-user-check"></i> Attendance Management</h1>
            </div>
            
           
            
            <?php if (isset($success_message)): ?>
                <div class="notification notification-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Course Selection Card -->
            <div class="attendance-container">
                <h2 class="attendance-header"><i class="fas fa-book"></i> Select Course and Date</h2>
                
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="course">Course</label>
                            <select name="course_id" id="course" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['course_id']; ?>" 
                                        <?php if (isset($_GET['course_id']) && $_GET['course_id'] == $course['course_id']) echo 'selected'; ?>>
                                        <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="attendance_date">Date</label>
                            <input type="date" name="attendance_date" id="attendance_date" 
                                   value="<?php echo $attendance_date; ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-users"></i> Load Students
                    </button>
                </form>
            </div>
            
            <?php if ($selected_course): ?>
                <!-- Attendance Form Card -->
                <div class="attendance-container">
                    <h2 class="attendance-header">
                        <i class="fas fa-clipboard-check"></i> 
                        Mark Attendance for <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?>
                    </h2>
                    
                    <p style="margin-bottom: 20px; color: #666;">
                        <i class="fas fa-calendar"></i> 
                        Date: <?php echo date('F j, Y', strtotime($attendance_date)); ?>
                    </p>
                    
                    <?php if (count($students) > 0): ?>
                        <form method="POST" action="">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course['course_id']; ?>">
                            <input type="hidden" name="attendance_date" value="<?php echo $attendance_date; ?>">
                            
                            <div class="students-table-container">
                                <table class="students-table">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): 
                                            $existing_status = isset($attendance_statuses[$student['id']]) ? $attendance_statuses[$student['id']] : '';
                                        ?>
                                        <tr>
                                            <td><?php echo $student['id']; ?></td>
                                            <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                            <td>
                                                <select name="attendance[<?php echo $student['id']; ?>]" class="status-select" required>
                                                    <option value="Present" <?php if ($existing_status == 'Present') echo 'selected'; ?>>Present</option>
                                                    <option value="Absent" <?php if ($existing_status == 'Absent') echo 'selected'; ?>>Absent</option>
                                                    <option value="Late" <?php if ($existing_status == 'Late') echo 'selected'; ?>>Late</option>
                                                    <option value="Excused" <?php if ($existing_status == 'Excused') echo 'selected'; ?>>Excused</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <button type="submit" name="submit_attendance" class="btn-primary" style="margin-top: 25px;">
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-users-slash"></i></div>
                            <h3>No Students Enrolled</h3>
                            <p>There are no students enrolled in this course yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                    <h3>Select a Course</h3>
                    <p>Please select a course and date to view and manage attendance.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date to today if not already set
            const dateInput = document.getElementById('attendance_date');
            if (dateInput && !dateInput.value) {
                dateInput.value = new Date().toISOString().substr(0, 10);
            }
            
            // Add color coding to status selects
            const statusSelects = document.querySelectorAll('.status-select');
            statusSelects.forEach(select => {
                // Set initial color
                updateSelectColor(select);
                
                // Update color on change
                select.addEventListener('change', function() {
                    updateSelectColor(this);
                });
            });
            
            function updateSelectColor(select) {
                switch(select.value) {
                    case 'Present':
                        select.style.backgroundColor = '#d4edda';
                        select.style.color = '#155724';
                        select.style.borderColor = '#c3e6cb';
                        break;
                    case 'Absent':
                        select.style.backgroundColor = '#f8d7da';
                        select.style.color = '#721c24';
                        select.style.borderColor = '#f5c6cb';
                        break;
                    case 'Late':
                        select.style.backgroundColor = '#fff3cd';
                        select.style.color = '#856404';
                        select.style.borderColor = '#ffeeba';
                        break;
                    case 'Excused':
                        select.style.backgroundColor = '#e2e3e5';
                        select.style.color = '#383d41';
                        select.style.borderColor = '#d6d8db';
                        break;
                }
            }
        });
    </script>
</body>
</html>