<?php
include 'config.php';

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
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $room_number = $_POST['room_number'];
    
    $update_sql = "UPDATE faculty SET phone = ?, address = ?, room_number = ? WHERE faculty_id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("sssi", $phone, $address, $room_number, $faculty_id);
        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        $update_stmt->close();
        
        // Refresh page to show updated data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['faculty_id']);

// Get faculty data if logged in
if ($is_logged_in) {
    $faculty_id = $_SESSION['faculty_id'];
    
    // Get faculty info
    $faculty_sql = "SELECT * FROM faculty WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($faculty_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $faculty_result = $stmt->get_result();
    $faculty = $faculty_result->fetch_assoc();
    $stmt->close();
}

// Get course count for stats
if ($is_logged_in) {
    $course_count_sql = "SELECT COUNT(*) as course_count FROM course_instructor WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($course_count_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $course_count_result = $stmt->get_result();
    $course_count = $course_count_result->fetch_assoc()['course_count'];
    $stmt->close();
    
    // Get student count
    $student_count_sql = "SELECT COUNT(DISTINCT student_id) as student_count 
                         FROM enrollments WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($student_count_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $student_count_result = $stmt->get_result();
    $student_count = $student_count_result->fetch_assoc()['student_count'];
    $stmt->close();
    
    // Get unread notification count - Filtering by section instead of faculty_id
    $notification_count_sql = "SELECT COUNT(*) as notification_count FROM notice 
                              WHERE section = 'Faculty' AND viewed = 0 ";
    $stmt = $mysqli->prepare($notification_count_sql);
    $stmt->execute();
    $notification_count_result = $stmt->get_result();
    $notification_count = $notification_count_result->fetch_assoc()['notification_count'];
    $stmt->close();
}

// Handle notification view
if (isset($_GET['view_notifications']) && $is_logged_in) {
    // Mark all faculty notifications as viewed
    $update_sql = "UPDATE notice SET viewed = 1 
                  WHERE section = 'Faculty' AND viewed = 0 AND id=$faculty_id";
    $stmt = $mysqli->prepare($update_sql);
    $stmt->execute();
    $stmt->close();
    
    // Reset notification count
    $notification_count = 0;
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

        /* Login Styles */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            padding: 20px;
        }

        .login-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .login-header {
            background: #1a2a6c;
            color: white;
            text-align: center;
            padding: 30px 20px;
        }

        .login-header h1 {
            margin: 15px 0 5px;
            font-size: 24px;
        }

        .login-header p {
            opacity: 0.8;
        }

        .login-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #1a2a6c;
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #1a2a6c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 10px;
        }

        .login-btn:hover {
            background: #2a3a8c;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }

        .success-msg {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
        }

        /* Dashboard Styles */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1a2a6c;
            padding: 15px 30px;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo h1 {
            font-size: 22px;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-buttons button, .nav-buttons a {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .nav-buttons button:hover, .nav-buttons a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-badge {
            background: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            margin-left: 5px;
        }

        .main-layout {
            display: flex;
            min-height: calc(100vh - 70px);
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 16px;
        }

        .sidebar-menu a:hover, .sidebar-menu button:hover {
            background: #34495e;
        }

        .sidebar-menu a.active {
            background: #1a2a6c;
            border-left: 4px solid #fdbb2d;
        }

        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: #1a2a6c;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-edit {
            background: #1a2a6c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.3s;
        }

        .btn-edit:hover {
            background: #2a3a8c;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-img-container {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .profile-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            color: #777;
        }

        .edit-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #1a2a6c;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background 0.3s;
        }

        .edit-overlay:hover {
            background: #2a3a8c;
        }

        #file-input {
            display: none;
        }

        .profile-info h2 {
            color: #1a2a6c;
            margin-bottom: 10px;
        }

        .profile-info p {
            margin-bottom: 8px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .detail-card h3 {
            color: #1a2a6c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: #f0f7ff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px;
            font-size: 20px;
            color: #1a2a6c;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #1a2a6c;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #777;
        }

        /* Modal Styles */
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
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .modal-title {
            background: #1a2a6c;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .modal-form {
            padding: 20px;
        }

        .modal-btn {
            background: #1a2a6c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .modal-btn:hover {
            background: #2a3a8c;
        }

        /* Notices Styles */
        .notices-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .notices-heading {
            color: #1a2a6c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notice-card {
            border-left: 4px solid #1a2a6c;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 0 5px 5px 0;
        }

        .notice-card.unread {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }

        .notice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .notice-title {
            color: #1a2a6c;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notice-section {
            background: #1a2a6c;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .notice-content {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .notice-footer {
            display: flex;
            justify-content: space-between;
            color: #777;
            font-size: 14px;
        }

        .notice-author, .notice-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-button-container {
            margin-top: 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1a2a6c;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: #2a3a8c;
        }

        .no-notices {
            text-align: center;
            padding: 40px 20px;
            color: #777;
        }

        .no-notices i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .info-cards, .stats {
                grid-template-columns: 1fr;
            }
            
            .profile-card {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
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
                <button class= "login-btn" onclick="location.href='../index.html'"> Sign Out</button>                
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
            <h1 style="color: white;">SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <a href="?view_notifications=1" class="notification-link">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="faculty1.php" class="active">
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
                    <a href="attendance.php">
                        <i class="fas fa-user-check"></i> Attendance
                    </a>
                </li>
                <li>
                    <a href="Result.php">
                        <i class="fas fa-user-check"></i> Result
                    </a>
                </li>
                <li>
                    <a href="materials.php">
                        <i class="fas fa-file-alt"></i> Materials
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
            <?php if (isset($_GET['view_notifications'])): ?>
                <!-- Notifications Page -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-bell"></i> Notifications</h1>
                    <button class="btn-edit" onclick="location.href='faculty1.php'">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </button>
                </div>

                <div class="notices-container">
                    <h2 class="notices-heading"><i class="fas fa-bullhorn"></i> Latest Notices</h2>
                    
                    <?php
                    // Reconnect to database for notifications
                    $mysqli = new mysqli('localhost','root','','skst_university');
                    
                    // Get all faculty notices
                      $query = "SELECT * FROM notice 
                              WHERE section = 'Faculty'
                              ORDER BY created_at DESC";
                    $stmt = $mysqli->prepare($query);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $cardClass = $row['viewed'] == 0 ? "notice-card unread" : "notice-card read";
                            echo "<div class='$cardClass'>";
                            echo "<div class='notice-header'>";
                            echo "<h3 class='notice-title'><i class='fas fa-chevron-circle-right'></i> " . htmlspecialchars($row['title']) . "</h3>";
                            echo "<span class='notice-section'>" . htmlspecialchars($row['section']) . "</span>";
                            echo "</div>";
                            echo "<div class='notice-content'>" . nl2br(htmlspecialchars($row['content'])) . "</div>";
                            echo "<div class='notice-footer'>";
                            echo "<span class='notice-author'><i class='fas fa-user'></i> " . htmlspecialchars($row['author']) . "</span>";
                            echo "<span class='notice-date'><i class='far fa-calendar-alt'></i> " . date('F j, Y h:i A', strtotime($row['created_at'])) . "</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='no-notices'>";
                        echo "<i class='far fa-folder-open'></i>";
                        echo "<p>No notices found at this time</p>";
                        echo "</div>";
                    }
                    $stmt->close();
                    $mysqli->close();
                    ?>
                </div>
            <?php else: ?>
                <!-- Profile Page -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-chalkboard-teacher"></i> Faculty Dashboard</h1>
                    <button class="btn-edit" onclick="openEditModal()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success-msg"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Profile Card with Picture Upload -->
                <div class="profile-card">
                    <div class="profile-img-container">
                        <?php if (!empty($faculty['profile_picture'])): ?>
                            <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($faculty['profile_picture']); ?>" alt="Profile Image">
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
                        <h2><?php echo htmlspecialchars($faculty['name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($faculty['email']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($faculty['phone'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($faculty['address'] ?? 'Not provided'); ?></p>
                    </div>
                </div>
                
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
                        <div class="stat-number"><?php echo $course_count ?? 0; ?></div>
                        <div class="stat-label">Courses</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $student_count ?? 0; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo ($course_count ?? 0) * 3; // Assuming 3 hours per course ?></div>
                        <div class="stat-label">Hours/Week</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number">4.8</div>
                        <div class="stat-label">Rating</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
            <h2 class="modal-title"><i class="fas fa-user-edit"></i> Edit Profile Information</h2>
            
            <form class="modal-form" method="post" action="">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="form-group">
                    <label for="edit-phone">Phone Number</label>
                    <input type="text" id="edit-phone" name="phone" value="<?php echo htmlspecialchars($faculty['phone'] ?? ''); ?>" placeholder="Enter phone number">
                </div>
                
                <div class="form-group">
                    <label for="edit-address">Address</label>
                    <textarea id="edit-address" name="address" rows="3" placeholder="Enter your address"><?php echo htmlspecialchars($faculty['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-room">Room Number</label>
                    <input type="text" id="edit-room" name="room_number" value="<?php echo htmlspecialchars($faculty['room_number'] ?? ''); ?>" placeholder="Enter room number">
                </div>
                
                <button type="submit" class="modal-btn">Update Profile</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle profile picture upload
            const fileInput = document.getElementById('file-input');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        document.getElementById('upload-form').submit();
                    }
                });
            }
        });
        
        // Modal functions
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                closeEditModal();
            }
        };
    </script>
</body>
</html>