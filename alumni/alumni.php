<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

$error = "";
$success = "";
$showSignup = false;
$showReset = false;
$editMode = false;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: alumni.php");
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $graduation_year = $_POST['graduation_year'];
    $degree = $_POST['degree'];
    $current_job = $_POST['current_job'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $aid = $_SESSION['alumni_id'];

    $stmt = $conn->prepare("UPDATE alumni SET name=?, email=?, graduation_year=?, degree=?, current_job=?, phone=?, address=? WHERE alumni_id=?");
    $stmt->bind_param("sssssssi", $name, $email, $graduation_year, $degree, $current_job, $phone, $address, $aid);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['alumni_name'] = $name;
        // Refresh alumni data
        $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
        $stmt->bind_param("i", $aid);
        $stmt->execute();
        $result = $stmt->get_result();
        $alumni = $result->fetch_assoc();
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
    $editMode = true; // Stay in edit mode
}

// Handle sign-up
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
        $showSignup = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
        $showSignup = true;
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
        $showSignup = true;
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM alumni WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
            $showSignup = true;
        } else {
            // Insert new alumni
            $stmt = $conn->prepare("INSERT INTO alumni (name, email, password, registration_date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $password);
            
            if ($stmt->execute()) {
                $success = "Registration successful! Please login.";
                $showSignup = false;
            } else {
                $error = "Registration failed: " . $conn->error;
                $showSignup = true;
            }
        }
    }
}

// Handle password reset
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset'])) {
    $email = $_POST['email'];
    
    if (empty($email)) {
        $error = "Please enter your email";
        $showReset = true;
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM alumni WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $success = "Password reset instructions have been sent to your email";
            $showReset = false;
        } else {
            $error = "No account found with that email";
            $showReset = true;
        }
    }
}

if (!isset($_SESSION['alumni_id']) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM alumni WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) {
            $_SESSION['alumni_id'] = $row['alumni_id'];
            $_SESSION['alumni_name'] = $row['name'];

            $current_datetime = date('Y-m-d H:i:s');
            $upd = $conn->prepare("UPDATE alumni SET last_login = ? WHERE alumni_id = ?");
            $upd->bind_param("si", $current_datetime, $row['alumni_id']);
            $upd->execute();
            $upd->close();

            header("Location: alumni.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Alumni not found.";
    }
}

if (isset($_GET['signup'])) {
    $showSignup = true;
}

if (isset($_GET['reset'])) {
    $showReset = true;
}

if (isset($_GET['edit'])) {
    $editMode = true;
}

