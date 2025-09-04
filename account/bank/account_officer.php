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
    
    // For demo purposes - in real application, use password_verify with hashed passwords
    $sql = "SELECT officer_id, name, email, password FROM bank_officers WHERE email = ? AND status = 'active'";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $name, $email, $hashed_password);
                if ($stmt->fetch()) {
                    // In a real application, verify password with password_verify()
                    // For demo, we'll just check if password field is not empty
                    if (!empty($password)) {
                        $_SESSION['officer_id'] = $id;
                        $_SESSION['officer_name'] = $name;
                        $_SESSION['officer_email'] = $email;
                        
                        // Update last login time
                        $update_sql = "UPDATE bank_officers SET last_login = NOW() WHERE officer_id = ?";
                        if ($update_stmt = $mysqli->prepare($update_sql)) {
                            $update_stmt->bind_param("i", $id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                        
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error = "Invalid password.";
                    }
                }
            } else {
                $error = "Invalid email or account is not active.";
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

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['officer_id'])) {
    $officer_id = $_SESSION['officer_id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    
    $update_sql = "UPDATE bank_officers SET name = ?, phone = ?, department = ?, position = ?, updated_at = NOW() WHERE officer_id = ?";
    
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("ssssi", $name, $phone, $department, $position, $officer_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['officer_name'] = $name;
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=Profile updated successfully");
            exit();
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['officer_id']);

// Get officer data if logged in
if ($is_logged_in) {
    $officer_id = $_SESSION['officer_id'];
    $sql = "SELECT * FROM bank_officers WHERE officer_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $officer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $officer = $result->fetch_assoc();
    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Officers Portal - SKST Bank</title>
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
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .page-title {
            color: #2b5876;
            font-size: 24px;
            font-weight: 600;
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-top: 3px solid;
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
        
        .success-msg {
            color: #00a651;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #ecffec;
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
        
        /* ================ Modal Styles ============ */
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
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-title {
            color: #2b5876;
            font-size: 22px;
            font-weight: 600;
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
        
        .modal-form .form-group {
            margin-bottom: 20px;
        }
        
        .modal-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .modal-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .modal-form input:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            background: #ddd;
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
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .login-form {
                padding: 20px;
            }
            
            .modal-content {
                padding: 20px;
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
                <h1>Bank Officers Portal</h1>
                <p>SKST Bank - Sign in to your account</p>
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
            </form>
        </div>
    </div>
    <?php else: ?>
    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <h1>SKST Bank Officers Portal</h1>
        </div>
        
        
        
        <div class="nav-buttons">
          <button onclick="location.href='../../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <button onclick="location.href='../../working.html'">
                <i class="fas fa-bell"></i> Notifications
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
                    <a href="dev_Student_payments.php">
                        <i class="fas fa-users"></i> Student Accounts
                    </a>
                </li>
                <li>
                    <a href="../../working.html">
                        <i class="fas fa-chalkboard-teacher"></i> Faculty Accounts
                    </a>
                </li>
                <li>
                    <a href="../../working.html">
                        <i class="fas fa-user-tie"></i> Employee Accounts
                    </a>
                </li>
                <li>
                    <a href="../../working.html">
                        <i class="fas fa-cog"></i> Settings
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
                <h1 class="page-title"><i class="fas fa-user-tie"></i> Officer Dashboard</h1>
                <button class="btn-edit" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-msg"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-img-container">
                    <div class="profile-placeholder">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($officer['name']); ?></h2>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($officer['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($officer['phone'] ?? 'Not provided'); ?></p>
                    <p><i class="fas fa-building"></i> <?php echo htmlspecialchars($officer['department'] ?? 'Not provided'); ?></p>
                </div>
            </div>
            
            <!-- Stats Section -->
            
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['name']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['phone'] ?? 'Not provided'); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-briefcase"></i> Professional Information</h3>
                    <div class="info-group">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['department'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Position</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['position'] ?? 'Not provided'); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status</div>
                        <div class="info-value"><?php echo ucfirst($officer['status']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-history"></i> Employment History</h3>
                    <div class="info-group">
                        <div class="info-label">Hire Date</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($officer['hire_date'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Years of Service</div>
                        <div class="info-value"><?php echo date('Y') - date('Y', strtotime($officer['hire_date'])); ?> years</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $officer['last_login'] ? date('M j, Y g:i A', strtotime($officer['last_login'])) : 'Never logged in'; ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-database"></i> System Information</h3>
                    <div class="info-group">
                        <div class="info-label">Officer ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($officer['officer_id']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Account Created</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($officer['created_at'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Updated</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($officer['updated_at'])); ?></div>
                    </div>
                </div>
            </div>
        
        <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo date('Y') - date('Y', strtotime($officer['hire_date'])); ?>+</div>
                    <div class="stat-label">Years of Service</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo ucfirst($officer['status']); ?></div>
                    <div class="stat-label">Account Status</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-number"><?php echo htmlspecialchars($officer['position']); ?></div>
                    <div class="stat-label">Position</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="stat-number"><?php echo $officer['last_login'] ? date('M j', strtotime($officer['last_login'])) : 'Never'; ?></div>
                    <div class="stat-label">Last Login</div>
                </div>
            </div>
    </div>
    
    
    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Profile</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            
            <form class="modal-form" method="post">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label for="edit-name">Full Name</label>
                    <input type="text" id="edit-name" name="name" value="<?php echo htmlspecialchars($officer['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="edit-phone">Phone Number</label>
                    <input type="tel" id="edit-phone" name="phone" value="<?php echo htmlspecialchars($officer['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edit-department">Department</label>
                    <input type="text" id="edit-department" name="department" value="<?php echo htmlspecialchars($officer['department'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="edit-position">Position</label>
                    <input type="text" id="edit-position" name="position" value="<?php echo htmlspecialchars($officer['position'] ?? ''); ?>">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Function to open edit modal
        function openEditModal() {
            document.getElementById('editModal').style.display = 'block';
        }
        
        // Function to close edit modal
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        };
        
        // Add active class to clicked sidebar items
        const sidebarItems = document.querySelectorAll('.sidebar-menu a, .sidebar-menu button');
        sidebarItems.forEach(item => {
            item.addEventListener('click', function() {
                sidebarItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>