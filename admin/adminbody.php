<?php
require_once 'admin.php';
// Add database connection
$conn = mysqli_connect('localhost','root','','skst_university');

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

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
// $username = $adminData['username'];
$email = $adminData['email'];
$phone = $adminData['phone'];
// $password = $adminData['password'];
$admin_key = $adminData['admin_key'];
$login_time = $adminData['login_time'];

// Initialize message variables
$success_message = '';
$error_message = '';

// Determine active section
$active_section = 'dashboard'; // Default
if (isset($_POST['student'])) $active_section = 'student';
if (isset($_POST['faculty'])) $active_section = 'faculty';
if (isset($_POST['staff'])) $active_section = 'staff';
if (isset($_POST['course'])) $active_section = 'course';
if (isset($_POST['stats'])) $active_section = 'stats';
if (isset($_POST['notification'])) $active_section = 'notification';
if (isset($_POST['edit_admin_bio'])) $active_section = 'edit_admin_bio';
if (isset($_POST['update_admin'])) $active_section = 'edit_admin_bio';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $new_full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $new_username = mysqli_real_escape_string($conn, $_POST['username']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);
    $new_key = mysqli_real_escape_string($conn, $_POST['key']);
    
    // Build the SQL query
    $sql = "UPDATE admin_users SET 
            full_name = '$new_full_name', 
            username = '$new_username',
            email = '$new_email', 
            phone = '$new_phone',
            password = '$new_password',
            admin_key = '$new_key'
            WHERE id = $admin_id";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Profile updated successfully!";
        
        // Update session data with new values
        $_SESSION['admin_data']['full_name'] = $new_full_name;
        $_SESSION['admin_data']['username'] = $new_username;
        $_SESSION['admin_data']['email'] = $new_email;
        $_SESSION['admin_data']['phone'] = $new_phone;
        $_SESSION['admin_data']['password'] = $new_password;
        $_SESSION['admin_data']['admin_key'] = $new_key;
        
        // Refresh the page data
        $adminData = $_SESSION['admin_data'];
        $full_name = $adminData['full_name'];
        $username = $adminData['username'];
        $email = $adminData['email'];
        $phone = $adminData['phone'];
        $password = $adminData['password'];
        $admin_key = $adminData['admin_key'];
    } else {
        $error_message = "Error updating profile: " . mysqli_error($conn);
    }
}
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
        
        /* =====================edit biodata form============== */
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
                <!-- Main navigation form -->
                <form method="post" id="main-nav-form">
                    <button type="submit" name="dashboard" <?php if($active_section == 'dashboard') echo 'class="active"'; ?>>
                        <i class="fas fa-th-large"></i> Dashboard
                    </button>
                    <button type="submit" name="student" <?php if($active_section == 'student') echo 'class="active"'; ?>>
                        <i class="fas fa-user-graduate"></i> Students
                    </button>
                    <button type="submit" name="faculty" <?php if($active_section == 'faculty') echo 'class="active"'; ?>>
                        <i class="fas fa-chalkboard-teacher"></i> Faculty
                    </button>
                    <button type="submit" name="staff" <?php if($active_section == 'staff') echo 'class="active"'; ?>>
                        <i class="fas fa-users"></i> Staff
                    </button>
                    <button type="submit" name="course" <?php if($active_section == 'course') echo 'class="active"'; ?>>
                        <i class="fas fa-book"></i> Courses
                    </button>
                    <button type="submit" name="stats" <?php if($active_section == 'stats') echo 'class="active"'; ?>>
                        <i class="fas fa-chart-bar"></i> University Statistics
                    </button>
                    <button type="submit" name="notification" <?php if($active_section == 'notification') echo 'class="active"'; ?>>
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                </form>
                
                <?php if($active_section == 'student'): ?>
                <div class="student-search-sidebar">
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search by Student ID or Name" required>
                        <button type="submit" name="stbio"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
                <?php endif; ?>
                
                <li><a href="adminlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="content-area">
            <?php
            // Show success/error messages at the top of the content area
            if (!empty($success_message)) {
                echo '<div class="success-msg"><i class="fas fa-check-circle"></i> ' . $success_message . '</div>';
            }
            if (!empty($error_message)) {
                echo '<div class="error-msg"><i class="fas fa-exclamation-circle"></i> ' . $error_message . '</div>';
            }

            if($active_section == 'student'): ?>
                <div class="page-header">
                    <h2 class="page-title"><i class="fas fa-user-graduate"></i> Student Management</h2>
                </div>

                <div class="content-card">
                    <h3><i class="fas fa-search"></i> Student Search</h3>
                    <p>Use the search form in the sidebar to find students by ID or name.</p>
                    <p>You can also perform advanced searches and manage student records from this section.</p>
                    
                    <?php
                    // Display search results if a search was performed
                    if (isset($_GET['stbio']) && isset($_GET['search'])) {
                        $search_term = mysqli_real_escape_string($conn, $_GET['search']);
                        echo '<div class="search-results">';
                        echo '<h4>Search Results for: "' . htmlspecialchars($search_term) . '"</h4>';
                        // Here you would typically query the database and display results
                        echo '<p>No results found. This is a placeholder for actual search functionality.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php elseif ($active_section == 'stats'): ?>
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
           
            <?php else: ?>
                <!-- Default Dashboard View -->
                <form action="" method="post">
                    <div class="page-header">
                        <h2 class="page-title">Admin Profile</h2>
                        <button class="btn-edit" name="edit_admin_bio"><i class="fas fa-edit"></i> Edit Profile</button>
                    </div>
                </form>
                
                <div class="profile-card">
                    <img src="../picture/profilepicture.png" alt="Admin" class="profile-img">
                    <div class="profile-info">
                        <?php 
                        echo '<h2>'.$full_name.'</h2>';
                        echo '<p><i class="fas fa-user"></i> '.$username.'</p>';
                        echo '<p><i class="fas fa-envelope"></i> '.$email.'</p>'; 
                        echo '<p><i class="fas fa-phone"></i> '.$phone.'</p>'; 
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
                            <div class="info-label">Username</div>
                            <?php echo '<div class="info-value">'.$username.'</div>'; ?>
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
                            <div class="info-label">Password Security</div>
                            <div class="info-value"><span style="color: #28a745;">Hashed & Secure</span></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>