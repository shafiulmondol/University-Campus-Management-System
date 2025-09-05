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
        $cgpa = $_POST['cgpa'][$index];
        $sgpa = $_POST['sgpa'][$index];
        
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
                           SET grade = ?, marks = ?, cgpa = ?, sgpa = ?
                           WHERE st_id = ? AND course = ? AND semister = ?";
            $stmt = $mysqli->prepare($update_sql);
            $stmt->bind_param("sdddiss", $grade, $marks, $cgpa, $sgpa, $student_id, $course_code, $semester);
        } else {
            // Insert new result
            $insert_sql = "INSERT INTO student_result (st_id, semister, course, grade, marks, cgpa, sgpa)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($insert_sql);
            $stmt->bind_param("iissddd", $student_id, $semester, $course_code, $grade, $marks, $cgpa, $sgpa);
        }
        
        if ($stmt->execute()) {
            $success_message = "Results saved successfully!";
        } else {
            $error = "Failed to save results: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // Refresh the page to show updated results
    header("Location: result.php?course_id=" . $course_id);
    exit();
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
            
            .results-table {
                display: block;
                overflow-x: auto;
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
            <!-- Results Management Card -->
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
                                <th>CGPA</th>
                                <th>SGPA</th>
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
                                           value="<?php echo isset($result['marks']) ? $result['marks'] : ''; ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="grade[]" 
                                           value="<?php echo isset($result['grade']) ? $result['grade'] : ''; ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="cgpa[]" step="0.01" min="0" max="4.00" 
                                           value="<?php echo isset($result['cgpa']) ? $result['cgpa'] : ''; ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="sgpa[]" step="0.01" min="0" max="4.00" 
                                           value="<?php echo isset($result['sgpa']) ? $result['sgpa'] : ''; ?>" required>
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
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-calculate grade based on marks
        document.addEventListener('input', function(e) {
            if (e.target.name === 'marks[]') {
                const marks = parseFloat(e.target.value);
                const gradeInput = e.target.parentElement.nextElementSibling.querySelector('input[name="grade[]"]');
                
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
        });
        
    </script>

</body>

</html>