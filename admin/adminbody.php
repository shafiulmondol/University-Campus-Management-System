<?php
require_once 'admin.php';
// adminbody.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_data'])) {
    header("Location: adminlogin.php");
    exit();
}

// Get the admin data from session
$adminData = $_SESSION['admin_data'];

// Extract values for easier access
$admin_id = $adminData['id'];
$full_name = $adminData['full_name'];
$email = $adminData['email'];
$phone = $adminData['phone'];
$admin_key = $adminData['admin_key'];
$role = $adminData['role'];
$login_time = $adminData['login_time'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="../Design/buttom_bar.css">
    <link rel="stylesheet" href="admin.css">
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
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">
            <img src="../picture/logo.gif" alt="SKST Logo">
            <h1><i class="fas fa-university"></i> SKST University || Administration</h1>
        </div>
       
        <div class="nav-buttons">
            <button><i class="fas fa-home"></i><a style="text-decoration: none;color:aliceblue" href="administration.html">Home</a> </button>
            <button><i class="fas fa-bell"></i> Notifications</button>
    
        </div>
        
        
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
           <ul class="sidebar-menu">
               
                    <form method="post" style="display: contents;">

                        <button type="submit" name="dashboard"><i class="fas fa-th-large"></i> Dashboard</button>

                        <button type="submit" name="student"><i class="fas fa-user-graduate"></i> Students</button>
                        <button type="submit" name="faculty"><i class="fas fa-chalkboard-teacher"></i> Faculty</button>

                        <button type="submit" name="staff"><i class="fas fa-users"></i> Staff</button>

                        <button type="submit" name="course"><i class="fas fa-book"></i> Courses</button>

                        <button type="submit" name="stats"><i class="fas fa-chart-bar"></i> University Statistics</button>
                        <button type="submit" name="notification"><i class="fas fa-bell"></i> Notifications</button>
                    </form>
                </li>
                
                <li><a href="adminlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <?php
        if(isset($_POST['student'])){
            echo "student";
        }
        elseif (isset($_POST['stats'])){?>
        <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                <?php  echo  '<div class="stat-number">'.student_count().'</div>'; ?>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <?php  echo  '<div class="stat-number">'.faculty_count().'</div>'; ?>
                    <div class="stat-label">Faculty Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number">87</div>
                    <div class="stat-label">Active Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Pending Tasks</div>
                </div>
            </div>
        
<?php 
}
        
        else {?>
        <div class="content-area">
            <div class="page-header">
                <h2 class="page-title">Admin Profile</h2>
                <button class="btn-edit"><i class="fas fa-edit"></i> Edit Profile</button>
            </div>
            
            <div class="profile-card">
                <img src="../picture/profilepicture.png" alt="Admin" class="profile-img">
                <div class="profile-info">
                    <?php 
                    echo '<h2>'.$full_name.'</h2>';
                    echo '<p><i class="fas fa-envelope"></i>'.$email.'</p>'; 
                    echo '<p><i class="fas fa-phone"></i>'.$phone.'</p>'; 
                    ?>
                    <p><i class="fas fa-user-shield"></i> Administrator</p>
                </div>
            </div>
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="info-group">
                        <div class="info-label">Admin ID</div>
                        <?php echo '<div class="info-value">'.$admin_id.'</div>'; ?>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <?php echo '<div class="info-value">'.$full_name.'</div>'; ?>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email Address</div>
                        <?php echo '<div class="info-value">'.$email.'</div>'; ?>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone Number</div>
                        <?php echo '<div class="info-value">'.$phone.'</div>'; ?>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-key"></i> Security Information</h3>
                    <div class="info-group">
                        <div class="info-label">Admin Key</div>
                        <?php echo '<div class="info-value">'.$admin_key.'</div>'; ?>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <?php echo '<div class="info-value">'.$login_time.'</div>'; ?>
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

