<?php
require_once 'student.php';


// Check if student is logged in
if (!isset($_SESSION['student_data'])) {
    header("Location: studentlogin.php");
    exit();
}

// Get the student data from session
$stdata = $_SESSION['student_data'];

// Extract values for easier access
$id                = $stdata['id'] ?? '';
$full_name         = $stdata['full_name'] ?? '';
$email             = $stdata['email'] ?? '';
$phone             = $stdata['student_phone'] ?? '';
$father_name       = $stdata['father_name'] ?? '';
$mother_name       = $stdata['mother_name'] ?? '';
$guardian_phone    = $stdata['guardian_phone'] ?? '';
$student_phone     = $stdata['student_phone'] ?? '';
$password          = $stdata['password'] ?? '';
$last_exam         = $stdata['last_exam'] ?? '';
$board             = $stdata['board'] ?? '';
$other_board       = $stdata['other_board'] ?? '';
$year_of_passing   = $stdata['year_of_passing'] ?? '';
$institution_name  = $stdata['institution_name'] ?? '';
$result            = $stdata['result'] ?? '';
$subject_group     = $stdata['subject_group'] ?? '';
$gender            = $stdata['gender'] ?? '';
$nationality       = $stdata['nationality'] ?? '';
$religion          = $stdata['religion'] ?? '';
$present_address   = $stdata['present_address'] ?? '';
$permanent_address = $stdata['permanent_address'] ?? '';
$department        = $stdata['department'] ?? '';
$submission_date   = $stdata['submission_date'] ?? '';
$date_of_birth     = $stdata['date_of_birth'] ?? '';
$student_key       = $stdata['student_key'] ?? '';
$role              = $stdata['role'] ?? 'Student';
$login_time        = $stdata['login_time'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="../Design/buttom_bar.css">
    <link rel="stylesheet" href="../admin/admin.css">
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/logo.gif" alt="SKST Logo">
            <h1><i class="fas fa-university"></i> SKST University || Student Portal</h1>
        </div>
       
        <div class="nav-buttons">
            <button><i class="fas fa-home"></i><a style="text-decoration: none;color:aliceblue" href="administration.html">Home</a></button>
            <button><i class="fas fa-bell"></i> Notifications</button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
           <ul class="sidebar-menu">
                <form method="post" style="display: contents;">
                    <li><button type="submit" name="dashboard"><i class="fas fa-th-large"></i> Dashboard</button></li>
                    <li><button type="submit" name="student"><i class="fas fa-user-graduate"></i> Students</button></li>
                    <li><button type="submit" name="faculty"><i class="fas fa-chalkboard-teacher"></i> Faculty</button></li>
                    <li><button type="submit" name="staff"><i class="fas fa-users"></i> Staff</button></li>
                    <li><button type="submit" name="course"><i class="fas fa-book"></i> Courses</button></li>
                    <li><button type="submit" name="stats"><i class="fas fa-chart-bar"></i> University Statistics</button></li>
                    <li><button type="submit" name="notification"><i class="fas fa-bell"></i> Notifications</button></li>
                </form>
                <li><a href="studentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <?php
        if (isset($_POST['student'])) {
            echo "<div class='content-area'><h2>Student Section</h2></div>";
        } elseif (isset($_POST['stats'])) { ?>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-number"><?= student_count() ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="stat-number"><?= faculty_count() ?></div>
                    <div class="stat-label">Faculty Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-number">87</div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
            </div>
        <?php } else { ?>
            <!-- Profile Page -->
            <div class="content-area">
                <div class="page-header">
                    <h2 class="page-title">Student Profile</h2>
                    <button class="btn-edit"><i class="fas fa-edit"></i> Edit Profile</button>
                </div>
                
                <div class="profile-card">
                    <img src="../picture/profilepicture.png" alt="Student" class="profile-img">
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($full_name) ?></h2>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($phone) ?></p>
                        <p><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($role) ?></p>
                    </div>
                </div>
                
                <div class="info-cards">
                    <div class="detail-card">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        <div class="info-group">
                            <div class="info-label">Student ID</div>
                            <div class="info-value"><?= $id ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?= htmlspecialchars($full_name) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?= htmlspecialchars($email) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?= htmlspecialchars($phone) ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-key"></i> Security Information</h3>
                        <div class="info-group">
                            <div class="info-label">Student Key</div>
                            <div class="info-value"><?= htmlspecialchars($student_key) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Last Login</div>
                            <div class="info-value"><?= htmlspecialchars($login_time) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Account Status</div>
                            <div class="info-value"><span style="color: #28a745;">Active</span></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Two-Factor Authentication</div>
                            <div class="info-value">Enabled</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</body>
</html>
