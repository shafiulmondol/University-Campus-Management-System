<?php
ob_start(); // Start output buffering to avoid header issues
session_start();

$error = '';
require_once '../library/notice.php';

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

// ------------------- Handle Add Notice Submission -------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_notice'])) {
    $title = $_POST['title'] ?? '';
    $id = $_POST['id'] ?? '';
    $section = $_POST['section_notice'] ?? '';
    $content = $_POST['content'] ?? '';
    $author = $_POST['author'] ?? '';
    $created_at = date('Y-m-d H:i:s');
    $viewed = 0;

    // Use the existing $mysqli connection instead of creating a new one
    $stmt = $mysqli->prepare("INSERT INTO notice (title, id, section, content, author, created_at, viewed) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssssi", $title, $id, $section, $content, $author, $created_at, $viewed);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Notice added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Execute error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color:red;'>Prepare error: " . $mysqli->error . "</p>";
    }
}
// -----------------------
// Handle Login
// -----------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, username, email, phone FROM admin_users WHERE email = ? AND password = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $full_name, $username, $email, $phone);
            if ($stmt->fetch()) {
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_name'] = $full_name;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_phone'] = $phone;

                // Update last login
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
// -----------------------
// Handle Logout
// -----------------------
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// -----------------------
// Handle Profile Picture Upload
// -----------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $uploadDir = 'uploads/admin_pictures/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');

    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            $update_sql = "UPDATE admin_users SET profile_picture = ? WHERE id = ?";
            if ($update_stmt = $mysqli->prepare($update_sql)) {
                $update_stmt->bind_param("si", $targetFilePath, $admin_id);
                $update_stmt->execute();
                $update_stmt->close();

                $_SESSION['admin_profile_picture'] = $targetFilePath;

                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $error = "Error uploading your file.";
        }
    } else {
        $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
    }
}

// -----------------------
// Handle Profile Update
// -----------------------
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
            $_SESSION['admin_name'] = $full_name;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_phone'] = $phone;

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }

        $update_stmt->close();
    }
}

