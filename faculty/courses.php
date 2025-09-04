<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Courses - SKST University</title>
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='faculty1.php'">
                <i class="fas fa-user"></i> Profile
            </button>
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <button onclick="location.href='?logout=1'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="faculty1.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="faculty_courses.php" class="active">
                        <i class="fas fa-book"></i> Courses
                    </a>
                </li>
                <li>
                    <a href="faculty_schedule.php">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="faculty_students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fas fa-user-check"></i> Attendance
                    </a>
                </li>
                <li>
                    <a href="faculty_materials.php">
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
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-book"></i> My Courses</h1>
                <button class="btn-primary">
                    <i class="fas fa-plus"></i> Add New Course
                </button>
            </div>
            
            <!-- Courses Grid -->
            <div class="courses-container">
                <!-- Course Card 1 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-code">CSC 112</div>
                        <div class="course-name">Database Management Systems</div>
                        <div class="course-credits">4 Credits</div>
                    </div>
                    <div class="course-body">
                        <div class="course-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Enrolled Students: <span class="students-count">42</span></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>Semester: Fall 2025</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Schedule: Mon, Wed 10:00-11:30</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Room: CS-102</span>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn btn-attendance">
                                <i class="fas fa-clipboard-check"></i> Attendance
                            </button>
                            <button class="action-btn btn-materials">
                                <i class="fas fa-file-upload"></i> Materials
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course Card 2 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-code">CSC 222</div>
                        <div class="course-name">Computer Architecture</div>
                        <div class="course-credits">3 Credits</div>
                    </div>
                    <div class="course-body">
                        <div class="course-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Enrolled Students: <span class="students-count">38</span></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>Semester: Fall 2025</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Schedule: Tue, Thu 2:00-3:30</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Room: CS-201</span>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn btn-attendance">
                                <i class="fas fa-clipboard-check"></i> Attendance
                            </button>
                            <button class="action-btn btn-materials">
                                <i class="fas fa-file-upload"></i> Materials
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course Card 3 -->
                <div class="course-card">
                    <div class="course-header">
                        <div class="course-code">MATH 247</div>
                        <div class="course-name">Calculus</div>
                        <div class="course-credits">3 Credits</div>
                    </div>
                    <div class="course-body">
                        <div class="course-info">
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>Enrolled Students: <span class="students-count">55</span></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>Semester: Fall 2025</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Schedule: Mon, Wed, Fri 9:00-10:00</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Room: MATH-105</span>
                            </div>
                        </div>
                        <div class="course-actions">
                            <button class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="action-btn btn-attendance">
                                <i class="fas fa-clipboard-check"></i> Attendance
                            </button>
                            <button class="action-btn btn-materials">
                                <i class="fas fa-file-upload"></i> Materials
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Section -->
            <div class="stats-section">
                <h2 class="stats-header"><i class="fas fa-chart-bar"></i> Teaching Overview</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon icon-courses">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">3</div>
                            <div class="stat-label">Total Courses</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon icon-students">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">135</div>
                            <div class="stat-label">Total Students</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon icon-hours">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">10</div>
                            <div class="stat-label">Weekly Hours</div>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon icon-rating">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-value">4.8</div>
                            <div class="stat-label">Average Rating</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to clicked sidebar items
            const sidebarItems = document.querySelectorAll('.sidebar-menu a, .sidebar-menu button');
            sidebarItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebarItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Course action buttons functionality
            const viewButtons = document.querySelectorAll('.btn-view');
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.closest('.course-card').querySelector('.course-code').textContent;
                    alert(`View details for ${courseCode}`);
                });
            });
            
            const attendanceButtons = document.querySelectorAll('.btn-attendance');
            attendanceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.closest('.course-card').querySelector('.course-code').textContent;
                    alert(`Take attendance for ${courseCode}`);
                });
            });
            
            const materialsButtons = document.querySelectorAll('.btn-materials');
            materialsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const courseCode = this.closest('.course-card').querySelector('.course-code').textContent;
                    alert(`Upload materials for ${courseCode}`);
                });
            });
        });
    </script>
</body>
</html>