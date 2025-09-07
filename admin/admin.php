<?php
session_start();
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



// Get the logged-in admin's email
$admin_email = $_SESSION['admin_email'] ?? '';

// Function to get total unread notifications
function getTotalUnreadNotifications($mysqli, $admin_email) {
    $total = 0;
    
    // Get counts for regular notice sections
    $query = "SELECT COUNT(*) as count FROM notice WHERE viewed = 0";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total += $row['count'];
        $stmt->close();
    }
    
    // Get count for update requests (only for this admin)
    $query = "SELECT COUNT(*) as count FROM update_requests WHERE admin_email = ? AND action = 0";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $total += $row['count'];
        $stmt->close();
    }
    
    return $total;
}

// Get total unread notifications
$totalUnread = getTotalUnreadNotifications($mysqli, $admin_email);
$badgeHtml = $totalUnread > 0 ? " <span class='notification-badge'>$totalUnread</span>" : "";



// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // ✅ Use correct columns (full_name, username, email)
    $sql = "SELECT id, full_name, email, phone FROM admin_users WHERE email = ? AND password = ? LIMIT 1";

    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $full_name, $email, $phone);
            if ($stmt->fetch()) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_name'] = $full_name;  // use full_name from DB
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_phone'] = $phone;

                // Update last login time
                $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                $update_stmt = $mysqli->prepare($update_sql);
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                header("Location: admin.php");
                exit();
            }
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    } else {
        $error = "SQL Prepare failed: " . $mysqli->error;
    }
}


// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $uploadDir = 'uploads/admin_pictures/';
    
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
            $update_sql = "UPDATE admin_users SET profile_picture = ? WHERE id = ?";
            if ($update_stmt = $mysqli->prepare($update_sql)) {
                $update_stmt->bind_param("si", $targetFilePath, $admin_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Update session variable
                $_SESSION['admin_profile_picture'] = $targetFilePath;
                
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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $key = trim($_POST['key']);
    
    $update_sql = "UPDATE admin_users SET full_name = ?, username = ?, email = ?, phone = ?, `key` = ? WHERE id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("sssssi", $full_name, $username, $email, $phone, $key, $admin_id);
        
        if ($update_stmt->execute()) {
            // Update session variables
            $_SESSION['admin_name'] = $full_name;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_phone'] = $phone;
            
            // Refresh page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}

// Handle password reset request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $sql = "SELECT id, username FROM admin_users WHERE email = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username);
                $stmt->fetch();
                
                // In a real application, you would generate a reset token and send an email
                // For demo purposes, we'll just show a success message
                $error = "Password reset instructions have been sent to your email.";
            } else {
                $error = "No account found with that email address.";
            }
        } else {
            $error = "Oops! Something went wrong. Please try again later.";
        }
        
        $stmt->close();
    }
}

// Check if admin is logged in
$is_logged_in = isset($_SESSION['admin_id']);