if (isset($_SESSION['alumni_id'])) {
    $aid = $_SESSION['alumni_id'];
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumni = $result->fetch_assoc();
    $stmt->close();
}
// Don't close connection here - we need it for other sections
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Alumni Portal</title>
    <link rel="icon" href="picture/SKST.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern Color Palette */
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #06d6a0;
            --background: #f8f9fc;
            --card-bg: #ffffff;
            --text-dark: #2d3748;
            --text-light: #718096;
            --border: #e2e8f0;
            --success: #06d6a0;
            --warning: #ffd166;
            --danger: #ef476f;
            --sidebar-bg: #1e293b;
            --sidebar-active: #334155;
        }
        
        * {
            box-sizing: border-box;
            margin: 0; 
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.6;
            position: relative;
        }
        
        a {
            color: inherit;
            text-decoration: none;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        /* Close button - only for forms */
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: black;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s ease;
            border: none;
        }
        
        .close-btn:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        
        /* Welcome Dashboard */
        .welcome-msg {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 50px;
            position: relative;
            color: var(--text-dark);
        }
        
        .welcome-msg .emoji {
            font-size: 3rem;
            margin-right: 10px;
            color: var(--primary);
        }
        
        .cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            width: 260px;
            padding: 30px 25px;
            text-align: center;
            font-weight: 600;
            font-size: 1.3rem;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-dark);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(67, 97, 238, 0.15);
        }
        
        .card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .card:hover::after {
            transform: scaleX(1);
        }
        
        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: var(--primary);
        }
        
        .card-desc {
            font-weight: 400;
            font-size: 1rem;
            margin-top: 10px;
            color: var(--text-light);
        }
        
        /* Layout Sidebar + Content */
        .layout {
            display: flex;
            gap: 25px;
            margin-top: 40px;
            position: relative;
        }
        
        .sidebar {
            background: var(--sidebar-bg);
            border-radius: 16px;
            width: 280px;
            padding: 30px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 12px;
            height: fit-content;
        }
        
        .sidebar h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: white;
            text-align: center;
        }
        
        .sidebar a {
            padding: 15px 20px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 1.1rem;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            color: #cbd5e1;
        }
        
        .sidebar a:hover,
        .sidebar a.active {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .sidebar a .icon {
            font-size: 1.5rem;
            width: 24px;
            text-align: center;
        }
        
        main.content {
            flex-grow: 1;
            background: var(--card-bg);
            color: var(--text-dark);
            border-radius: 16px;
            padding: 35px 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            min-height: 520px;
            border: 1px solid var(--border);
        }
        
        /* Content Headers */
        .content h2 {
            font-size: 1.8rem;
            margin-bottom: 25px;
            color: var(--primary);
            position: relative;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--primary);
            border-radius: 4px;
        }
        
        /* Profile */
        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-info p {
            font-size: 1.1rem;
            margin: 15px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
            display: flex;
        }
        
        .profile-info p strong {
            color: var(--primary);
            font-weight: 600;
            width: 140px;
            display: inline-block;
        }
        
        .edit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .edit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .edit-btn i {
            font-size: 1.1rem;
        }
        
        /* Profile Edit Form */
        .edit-form {
            display: none;
            margin-top: 20px;
        }
        
        .edit-form.active {
            display: block;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-outline:hover {
            background: rgba(67, 97, 238, 0.1);
        }
        
        /* Forms */
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: var(--card-bg);
            border-radius: 5px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            border: 1px solid var(--border);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 5px;
        }
        
        .form-header h2 {
            font-size: 2rem;
            color: var(--primary);
        }
        
        .form-header p {
            color: var(--text-light);
            font-size: 1.05rem;
        }
        
        .input-group {
            margin-bottom: 2px;
        }
        
        .input-group label {
            display: block;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .form-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
            margin-top: 5px;
        }
        
        .form-btn:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background: rgba(67, 97, 238, 0.1);
        }
        
        .form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.95rem;
        }
        
        .form-footer a {
            color: var(--primary);
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .form-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .error-msg {
            color: var(--danger);
            font-weight: 600;
            margin: -15px 0 20px;
            text-align: center;
            background: rgba(239, 71, 111, 0.1);
            padding: 10px;
            border-radius: 8px;
        }
        
        .success-msg {
            color: var(--success);
            font-weight: 600;
            margin: -15px 0 20px;
            text-align: center;
            background: rgba(6, 214, 160, 0.1);
            padding: 10px;
            border-radius: 8px;
        }
        
        /* Toggle forms */
        .form-toggle {
            display: none;
        }
        
        .active-form {
            display: block;
        }
        
        /* Degree and Internship sections */
        .degree-info, .internship-info {
            margin-top: 20px;
        }
        
        .degree-item, .internship-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }
        
        .degree-item h3, .internship-item h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .degree-item p, .internship-item p {
            margin: 5px 0;
            color: var(--text-light);
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .container {
                margin: 20px auto;
                padding: 0 15px;
            }
            
            .cards {
                flex-direction: column;
                align-items: center;
                gap: 20px;
            }
            
            .card {
                width: 100%;
                max-width: 400px;
            }
            
            .layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                padding: 15px 10px;
                border-radius: 12px;
                gap: 8px;
                overflow-x: auto;
            }
            
            .sidebar h2 {
                display: none;
            }
            
            .sidebar a {
                flex: 0 0 auto;
                padding: 12px 15px;
                font-size: 0.95rem;
                justify-content: center;
                gap: 8px;
                border-radius: 8px;
            }
            
            .sidebar a span:not(.icon) {
                display: none;
            }
            
            main.content {
                margin-top: 20px;
                padding: 25px 20px;
                border-radius: 12px;
                min-height: auto;
            }
            
            .form-container {
                margin: 30px auto;
                padding: 30px 25px;
                max-width: 90%;
            }
            
            .close-btn {
                top: 15px;
                right: 15px;
                width: 35px;
                height: 35px;
                font-size: 1.3rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        /* Utilities */
        .text-center {
            text-align: center;
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
        
        .flex-center {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['alumni_id'])): ?>
    <!-- Login Form -->
    <div class="form-container <?php echo $showSignup || $showReset ? 'form-toggle' : 'active-form'; ?>" id="login-form">
        <button class="close-btn" onclick="window.location.href='alumni.html'">×</button>
        <div class="form-header">
            <img src="../picture/SKST.png" alt="SKST Logo" style="width: 80px; margin-bottom: -15px;">
            <h2>Alumni Login</h2>
            <p>Sign in to access your alumni account</p>
        </div>
        
        <?php if ($error && !$showSignup && !$showReset): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-msg"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" name="login" class="form-btn">Login</button>
            
            <div class="form-footer">
                <p><a href="#" onclick="showForm('reset')">Forgot Password?</a></p>
                <p>Don't have an account? <a href="#" onclick="showForm('signup')">Sign Up</a></p>
            </div>
        </form>
    </div>
    
    <!-- Sign Up Form -->
    <div class="form-container form-toggle <?php echo $showSignup ? 'active-form' : ''; ?>" id="signup-form">
        <button class="close-btn" onclick="showForm('login')">×</button>
        <div class="form-header">
            <h2>Create Account</h2>
            <p>Join our alumni community today</p>
        </div>
        
        <?php if ($error && $showSignup): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
            </div>
            
            <div class="input-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="input-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
            </div>
            
            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            
            <button type="submit" name="signup" class="form-btn">Sign Up</button>
            
            <div class="form-footer">
                <p>Already have an account? <a href="#" onclick="showForm('login')">Login</a></p>
            </div>
        </form>
    </div>
    
    <!-- Reset Password Form -->
    <div class="form-container form-toggle <?php echo $showReset ? 'active-form' : ''; ?>" id="reset-form">
        <button class="close-btn" onclick="showForm('login')">×</button>
        <div class="form-header">
            <h2>Reset Password</h2>
            <p>Enter your email to reset your password</p>
        </div>
        
        <?php if ($error && $showReset): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success && $showReset): ?>
            <div class="success-msg"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label for="reset-email">Email</label>
                <input type="email" id="reset-email" name="email" placeholder="Enter your email" required>
            </div>
            
            <button type="submit" name="reset" class="form-btn">Reset Password</button>
            
            <div class="form-footer">
                <p>Remember your password? <a href="#" onclick="showForm('login')">Login</a></p>
            </div>
        </form>
    </div>
    
    <script>
        function showForm(formName) {
            // Hide all forms
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active-form');
                form.classList.add('form-toggle');
            });
            
            // Show the requested form
            document.getElementById(formName + '-form').classList.add('active-form');
            document.getElementById(formName + '-form').classList.remove('form-toggle');
        }
    </script>
