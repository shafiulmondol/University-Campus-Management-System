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

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT faculty_id, name, email FROM faculty WHERE email = ? AND password = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ss", $email, $password);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $name, $email);
                if ($stmt->fetch()) {
                    $_SESSION['faculty_id'] = $id;
                    $_SESSION['faculty_name'] = $name;
                    $_SESSION['faculty_email'] = $email;
                    
                    // Update last login time
                    $update_sql = "UPDATE faculty SET last_login = NOW() WHERE faculty_id = ?";
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

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['faculty_id'])) {
    $faculty_id = $_SESSION['faculty_id'];
    $uploadDir = 'uploads/faculty_pictures/';
    
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
            $update_sql = "UPDATE faculty SET profile_picture = ? WHERE faculty_id = ?";
            if ($update_stmt = $mysqli->prepare($update_sql)) {
                $update_stmt->bind_param("si", $targetFilePath, $faculty_id);
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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['faculty_id'])) {
    $faculty_id = $_SESSION['faculty_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $room_number = trim($_POST['room_number']);
    $department = trim($_POST['department']);
    
    $update_sql = "UPDATE faculty SET name = ?, email = ?, phone = ?, address = ?, room_number = ?, department = ? WHERE faculty_id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("ssssssi", $name, $email, $phone, $address, $room_number, $department, $faculty_id);
        
        if ($update_stmt->execute()) {
            // Update session variables
            $_SESSION['faculty_name'] = $name;
            $_SESSION['faculty_email'] = $email;
            
            // Refresh page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating profile: " . $mysqli->error;
        }
        
        $update_stmt->close();
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['faculty_id']);

// Get faculty data if logged in
if ($is_logged_in) {
    $faculty_id = $_SESSION['faculty_id'];
    $sql = "SELECT * FROM faculty WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();
    
    // Get statistics data
    // Courses count
    $courses_sql = "SELECT COUNT(*) as course_count FROM course_instructor WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($courses_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $courses_result = $stmt->get_result();
    $courses_data = $courses_result->fetch_assoc();
    $course_count = $courses_data['course_count'] ?? 0;
    $stmt->close();
    
    // Students count
    $students_sql = "SELECT COUNT(DISTINCT e.student_id) as student_count 
                     FROM enrollments e 
                     JOIN course_instructor ci ON e.course_id = ci.course_id 
                     WHERE ci.faculty_id = ?";
    $stmt = $mysqli->prepare($students_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $students_result = $stmt->get_result();
    $students_data = $students_result->fetch_assoc();
    $student_count = $students_data['student_count'] ?? 0;
    $stmt->close();
    
    // Hours per week (assuming each class is 1 hour)
    $hours_sql = "SELECT COUNT(*) as hours_count FROM course_instructor WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($hours_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $hours_result = $stmt->get_result();
    $hours_data = $hours_result->fetch_assoc();
    $hours_count = $hours_data['hours_count'] ?? 0;
    $stmt->close();
    
    // Rating (static for now as there's no rating system in the database)
    $rating = 4.8;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Portal - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* All the existing CSS styles remain exactly the same */
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
        
        /* Edit Form Styles */
        .edit-form {
            display: none;
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .edit-form h2 {
            color: #2b5876;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-col {
            flex: 1;
            min-width: 250px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-cancel:hover {
            opacity: 0.9;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-save:hover {
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
            
            .form-row {
                flex-direction: column;
            }
            
            .form-col {
                width: 100%;
            }
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
                <h1>Faculty Portal</h1>
                <p>SKST University - Sign in to your account</p>
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
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="demo-credentials">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Email: 23303105@iubat.edu</p>
                    <p>Password: kawsar</p>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <button onclick="location.href='?logout=1'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="courses.php">
                        <i class="fas fa-book"></i> Courses
                    </a>
                </li>
                <li>
                    <a href="schedule.php">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                </li>
                <li>
                    <a href="classes.php">
                        <i class="fas fa-chalkboard"></i> Classes
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fas fa-user-check"></i> Attendance
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
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
                <h1 class="page-title"><i class="fas fa-chalkboard-teacher"></i> Faculty Dashboard</h1>
                <button class="btn-edit" id="toggleEditBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <!-- Profile Card with Picture Upload -->
            <div class="profile-card" id="profileView">
                <div class="profile-img-container">
                    <?php if (!empty($faculty['profile_picture'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($faculty['profile_picture']); ?>" alt="Profile Image">
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
                    <h2><?php echo htmlspecialchars($faculty['name']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($faculty['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($faculty['phone'] ?? 'Not provided'); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($faculty['address'] ?? 'Not provided'); ?></p>
                </div>
            </div>
            
            <!-- Edit Profile Form -->
            <div class="edit-form" id="profileEdit">
                <h2><i class="fas fa-edit"></i> Edit Profile</h2>
                <form method="post">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($faculty['name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($faculty['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($faculty['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($faculty['address'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="room_number">Room Number</label>
                                <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($faculty['room_number'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($faculty['department'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-building"></i> Department Information</h3>
                    <div class="info-group">
                        <div class="info-label">Faculty ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['faculty_id']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['department'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Room Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['room_number'] ?? 'Not provided'); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-money-check-alt"></i> Salary Information</h3>
                    <div class="info-group">
                        <div class="info-label">Salary</div>
                        <div class="info-value">$<?php echo isset($faculty['salary']) ? number_format($faculty['salary'], 2) : 'Not provided'; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value">Direct Deposit</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Pay Schedule</div>
                        <div class="info-value">Monthly</div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-address-card"></i> Contact Information</h3>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['phone'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['address'] ?? 'Not provided'); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    <div class="info-group">
                        <div class="info-label">Faculty Since</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($faculty['registration_date'] ?? 'now')); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $faculty['last_login'] ? date('M j, Y g:i A', strtotime($faculty['last_login'])) : 'First login'; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span style="color: #00a651;">Active</span></div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="page-header">
                <h2 class="page-title"><i class="fas fa-chart-line"></i> Teaching Statistics</h2>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?php echo $course_count; ?></div>
                    <div class="stat-label">Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $student_count; ?></div>
                    <div class="stat-label">Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $hours_count; ?></div>
                    <div class="stat-label">Hours/Week</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number"><?php echo $rating; ?></div>
                    <div class="stat-label">Rating</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Simple JavaScript for interactive elements
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
            
            // Toggle edit profile form
            const toggleEditBtn = document.getElementById('toggleEditBtn');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const profileView = document.getElementById('profileView');
            const profileEdit = document.getElementById('profileEdit');
            
            if (toggleEditBtn && profileView && profileEdit) {
                toggleEditBtn.addEventListener('click', function() {
                    profileView.style.display = 'none';
                    profileEdit.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-eye"></i> View Profile';
                    this.setAttribute('data-editing', 'true');
                });
                
                // If cancel button exists, handle it
                if (cancelEditBtn) {
                    cancelEditBtn.addEventListener('click', function() {
                        profileView.style.display = 'flex';
                        profileEdit.style.display = 'none';
                        toggleEditBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                        toggleEditBtn.setAttribute('data-editing', 'false');
                    });
                }
                
                // Check if we're returning from a form submission with errors
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('edit')) {
                    profileView.style.display = 'none';
                    profileEdit.style.display = 'block';
                    toggleEditBtn.innerHTML = '<i class="fas fa-eye"></i> View Profile';
                    toggleEditBtn.setAttribute('data-editing', 'true');
                }
            }
        });
    </script>
</body>
</html>