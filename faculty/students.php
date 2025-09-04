<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
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
        
        .form-group select {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: var(--white);
        }
        
        .form-group select:focus {
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
        
        /* Students Table */
        .students-table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .students-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .students-table th {
            background-color: var(--primary);
            color: white;
            padding: 16px;
            text-align: left;
            font-weight: 500;
        }
        
        .students-table td {
            padding: 16px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .students-table tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .student-contact {
            display: flex;
            gap: 8px;
        }
        
        .contact-btn {
            background: var(--light);
            border: none;
            border-radius: 8px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary);
            transition: all 0.3s;
        }
        
        .contact-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--light-gray);
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
            <li><a href="students.php" class="active"><i class="fas fa-users"></i> <span>Students</span></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
            <li><a href="materials.php"><i class="fas fa-file-alt"></i> <span>Materials</span></a></li>
            <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <div class="top-navbar">
            <h1 class="page-title"><i class="fas fa-users"></i> Student Management</h1>
            <div class="nav-buttons">
                <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
                <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
                <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <!-- Course Selection Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-book"></i> Select Course to View Students</h2>
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
                    
                    <button type="submit" class="btn-primary"><i class="fas fa-users"></i> Load Students</button>
                </form>
            </div>
            
           <?php if ($selected_course): ?>
            <!-- Students List Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-user-graduate"></i> Students Enrolled in <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?></h2>
                    <p>Total Students: <span class="badge"><?php echo count($students); ?></span></p>
                </div>
                
                <?php if (count($students) > 0): ?>
                <div class="students-table-container">
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
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h3>No Students Enrolled</h3>
                        <p>There are no students enrolled in this course yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function viewStudentProfile(studentId) {
            // Create a modal for student profile
            const modal = document.createElement('div');
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            modal.style.display = 'flex';
            modal.style.justifyContent = 'center';
            modal.style.alignItems = 'center';
            modal.style.zIndex = '2000';
            
            modal.innerHTML = `
                <div style="background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px;">
                    <h2 style="margin-bottom: 20px; color: #4361ee;">Student Profile</h2>
                    <p>Viewing profile for student ID: ${studentId}</p>
                    <p style="margin-top: 20px; color: #6c757d;">This feature would show detailed student information in a real application.</p>
                    <div style="margin-top: 25px; text-align: right;">
                        <button onclick="this.closest('div').style.display='none'" style="padding: 10px 20px; background: #e9ecef; border: none; border-radius: 8px; cursor: pointer;">Close</button>
                    </div>
                </div>
            `;
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
            
            document.body.appendChild(modal);
        }
    </script>
</body>
</html>