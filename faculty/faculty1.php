<?php
include 'config.php';
// session_start();

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
    $sql = "SELECT * FROM faculty WHERE faculty_id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $faculty = $result->fetch_assoc();
        $stmt->close();
    }
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
}

// Function to count unread notifications
function count_faculty_notices($faculty_id, $mysqli) {
    $sql = "SELECT COUNT(*) AS unread_count 
            FROM notice 
            WHERE section = 'Faculty' AND viewed = 0";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['unread_count'];
    }
    return 0;
}

// Function to show faculty notices with read/unread design
function see_faculty_notice($mysqli) {
    // Mark all faculty notices as read once viewed
    $update = "UPDATE notice 
               SET viewed = 1 
               WHERE section = 'Faculty' AND viewed = 0";
    $stmt = $mysqli->prepare($update);
    $stmt->execute();

    // Get all notices for faculty
    $query = "SELECT * FROM notice 
              WHERE section = 'Faculty'
              ORDER BY created_at DESC";
    $stmt = $mysqli->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='notices-container'>";
        echo "<h2 class='notices-heading'><i class='fas fa-bullhorn'></i> Latest Notices</h2>";

        while ($row = $result->fetch_assoc()) {
            // Different colors for read/unread
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
            echo "</div>"; // Close notice-card
        }

        echo "<div class='back-button-container'>";
        echo "<a href='" . $_SERVER['PHP_SELF'] . "' class='back-button'><i class='fas fa-arrow-left'></i> Back to Dashboard</a>";
        echo "</div>";

        echo "</div>"; // Close notices-container
    } else {
        echo "<div class='no-notices'>";
        echo "<i class='far fa-folder-open'></i>";
        echo "<p>No notices found at this time</p>";
        echo "<a href='" . $_SERVER['PHP_SELF'] . "' class='back-button'><i class='fas fa-arrow-left'></i> Back to Dashboard</a>";
        echo "</div>";
    }
}

