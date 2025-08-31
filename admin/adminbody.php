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
$username = $adminData['username'];
$email = $adminData['email'];
$phone = $adminData['phone'];
$password = $adminData['password'];
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
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(10px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        .btn-submit {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-submit:hover {
            background: #218838;
        }
        .success-msg, .error-msg {
            padding: 12px 15px;
            border-radius: 8px;
            text-align: center;
            margin: 15px auto;
            max-width: 600px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {opacity: 0; transform: translateY(-10px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .notification-container {
            width: 100%;
            position: relative;
        }
        
        /* Student search form in sidebar */
        .student-search-sidebar {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .student-search-sidebar input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .student-search-sidebar button {
            width: 100%;
            padding: 8px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .student-search-sidebar button:hover {
            background: #0069d9;
        }
        
        /* Active menu item style */
        .sidebar-menu button.active {
            background-color: #e9ecef;
            border-left: 4px solid #007bff;
        }
        
        /* Sidebar menu buttons */
        .sidebar-menu button {
            width: 100%;
            text-align: left;
            padding: 12px 15px;
            background: none;
            border: none;
            border-left: 4px solid transparent;
            cursor: pointer;
            margin-bottom: 5px;
            transition: all 0.2s;
        }
        .sidebar-menu button:hover {
            background-color: #f8f9fa;
        }
        
        .password-info {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .db-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        /* Profile display styles */
        .profile-card {
            display: flex;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
        }
        
        .profile-info h2 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .profile-info p {
            margin-bottom: 5px;
            color: #7f8c8d;
        }
        
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .detail-card h3 {
            margin-bottom: 15px;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
        }
        
        .info-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .info-label {
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .info-value {
            color: #2c3e50;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-title {
            color: #2c3e50;
        }
        
        .btn-edit {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-edit:hover {
            background: #2980b9;
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
            <?php elseif ($active_section == 'edit_admin_bio'): ?>
                <!-- Edit Profile Form with Username Field -->
                <div class="form-container">
                    <h2><i class="fas fa-user-edit"></i> Edit Admin Profile</h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($full_name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($phone) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" value="<?= htmlspecialchars($password) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="key">Admin Key</label>
                            <input type="text" name="key" id="key" value="<?= htmlspecialchars($admin_key) ?>" required>
                        </div>

                        <button type="submit" name="update_admin" class="btn-submit"><i class="fas fa-save"></i> Update Profile</button>
                    </form>
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