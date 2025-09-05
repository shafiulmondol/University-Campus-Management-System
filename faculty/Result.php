<?php
include 'config.php';
checkFacultyLogin();

$faculty_id = $_SESSION['faculty_id'];
$message = '';
$error = '';

// Get faculty information
$faculty = getFacultyInfo($faculty_id, $mysqli);

// Get courses taught by this faculty
$courses = [];
$sql = "SELECT c.course_id, c.course_code, c.course_name 
        FROM course_instructor ci 
        JOIN course c ON ci.course_id = c.course_id 
        WHERE ci.faculty_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
$stmt->close();

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_result'])) {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $semester = $_POST['semester'];
        $grade = $_POST['grade'];
        
        // Check if result already exists
        $check_sql = "SELECT * FROM student_result WHERE st_id = ? AND course = ? AND semister = ?";
        $check_stmt = $mysqli->prepare($check_sql);
        $check_stmt->bind_param("isi", $student_id, $course_id, $semester);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Result for this student in the selected course and semester already exists.";
        } else {
            // Insert new result
            $insert_sql = "INSERT INTO student_result (st_id, semister, course, grade) VALUES (?, ?, ?, ?)";
            $insert_stmt = $mysqli->prepare($insert_sql);
            $insert_stmt->bind_param("iiss", $student_id, $semester, $course_id, $grade);
            
            if ($insert_stmt->execute()) {
                $message = "Result added successfully!";
            } else {
                $error = "Error adding result: " . $mysqli->error;
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } 
    elseif (isset($_POST['update_result'])) {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $semester = $_POST['semester'];
        $grade = $_POST['grade'];
        
        $update_sql = "UPDATE student_result SET grade = ? WHERE st_id = ? AND course = ? AND semister = ?";
        $update_stmt = $mysqli->prepare($update_sql);
        $update_stmt->bind_param("sisi", $grade, $student_id, $course_id, $semester);
        
        if ($update_stmt->execute()) {
            $message = "Result updated successfully!";
        } else {
            $error = "Error updating result: " . $mysqli->error;
        }
        $update_stmt->close();
    } 
    elseif (isset($_POST['delete_result'])) {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];
        $semester = $_POST['semester'];
        
        $delete_sql = "DELETE FROM student_result WHERE st_id = ? AND course = ? AND semister = ?";
        $delete_stmt = $mysqli->prepare($delete_sql);
        $delete_stmt->bind_param("isi", $student_id, $course_id, $semester);
        
        if ($delete_stmt->execute()) {
            $message = "Result deleted successfully!";
        } else {
            $error = "Error deleting result: " . $mysqli->error;
        }
        $delete_stmt->close();
    }
}