// Show notices when button is clicked
if (isset($_GET['show_notices']) && $is_logged_in) {
    // Output the notices and exit to avoid rendering the dashboard
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Faculty Notices</title>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f7fb;
                color: #333;
                line-height: 1.6;
                padding: 20px;
            }
            .notices-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 10px;
                padding: 20px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }
            .notices-heading {
                color: #182848;
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 2px solid #4b6cb7;
            }
            .notice-card {
                border-left: 4px solid #4b6cb7;
                background: #f9fafc;
                margin-bottom: 20px;
                padding: 15px;
                border-radius: 5px;
            }
            .notice-card.unread {
                border-left: 4px solid #ff4757;
                background: #eef2ff;
            }
            .notice-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }
            .notice-title {
                color: #182848;
                margin: 0;
            }
            .notice-section {
                background: #4b6cb7;
                color: white;
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
            }
            .notice-content {
                margin-bottom: 15px;
                color: #666;
            }
            .notice-footer {
                display: flex;
                justify-content: space-between;
                font-size: 14px;
                color: #888;
            }
            .back-button-container {
                text-align: center;
                margin-top: 30px;
            }
            .back-button {
                display: inline-block;
                background: #4b6cb7;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                text-decoration: none;
                transition: all 0.3s;
            }
            .back-button:hover {
                background: #182848;
            }
            .no-notices {
                text-align: center;
                padding: 40px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }
            .no-notices i {
                font-size: 60px;
                color: #ccc;
                margin-bottom: 15px;
            }
        </style>
    </head>
    <body>";
    
    see_faculty_notice($mysqli);
    
    echo "</body>
    </html>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-header h1 {
            color: #182848;
            margin: 10px 0;
        }

        .login-header p {
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #4b6cb7;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #4b6cb7;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }

        .login-btn:hover {
            background: #182848;
        }

        .error-msg {
            color: #ff4757;
            text-align: center;
            margin-top: 15px;
        }

        .success-msg {
            color: #2ed573;
            text-align: center;
            margin-top: 15px;
        }

        .navbar {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo h1 {
            font-size: 24px;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-buttons button {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .nav-buttons button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            color: white;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .main-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: calc(100vh - 80px);
        }

        .sidebar {
            background: white;
            padding: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a, .sidebar-menu button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }

        .sidebar-menu a:hover, .sidebar-menu button:hover,
        .sidebar-menu a.active {
            background-color: #4b6cb7;
            color: white;
        }

        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .content-area {
            padding: 20px;
            background: #f5f7fb;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            color: #182848;
            font-size: 24px;
        }

        .btn-edit {
            background: #4b6cb7;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit:hover {
            background: #182848;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .profile-img-container {
            position: relative;
            width: 150px;
            height: 150px;
        }

        .profile-img, .profile-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f5f7fb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-placeholder {
            background: #4b6cb7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
        }

        .edit-overlay {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #4b6cb7;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .edit-overlay:hover {
            background: #182848;
        }

        #file-input {
            display: none;
        }

        .profile-info h2 {
            color: #182848;
            margin-bottom: 10px;
        }

        .profile-info p {
            margin-bottom: 8px;
            color: #666;
        }

        .profile-info i {
            margin-right: 10px;
            color: #4b6cb7;
            width: 20px;
        }

        .info-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .detail-card h3 {
            color: #182848;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f5f7fb;
        }

        .info-label {
            font-weight: 600;
            color: #4b6cb7;
        }

        .info-value {
            color: #666;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-icon {
            font-size: 36px;
            color: #4b6cb7;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #182848;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .modal-title {
            color: #182848;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #888;
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .modal-form {
            margin-top: 20px;
        }

        .modal-btn {
            background: #4b6cb7;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
        }

        .modal-btn:hover {
            background: #182848;
        }

        .notifications-dropdown {
            display: none;
            position: absolute;
            top: 60px;
            right: 20px;
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }

        .notifications-dropdown.show {
            display: block;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        .notifications-header h3 {
            margin: 0;
            color: #182848;
        }

        .notifications-header a {
            color: #4b6cb7;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .notifications-list {
            padding: 10px;
        }

        .notification-item {
            padding: 15px;
            border-left: 4px solid #4b6cb7;
            background: #f9fafc;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .notification-item.unread {
            background: #eef2ff;
            border-left: 4px solid #ff4757;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #182848;
        }

        .notification-message {
            margin-bottom: 5px;
            color: #666;
        }

        .notification-time {
            font-size: 12px;
            color: #888;
        }

        .no-notifications {
            text-align: center;
            padding: 20px;
            color: #888;
        }

        @media (max-width: 900px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                display: none;
            }
            
            .info-cards {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .profile-card {
                flex-direction: column;
                text-align: center;
            }
            
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            
            .logo h1 {
                font-size: 20px;
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
                <button class="login-btn" onclick="location.href='../index.html'">Sign Out</button>                
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
            <div class="notification-bell" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <?php
                $unread_count = count_faculty_notices($faculty_id, $mysqli);
                if ($unread_count > 0): ?>
                    <span class="notification-count"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </div>
            <button onclick="location.href='?logout=1'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <!-- Notifications Dropdown -->
    <div class="notifications-dropdown" id="notificationsDropdown">
        <div class="notifications-header">
            <h3>Notifications</h3>
            <a href="?show_notices=1">View All</a>
        </div>
        <div class="notifications-list">
            <?php
            // Get recent notices for dropdown
            $query = "SELECT * FROM notice 
                      WHERE section = 'Faculty'
                      ORDER BY created_at DESC 
                      LIMIT 5";
            $stmt = $mysqli->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()): 
                    $isUnread = $row['viewed'] == 0;
                    ?>
                    <div class="notification-item <?php echo $isUnread ? 'unread' : ''; ?>">
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($row['title']); ?></div>
                            <div class="notification-message"><?php echo nl2br(htmlspecialchars($row['content'])); ?></div>
                            <div class="notification-time"><?php echo date('F j, Y g:i A', strtotime($row['created_at'])); ?></div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <p class="no-notifications">No notifications at this time.</p>
            <?php endif; ?>
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
            </ul>
        </div>
        
        <div class="content-area">
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
        
        // Notifications dropdown function
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationsDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                closeEditModal();
            }
            
            // Close notifications dropdown if clicking outside
            const dropdown = document.getElementById('notificationsDropdown');
            if (dropdown.classList.contains('show') && !event.target.matches('.notification-bell') && !event.target.closest('.notification-bell')) {
                dropdown.classList.remove('show');
            }
        };
    </script>
</body>
</html>
<?php
$mysqli->close();
?>