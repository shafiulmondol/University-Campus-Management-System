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
$results = [];
$view_type = isset($_GET['view']) ? $_GET['view'] : 'subject'; // 'subject' or 'all'

// If a course is selected, get the enrolled students and their results
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
    $students_sql = "SELECT sr.id, sr.first_name, sr.last_name 
                     FROM student_registration sr
                     JOIN enrollments e ON sr.id = e.student_id
                     WHERE e.course_id = ? AND e.faculty_id = ?
                     ORDER BY sr.first_name, sr.last_name";
    $stmt = $mysqli->prepare($students_sql);
    $stmt->bind_param("ii", $course_id, $faculty_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Get existing results for these students in this course
    $results_sql = "SELECT st_id, semister, course, grade, marks, cgpa, sgpa 
                    FROM student_result 
                    WHERE course = ?";
    $stmt = $mysqli->prepare($results_sql);
    $course_code = $selected_course['course_code'];
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $results_result = $stmt->get_result();
    
    // Create an associative array for easy access to results
    while ($row = $results_result->fetch_assoc()) {
        $results[$row['st_id']] = $row;
    }
    $stmt->close();
}

// Handle result submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_results'])) {
    $course_id = $_POST['course_id'];
    $course_code = $_POST['course_code'];
    $semester = $_POST['semester'];
    
    // Process each student's result
    foreach ($_POST['student_id'] as $index => $student_id) {
        $marks = $_POST['marks'][$index];
        $grade = $_POST['grade'][$index];
        
        // Check if result already exists for this student, course, and semester
        $check_sql = "SELECT * FROM student_result WHERE st_id = ? AND course = ? AND semister = ?";
        $stmt = $mysqli->prepare($check_sql);
        $stmt->bind_param("isi", $student_id, $course_code, $semester);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        
        if ($exists) {
            // Update existing result
            $update_sql = "UPDATE student_result 
                           SET grade = ?, marks = ?
                           WHERE st_id = ? AND course = ? AND semister = ?";
            $stmt = $mysqli->prepare($update_sql);
            $stmt->bind_param("sdisi", $grade, $marks, $student_id, $course_code, $semester);
        } else {
            // Insert new result
            $insert_sql = "INSERT INTO student_result (st_id, semister, course, grade, marks)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($insert_sql);
            $stmt->bind_param("iissd", $student_id, $semester, $course_code, $grade, $marks);
        }
        
        if ($stmt->execute()) {
            // Update SGPA and CGPA for this student
            updateStudentGPA($student_id, $semester);
            $success_message = "Results saved successfully!";
        } else {
            $error = "Failed to save results: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Refresh the page to show updated results
    header("Location: result.php?course_id=" . $course_id . "&view=" . $view_type);
    exit();
}

// Function to calculate grade point based on marks
function calculateGradePoint($marks) {
    if ($marks >= 80) return 4.00;
    if ($marks >= 75) return 3.75;
    if ($marks >= 70) return 3.50;
    if ($marks >= 65) return 3.25;
    if ($marks >= 60) return 3.00;
    if ($marks >= 55) return 2.75;
    if ($marks >= 50) return 2.50;
    if ($marks >= 45) return 2.25;
    if ($marks >= 40) return 2.00;
    return 0.00;
}

// Function to update student's SGPA and CGPA
function updateStudentGPA($student_id, $semester) {
    global $mysqli;
    
    // Calculate SGPA for the semester
    $sgpa_sql = "SELECT marks FROM student_result WHERE st_id = ? AND semister = ?";
    $stmt = $mysqli->prepare($sgpa_sql);
    $stmt->bind_param("ii", $student_id, $semester);
    $stmt->execute();
    $sgpa_result = $stmt->get_result();
    
    $total_grade_points = 0;
    $count = 0;
    
    while ($row = $sgpa_result->fetch_assoc()) {
        $grade_point = calculateGradePoint($row['marks']);
        $total_grade_points += $grade_point;
        $count++;
    }
    
    $sgpa = $count > 0 ? $total_grade_points / $count : 0;
    $stmt->close();
    
    // Calculate CGPA for the student
    $cgpa_sql = "SELECT marks FROM student_result WHERE st_id = ?";
    $stmt = $mysqli->prepare($cgpa_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $cgpa_result = $stmt->get_result();
    
    $total_grade_points_all = 0;
    $count_all = 0;
    
    while ($row = $cgpa_result->fetch_assoc()) {
        $grade_point = calculateGradePoint($row['marks']);
        $total_grade_points_all += $grade_point;
        $count_all++;
    }
    
    $cgpa = $count_all > 0 ? $total_grade_points_all / $count_all : 0;
    $stmt->close();
    
    // Update all records for this student with the new SGPA and CGPA
    $update_sql = "UPDATE student_result 
                   SET sgpa = ?, cgpa = ?
                   WHERE st_id = ?";
    $stmt = $mysqli->prepare($update_sql);
    $stmt->bind_param("ddi", $sgpa, $cgpa, $student_id);
    $stmt->execute();
    $stmt->close();
}

// Get all results for a student (for the "View All Results" option)
$all_student_results = [];
if ($view_type == 'all' && isset($_GET['student_id'])) {
    $student_id = $_GET['student_id'];
    $all_results_sql = "SELECT sr.semister, sr.course, sr.grade, sr.marks, sr.sgpa, sr.cgpa, c.course_name
                        FROM student_result sr
                        JOIN course c ON sr.course = c.course_code
                        WHERE sr.st_id = ?
                        ORDER BY sr.semister, sr.course";
    $stmt = $mysqli->prepare($all_results_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $all_results_result = $stmt->get_result();
    
    // Organize results by semester
    while ($row = $all_results_result->fetch_assoc()) {
        $all_student_results[$row['semister']][] = $row;
    }
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f9c74f;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px 0;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary);
            font-size: 18px;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 15px;
            gap: 12px;
        }
        
        .sidebar-menu a:hover, .sidebar-menu button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 30px;
        }
        
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid var(--success);
        }
        
        .sidebar-menu i {
            width: 20px;
            font-size: 16px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Navigation */
        .top-navbar {
            background: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .nav-buttons button {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: var(--light);
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-buttons button:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }
        
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }
        
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group select, .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: var(--white);
        }
        
        .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }
        
        /* View Toggle Buttons */
        .view-toggle {
            display: flex;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--light);
        }
        
        .view-toggle button {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .view-toggle button.active {
            background: var(--primary);
            color: white;
        }
        
        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        
        .results-table th, .results-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .results-table th {
            background-color: var(--light);
            font-weight: 600;
            color: var(--dark);
        }
        
        .results-table tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .results-table input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 14px;
        }
        
        .results-table input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.15);
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .message.success {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .message.error {
            background: rgba(249, 65, 68, 0.15);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        /* Student Selector for All Results */
        .student-selector {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .student-selector select {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
        }
        
        /* Semester Results */
        .semester-results {
            margin-bottom: 30px;
        }
        
        .semester-header {
            background: var(--primary);
            color: white;
            padding: 12px 15px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
        
        .semester-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .semester-table th, .semester-table td {
            padding: 12px 15px;
            border: 1px solid var(--light-gray);
        }
        
        .semester-table th {
            background: var(--light);
        }
        
        .gpa-summary {
            margin-top: 20px;
            padding: 15px;
            background: var(--light);
            border-radius: 8px;
            display: flex;
            gap: 20px;
        }
        
        .gpa-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }
        
        .gpa-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu a, .sidebar-menu button {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar-menu a:hover, .sidebar-menu button:hover {
                padding-left: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .nav-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .results-table, .semester-table {
                display: block;
                overflow-x: auto;
            }
            
            .gpa-summary {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">SKST</div>
                <div class="logo-text">SKST Faculty</div>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="faculty1.php"><i class="fas fa-user"></i> <span>Profile</span></a></li>
            <li><a href="courses.php"><i class="fas fa-book"></i> <span>Courses</span></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a></li>
            <li><a href="students.php"><i class="fas fa-users"></i> <span>Students</span></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
            <li><a href="materials.php"><i class="fas fa-file-alt"></i> <span>Materials</span></a></li>
            <li><a href="result.php" class="active"><i class="fas fa-chart-bar"></i> <span>Results</span></a></li>
            <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <div class="top-navbar">
            <h1 class="page-title"><i class="fas fa-chart-bar"></i> Student Results</h1>
            <div class="nav-buttons">
                <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
                <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
                <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php if (isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Course Selection Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-book"></i> Select Course to Manage Results</h2>
                </div>
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="course">Course:</label>
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
                    
                    <button type="submit" class="btn-primary"><i class="fas fa-folder-open"></i> Load Students</button>
                </form>
            </div>
            
            <?php if ($selected_course): ?>
            <!-- View Toggle Buttons -->
            <div class="view-toggle">
                <button class="<?php echo $view_type == 'subject' ? 'active' : ''; ?>" 
                        onclick="changeView('subject')">
                    <i class="fas fa-book"></i> Subject Results
                </button>
                <button class="<?php echo $view_type == 'all' ? 'active' : ''; ?>" 
                        onclick="changeView('all')">
                    <i class="fas fa-graduation-cap"></i> All Results
                </button>
            </div>
            
            <!-- Subject Results View -->
            <div id="subjectView" style="display: <?php echo $view_type == 'subject' ? 'block' : 'none'; ?>;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-chart-bar"></i> Results for <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?></h2>
                        <p>Total Students: <span class="badge"><?php echo count($students); ?></span></p>
                    </div>
                    
                    <?php if (count($students) > 0): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="course_id" value="<?php echo $selected_course['course_id']; ?>">
                        <input type="hidden" name="course_code" value="<?php echo $selected_course['course_code']; ?>">
                        
                        <div class="form-group">
                            <label for="semester">Semester:</label>
                            <input type="number" name="semester" id="semester" required min="1" max="12" 
                                   value="<?php echo isset($results[key($results)]['semister']) ? $results[key($results)]['semister'] : ''; ?>">
                        </div>
                        
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $student_id = $student['id'];
                                    $result = isset($results[$student_id]) ? $results[$student_id] : [];
                                ?>
                                <tr>
                                    <td><?php echo $student_id; ?>
                                        <input type="hidden" name="student_id[]" value="<?php echo $student_id; ?>">
                                    </td>
                                    <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                                    <td>
                                        <input type="number" name="marks[]" step="0.01" min="0" max="100" 
                                               value="<?php echo isset($result['marks']) ? $result['marks'] : ''; ?>" required
                                               onchange="calculateGrade(this)">
                                    </td>
                                    <td>
                                        <input type="text" name="grade[]" readonly
                                               value="<?php echo isset($result['grade']) ? $result['grade'] : ''; ?>" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-primary" 
                                                onclick="viewAllResults(<?php echo $student_id; ?>)">
                                            <i class="fas fa-eye"></i> View All
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <button type="submit" name="submit_results" class="btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-save"></i> Save Results
                        </button>
                    </form>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <h3>No Students Enrolled</h3>
                            <p>There are no students enrolled in this course yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- All Results View -->
            <div id="allView" style="display: <?php echo $view_type == 'all' ? 'block' : 'none'; ?>;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><i class="fas fa-graduation-cap"></i> All Results</h2>
                    </div>
                    
                    <div class="student-selector">
                        <label for="studentSelect">Select Student:</label>
                        <select id="studentSelect" onchange="changeStudent(this.value)">
                            <option value="">-- Select Student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>" 
                                    <?php if (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) echo 'selected'; ?>>
                                    <?php echo $student['id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if (!empty($all_student_results) && isset($_GET['student_id'])): 
                        $student_id = $_GET['student_id'];
                        $student_name = '';
                        foreach ($students as $s) {
                            if ($s['id'] == $student_id) {
                                $student_name = $s['first_name'] . ' ' . $s['last_name'];
                                break;
                            }
                        }
                    ?>
                        <h3>Results for: <?php echo $student_name . ' (' . $student_id . ')'; ?></h3>
                        
                        <?php 
                        $overall_cgpa = 0;
                        $semester_count = 0;
                        ?>
                        
                        <?php foreach ($all_student_results as $semester => $courses): 
                            $semester_gpa = 0;
                            $total_credits = 0;
                        ?>
                            <div class="semester-results">
                                <h4 class="semester-header">Semester <?php echo $semester; ?></h4>
                                <table class="semester-table">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                            <th>Marks</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $course): 
                                            // For demonstration, we'll use a simple GPA calculation
                                            // In a real system, you would use credit hours and proper GPA calculation
                                            $course_gpa = calculateGradePoint($course['marks']);
                                            $semester_gpa += $course_gpa;
                                            $total_credits += 1; // Assuming each course has 1 credit for simplicity
                                        ?>
                                            <tr>
                                                <td><?php echo $course['course']; ?></td>
                                                <td><?php echo $course['course_name']; ?></td>
                                                <td><?php echo $course['marks']; ?></td>
                                                <td><?php echo $course['grade']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <?php 
                                $semester_gpa = $total_credits > 0 ? $semester_gpa / $total_credits : 0;
                                $overall_cgpa += $semester_gpa;
                                $semester_count++;
                                ?>
                                
                                <div class="gpa-summary">
                                    <div class="gpa-item">
                                        <div>Semester GPA</div>
                                        <div class="gpa-value"><?php echo number_format($semester_gpa, 2); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        $overall_cgpa = $semester_count > 0 ? $overall_cgpa / $semester_count : 0;
                        ?>
                        
                        <div class="gpa-summary">
                            <div class="gpa-item">
                                <div>Overall CGPA</div>
                                <div class="gpa-value"><?php echo number_format($overall_cgpa, 2); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-graduate"></i>
                            <h3>No Student Selected</h3>
                            <p>Please select a student to view their complete results.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-calculate grade based on marks
        function calculateGrade(input) {
            const marks = parseFloat(input.value);
            const gradeInput = input.parentElement.nextElementSibling.querySelector('input[name="grade[]"]');
            
            if (!isNaN(marks)) {
                if (marks >= 80) gradeInput.value = 'A+';
                else if (marks >= 75) gradeInput.value = 'A';
                else if (marks >= 70) gradeInput.value = 'A-';
                else if (marks >= 65) gradeInput.value = 'B+';
                else if (marks >= 60) gradeInput.value = 'B';
                else if (marks >= 55) gradeInput.value = 'B-';
                else if (marks >= 50) gradeInput.value = 'C+';
                else if (marks >= 45) gradeInput.value = 'C';
                else if (marks >= 40) gradeInput.value = 'D';
                else gradeInput.value = 'F';
            }
        }
        
        // Change view between subject and all results
        function changeView(viewType) {
            const url = new URL(window.location.href);
            url.searchParams.set('view', viewType);
            window.location.href = url.toString();
        }
        
        // View all results for a specific student
        function viewAllResults(studentId) {
            const url = new URL(window.location.href);
            url.searchParams.set('view', 'all');
            url.searchParams.set('student_id', studentId);
            window.location.href = url.toString();
        }
        
        // Change student in all results view
        function changeStudent(studentId) {
            const url = new URL(window.location.href);
            url.searchParams.set('student_id', studentId);
            window.location.href = url.toString();
        }
    </script>

</body>

</html>