// -----------------------
// Check if admin is logged in
// -----------------------
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

    if (!empty($admin['phone'])) {
        $_SESSION['admin_phone'] = $admin['phone'];
    }
}
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
            font-size: 18px;
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
        }
        
        .login-links a {
            color: #4e4376;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .login-links a:hover {
            color: #2b5876;
            text-decoration: underline;
        }
        
        /* Forgot Password Modal */
        #forgotPasswordModal .modal-content {
            max-width: 500px;
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
.notices-container { max-width: 900px; margin:20px auto; padding:20px;background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.notices-heading { font-size:22px; margin-bottom:15px; color:#333; }
.filter-form { margin-bottom:15px; }
.filter-form select, .filter-form input { padding:6px; border-radius:5px; border:1px solid #ccc; }
.filter-form button { padding:6px 12px; border:none; background:#007bff; color:#fff; border-radius:5px; cursor:pointer; }
.notice-card { padding:15px; margin:12px 0; border-radius:10px; border:1px solid #eee; background:#fafafa; }
.notice-card.unread { border-left:5px solid #007bff; }
.notice-card.read { border-left:5px solid #ccc; }
.notice-card.update { border-left:5px solid #ff9800; background:#fff8e1; }
.notice-footer { display:flex; justify-content:space-between; font-size:13px; margin-top:8px; color:#666; }
.no-notices { text-align:center; padding:20px; color:#777; }
.btn-approve { background:#28a745; color:white; padding:6px 10px; border:none; border-radius:5px; cursor:pointer; }
.btn-reject { background:#dc3545; color:white; padding:6px 10px; border:none; border-radius:5px; cursor:pointer; }
.btn-back { background:#6c757d; color:white; padding:6px 14px; border:none; border-radius:5px; cursor:pointer; margin-top:10px; }

/* Course Container */
.course-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.course-heading {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-add-course, .btn-back, .btn-submit-course, .btn-cancel {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    transition: 0.3s;
}

.btn-add-course {
    background: #3498db;
    color: #fff;
}
.btn-add-course:hover {
    background: #2980b9;
}

.btn-back {
    background: #7f8c8d;
    color: #fff;
}
.btn-back:hover {
    background: #636e72;
}

.btn-submit-course {
    background: #27ae60;
    color: #fff;
}
.btn-submit-course:hover {
    background: #219150;
}

.btn-cancel {
    background: #e74c3c;
    color: #fff;
}
.btn-cancel:hover {
    background: #c0392b;
}

/* Course Table */
.course-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.course-table th, .course-table td {
    padding: 12px 15px;
    text-align: center;
    font-size: 0.95rem;
}

.course-table th {
    background: #34495e;
    color: #fff;
    text-transform: uppercase;
    font-size: 0.9rem;
}

.course-table tr:nth-child(even) {
    background: #f2f2f2;
}

.course-table tr:hover {
    background: #ecf0f1;
    transition: 0.2s;
}

/* Search Form */
.search-form {
    margin: 10px 0 20px;
    display: flex;
    gap: 10px;
}

.search-form input[type="text"] {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    width: 250px;
    font-size: 0.95rem;
}

.search-form button {
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
    transition: 0.3s;
}
.search-form button:hover {
    background: #27ae60;
}

/* Messages */
.success-msg, .error-msg {
    padding: 12px 16px;
    margin: 15px 0;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 500;
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
                    <a href="help.php" id="helpLink"><i class="fas fa-question-circle"></i> Help</a>
                </div>
                
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
            <h1 style="color:white; text-align:center;margin:0px;">SKST University Admin Portal</h1>
        </div>
        
        <div class="nav-buttons">
            <a href="../index.html" style="text-decoration: none;">
                <button>
                    <i class="fas fa-home"></i> Home
                </button>
            </a>
            <a href="../working.html" style="text-decoration: none;">
                <button>
                    <i class="fas fa-bell"></i> Notifications
                    <?php 
            $unread = get_unread_admin_notification_count(); 
            if($unread > 0): ?>
                <span class="badge"><?= htmlspecialchars($unread) ?></span>
            <?php endif; ?>
                </button>
            </a>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="admin.php" class="active">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="../student/dev_student.php">
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
                    <a href="#">
                        <i class="fas fa-briefcase"></i> Employee
                    </a>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </li>
                <li>
                     <form action="" method="post">
                        <button name="course"><i class="fas fa-bell"></i> Course</button>
                    </form>
                </li>
                <li>
                    <form action="" method="post">
                        <button name="notification"><i class="fas fa-bell"></i> Notifications</button>
                    </form>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-cog"></i> System Settings
                    </a>
                </li>
                <li>
                    <a href="?logout=1">
                        <button>
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="content-area">
         <?php

// ------------------- Handle Add Notice Form Display -------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_add_notice_form'])) {
    echo "<div class='add-notice-container'>";
    echo "<h3><i class='fas fa-plus-circle'></i> Add Notice</h3>";
    echo "<form method='post'>";
    echo "<label>Title:</label><br>";
    echo "<input type='text' name='title' required><br><br>";
    echo "<label>ID:</label><br>";
    echo "<input type='text' name='id' required><br><br>";

    echo "<label>Section:</label><br>";
    echo "<select name='section_notice' required>";
    $sections_notice = ['Student', 'Staff', 'Department', 'Faculty', 'Library', 'Account', 'Admin'];
    foreach ($sections_notice as $sec) {
        echo "<option value='{$sec}'>{$sec}</option>";
    }
    echo "</select><br><br>";

    echo "<label>Content:</label><br>";
    echo "<textarea name='content' rows='4' required></textarea><br><br>";

    echo "<label>Author:</label><br>";
    echo "<input type='text' name='author' required><br><br>";

    echo "<button type='submit' name='submit_notice'>Add Notice</button>";
    echo "</form>";
    echo "</div><hr>";
}


// ------------------- Handle Accept/Reject Update Requests -------------------
elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accept_update']) || isset($_POST['reject_update'])) {
        $applicant_id = intval($_POST['applicant_id']);
        $action = isset($_POST['accept_update']) ? 1 : 2;

        $stmt = $con->prepare("UPDATE update_requests SET action = ? WHERE applicant_id = ? AND action = 0");
        if ($stmt) {
            $stmt->bind_param("ii", $action, $applicant_id);
            $stmt->execute();
            $stmt->close();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// ------------------- Show Notifications -------------------
if (isset($_POST['notification']) || isset($_POST['show_add_notice_form']) || isset($_POST['submit_notice'])) {
    echo "<div class='notices-container'>";
    echo "<h2 class='notices-heading'><i class='fas fa-bullhorn'></i> Latest Notifications</h2>";

    // Section selection and search
    $selected_section = $_POST['section'] ?? 'Admin';
    $search_applicant = $_POST['search_applicant'] ?? '';

    // ------------------- Filter Form -------------------
    echo "<form method='post' class='filter-form'>";
    echo "<label for='section'>Select Section: </label>";
    echo "<select name='section'>";
    $sections = ['Student', 'Faculty', 'Staff', 'Admin', 'AO', 'update_request'];
    foreach ($sections as $sec) {
        $selected = ($sec == $selected_section) ? "selected" : "";
        echo "<option value='{$sec}' {$selected}>{$sec}</option>";
    }
    echo "</select>";
    echo " <input type='text' name='search_applicant' placeholder='Search by Applicant ID' value='" . htmlspecialchars($search_applicant) . "'>";
    echo " <button type='submit' name='notification'>Filter</button>";
    echo "</form><br>";

    // ------------------- Add Notice Button -------------------
    echo "<form method='post'>";
    echo "<button type='submit' name='show_add_notice_form'>➕ Add Notice</button>";
    echo "</form><br>";

    // ------------------- Fetch Notices & Update Requests -------------------
    $notices = [];

    // Fetch notices
    if ($selected_section != 'update_request') {
        $query1 = "SELECT id, title, content, author, created_at, viewed, section 
                   FROM notice 
                   WHERE section = '" . mysqli_real_escape_string($con, $selected_section) . "' 
                   ORDER BY created_at DESC";
        $result1 = mysqli_query($con, $query1);
        if ($result1) {
            while ($row = mysqli_fetch_assoc($result1)) {
                $row['source'] = 'notice';
                $notices[] = $row;
            }
        }
    }

    // Fetch update requests
    if ($selected_section == 'update_request') {
        $query2 = "SELECT id, applicant_id, admin_email, category, update_type, current_value, new_value, comments, request_time, action 
                   FROM update_requests WHERE 1";
        if (!empty($search_applicant)) {
            $query2 .= " AND applicant_id LIKE '%" . mysqli_real_escape_string($con, $search_applicant) . "%'";
        }
        $query2 .= " ORDER BY request_time DESC";
        $result2 = mysqli_query($con, $query2);
        if ($result2) {
            while ($row = mysqli_fetch_assoc($result2)) {
                $row['source'] = 'update';
                $notices[] = $row;
            }
        }
    }

    // ------------------- Display Notifications -------------------
    if (count($notices) > 0) {
        foreach ($notices as $row) {
            if ($row['source'] == 'notice') {
                $noticeClass = ($row['viewed'] == 0) ? "notice-card unread" : "notice-card read";
                echo "<div class='{$noticeClass}'>";
                echo "<div class='notice-header'>";
                echo "<h3 class='notice-title'>" . htmlspecialchars($row['title']) . "</h3>";
                echo "<span class='notice-section'>" . htmlspecialchars($row['section']) . "</span>";
                echo "</div>";
                echo "<div class='notice-content'>" . nl2br(htmlspecialchars($row['content'])) . "</div>";
                echo "<div class='notice-footer'>";
                echo "<span class='notice-author'>" . htmlspecialchars($row['author']) . "</span>";
                echo "<span class='notice-date'>" . date('F j, Y h:i A', strtotime($row['created_at'])) . "</span>";
                echo "</div></div>";
            } else {
                // Update requests
                echo "<div class='notice-card update'>";
                echo "<div class='notice-header'>";
                echo "<h3 class='notice-title'>Update Request (" . htmlspecialchars($row['update_type']) . ")</h3>";
                echo "<span class='notice-section'>Profile Update</span>";
                echo "</div>";
                echo "<div class='notice-content'>";
                echo "<b>Applicant ID:</b> " . htmlspecialchars($row['applicant_id']) . "<br>";
                echo "<b>Category:</b> " . htmlspecialchars($row['category']) . "<br>";
                echo "<b>Old Value:</b> " . htmlspecialchars($row['current_value']) . "<br>";
                echo "<b>New Value:</b> " . htmlspecialchars($row['new_value']) . "<br>";
                if (!empty($row['comments'])) {
                    echo "<b>Reason / Comment:</b> " . htmlspecialchars($row['comments']) . "<br>";
                }
                if ($row['action'] == 0) {
                    echo "<b>Status:</b> ⏳ Pending<br>";
                    echo "<form action='' method='post'>";
                    echo "<input type='hidden' name='applicant_id' value='" . $row['applicant_id'] . "'>";
                    echo "<button type='submit' name='accept_update'>✅ Accept</button> ";
                    echo "<button type='submit' name='reject_update'>❌ Reject</button>";
                    echo "</form>";
                } elseif ($row['action'] == 1) {
                    echo "<b>Status:</b> ✅ Request Accepted<br>";
                } else {
                    echo "<b>Status:</b> ❌ Request Rejected<br>";
                }
                echo "</div>";
                echo "</div>";
            }
        }
    } else {
        echo "<div class='no-notices'><p>No notifications found.</p></div>";
    }

    // Mark Admin notices as read
    if ($selected_section == 'Admin') {
        $update = "UPDATE notice SET viewed = 1 WHERE viewed = 0 AND section='Admin'";
        mysqli_query($con, $update);
    }

    echo "</div>"; // notices-container
}

// ------------------- Course Management -------------------
elseif (isset($_POST['course'])) {
    echo "<div class='course-container'>";
    echo "<h2 class='course-heading'><i class='fas fa-book'></i> Course Management</h2>";

    // Back button
    echo "<form method='post' style='margin-bottom: 20px;'>";
    echo "<button type='submit' name='dashboard' class='btn-back'>";
    echo "<i class='fas fa-arrow-left'></i> Back to Dashboard";
    echo "</button></br></br>";
    
   
    echo '<button type="submit" name="show_instructors" class="btn-add-course">'.'👨‍🏫 Show Instructors';
    echo '</button></br></br>';
    echo '<button type="submit" name="show_add_instructor_form" class="btn-add-course">'.'➕ Add Instructor';
    echo '</button>';
echo "</form>";

    // Search bar
    $search_code = $_POST['search_code'] ?? '';

    echo "<form method='post' class='search-form'>";
    echo "<input type='hidden' name='course' value='1'>";
    echo "<input type='text' name='search_code' placeholder='Search by Course Code' value='" . htmlspecialchars($search_code) . "'>";
    echo "<button type='submit'>Search</button>";
    echo "</form><br>";

    // Add course button
    if (!isset($_POST['show_add_course_form'])) {
        echo "<form method='post'>";
        echo "<button type='submit' name='show_add_course_form' class='btn-add-course'>";
        echo "<i class='fas fa-plus'></i> Add Course";
        echo "</button>";
        echo "</form><br>";
    }

    // Fetch and display courses
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $query = "SELECT course_id, course_code, course_name, credit_hours, department, semester, created_at, updated_at 
              FROM course WHERE 1";

    if (!empty($search_code)) {
        $query .= " AND course_code LIKE '%" . $mysqli->real_escape_string($search_code) . "%'";
    }

    $query .= " ORDER BY created_at DESC";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0) {
        echo "<table class='course-table'>";
        echo "<tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Credit Hours</th>
                <th>Department</th>
                <th>Semester</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['course_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['course_code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['credit_hours']) . "</td>";
            echo "<td>" . htmlspecialchars($row['department']) . "</td>";
            echo "<td>" . htmlspecialchars($row['semester']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='no-courses'><p>No courses found.</p></div>";
    }

    $mysqli->close();
}

// ------------------- Show Add Course Form -------------------
elseif (isset($_POST['show_add_course_form'])) {
    echo "<div class='add-course-form'>";
    echo "<h3><i class='fas fa-plus-circle'></i> Add New Course</h3>";
    echo "<form method='post'>";

    echo "<div class='form-group'>";
    echo "<label>Course ID: *</label>";
    echo "<input type='text' name='course_id' required>";
    echo "</div>";

    echo "<div class='form-group'>";
    echo "<label>Course Code: *</label>";
    echo "<input type='text' name='course_code' required>";
    echo "</div>";

    echo "<div class='form-group'>";
    echo "<label>Course Name: *</label>";
    echo "<input type='text' name='course_name' required>";
    echo "</div>";

    echo "<div class='form-group'>";
    echo "<label>Credit Hours: *</label>";
    echo "<input type='number' name='credit_hours' required>";
    echo "</div>";

    echo "<div class='form-group'>";
    echo "<label>Department: *</label>";
    echo "<input type='text' name='department' required>";
    echo "</div>";

    echo "<div class='form-group'>";
    echo "<label>Semester: *</label>";
    echo "<input type='text' name='semester' required>";
    echo "</div>";

    echo "<button type='submit' name='submit_course' class='btn-submit-course'>Add Course</button>";
    echo " <button type='submit' name='course' class='btn-cancel'>Cancel</button>";
    echo "</form>";
    echo "</div><hr>";
}

// ------------------- Handle Submit Course -------------------
elseif (isset($_POST['submit_course'])) {
    $course_id = $_POST['course_id'];
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credit_hours = $_POST['credit_hours'];
    $department = $_POST['department'];
    $semester = $_POST['semester'];

    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    $stmt = $mysqli->prepare("INSERT INTO course (course_id, course_code, course_name, credit_hours, department, semester, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("sssiss", $course_id, $course_code, $course_name, $credit_hours, $department, $semester);

    if ($stmt->execute()) {
        echo "<div class='success-msg'>✅ Course added successfully!</div>";
    } else {
        echo "<div class='error-msg'>❌ Failed to add course. Error: " . $mysqli->error . "</div>";
    }

    $stmt->close();
    $mysqli->close();

    echo "<form method='post'><button type='submit' name='course'>Back to Courses</button></form>";
}
if (isset($_POST['show_instructors'])) {
    echo "<div class='course-container'>";
    echo "<h2 class='course-heading'>👨‍🏫 Course Instructors</h2>";

    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    $query = "SELECT ci.faculty_id, f.name AS faculty_name, ci.course_id, c.course_name, 
                     ci.class_day, ci.class_time, ci.room_number
              FROM course_instructor ci
              JOIN faculty f ON ci.faculty_id = f.faculty_id
              JOIN course c ON ci.course_id = c.course_id
              ORDER BY ci.class_day, ci.class_time";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        echo "<table class='course-table'>";
        echo "<tr><th>Faculty</th><th>Course</th><th>Day</th><th>Time</th><th>Room</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['faculty_name']) . " (ID: " . $row['faculty_id'] . ")</td>";
            echo "<td>" . htmlspecialchars($row['course_name']) . " (ID: " . $row['course_id'] . ")</td>";
            echo "<td>" . htmlspecialchars($row['class_day']) . "</td>";
            echo "<td>" . htmlspecialchars($row['class_time']) . "</td>";
            echo "<td>" . htmlspecialchars($row['room_number']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='no-notices'><p>No instructors assigned yet.</p></div>";
    }

    $mysqli->close();
    echo "</div>";
}
if (isset($_POST['show_add_instructor_form'])) {
    echo "<div class='course-container'>";
    echo "<h2 class='course-heading'>➕ Assign Instructor to Course</h2>";

    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Faculty dropdown
    $faculty_result = $mysqli->query("SELECT faculty_id, name FROM faculty");
    $course_result = $mysqli->query("SELECT course_id, course_name FROM course");

    echo "<form method='post'>";
    
    echo "<div class='form-group'>";
    echo "<label>Faculty: *</label>";
    echo "<select name='faculty_id' required>";
    while ($f = $faculty_result->fetch_assoc()) {
        echo "<option value='{$f['faculty_id']}'>{$f['name']} (ID: {$f['faculty_id']})</option>";
    }
    echo "</select></div>";

    echo "<div class='form-group'>";
    echo "<label>Course: *</label>";
    echo "<select name='course_id' required>";
    while ($c = $course_result->fetch_assoc()) {
        echo "<option value='{$c['course_id']}'>{$c['course_name']} (ID: {$c['course_id']})</option>";
    }
    echo "</select></div>";

    echo "<div class='form-group'>";
    echo "<label>Class Day: *</label>";
    echo "<select name='class_day' required>
            <option>Sunday</option><option>Monday</option><option>Tuesday</option>
            <option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option>
          </select></div>";

    echo "<div class='form-group'>";
    echo "<label>Class Time: *</label>";
    echo "<select name='class_time' required>
            <option>8:30-9:30</option><option>9:35-10:35</option>
            <option>10:40-11:40</option><option>11:45-12:45</option>
            <option>1:10-2:10</option><option>2:15-3:15</option>
            <option>4:20-5:20</option>
          </select></div>";

    echo "<div class='form-group'>";
    echo "<label>Room Number: *</label>";
    echo "<input type='text' name='room_number' required>";
    echo "</div>";

    echo "<button type='submit' name='add_instructor' class='btn-submit-course'>Save</button>";
    echo " <button type='submit' name='show_instructors' class='btn-cancel'>Cancel</button>";
    echo "</form></div>";

    $mysqli->close();
}
if (isset($_POST['add_instructor'])) {
    $faculty_id = $_POST['faculty_id'];
    $course_id = $_POST['course_id'];
    $class_day = $_POST['class_day'];
    $class_time = $_POST['class_time'];
    $room_number = $_POST['room_number'];

    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check for duplicate slot
    $check = $mysqli->prepare("SELECT * FROM course_instructor 
                               WHERE course_id = ? AND class_day = ? 
                               AND class_time = ? AND room_number = ?");
    $check->bind_param("isss", $course_id, $class_day, $class_time, $room_number);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='error-msg'>❌ This course already has an instructor at this time and room.</div>";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO course_instructor 
            (faculty_id, course_id, class_day, class_time, room_number) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $faculty_id, $course_id, $class_day, $class_time, $room_number);
        
        if ($stmt->execute()) {
            echo "<div class='success-msg'>✅ Instructor assigned successfully!</div>";
        } else {
            echo "<div class='error-msg'>❌ Error: " . $mysqli->error . "</div>";
        }
        $stmt->close();
    }
    $check->close();
    $mysqli->close();
}



else{ ?>
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-user-shield"></i> Administrator Dashboard</h1>
                <a href="#edit-form"><button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </button></a>
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
                    <form id="upload-form" method="post" enctype="multipart/form-data" style="margin-top: 10px;">
                        <label for="file-input" class="edit-overlay">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="file-input" name="profile_picture" accept="image/*">
                        <button type="submit" style="margin-top: 10px; padding: 5px 10px;">Upload Photo</button>
                    </form>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo (!empty($_SESSION['admin_phone'])) ? htmlspecialchars($_SESSION['admin_phone']) : 'N/A'; ?></p>
                </div>
            </div>
            
            <?php
            if (!empty($error)): ?>
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
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">1,254</div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number">78</div>
                    <div class="stat-label">Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number">542</div>
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
            
            <!-- Edit Profile Form (always visible) -->
            <div id="edit-form" class="detail-card" style="margin-top: 30px;">
                <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="key">Security Key</label>
                        <input type="text" id="key" name="key" value="<?php echo htmlspecialchars($admin['key']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
            <?php }?>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>