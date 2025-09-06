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

// Handle admission officer login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Input validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Prepare SQL statement to get user by email only
        $sql = "SELECT id, full_name, username, password, email, phone, profile_picture, `key` 
                FROM admin_users 
                WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $full_name, $username, $db_password, $email, $phone, $profile_picture, $key);
                    if ($stmt->fetch()) {
                        if ($password === $db_password) {
                            loginUser($id, $full_name, $username, $email, $phone, $profile_picture, $mysqli);
                        } else {
                            $error = "Invalid email or password.";
                        }
                    }
                } else {
                    $error = "Invalid email or password.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
                error_log("Login query failed: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
            error_log("Prepare statement failed: " . $mysqli->error);
        }
    }
}

// Function to handle user login
function loginUser($id, $full_name, $username, $email, $phone, $profile_picture, $mysqli) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    $_SESSION['admission_officer_id'] = $id;
    $_SESSION['admission_officer_name'] = $full_name;
    $_SESSION['admission_officer_username'] = $username;
    $_SESSION['admission_officer_email'] = $email;
    $_SESSION['admission_officer_phone'] = $phone;
    $_SESSION['admission_officer_profile_picture'] = $profile_picture;
    
    // Update last login time
    $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("i", $id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    // Redirect to prevent form resubmission
    header("Location: admission_officer.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['admission_officer_id'])) {
    $officer_id = $_SESSION['admission_officer_id'];
    $uploadDir = 'uploads/admission_officer_pictures/';
    
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
                $update_stmt->bind_param("si", $targetFilePath, $officer_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Update session variable
                $_SESSION['admission_officer_profile_picture'] = $targetFilePath;
                
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['admission_officer_id'])) {
    $officer_id = $_SESSION['admission_officer_id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $key = trim($_POST['key']);
    
    $update_sql = "UPDATE admin_users SET full_name = ?, username = ?, email = ?, phone = ?, `key` = ? WHERE id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("sssssi", $full_name, $username, $email, $phone, $key, $officer_id);
        
        if ($update_stmt->execute()) {
            // Update session variables
            $_SESSION['admission_officer_name'] = $full_name;
            $_SESSION['admission_officer_username'] = $username;
            $_SESSION['admission_officer_email'] = $email;
            $_SESSION['admission_officer_phone'] = $phone;
            
            // Refresh page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password']) && isset($_SESSION['admission_officer_id'])) {
    $officer_id = $_SESSION['admission_officer_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "New password and confirmation do not match.";
    } else {
        // Get current password from database
        $sql = "SELECT password FROM admin_users WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $officer_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();
            
            // Verify current password
            if (password_verify($current_password, $hashed_password) || $current_password === $hashed_password) {
                // Hash new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in database
                $update_sql = "UPDATE admin_users SET password = ? WHERE id = ?";
                if ($update_stmt = $mysqli->prepare($update_sql)) {
                    $update_stmt->bind_param("si", $new_hashed_password, $officer_id);
                    
                    if ($update_stmt->execute()) {
                        $error = "Password changed successfully.";
                    } else {
                        $error = "Error changing password: " . $update_stmt->error;
                    }
                    
                    $update_stmt->close();
                }
            } else {
                $error = "Current password is incorrect.";
            }
        }
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

// Check if admission officer is logged in
$is_logged_in = isset($_SESSION['admission_officer_id']);

// Get admission officer data if logged in
if ($is_logged_in) {
    $officer_id = $_SESSION['admission_officer_id'];
    $sql = "SELECT * FROM admin_users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $officer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $officer = $result->fetch_assoc();
    $stmt->close();
}

$pending_count = 0;
$res = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM update_requests WHERE action = 1");
if ($res) {
    $pending_count = (int) mysqli_fetch_assoc($res)['cnt'];
}


$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Officer Portal - SKST University</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
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
            cursor: pointer;
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
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
                <h1>Admission Officer Portal</h1>
                <p>SKST University - Admission Officer Access</p>
            </div>
            
            <form class="login-form" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Login to Admission Portal</button>
                <button type="button" class="login-btn" onclick="window.location.href='admission.html'">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                
                <div class="login-links">
                    <a href="../admin/forgot_password.php" id="forgotPasswordLink"><i class="fas fa-unlock-alt"></i> Forgot Password?</a>
                    <a href="#" id="helpLink"><i class="fas fa-question-circle"></i> Help</a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
            </form>
        </div>
    </div>

    <!-- Forgot Password Button -->
    <!-- Forgot Password Modal Trigger (button) -->
    <div style="text-align: center; margin-top: 20px;"></div>
        <button type="button" class="login-btn" id="forgotPasswordLink" style="display:inline-block;">
            <i class="fas fa-unlock-alt"></i> Forgot Password
        </button>
    </div>

   
    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-question-circle"></i> Help & Support</h2>
            </div>
            <div class="modal-body">
                <h3>Admission Officer Login Assistance</h3>
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
            <h1 style="color:white; text-align:center;margin:0px;">SKST University Admission Portal</h1>
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
                    <a href="../admission/dev_admission.php">
                        <i class="fas fa-user-graduate"></i> Manage Admissions
                    </a>
                </li>
                <li>
                    <a href="../admin/student_dev.php">
                        <i class="fas fa-user"></i> Students
                    </a>
                </li>
                <li>
                    <a href="../admin/course.php">
                        <i class="fas fa-tasks"></i> Course Menagement
                    </a>
                </li>
                <li>
    <a href="update_request.php">
        <i class="fas fa-chart-bar"></i> Update Request
        <?php if ($pending_count > 0): ?>
            <span style="
                background:#dc3545;
                color:#fff;
                padding:2px 8px;
                border-radius:12px;
                font-size:12px;
                margin-left:6px;
                font-weight:bold;">
                <?php echo $pending_count; ?>
            </span>
        <?php endif; ?>
    </a>
</li>

                <li>
                    <a href="exam_routine.php">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="#" id="changePasswordLink">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </li>
                <li>
                    <button onclick="location.href='?logout=1'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
            </ul>
        </div>
        
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-user-tie"></i> Admission Officer Dashboard</h1>
                <button class="btn-edit" id="editProfileBtn">
                  <a style="text-decoration: none;color:#f0f5ff" href="edit_pp.php">  <i class="fas fa-edit"></i> Edit Profile</a>
                </button>
            </div>

            <!-- Profile Card with Picture Upload -->
            <div class="profile-card">
                <div class="profile-img-container">
                    <?php if (!empty($_SESSION['admission_officer_profile_picture'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($_SESSION['admission_officer_profile_picture']); ?>" alt="Profile Image">
                    <?php else: ?>
                        <div id="profile-placeholder" class="profile-placeholder">
                            <i class="fas fa-user-tie"></i>
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
                    <h2><?php echo htmlspecialchars($_SESSION['admission_officer_name']); ?></h2>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admission_officer_username']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['admission_officer_email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($_SESSION['admission_officer_phone']); ?></p>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    <div class="info-group">
                        <div class="info-label">Officer ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['id']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['username']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security Key</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['key']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-user-tie"></i> Personal Information</h3>
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['full_name']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['phone']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-calendar-alt"></i> Account Activity</h3>
                    <div class="info-group">
                        <div class="info-label">Registration Date</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($officer['registration_date'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $officer['last_login'] ? date('M j, Y g:i A', strtotime($officer['last_login'])) : 'First login'; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span style="color: #00a651;"><?php echo $officer['is_active'] ? 'Active' : 'Inactive'; ?></span></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-shield-alt"></i> Officer Privileges</h3>
                    <div class="info-group">
                        <div class="info-label">User Level</div>
                        <div class="info-value">Admission Officer</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Access Rights</div>
                        <div class="info-value">Student Admission Management</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security</div>
                        <div class="info-value">Standard Authentication</div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Section -->
             
    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-key"></i> Change Password</h2>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-body">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="password-container">
                            <input type="password" id="current_password" name="current_password" required>
                            <i class="fa-solid fa-eye toggle-password" id="toggleCurrentPassword"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="password-container">
                            <input type="password" id="new_password" name="new_password" required>
                            <i class="fa-solid fa-eye toggle-password" id="toggleNewPassword"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-container">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <i class="fa-solid fa-eye toggle-password" id="toggleConfirmPassword"></i>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelPassword">Cancel</button>
                    <button type="submit" class="btn-save">Change Password</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggles
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('password');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
            if (toggleCurrentPassword) {
                toggleCurrentPassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('current_password');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            if (toggleNewPassword) {
                toggleNewPassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('new_password');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            if (toggleConfirmPassword) {
                toggleConfirmPassword.addEventListener('click', function() {
                    const passwordInput = document.getElementById('confirm_password');
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
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
            
            // Change Password Modal functionality
            const changePasswordLink = document.getElementById("changePasswordLink");
            const changePasswordModal = document.getElementById("changePasswordModal");
            const cancelPassword = document.getElementById("cancelPassword");
            const closePassword = changePasswordModal ? changePasswordModal.getElementsByClassName("close")[0] : null;
            
            if (changePasswordLink && changePasswordModal) {
                changePasswordLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    changePasswordModal.style.display = "block";
                });
            }
            
            if (cancelPassword) {
                cancelPassword.onclick = function() {
                    changePasswordModal.style.display = "none";
                }
            }
            
            if (closePassword) {
                closePassword.onclick = function() {
                    changePasswordModal.style.display = "none";
                }
            }
            
            // Forgot Password Modal
            const forgotPasswordLink = document.getElementById("forgotPasswordLink");
            const forgotPasswordModal = document.getElementById("forgotPasswordModal");
            const cancelReset = document.getElementById("cancelReset");
            const closeForgot = forgotPasswordModal ? forgotPasswordModal.getElementsByClassName("close")[0] : null;
            
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
            
            if (closeForgot) {
                closeForgot.onclick = function() {
                    forgotPasswordModal.style.display = "none";
                }
            }
            
            // Help Modal
            const helpLink = document.getElementById("helpLink");
            const helpModal = document.getElementById("helpModal");
            const closeHelp = document.getElementById("closeHelp");
            const closeHelpModal = helpModal ? helpModal.getElementsByClassName("close")[0] : null;
            
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
            
            if (closeHelpModal) {
                closeHelpModal.onclick = function() {
                    helpModal.style.display = "none";
                }
            }
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (modal && event.target == modal) {
                    modal.style.display = "none";
                }
                if (forgotPasswordModal && event.target == forgotPasswordModal) {
                    forgotPasswordModal.style.display = "none";
                }
                if (helpModal && event.target == helpModal) {
                    helpModal.style.display = "none";
                }
                if (changePasswordModal && event.target == changePasswordModal) {
                    changePasswordModal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>