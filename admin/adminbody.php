<?php
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
    <title>Administration</title>
    <link rel="stylesheet" href="admin.css">
     <title>SKST University Administration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="icon" href="../picture/SKST.png" type="image/png" />
  <!-- <link rel="stylesheet" href="Design/buttom_bar.css"> -->
  <link rel="stylesheet" href="../Design/buttom_bar.css">
  
</head>
<body>
       <div class="bodysection">
    <div class="navbar">
        <div class="logo">
        <img src="../picture/logo.gif" alt="SKST Logo" style="border-radius: 50%;">
        <h1><i class="fas fa-university"></i> SKST University || Administration</h1>
      </div>
       
        <div class="nav-buttons">
            <button><i class="fas fa-home"></i><a style="text-decoration: none;color:aliceblue" href="../administration.html">Home</a> </button>
            <button><i class="fas fa-bell"></i> Notifications</button>
            <button><i class="fas fa-sign-out-alt"></i><a style="text-decoration: none;color:aliceblue" href="administration.html">Sign Out</a></button>
        </div>
        
        <?php
      echo ' <div class="welcome">';
               echo ' <i class="fas fa-user-shield"></i>';echo 'Welcome '. $full_name;
            echo "</div>"; ?>
    </div>
    
 <div class="container">
        <div class="button_bar">
            <div class="button_group">
                <a href="#" class="button_notification">
                    <i class="fas fa-bell"></i> Notifications
                    <span class="notification_badge">5</span>
                </a>
                <a href="#" class="button_student">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
                <a href="#" class="button_profile">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
                <a href="#" class="button_faculty">
                    <i class="fas fa-chalkboard-teacher"></i> Faculty
                </a>
                <a href="#" class="button_staff">
                    <i class="fas fa-users"></i> Staff
                </a>
            </div>
            
            <a href="#" class="button_user">
                <div class="button_user_avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div>Admin User</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">Administrator</div>
                </div>
            </a>
        </div>
        
        <div class="content">
            <h2>Dashboard Overview</h2>
            <p>Welcome to the SKST University Administration Panel. Use the buttons above to navigate through different sections of the system.</p>
            <p>You can manage students, faculty, staff, view notifications, and update your profile from the navigation bar.</p>
            
            <h3 style="margin-top: 2rem;">Recent Activities</h3>
            <ul style="list-style-type: none; margin-top: 1rem;">
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    <i class="fas fa-user-plus" style="color: #2b5876; margin-right: 10px;"></i>
                    New student registration - John Doe
                </li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    <i class="fas fa-book" style="color: #2b5876; margin-right: 10px;"></i>
                    Course updated - Computer Science 101
                </li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                    <i class="fas fa-chalkboard-teacher" style="color: #2b5876; margin-right: 10px;"></i>
                    New faculty member added - Dr. Jane Smith
                </li>
            </ul>
        </div>
    </div>

   



</body>
</html>