<?php
include 'config.php';
checkFacultyLogin();

$faculty_id = $_SESSION['faculty_id'];

// Query to get courses taught by the faculty
$sql = "SELECT c.course_id, c.course_code, c.course_name, c.credit_hours, 
               ci.class_day, ci.class_time, ci.room_number
        FROM course c
        JOIN course_instructor ci ON c.course_id = ci.course_id
        WHERE ci.faculty_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Query to get the number of students enrolled in each course
$enrollment_sql = "SELECT course_id, COUNT(*) as num_students 
                   FROM enrollments 
                   WHERE faculty_id = ? 
                   GROUP BY course_id";
$stmt = $mysqli->prepare($enrollment_sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();
$enrollments = [];
while ($row = $enrollment_result->fetch_assoc()) {
    $enrollments[$row['course_id']] = $row['num_students'];
}
$stmt->close();

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Courses - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS from the original courses.php file */
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
        
        .btn-primary {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        /* ================ Course Cards ============ */
        .courses-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .course-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .course-header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .course-code {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .course-name {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .course-credits {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .course-body {
            padding: 20px;
        }
        
        .course-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
        }
        
        .info-item i {
            color: #4e4376;
            width: 20px;
        }
        
        .info-item span {
            font-weight: 500;
        }
        
        .students-count {
            display: inline-block;
            background: #f0f5ff;
            color: #2b5876;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 5px;
        }
        
        .course-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .action-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: #e1f5fe;
            color: #0288d1;
        }
        
        .btn-view:hover {
            background: #b3e5fc;
        }
        
        .btn-attendance {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .btn-attendance:hover {
            background: #c8e6c9;
        }
        
        .btn-materials {
            background: #fbe9e7;
            color: #d84315;
        }
        
        .btn-materials:hover {
            background: #ffccbc;
        }
        
        /* ================ Statistics Section ============ */
        .stats-section {
            margin-top: 40px;
        }
        
        .stats-header {
            color: #2b5876;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .icon-courses {
            background: linear-gradient(135deg, #4e4376, #826ab4);
        }
        
        .icon-students {
            background: linear-gradient(135deg, #2b5876, #4e8fa8);
        }
        
        .icon-hours {
            background: linear-gradient(135deg, #4a90e2, #6bb9ff);
        }
        
        .icon-rating {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #2b5876;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
        }
        
        /* ================ Responsive Design ============ */
        @media (max-width: 1024px) {
            .courses-container {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        
        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                top: 0;
            }
            
            .content-area {
                height: auto;
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
            
            .courses-container {
                grid-template-columns: 1fr;
            }
            
            .course-actions {
                flex-direction: column;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
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
        
        /* Connection status */
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            font-size: 24px;
            color: #2b5876;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }
        
        /* ... (rest of the CSS from the original courses.php file) ... */
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
                <li><a href="courses.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-user-check"></i> Attendance</a></li>
                <li><a href="materials.php"><i class="fas fa-file-alt"></i> Materials</a></li>
                <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
            </ul>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-book"></i> My Courses</h1>
            </div>
            
            <!-- Database Connection Status -->
            <div class="connection-status connected">
                <i class="fas fa-check-circle"></i> 
                Connected to database: skst_university
            </div>
            
            <!-- Courses Grid -->
            <div class="courses-container">
                <?php if (count($courses) > 0): ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                                <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                <div class="course-credits"><?php echo htmlspecialchars($course['credit_hours']); ?> Credits</div>
                            </div>
                            <div class="course-body">
                                <div class="course-info">
                                    <div class="info-item"><i class="fas fa-users"></i>
                                        <span>Enrolled Students: <span class="students-count">
                                            <?php echo isset($enrollments[$course['course_id']]) ? $enrollments[$course['course_id']] : 0; ?>
                                        </span></span>
                                    </div>
                                    <div class="info-item"><i class="fas fa-calendar"></i>
                                        <span>Class Day: <?php echo htmlspecialchars($course['class_day']); ?></span>
                                    </div>
                                    <div class="info-item"><i class="fas fa-clock"></i>
                                        <span>Schedule: <?php echo htmlspecialchars($course['class_time']); ?></span>
                                    </div>
                                    <div class="info-item"><i class="fas fa-map-marker-alt"></i>
                                        <span>Room: <?php echo htmlspecialchars($course['room_number']); ?></span>
                                    </div>
                                </div>
                                <div class="course-actions">
                                    <button class="action-btn btn-view" data-course="<?php echo htmlspecialchars($course['course_code']); ?>"><i class="fas fa-eye"></i> View</button>
                                    <a href="attendance.php?course_id=<?php echo $course['course_id']; ?>&attendance_date=<?php echo date('Y-m-d'); ?>" 
                                     class="action-btn btn-attendance">
                                     <i class="fas fa-clipboard-check"></i> Attendance
                                    </a>

                                    <button class="action-btn btn-materials" data-course="<?php echo htmlspecialchars($course['course_code']); ?>"><i class="fas fa-file-upload"></i> Materials</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-book-open"></i></div>
                        <h3>No Courses Assigned</h3>
                        <p>You are not currently assigned to any courses.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistics Section -->
            <div class="stats-section">
                <h2 class="stats-header"><i class="fas fa-chart-bar"></i> Teaching Overview</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon icon-courses"><i class="fas fa-book"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo count($courses); ?></div>
                            <div class="stat-label">Total Courses</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-students"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo array_sum($enrollments); ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-hours"><i class="fas fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?php echo count($courses) * 3; // Assuming 3 hours per course ?></div>
                            <div class="stat-label">Weekly Hours</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-rating"><i class="fas fa-star"></i></div>
                        <div class="stat-info">
                            <div class="stat-value">4.8</div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Course Details -->
    <div class="modal" id="courseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Course Details</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // JavaScript from the original courses.php file
        // This would be handled by PHP in a real environment
        console.log("Faculty ID: <?php echo $faculty_id; ?>");
        console.log("Database: skst_university");
        
        // Add event listeners to buttons
        document.addEventListener('DOMContentLoaded', function() {
            // View buttons
            const viewButtons = document.querySelectorAll('.btn-view');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.getAttribute('data-course');
                    showCourseDetails(courseCode);
                });
            });
            
            // Attendance buttons
            const attendanceButtons = document.querySelectorAll('.btn-attendance');
            attendanceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.getAttribute('data-course');
                    showAttendance(courseCode);
                });
            });
            
            // Materials buttons
            const materialsButtons = document.querySelectorAll('.btn-materials');
            materialsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.getAttribute('data-course');
                    showMaterials(courseCode);
                });
            });
            
            // Close modal button
            document.querySelector('.close-modal').addEventListener('click', function() {
                document.getElementById('courseModal').style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('courseModal');
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
        
        // Function to show course details
        function showCourseDetails(courseCode) {
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = `${courseCode} - Details`;
            
            // In a real application, this would fetch data from the server
            modalBody.innerHTML = `
                <h3>Course Information</h3>
                <p>Detailed information about ${courseCode} would appear here.</p>
                <div class="course-info">
                    <p><strong>Instructor:</strong> <?php echo htmlspecialchars($_SESSION['faculty_name']); ?></p>
                    <p><strong>Course Code:</strong> ${courseCode}</p>
                </div>
                <button class="btn-primary" style="margin-top: 15px;">View Student Roster</button>
            `;
            
            document.getElementById('courseModal').style.display = 'flex';
        }
        
        // Function to show attendance
        function showAttendance(courseCode) {
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = `${courseCode} - Attendance`;
            
            // In a real application, this would fetch data from the server
            modalBody.innerHTML = `
                <h3>Attendance Management</h3>
                <p>Manage attendance for ${courseCode}</p>
                <div style="margin: 20px 0;">
                    <label for="attendanceDate">Select Date:</label>
                    <input type="date" id="attendanceDate" style="padding: 8px; margin-left: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f5f7fa;">
                                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Student ID</th>
                                <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Name</th>
                                <th style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;">23303101</td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;">Rahim Ahmed</td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">
                                    <select style="padding: 5px;">
                                        <option>Present</option>
                                        <option>Absent</option>
                                        <option>Late</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;">23303102</td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd;">Fatima Khan</td>
                                <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">
                                    <select style="padding: 5px;">
                                        <option>Present</option>
                                        <option>Absent</option>
                                        <option>Late</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button class="btn-primary" style="margin-top: 15px;">Save Attendance</button>
            `;
            
            document.getElementById('courseModal').style.display = 'flex';
        }
        
        // Function to show materials
        function showMaterials(courseCode) {
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = `${courseCode} - Course Materials`;
            
            // In a real application, this would fetch data from the server
            modalBody.innerHTML = `
                <h3>Course Materials</h3>
                <p>Manage materials for ${courseCode}</p>
                
                <div style="margin: 20px 0;">
                    <h4>Upload New Material</h4>
                    <input type="file" id="materialFile" style="margin: 10px 0;">
                    <input type="text" placeholder="Title" style="padding: 8px; width: 100%; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <textarea placeholder="Description" style="padding: 8px; width: 100%; height: 80px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                    <button class="btn-primary">Upload Material</button>
                </div>
                
                <div style="max-height: 300px; overflow-y: auto;">
                    <h4>Existing Materials</h4>
                    <div style="border: 1px solid #eee; border-radius: 5px; padding: 15px; margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h5 style="margin: 0;">Lecture 1 Slides</h5>
                                <p style="margin: 5px 0; color: #666;">Introduction to Database Systems</p>
                            </div>
                            <div>
                                <button class="action-btn btn-view" style="margin-right: 5px;">Download</button>
                                <button class="action-btn btn-materials">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('courseModal').style.display = 'flex';
        }
        
        // Function to show toast notifications
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            
            setTimeout(function() {
                toast.style.display = 'none';
            }, 3000);
       
        }
    
    </script>

</body>

</html>