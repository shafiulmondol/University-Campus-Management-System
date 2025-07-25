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
 <style>
  /* Styles are unchanged from your faculty.php CSS */
  /* (Use your previous CSS exactly here) */
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
$pageSelected = isset($_GET['profile']) || isset($_GET['career']) || isset($_GET['notice']) || isset($_GET['account']);
?>

<div class="container">
  <?php if (!$pageSelected): ?>
    <h1 class="welcome-msg"><span class="emoji">👋</span> Welcome, <?= htmlspecialchars($alumni['name']) ?></h1>

    <div class="cards">
      <a href="alumni.php?profile=true" class="card">
        <div class="card-icon">👤</div>
        Profile
        <div class="card-desc">View and update your profile info</div>
      </a>
      <a href="alumni.php?career=true" class="card">
        <div class="card-icon">💼</div>
        Career
        <div class="card-desc">See your career achievements</div>
      </a>
      <a href="alumni.php?notice=true" class="card">
        <div class="card-icon">📢</div>
        Notices
        <div class="card-desc">View alumni-related notices</div>
      </a>
      <a href="alumni.php?account=true" class="card">
        <div class="card-icon">💰</div>
        Account
        <div class="card-desc">View your contribution or donations</div>
      </a>
      <a href="alumni.php?logout=true" class="card">
        <div class="card-icon">🚪</div>
        Logout
        <div class="card-desc">Logout safely</div>
      </a>
    </div>

  <?php else: ?>
    <div class="layout">
      <nav class="sidebar">
        <h2>SKST Portal</h2>
        <a href="alumni.php?profile=true" class="<?= isset($_GET['profile']) ? 'active' : '' ?>">
          <span class="icon">👤</span> Profile
        </a>
        <a href="alumni.php?career=true" class="<?= isset($_GET['career']) ? 'active' : '' ?>">
          <span class="icon">💼</span> Career
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
            <p><strong>Batch:</strong> <?= htmlspecialchars($alumni['batch']) ?></p>
            <p><strong>Graduation Year:</strong> <?= htmlspecialchars($alumni['graduation_year']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($alumni['phone']) ?></p>
            <p><strong>Last Login:</strong> <?= htmlspecialchars($alumni['last_login'] ?: "First time login") ?></p>
          </div>

        <?php elseif (isset($_GET['career'])): ?>
          <p>Career section coming soon...</p>

        <?php elseif (isset($_GET['notice'])): ?>
          <p>Alumni notices coming soon...</p>

        <?php elseif (isset($_GET['account'])): ?>
          <p>Account details coming soon...</p>

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
