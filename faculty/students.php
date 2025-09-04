//php file
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
    $students_sql = "SELECT s.id, s.first_name, s.last_name, s.email, s.student_phone 
                     FROM student_registration s
                     JOIN enrollments e ON s.id = e.student_id
                     WHERE e.course_id = ? AND e.faculty_id = ?";
    $stmt = $mysqli->prepare($students_sql);
    $stmt->bind_param("ii", $course_id, $faculty_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse the CSS from faculty1.php with some additions */
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
        
        /* ... (other styles from faculty1.php) ... */
        
        .students-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .students-table th, .students-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .students-table th {
            background-color: #f0f5ff;
            color: #2b5876;
        }
        
        .btn-load {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .student-contact {
            display: flex;
            gap: 10px;
        }
        
        .contact-btn {
            background: #f0f5ff;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #2b5876;
            transition: all 0.3s;
        }
        
        .contact-btn:hover {
            background: #2b5876;
            color: white;
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
                <li><a href="students.php" class="active"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-user-check"></i> Attendance</a></li>
                <li><a href="materials.php"><i class="fas fa-file-alt"></i> Materials</a></li>
                <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
            </ul>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-users"></i> Student Management</h1>
            </div>
            
            <div class="students-container">
                <h2>Select Course to View Students</h2>
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
                    
                    <button type="submit" class="btn-load">Load Students</button>
                </form>
            </div>
            
            <?php if ($selected_course): ?>
            <div class="students-container">
                <h2>Students Enrolled in <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?></h2>
                <p>Total Students: <?php echo count($students); ?></p>
                
                <?php if (count($students) > 0): ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['student_phone']; ?></td>
                            <td>
                                <div class="student-contact">
                                    <a href="mailto:<?php echo $student['email']; ?>" class="contact-btn" title="Send Email">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="tel:<?php echo $student['student_phone']; ?>" class="contact-btn" title="Call Student">
                                        <i class="fas fa-phone"></i>
                                    </a>
                                    <button class="contact-btn" title="View Profile" onclick="viewStudentProfile(<?php echo $student['id']; ?>)">
                                        <i class="fas fa-user"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No students enrolled in this course.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewStudentProfile(studentId) {
            alert('Viewing profile for student ID: ' + studentId);
            // In a real application, this would redirect to a student profile page
            // or show a modal with student details
        }
    </script>
</body>
</html>