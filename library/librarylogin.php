<?php
session_start();
ob_start(); // Start output buffering

$error = '';
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'skst_university');

// Create connection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle login first (before any output)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Using plain text password comparison as per requirements
    $sql = "SELECT id, first_name, last_name, email, position FROM stuf WHERE email = ? AND password = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ss", $email, $password);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $first_name, $last_name, $email, $position);
                if ($stmt->fetch()) {
                    $_SESSION['stuf_id'] = $id;
                    $_SESSION['stuf_name'] = $first_name . ' ' . $last_name;
                    $_SESSION['stuf_email'] = $email;
                    $_SESSION['stuf_position'] = $position;
                    
                    // Update last login time
                    $update_sql = "UPDATE stuf SET last_login = NOW() WHERE id = ?";
                    if ($update_stmt = $mysqli->prepare($update_sql)) {
                        $update_stmt->bind_param("i", $id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Oops! Something went wrong. Please try again later.";
        }
        
        $stmt->close();
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Now include notice.php after handling redirects
require_once 'notice.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['stuf_id']);

if ($is_logged_in) {
    $unread_count = get_unread_notification_count();
} else {
    $unread_count = 0;
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['stuf_id'])) {
    $stuf_id = $_SESSION['stuf_id'];
    $uploadDir = 'uploads/stuf_pictures/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update database with file path
            $update_sql = "UPDATE stuf SET photo_path = ? WHERE id = ?";
            if ($update_stmt = $mysqli->prepare($update_sql)) {
                $update_stmt->bind_param("si", $targetFilePath, $stuf_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Refresh page to show new image
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
}

// Get stuf data if logged in
if ($is_logged_in) {
    $stuf_id = $_SESSION['stuf_id'];
    $sql = "SELECT * FROM stuf WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $stuf_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stuf = $result->fetch_assoc();
    $stmt->close();
}

$mysqli->close();

// Check if notifications should be shown
$show_notifications = isset($_GET['notice']) && $_GET['notice'] == 1;

// Check if profile should be shown (default if nothing else is specified)
$show_profile = !$show_notifications;
ob_end_flush(); // Send the output buffer and turn off output buffering
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <title>Library Staff Portal - SKST University</title>
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
        
        .nav-buttons button, .nav-buttons a {
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
            text-decoration: none;
        }
        
        .nav-buttons button:hover, .nav-buttons a:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
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
        
        .btn-edit {
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
        
        .btn-edit:hover {
            opacity: 0.9;
        }
        
        /* ================ Profile Section ============ */
        .profile-card {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .profile-img-container {
            position: relative;
            margin-right: 30px;           
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #2b5876;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
            border: 5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .edit-overlay {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: #4e4376;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid white;
        }
        
        .edit-overlay:hover {
            background: #2b5876;
            transform: scale(1.1);
        }
        
        #file-input {
            display: none;
        }
        
        .profile-info h2 {
            color: #2b5876;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .profile-info p {
            color: #666;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .profile-info i {
            margin-right: 10px;
            color: #4e4376;
        }
        
        /* ================ Info Cards ============ */
        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .detail-card h3 {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
            display: flex;
            align-items: center;
        }
        
        .detail-card h3 i {
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
        
        .info-group {
            margin-bottom: 18px;
        }
        
        .info-label {
            font-size: 14px;
            color: #888;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 18px;
            color: #444;
            font-weight: 500;
        }
        
        /* ================ Stats Section ============ */
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex: 1;
            margin: 10px;
            min-width: 180px;
            transition: all 0.3s ease;
            border-top: 3px solid;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }

        .stat-card:nth-child(1) {
            border-color: #4e4376;
        }

        .stat-card:nth-child(2) {
            border-color: #2b5876;
        }

        .stat-card:nth-child(3) {
            border-color: #4a90e2;
        }

        .stat-card:nth-child(4) {
            border-color: #f39c12;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #4e4376, #826ab4);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #2b5876, #4e8fa8);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #4a90e2, #6bb9ff);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
        }

        .stat-number {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 3px;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* ================ Login Form Styles ============ */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .login-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .login-form {
            padding: 30px;
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
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .login-btn {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .error-msg {
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #ffecec;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .demo-credentials {
            margin-top: 25px;
            padding: 15px;
            background: #f0f5ff;
            border-radius: 8px;
            font-size: 14px;
            color: #4e4376;
            text-align: center;
        }
        
        /* ================ Notification Styles ============ */
        .notification-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
        }
        
        .notification-title {
            color: #2b5876;
            font-size: 24px;
            display: flex;
            align-items: center;
        }
        
        .notification-title i {
            margin-right: 10px;
            background: #f0f5ff;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #4e4376;
        }
        
        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: flex-start;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item.unread {
            background-color: #f0f8ff;
            border-left: 4px solid #4e4376;
        }
        
        .notification-icon {
            margin-right: 15px;
            color: #4e4376;
            font-size: 18px;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-message {
            margin-bottom: 5px;
            line-height: 1.5;
        }
        
        .notification-time {
            font-size: 12px;
            color: #888;
        }
        
        .notification-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .btn-mark-all {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-mark-all:hover {
            opacity: 0.9;
        }
        
        /* ================ Responsive Design ============ */
        @media (max-width: 1024px) {
            .info-cards {
                grid-template-columns: 1fr;
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
            
            .profile-card {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-img-container {
                margin-right: 0;
                margin-bottom: 20px;
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
            
            .info-cards {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .stat-card {
                min-width: auto;
            }
        }
        
        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .login-form {
                padding: 20px;
            }
        }

        /* Hide profile content when notifications are shown */
        .profile-content {
            display: block;
        }
        
        .show-notifications .profile-content {
            display: none;
        }
        
        .show-notifications .notification-container {
            display: block;
        }
        
        .notification-container {
            display: none;
        }
    </style>
</head>
<body <?php if ($show_notifications) echo 'class="show-notifications"'; ?>>
    <?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
                <h1>Library Staff Portal</h1>
                
            </div>
            
            <form class="login-form" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">Login to Dashboard</button>
                
                <!-- Fixed the button closing tag and added proper spacing -->
                <button type="button" class="login-btn" onclick="location.href='../index.html'">Sign Out</button>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
            
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1>SKST University Library</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <!-- Fixed notification button to use a link instead of form -->
            <a href="?notice=1" id="notification-link">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($unread_count > 0): ?>
                    <span style="background:red; color:white; border-radius:50%; padding:2px 6px; font-size:12px;">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    

    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="?" class="<?php echo $show_profile ? 'active' : ''; ?>" id="profile-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="dev_books.php">
                        <i class="fas fa-book"></i> Manage Books
                    </a>
                </li>
                <li>
                    <a href="dev_ebook.php">
                        <i class="fas fa-book"></i> Manage E-Books
                    </a>
                </li>
                <li>
                    <a href="dev_library_management.php">
                        <i class="fas fa-users"></i> Member Management
                    </a>
                </li>
                <li>
                    <a href="dev_books_borrowing.php">
                        <i class="fas fa-exchange-alt"></i> Issue/Return
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
            <?php if ($show_notifications): ?>
            <!-- Notifications Section -->
            <div class="notification-container">
                <div class="notification-header">
                    <h2 class="notification-title"><i class="fas fa-bell"></i> Notifications</h2>
                    <div class="notification-actions">
                        <button class="btn-mark-all">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                    </div>
                </div>
                
                <div class="notification-list">
                    <?php
                    // Display notifications using the function from notice.php
                    if (function_exists('see_staff_notice')) {
                        echo see_staff_notice();
                    } else {
                        echo '<div class="notification-item">
                                <div class="notification-icon"><i class="fas fa-info-circle"></i></div>
                                <div class="notification-content">
                                    <div class="notification-message">No notifications available at this time.</div>
                                </div>
                              </div>';
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="profile-content">
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-book-reader"></i> Library Staff Dashboard</h1>
                </div>

                <!-- Profile Card with Picture Upload -->
                <div class="profile-card">
                    <div class="profile-img-container">
                        <?php if (!empty($stuf['photo_path'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars((string) $stuf['photo_path']); ?>" alt="Profile Image">
                      <?php else: ?>
                        <div id="profile-placeholder" class="profile-placeholder">
                          <i class="fas fa-user-tie"></i>
                        </div>
                      <?php endif; ?>
                      <div class="edit-overlay" onclick="document.getElementById('file-input').click()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" 
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                      </div>
                      <form id="upload-form" method="post" enctype="multipart/form-data">
                        <input type="file" id="file-input" name="profile_picture" accept="image/*">
                      </form>
                    </div>

                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($stuf['first_name'] . ' ' . $stuf['last_name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($stuf['email']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($stuf['stuff_phone'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($stuf['position']); ?></p>
                    </div>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="info-cards">
                    <div class="detail-card">
                        <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                        <div class="info-group">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['first_name'] . ' ' . $stuf['last_name']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Father's Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['father_name'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Mother's Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['mother_name'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?php echo !empty($stuf['date_of_birth']) ? date('M j, Y', strtotime($stuf['date_of_birth'])) : 'Not provided'; ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-graduation-cap"></i> Educational Information</h3>
                        <div class="info-group">
                            <div class="info-label">Last Exam</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['last_exam'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Board</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['board'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Year of Passing</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['year_of_passing'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Result</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['result'] ?? 'Not provided'); ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                        <div class="info-group">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['email']); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['stuff_phone'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Guardian Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['guardian_phone'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Present Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['present_address'] ?? 'Not provided'); ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-info-circle"></i> Additional Information</h3>
                        <div class="info-group">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['gender'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Blood Group</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['blood_group'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Nationality</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['nationality'] ?? 'Not provided'); ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Religion</div>
                            <div class="info-value"><?php echo htmlspecialchars($stuf['religion'] ?? 'Not provided'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <?php endif; ?>

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
            
            // Handle profile picture upload
            const fileInput = document.getElementById('file-input');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        document.getElementById('upload-form').submit();
                    }
                });
            }
            
            // Mark all notifications as read
            const markAllButton = document.querySelector('.btn-mark-all');
            if (markAllButton) {
                markAllButton.addEventListener('click', function() {
                    // This would typically make an AJAX request to mark all notifications as read
                    alert('This would mark all notifications as read. Implementation would require server-side processing.');
                });
            }

            // Handle profile link click
            const profileLink = document.getElementById('profile-link');
            if (profileLink) {
                profileLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = '?';
                });
            }
        });
    </script>
</body>
</html>