// Get results for the faculty's courses
$results = [];
if (!empty($courses)) {
    $course_ids = array_column($courses, 'course_id');
    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
    
    $sql = "SELECT sr.*, s.first_name, s.last_name, c.course_name 
            FROM student_result sr 
            JOIN student_registration s ON sr.st_id = s.id 
            JOIN course c ON sr.course = c.course_code 
            WHERE sr.course IN ($placeholders) 
            ORDER BY sr.semister, sr.course, sr.st_id";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($course_ids)), ...$course_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}

// Get students enrolled in faculty's courses
$enrolled_students = [];
if (!empty($courses)) {
    $course_ids = array_column($courses, 'course_id');
    $placeholders = implode(',', array_fill(0, count($course_ids), '?'));
    
    $sql = "SELECT e.student_id, s.first_name, s.last_name, c.course_id, c.course_code, c.course_name 
            FROM enrollments e 
            JOIN student_registration s ON e.student_id = s.id 
            JOIN course c ON e.course_id = c.course_id 
            WHERE e.course_id IN ($placeholders) 
            ORDER BY c.course_code, e.student_id";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($course_ids)), ...$course_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $enrolled_students[] = $row;
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
    <title>Result Management - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        
        .welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header h3 {
            display: flex;
            align-items: center;
        }
        
        .card-header h3 i {
            margin-right: 10px;
            background: #f0f5ff;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #4e4376;
        }
        
        .btn {
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        .btn-edit {
            background: #f39c12;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            color: #2b5876;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
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
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
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
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="welcome">
            <i class="fas fa-user"></i>
            Welcome, <?php echo htmlspecialchars($_SESSION['faculty_name']); ?>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='faculty1.php'">
                <i class="fas fa-home"></i> Dashboard
            </button>
            <button onclick="location.href='?logout=1'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="faculty1.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="courses.php">
                        <i class="fas fa-book"></i> Courses
                    </a>
                </li>
                <li>
                    <a href="schedule.php">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fas fa-user-check"></i> Attendance
                    </a>
                </li>
                <li>
                    <a href="Result.php" class="active">
                        <i class="fas fa-chart-bar"></i> Result
                    </a>
                </li>
                <li>
                    <a href="materials.php">
                        <i class="fas fa-file-alt"></i> Materials
                    </a>
                </li>
                <li>
                    <button onclick="location.href='?logout=1'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-chart-bar"></i> Result Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Result
                </button>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Student Results</h3>
                </div>
                
                <?php if (empty($results)): ?>
                    <p>No results found for your courses.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['st_id']); ?></td>
                                    <td><?php echo htmlspecialchars($result['first_name'] . ' ' . $result['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['course'] . ' - ' . $result['course_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['semister']); ?></td>
                                    <td><?php echo htmlspecialchars($result['grade']); ?></td>
                                    <td>
                                        <button class="btn btn-edit" onclick="openEditModal(
                                            '<?php echo $result['st_id']; ?>',
                                            '<?php echo $result['course']; ?>',
                                            '<?php echo $result['semister']; ?>',
                                            '<?php echo $result['grade']; ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-delete" onclick="openDeleteModal(
                                            '<?php echo $result['st_id']; ?>',
                                            '<?php echo $result['course']; ?>',
                                            '<?php echo $result['semister']; ?>'
                                        )">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Result Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2><i class="fas fa-plus"></i> Add Result</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="student_id">Student</label>
                    <select class="form-control" id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach ($enrolled_students as $student): ?>
                            <option value="<?php echo $student['student_id']; ?>">
                                <?php echo $student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['course_code'] . ')'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select class="form-control" id="course_id" name="course_id" required>
                        <option value="">Select Course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_code']; ?>">
                                <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <input type="number" class="form-control" id="semester" name="semester" min="1" max="12" required>
                </div>
                
                <div class="form-group">
                    <label for="grade">Grade</label>
                    <select class="form-control" id="grade" name="grade" required>
                        <option value="">Select Grade</option>
                        <option value="A+">A+</option>
                        <option value="A">A</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B">B</option>
                        <option value="B-">B-</option>
                        <option value="C+">C+</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="F">F</option>
                    </select>
                </div>
                
                <button type="submit" name="add_result" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Result
                </button>
            </form>
        </div>
    </div>
    
    <!-- Edit Result Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2><i class="fas fa-edit"></i> Edit Result</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_student_id" name="student_id">
                <input type="hidden" id="edit_course_id" name="course_id">
                <input type="hidden" id="edit_semester" name="semester">
                
                <div class="form-group">
                    <label>Student ID</label>
                    <p id="edit_student_display" class="form-control" style="background-color: #f8f9fa;"></p>
                </div>
                
                <div class="form-group">
                    <label>Course</label>
                    <p id="edit_course_display" class="form-control" style="background-color: #f8f9fa;"></p>
                </div>
                
                <div class="form-group">
                    <label>Semester</label>
                    <p id="edit_semester_display" class="form-control" style="background-color: #f8f9fa;"></p>
                </div>
                
                <div class="form-group">
                    <label for="edit_grade">Grade</label>
                    <select class="form-control" id="edit_grade" name="grade" required>
                        <option value="A+">A+</option>
                        <option value="A">A</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B">B</option>
                        <option value="B-">B-</option>
                        <option value="C+">C+</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="F">F</option>
                    </select>
                </div>
                
                <button type="submit" name="update_result" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Result
                </button>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteModal()">&times;</span>
            <h2><i class="fas fa-trash"></i> Delete Result</h2>
            <p>Are you sure you want to delete this result?</p>
            
            <div class="form-group">
                <label>Student ID</label>
                <p id="delete_student_display" class="form-control" style="background-color: #f8f9fa;"></p>
            </div>
            
            <div class="form-group">
                <label>Course</label>
                <p id="delete_course_display" class="form-control" style="background-color: #f8f9fa;"></p>
            </div>
            
            <div class="form-group">
                <label>Semester</label>
                <p id="delete_semester_display" class="form-control" style="background-color: #f8f9fa;"></p>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" id="delete_student_id" name="student_id">
                <input type="hidden" id="delete_course_id" name="course_id">
                <input type="hidden" id="delete_semester" name="semester">
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="delete_result" class="btn btn-delete">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-primary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(studentId, courseId, semester, grade) {
            document.getElementById('edit_student_id').value = studentId;
            document.getElementById('edit_course_id').value = courseId;
            document.getElementById('edit_semester').value = semester;
            document.getElementById('edit_grade').value = grade;
            
            document.getElementById('edit_student_display').textContent = studentId;
            document.getElementById('edit_course_display').textContent = courseId;
            document.getElementById('edit_semester_display').textContent = semester;
            
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        function openDeleteModal(studentId, courseId, semester) {
            document.getElementById('delete_student_id').value = studentId;
            document.getElementById('delete_course_id').value = courseId;
            document.getElementById('delete_semester').value = semester;
            
            document.getElementById('delete_student_display').textContent = studentId;
            document.getElementById('delete_course_display').textContent = courseId;
            document.getElementById('delete_semester_display').textContent = semester;
            
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Automatically populate course when student is selected in add form
        document.getElementById('student_id').addEventListener('change', function() {
            const studentId = this.value;
            const courseSelect = document.getElementById('course_id');
            
            // Reset course selection
            courseSelect.selectedIndex = 0;
            
            if (studentId) {
                // Find the student in enrolled students array
                const student = <?php echo json_encode($enrolled_students); ?>.find(s => s.student_id == studentId);
                
                if (student) {
                    // Set the course selection to match the student's course
                    for (let i = 0; i < courseSelect.options.length; i++) {
                        if (courseSelect.options[i].value === student.course_code) {
                            courseSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
        });
    
    </script>

</body>

</html>