// Get admin data if logged in
if ($is_logged_in) {
    $admin_id = $_SESSION['admin_id'];
    $sql = "SELECT * FROM admin_users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // Update session phone number if available
    if (!empty($admin['phone'])) {
        $_SESSION['admin_phone'] = $admin['phone'];
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - SKST University</title>
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
        .notification-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: 5px;
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
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        h1 {
            color: #2b5876;
            margin-bottom: 20px;
            font-size: 28px;
        }
        
        .description {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        /* Statistics Button */
        .stats-button {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(43, 88, 118, 0.2);
        }
        
        .stats-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(43, 88, 118, 0.3);
        }
        
        .stats-button:active {
            transform: translateY(0);
        }
        
        .stats-button i {
            margin-right: 10px;
            font-size: 20px;
        }
        
        /* Stats Panel (initially hidden) */
        .stats-panel {
            background: #f8faff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            text-align: left;
            display: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #4e4376;
        }
        
        .stats-panel.visible {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            color: #4e4376;
            font-weight: 500;
        }
        
        .stat-value {
            color: #2b5876;
            font-weight: 700;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .instructions {
            margin-top: 25px;
            padding: 15px;
            background: #f0f5ff;
            border-radius: 8px;
            font-size: 14px;
            color: #4e4376;
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
            box-sizing: border-box;
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
        
        /* ================ Edit Modal Styles ============ */
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
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            animation: modalFade 0.3s;
        }
        
        @keyframes modalFade {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
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
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
        }
        
        .modal-title {
            color: #2b5876;
            font-size: 24px;
            display: flex;
            align-items: center;
        }
        
        .modal-title i {
            margin-right: 10px;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel {
            background: #ccc;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* ================ Additional Styles ============ */
        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .login-links a {
            color: #4e4376;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
            margin: 5px 0;
        }
        
        .login-links a:hover {
            color: #2b5876;
            text-decoration: underline;
        }
        
        /* Forgot Password Modal */
        #forgotPasswordModal .modal-content {
            max-width: 500px;
        }
        
        /* Back button style */
        .back-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #f0f5ff;
            color: #2b5876;
            border: 1px solid #2b5876;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            text-align: center;
        }
        
        .back-button:hover {
            background: #2b5876;
            color: white;
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
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .login-links {
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }
        }
        .password-container {
    position: relative;
    width: 100%;
}
.password-container input {
    width: 100%;
    padding-right: 40px; /* space for eye icon */
}
.password-container .toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #666;
}
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
                <h1>Admin Portal</h1>
                <p>SKST University - Administrator Access</p>
            </div>
            
            <form class="login-form" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">Login to Admin Dashboard</button>
                
                <div class="login-links">
                    <a href="forgot_password.php" id="forgotPasswordLink"><i class="fas fa-unlock-alt"></i> Forgot Password?</a>
                    <a href="#" id="helpLink"><i class="fas fa-question-circle"></i> Help</a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Back Button -->
                <a href="administration.html" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Administration
                </a>
            </form>
        </div>
    </div>


    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-question-circle"></i> Help & Support</h2>
            </div>
            <div class="modal-body">
                <h3>Administrator Login Assistance</h3>
                <p>If you're having trouble accessing your account, please follow these steps:</p>
                <ol>
                    <li style="margin-left: 20px;">Ensure you're using the correct email and password (case sensitive)</li>
                    <li style="margin-left: 20px;">Try resetting your password using the "Forgot Password" link</li>
                    <li style="margin-left: 20px;">Clear your browser cache and cookies</li>
                    <li style="margin-left: 20px;">Try using a different browser</li>
                </ol>
                
                <h3>Contact Support</h3>
                <p>If you continue to experience issues, please contact the SKST University IT support team:</p>
                <ul>
                    <li style="margin-left: 20px;"><strong>Email:</strong> support@skstuniversity.edu</li>
                    <li style="margin-left: 20px;"><strong>Phone:</strong> 01884273156; 01610343595</li>
                    <li style="margin-left: 20px;"><strong>Hours:</strong> Saturday-Wednesday, 8:00 AM - 5:00 PM</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="closeHelp">Close</button>
            </div>
        </div>
    </div>
    <?php else: ?>

    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1 style="color:white; text-align:center;margin:0px;">SKST University Admin Portal</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
           
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="student_dev.php">
                        <i class="fas fa-user"></i> Student
                    </a>
                </li>
                <li>
                    <a href="../admission/dev_admission.php">
                        <i class="fas fa-user"></i> Admission
                    </a>
                </li>
                <li>
                    <a href="../alumni/dev_alumni.php">
                        <i class="fas fa-user-graduate"></i> Alumni
                    </a>
                </li>
                <li>
                    <a href="../faculty/dev_faculty.php">
                        <i class="fas fa-chalkboard-teacher"></i> Faculty
                    </a>
                </li>
                <li>
                    <a href="employee_dev.php">
                        <i class="fas fa-briefcase"></i> Employee
                    </a>
                </li>
                
                <li>
                    <a href="course.php">
                        <i class="fas fa-book"></i> Course
                    </a>
                </li>
                <li>
                  



<a href="notification.php" class="nav-link">
    <i class="fas fa-bell"></i> Notifications<?php echo $badgeHtml; ?>
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
                <h1 class="page-title"><i class="fas fa-user-shield"></i> Administrator Dashboard</h1>
                <button class="btn-edit" id="editProfileBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <!-- Profile Card with Picture Upload -->
            <div class="profile-card">
                <div class="profile-img-container">
                    <?php if (!empty($_SESSION['admin_profile_picture'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($_SESSION['admin_profile_picture']); ?>" alt="Profile Image">
                    <?php else: ?>
                        <div id="profile-placeholder" class="profile-placeholder">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    <?php endif; ?>
                    <div class="edit-overlay" onclick="document.getElementById('file-input').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                    <form id="upload-form" method="post" enctype="multipart/form-data">
                        <input type="file" id="file-input" name="profile_picture" accept="image/*">
                    </form>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo (!empty($_SESSION['admin_phone'])) ? htmlspecialchars($_SESSION['admin_phone']) : 'N/A'; ?></p>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    <div class="info-group">
                        <div class="info-label">Admin ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['id']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['username']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security Key</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['key']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-user-tie"></i> Personal Information</h3>
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['phone']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-calendar-alt"></i> Account Activity</h3>
                    <div class="info-group">
                        <div class="info-label">Registration Date</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($admin['registration_date'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'First login'; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span style="color: #00a651;"><?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?></span></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-shield-alt"></i> Admin Privileges</h3>
                    <div class="info-group">
                        <div class="info-label">User Level</div>
                        <div class="info-value">Super Administrator</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Access Rights</div>
                        <div class="info-value">Full System Access</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security</div>
                        <div class="info-value">Two-Factor Authentication Recommended</div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="page-header">
                <h2 class="page-title"><i class="fas fa-chart-line"></i> System Statistics</h2>
            </div>
            <?php
$mysqli = new mysqli("localhost", "root", "", "skst_university");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Count courses
$result = $mysqli->query("SELECT COUNT(*) AS total FROM course");
$row = $result->fetch_assoc();
$course_count = $row['total'];

// Count students
$result = $mysqli->query("SELECT COUNT(*) AS total FROM student_registration");
$row = $result->fetch_assoc();
$student_count = $row['total'];

// Count staff
$result = $mysqli->query("SELECT COUNT(*) AS total FROM stuf");
$row = $result->fetch_assoc();
$staff_count = $row['total'];
$result = $mysqli->query("SELECT COUNT(*) AS total FROM admin_users");
$row = $result->fetch_assoc();
$admin_count = $row['total'];

// Total count (courses + students + staff)
$total_count = $course_count + $student_count + $staff_count+$admin_count;
?>
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_count; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?php echo $course_count; ?></div>
                    <div class="stat-label">Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number"><?php echo $student_count; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                
                <?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "skst_university");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Count faculty
$result = $mysqli->query("SELECT COUNT(*) AS total FROM faculty");
$row = $result->fetch_assoc();
$faculty_count = $row['total'];
?>

<div class="stat-card">
    <div class="stat-icon">
        <i class="fas fa-chalkboard-teacher"></i>
    </div>
    <div class="stat-number"><?php echo $faculty_count; ?></div>
    <div class="stat-label">Faculty</div>
</div>

            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-user-edit"></i> Edit Profile</h2>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-body">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" 
                                value="<?php echo htmlspecialchars($admin['password']); ?>" required>
                            <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>

                    <script>
                    const passwordField = document.getElementById("password");
                    const togglePassword = document.getElementById("togglePassword");

                    togglePassword.addEventListener("click", function () {
                        const type = passwordField.type === "password" ? "text" : "password";
                        passwordField.type = type;

                        // Switch between eye and eye-slash
                        this.classList.toggle("fa-eye");
                        this.classList.toggle("fa-eye-slash");
                    });
                    </script>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="key">Security Key</label>
                        <input type="text" id="key" name="key" value="<?php echo htmlspecialchars($admin['key']); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Stats panel toggle functionality
            const statsButton = document.querySelector('.stats-button');
            const statsPanel = document.querySelector('.stats-panel');
            
            if (statsButton && statsPanel) {
                statsButton.addEventListener('click', function() {
                    statsPanel.classList.toggle('visible');
                });
            }
            
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
            
            // Edit Profile Modal functionality
            const modal = document.getElementById("editModal");
            const btn = document.getElementById("editProfileBtn");
            const span = document.getElementsByClassName("close")[0];
            const cancelBtn = document.getElementById("cancelEdit");
            
            if (btn) {
                btn.onclick = function() {
                    modal.style.display = "block";
                }
            }
            if (span) {
                span.onclick = function() {
                    modal.style.display = "none";
                }
            }
            
            if (cancelBtn) {
                cancelBtn.onclick = function() {
                    modal.style.display = "none";
                }
            }
            
            // Forgot Password Modal
            const forgotPasswordLink = document.getElementById("forgotPasswordLink");
            const forgotPasswordModal = document.getElementById("forgotPasswordModal");
            const cancelReset = document.getElementById("cancelReset");
            
            if (forgotPasswordLink && forgotPasswordModal) {
                forgotPasswordLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    forgotPasswordModal.style.display = "block";
                });
            }
            
            if (cancelReset) {
                cancelReset.onclick = function() {
                    forgotPasswordModal.style.display = "none";
                }
            }
            
            // Help Modal
            const helpLink = document.getElementById("helpLink");
            const helpModal = document.getElementById("helpModal");
            const closeHelp = document.getElementById("closeHelp");
            
            if (helpLink && helpModal) {
                helpLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    helpModal.style.display = "block";
                });
            }
            
            if (closeHelp) {
                closeHelp.onclick = function() {
                    helpModal.style.display = "none";
                }
            }
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
                if (event.target == forgotPasswordModal) {
                    forgotPasswordModal.style.display = "none";
                }
                if (event.target == helpModal) {
                    helpModal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>