<?php else: ?>

    <?php 
    $pageSelected = isset($_GET['profile']) || isset($_GET['courses']) || isset($_GET['notice']) || isset($_GET['degree']) || isset($_GET['internship']);
    ?>
    
    <div class="container">
        <?php if (!$pageSelected): ?>
            <!-- Welcome Dashboard -->
            <h1 class="welcome-msg"><span class="emoji">👋</span> Welcome, <?= htmlspecialchars($alumni['name']) ?></h1>
    
            <div class="cards">
                <a href="alumni.php?profile=true" class="card">
                    <div class="card-icon">👤</div>
                    Profile
                    <div class="card-desc">View and update your profile info</div>
                </a>
                <a href="alumni.php?courses=true" class="card">
                    <div class="card-icon">📚</div>
                    Courses
                    <div class="card-desc">See courses you completed</div>
                </a>
                <a href="alumni.php?notice=true" class="card">
                    <div class="card-icon">📢</div>
                    Notices
                    <div class="card-desc">View latest university notices</div>
                </a>
                <a href="alumni.php?degree=true" class="card">
                    <div class="card-icon">🎓</div>
                    Degree
                    <div class="card-desc">View your degree details</div>
                </a>
                <a href="alumni.php?internship=true" class="card">
                    <div class="card-icon">💼</div>
                    Internship
                    <div class="card-desc">View your internship details</div>
                </a>
                <a href="alumni.php?logout=true" class="card">
                    <div class="card-icon">🚪</div>
                    Logout
                    <div class="card-desc">Logout safely</div>
                </a>
            </div>
    
        <?php else: ?>
            <!-- Sidebar + content -->
            <div class="layout">
                <nav class="sidebar">
                    <h2>SKST Portal</h2>
                    <a href="alumni.php?profile=true" class="<?= isset($_GET['profile']) ? 'active' : '' ?>">
                        <span class="icon">👤</span> Profile
                    </a>
                    <a href="alumni.php?courses=true" class="<?= isset($_GET['courses']) ? 'active' : '' ?>">
                        <span class="icon">📚</span> Courses
                    </a>
                    <a href="alumni.php?notice=true" class="<?= isset($_GET['notice']) ? 'active' : '' ?>">
                        <span class="icon">📢</span> Notices
                    </a>
                    <a href="alumni.php?degree=true" class="<?= isset($_GET['degree']) ? 'active' : '' ?>">
                        <span class="icon">🎓</span> Degree
                    </a>
                    <a href="alumni.php?internship=true" class="<?= isset($_GET['internship']) ? 'active' : '' ?>">
                        <span class="icon">💼</span> Internship
                    </a>
                    <a href="alumni.php?logout=true">
                        <span class="icon">🚪</span> Logout
                    </a>
                </nav>
    
                <main class="content">
                    <?php if (isset($_GET['profile'])): ?>
                        <div class="profile-header">
                            <h2>Profile Information</h2>
                            <button class="edit-btn" onclick="toggleEditMode()">
                                <i class="fas fa-edit"></i> Edit Profile
                            </button>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="success-msg"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <!-- Profile View -->
                        <div class="profile-view" <?= $editMode ? 'style="display:none;"' : '' ?>>
                            <div class="profile-info">
                                <p><strong>Name:</strong> <?= htmlspecialchars($alumni['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($alumni['email']) ?></p>
                                <p><strong>Graduation Year:</strong> <?= htmlspecialchars($alumni['graduation_year'] ?? '') ?></p>
                                <p><strong>Degree:</strong> <?= htmlspecialchars($alumni['degree'] ?? '') ?></p>
                                <p><strong>Current Job:</strong> <?= htmlspecialchars($alumni['current_job'] ?? '') ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($alumni['phone']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($alumni['address'] ?? '') ?></p>
                                <p><strong>Last Login:</strong> <?= htmlspecialchars($alumni['last_login'] ?: "First time login") ?></p>
                            </div>
                        </div>
                        
                        <!-- Profile Edit Form -->
                        <form class="edit-form <?= $editMode ? 'active' : '' ?>" method="POST" action="alumni.php?profile=true">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($alumni['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($alumni['email']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="graduation_year">Graduation Year</label>
                                    <input type="text" id="graduation_year" name="graduation_year" value="<?= htmlspecialchars($alumni['graduation_year'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="degree">Degree</label>
                                    <input type="text" id="degree" name="degree" value="<?= htmlspecialchars($alumni['degree'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="current_job">Current Job</label>
                                    <input type="text" id="current_job" name="current_job" value="<?= htmlspecialchars($alumni['current_job'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($alumni['phone']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($alumni['address'] ?? '') ?>">
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-outline" onclick="toggleEditMode()">Cancel</button>
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
    
                    <?php elseif (isset($_GET['courses'])): ?>
                        <h2>Completed Courses</h2>
                        <ul class="course-list">
                            <?php
                            // Reconnect to database
                            $conn_courses = new mysqli($host, $user, $pass, $db);
                            if ($conn_courses->connect_error) die("DB Connection failed: " . $conn_courses->connect_error);
                            
                            $aid = $_SESSION['alumni_id'];
                            $sql = "SELECT c.course_code, c.course_name, ac.completion_date, ac.grade 
                                    FROM alumni_courses ac
                                    JOIN courses c ON ac.course_id = c.course_id
                                    WHERE ac.alumni_id = ?";
                            $stmt = $conn_courses->prepare($sql);
                            $stmt->bind_param("i", $aid);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($course = $result->fetch_assoc()) {
                                    echo '<li>';
                                    echo '<strong>' . htmlspecialchars($course['course_code']) . ' - ' . htmlspecialchars($course['course_name']) . '</strong>';
                                    echo ' (Completed: ' . htmlspecialchars($course['completion_date']) . ', Grade: ' . htmlspecialchars($course['grade']) . ')';
                                    echo '</li>';
                                }
                            } else {
                                echo '<li>No courses found.</li>';
                            }
                            $stmt->close();
                            $conn_courses->close();
                            ?>
                        </ul>
    
                    <?php elseif (isset($_GET['notice'])): ?>
                        <script>
                            // Redirect to dedicated notices page
                            window.location.href = 'notice.html';
                        </script>
    
                    <?php elseif (isset($_GET['degree'])): ?>
                        <h2>Degree Information</h2>
                        <div class="degree-info">
                            <?php
                            // Reconnect to database
                            $conn_degrees = new mysqli($host, $user, $pass, $db);
                            if ($conn_degrees->connect_error) die("DB Connection failed: " . $conn_degrees->connect_error);
                            
                            $aid = $_SESSION['alumni_id'];
                            $sql = "SELECT * FROM degrees WHERE alumni_id = ?";
                            $stmt = $conn_degrees->prepare($sql);
                            $stmt->bind_param("i", $aid);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($degree = $result->fetch_assoc()) {
                                    echo '<div class="degree-item">';
                                    echo '<h3>' . htmlspecialchars($degree['degree_name']) . '</h3>';
                                    echo '<p><strong>Major:</strong> ' . htmlspecialchars($degree['major']) . '</p>';
                                    echo '<p><strong>Institution:</strong> ' . htmlspecialchars($degree['institution']) . '</p>';
                                    echo '<p><strong>Duration:</strong> ' . htmlspecialchars($degree['start_year']) . ' - ' . htmlspecialchars($degree['end_year']) . '</p>';
                                    echo '<p><strong>GPA:</strong> ' . htmlspecialchars($degree['gpa']) . '</p>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No degree information found.</p>';
                            }
                            $stmt->close();
                            $conn_degrees->close();
                            ?>
                        </div>
    
                    <?php elseif (isset($_GET['internship'])): ?>
                        <h2>Internship Information</h2>
                        <div class="internship-info">
                            <?php
                            // Reconnect to database
                            $conn_internships = new mysqli($host, $user, $pass, $db);
                            if ($conn_internships->connect_error) die("DB Connection failed: " . $conn_internships->connect_error);
                            
                            $aid = $_SESSION['alumni_id'];
                            $sql = "SELECT * FROM internships WHERE alumni_id = ?";
                            $stmt = $conn_internships->prepare($sql);
                            $stmt->bind_param("i", $aid);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while ($internship = $result->fetch_assoc()) {
                                    echo '<div class="internship-item">';
                                    echo '<h3>' . htmlspecialchars($internship['position']) . '</h3>';
                                    echo '<p><strong>Company:</strong> ' . htmlspecialchars($internship['company']) . '</p>';
                                    echo '<p><strong>Duration:</strong> ' . htmlspecialchars($internship['start_date']) . ' - ' . htmlspecialchars($internship['end_date']) . '</p>';
                                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($internship['description']) . '</p>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<p>No internship information found.</p>';
                            }
                            $stmt->close();
                            $conn_internships->close();
                            ?>
                        </div>
    
                    <?php else: ?>
                        <p style="text-align:center;">Select an option from the sidebar.</p>
                    <?php endif; ?>
    
                </main>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleEditMode() {
            const profileView = document.querySelector('.profile-view');
            const editForm = document.querySelector('.edit-form');
            
            if (profileView.style.display === 'none') {
                profileView.style.display = 'block';
                editForm.classList.remove('active');
            } else {
                profileView.style.display = 'none';
                editForm.classList.add('active');
            }
        }
    </script>
<?php endif; ?>
<?php $conn->close(); ?>
</body>
</html>