<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

$error = "";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: alumni.php");
    exit();
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

if (isset($_SESSION['alumni_id'])) {
    $aid = $_SESSION['alumni_id'];
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ?");
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    $alumni = $result->fetch_assoc();
    $stmt->close();

    if (empty($alumni['profile_picture'])) {
        $alumni['profile_picture'] = 'picture/profilepicture.png';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <title>Alumni Portal</title>
     <link rel="icon" href="picture/SKST.png" type="image/png">
<!-- (Head part remains unchanged; omitted here for brevity) -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  /* Reset and base */
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
            margin: 0; padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-dark);
            min-height: 100vh;
            line-height: 1.6;
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
        .profile-pic {
            display: block;
            margin: 0 auto 25px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--primary);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
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
        
        /* Courses */
        ul.course-list {
            list-style: none;
            font-size: 1.1rem;
            color: var(--text-dark);
        }
        
        ul.course-list li {
            margin-bottom: 15px;
            padding: 15px;
            background: #f1f5f9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }
        
        ul.course-list li:hover {
            background: #e2e8f0;
            transform: translateX(5px);
        }
        
        ul.course-list li::before {
            content: '•';
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        /* Account */
        .account-section {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .account-section h3 {
            font-size: 1.7rem;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .account-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid var(--border);
        }
        
        .account-card p {
            font-size: 1.1rem;
            margin: 15px 0;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
        }
        
        .account-card p strong {
            color: var(--primary);
        }
        
        /* Notices */
        .notice-item {
            border-bottom: 1px solid var(--border);
            padding: 20px 0;
            margin-bottom: 15px;
        }
        
        .notice-item:last-child {
            border-bottom: none;
        }
        
        .notice-item h4 {
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 1.3rem;
        }
        
        .notice-item p {
            color: var(--text-light);
            margin: 10px 0;
        }
        
        .notice-item small {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        /* Login form */
        form.login-form {
            max-width: 420px;
            margin: 100px auto;
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            color: var(--text-dark);
            font-weight: 500;
            border: 1px solid var(--border);
        }
        
        form.login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        form.login-form input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        form.login-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        form.login-form button {
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
            margin-top: 10px;
        }
        
        form.login-form button:hover {
            background: var(--primary-dark);
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
            
            form.login-form {
                margin: 70px auto;
                padding: 30px 25px;
                max-width: 90%;
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
</style>
</head>
<body>
<?php if (!isset($_SESSION['alumni_id'])): ?>
  <form class="login-form" method="POST" novalidate>
    <h2>Alumni Login</h2>
    <?php if ($error): ?>
      <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit" name="login">Login</button>
  </form>
<?php else: ?>

<?php 
$pageSelected = isset($_GET['profile']) || isset($_GET['courses']) || isset($_GET['notice']) || isset($_GET['account']);
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
      <a href="alumni.php?account=true" class="card">
        <div class="card-icon">💰</div>
        Account
        <div class="card-desc">View your alumni account details</div>
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
        <a href="alumni.php?account=true" class="<?= isset($_GET['account']) ? 'active' : '' ?>">
          <span class="icon">💰</span> Account
        </a>
        <a href="alumni.php?logout=true">
          <span class="icon">🚪</span> Logout
        </a>
      </nav>

      <main class="content">

        <?php if (isset($_GET['profile'])): ?>
          <img src="<?= htmlspecialchars($alumni['profile_picture']) ?>" alt="Profile Picture" class="profile-pic" />
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

        <?php elseif (isset($_GET['courses'])): ?>
          <p>Courses section coming soon...</p>

        <?php elseif (isset($_GET['notice'])): ?>
          <p>Notices section coming soon...</p>

        <?php elseif (isset($_GET['account'])): ?>
          <p>Account section coming soon...</p>

        <?php else: ?>
          <p style="text-align:center;">Select an option from the sidebar.</p>
        <?php endif; ?>

      </main>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>