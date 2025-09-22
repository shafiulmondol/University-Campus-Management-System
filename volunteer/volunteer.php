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

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $volunteer_sl = $_SESSION['volunteer_sl'];
    
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . "volunteer_" . $volunteer_sl . "." . $imageFileType;
    
    // Check if image file is an actual image
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if ($check === false) {
        $error = "File is not an image.";
    } 
    // Check file size (max 2MB)
    elseif ($_FILES["profile_picture"]["size"] > 2000000) {
        $error = "Sorry, your file is too large. Max size is 2MB.";
    }
    // Allow certain file formats
    elseif (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    // Upload file if no errors
    elseif (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        // Update database with profile picture path
        $sql = "UPDATE volunteers SET profile_picture = ? WHERE sl = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("si", $target_file, $volunteer_sl);
            $stmt->execute();
            $stmt->close();
            
            // Refresh page to show new image
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // For demo purposes - in real application, use password_verify with hashed passwords
    $sql = "SELECT sl, student_id, student_name, email, profile_picture FROM volunteers WHERE email = ? AND password = ?";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ss", $email, $password);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($sl, $student_id, $student_name, $email, $profile_picture);
                if ($stmt->fetch()) {
                    $_SESSION['volunteer_sl'] = $sl;
                    $_SESSION['volunteer_id'] = $student_id;
                    $_SESSION['volunteer_name'] = $student_name;
                    $_SESSION['volunteer_email'] = $email;
                    $_SESSION['profile_picture'] = $profile_picture;
                    
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

// Check if user is logged in
$is_logged_in = isset($_SESSION['volunteer_sl']);

// Get volunteer data if logged in
if ($is_logged_in) {
    $volunteer_sl = $_SESSION['volunteer_sl'];
    $sql = "SELECT * FROM volunteers WHERE sl = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $volunteer_sl);
    $stmt->execute();
    $result = $stmt->get_result();
    $volunteer = $result->fetch_assoc();
    $stmt->close();
    
    // Get volunteer statistics
    $stats_sql = "SELECT 
                    COUNT(*) as total_activities,
                    SUM(hours) as total_hours,
                    MIN(activity_date) as first_activity,
                    MAX(activity_date) as last_activity
                  FROM volunteers 
                  WHERE student_id = ? OR email = ?";
    $stats_stmt = $mysqli->prepare($stats_sql);
    $stats_stmt->bind_param("is", $volunteer['student_id'], $volunteer['email']);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();
    
    // Get all activities for this volunteer
    $activities_sql = "SELECT activity_name, activity_date, role, hours, remarks 
                       FROM volunteers 
                       WHERE student_id = ? OR email = ? 
                       ORDER BY activity_date DESC";
    $activities_stmt = $mysqli->prepare($activities_sql);
    $activities_stmt->bind_param("is", $volunteer['student_id'], $volunteer['email']);
    $activities_stmt->execute();
    $activities_result = $activities_stmt->get_result();
    $activities = [];
    while ($row = $activities_result->fetch_assoc()) {
        $activities[] = $row;
    }
    $activities_stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Portal - SKST University</title>
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
            margin-bottom: 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            background: #2b5876;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .edit-overlay:hover {
            background: #4e4376;
            transform: scale(1.1);
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
        
        /* ================ Stats Section ============ */
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex: 1;
            min-width: 180px;
            transition: all 0.3s ease;
            border-top: 3px solid;
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
        
        /* ================ Activities Table ============ */
        .activities-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .activities-card h3 {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
            display: flex;
            align-items: center;
        }
        
        .activities-card h3 i {
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
        
        .activities-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .activities-table th, 
        .activities-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .activities-table th {
            background-color: #f8f9fa;
            color: #2b5876;
            font-weight: 600;
        }
        
        .activities-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .activities-table .role {
            background: #e8f4ff;
            color: #2b5876;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
        
        .signup-btn {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .signup-btn:hover {
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
        
        .signup-prompt {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        
        .signup-prompt p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .signup-link {
            color: #2b5876;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .signup-link:hover {
            color: #4e4376;
            text-decoration: underline;
        }
        
        /* Modal Styles for Image Upload */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            color: #2b5876;
            font-size: 22px;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .upload-btn {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        /* ================ Responsive Design ============ */
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
            
            .stats {
                flex-direction: column;
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
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .activities-table {
                display: block;
                overflow-x: auto;
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
                <h1>Volunteer Portal</h1>
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
                
                <button type="button" class="signup-btn" onclick="window.location.href='volunteer_form.php'">
                    <i class="fas fa-user-plus"></i> Sign Up for New Account
                </button>
                
                <div class="signup-prompt">
                    <p>Don't have an account yet?</p>
                    <a href="volunteer_form.php" class="signup-link">Create a new volunteer account</a>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <h1>SKST University Volunteers</h1>
        </div>
        
        <div class="nav-buttons">
            <button type="button" onclick="history.back();"><i class="fas fa-arrow-left"></i> Back</button>
            <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
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
                    <a href="upcoming_event.php">
                        <i class="fas fa-calendar"></i> Upcoming Events
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
                <h1 class="page-title"><i class="fas fa-hands-helping"></i> Volunteer Dashboard</h1>
            </div>

            <!-- Profile Card with Picture Upload -->
            <div class="profile-card">
                <div class="profile-img-container">
                    <?php if (!empty($volunteer['profile_picture']) && file_exists($volunteer['profile_picture'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($volunteer['profile_picture']); ?>" alt="Profile Image">
                    <?php else: ?>
                        <div id="profile-placeholder" class="profile-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="edit-overlay" onclick="openModal()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($volunteer['student_name']); ?></h2>
                    <p><i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($volunteer['student_id'] ?? 'Not provided'); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($volunteer['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($volunteer['phone'] ?? 'Not provided'); ?></p>
                    <p><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($volunteer['department'] ?? 'Not specified'); ?></p>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Statistics Section -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_activities'] ?? 0; ?></div>
                    <div class="stat-label">Total Activities</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['total_hours'] ?? 0; ?></div>
                    <div class="stat-label">Total Hours</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['first_activity'] ? date('M Y', strtotime($stats['first_activity'])) : 'N/A'; ?></div>
                    <div class="stat-label">First Activity</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-star"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['last_activity'] ? date('M Y', strtotime($stats['last_activity'])) : 'N/A'; ?></div>
                    <div class="stat-label">Latest Activity</div>
                </div>
            </div>
            
            <!-- Activities Table -->
            <div class="activities-card">
                <h3><i class="fas fa-tasks"></i> Volunteer Activities</h3>
                
                <table class="activities-table">
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                            <th>Date</th>
                            <th>Role</th>
                            <th>Hours</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['activity_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($activity['activity_date'])); ?></td>
                                    <td><span class="role"><?php echo htmlspecialchars($activity['role']); ?></span></td>
                                    <td><?php echo $activity['hours']; ?> hrs</td>
                                    <td><?php echo htmlspecialchars($activity['remarks']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No volunteer activities found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal for Image Upload -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Profile Picture</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form class="upload-form" method="post" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*" required>
                <button type="submit" class="upload-btn">Upload Picture</button>
            </form>
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
        });
        
        // Modal functions
        function openModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('uploadModal').style.display = 'none';
        }
        
        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('uploadModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>