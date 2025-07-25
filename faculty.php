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
    header("Location: faculty.php");
    exit();
}

if (!isset($_SESSION['faculty_id']) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM faculty WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) {
            $_SESSION['faculty_id'] = $row['faculty_id'];
            $_SESSION['faculty_name'] = $row['name'];

            $current_datetime = date('Y-m-d H:i:s');
            $upd = $conn->prepare("UPDATE faculty SET last_login = ? WHERE faculty_id = ?");
            $upd->bind_param("si", $current_datetime, $row['faculty_id']);
            $upd->execute();
            $upd->close();

            header("Location: faculty.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Faculty not found.";
    }
}

if (isset($_SESSION['faculty_id'])) {
    $fid = $_SESSION['faculty_id'];
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $fid);
    $stmt->execute();
    $result = $stmt->get_result();
    $faculty = $result->fetch_assoc();
    $stmt->close();

    if (empty($faculty['profile_picture'])) {
        $faculty['profile_picture'] = 'picture/profilepicture.png';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
 <title>Faculty Portal</title>
     <link rel="icon" href="picture/SKST.png" type="image/png">
<!-- (Head part remains unchanged; omitted here for brevity) -->
<style>
  /* Reset and base */
  * {
    box-sizing: border-box;
    margin: 0; padding: 0;
  }
  body {
    font-family: 'Poppins', sans-serif;
    background: #273947;
    color: #f0f0f0;
    min-height: 100vh;
  }
  a {
    color: inherit;
    text-decoration: none;
  }
  /* Container */
  .container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 15px;
  }
  /* Welcome Dashboard */
  .welcome-msg {
    text-align: center;
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 50px;
    position: relative;
  }
  .welcome-msg .emoji {
    font-size: 3rem;
    margin-right: 10px;
  }
  .cards {
    display: flex;
    justify-content: center;
    gap: 30px;
    flex-wrap: wrap;
  }
  .card {
    background: #0f2f53ff;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(15, 11, 11, 0.3);
    width: 260px;
    padding: 30px 25px;
    text-align: center;
    font-weight: 600;
    font-size: 1.3rem;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #e1e8f0;
  }
  .card:hover {
    transform: translateY(-12px);
    box-shadow: 0 14px 35px rgba(0,0,0,0.5);
  }
  .card-icon {
    font-size: 4.5rem;
    margin-bottom: 20px;
  }
  .card-desc {
    font-weight: 400;
    font-size: 1rem;
    margin-top: 10px;
    color: #b0b7c1;
  }

  /* Layout Sidebar + Content */
  .layout {
    display: flex;
    gap: 25px;
  }
  .sidebar {
    background: #273947;
    border-radius: 20px;
    width: 260px;
    padding: 30px 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    gap: 18px;
  }
  .sidebar h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 25px;
    color: #1e90ff; /* Changed color */
    text-align: center;
  }
  .sidebar a {
    padding: 15px 20px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 1.1rem;
    background: #1b2735;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: background-color 0.3s ease;
  }
  .sidebar a:hover,
  .sidebar a.active {
    background: #1e90ff; /* Changed color */
    color: #1b2735;
    font-weight: 700;
  }
  .sidebar a .icon {
    font-size: 1.8rem;
  }
  main.content {
    flex-grow: 1;
    background: #f9f9f9;
    color: #333;
    border-radius: 20px;
    padding: 40px 35px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    min-height: 520px;
  }

  /* Profile */
  .profile-pic {
    display: block;
    margin: 0 auto 25px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid #1e90ff; /* Changed color */
  }
  .profile-info p {
    font-size: 1.15rem;
    margin: 12px 0;
  }
  .profile-info p strong {
    color: #1e90ff; /* Changed color */
    font-weight: 700;
    font-size: 1.2rem;
  }

  /* Courses */
  ul.course-list {
    list-style: disc inside;
    font-size: 1.15rem;
    color: #444;
  }
  ul.course-list li {
    margin-bottom: 10px;
  }

  /* Account */
  .account-section {
    max-width: 550px;
    margin: 0 auto;
  }
  .account-section h3 {
    font-size: 1.9rem;
    color: #1e90ff; /* Changed color */
    margin-bottom: 20px;
    border-bottom: 3px solid #1e90ff; /* Changed color */
    padding-bottom: 8px;
  }
  .account-card p {
    font-size: 1.1rem;
    margin: 12px 0;
  }

  /* Notices */
  .notice-item {
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
    margin-bottom: 20px;
  }
  .notice-item h4 {
    margin-bottom: 8px;
    color: #1e90ff; /* Changed color */
  }
  .notice-item small {
    color: #888;
  }

  /* Login form */
  form.login-form {
    max-width: 400px;
    margin: 120px auto;
    background: #f9f9f9;
    border-radius: 25px;
    padding: 40px 35px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    color: #333;
    font-weight: 600;
  }
  form.login-form h2 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 2rem;
    color: #1e90ff; /* Changed color */
  }
  form.login-form input {
    width: 100%;
    padding: 15px 20px;
    border-radius: 18px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    font-size: 1rem;
    font-weight: 500;
  }
  form.login-form button {
    width: 100%;
    padding: 15px;
    background: #1e90ff; /* Changed color */
    border: none;
    border-radius: 18px;
    color: white;
    font-size: 1.1rem;
    cursor: pointer;
    font-weight: 700;
    transition: background 0.3s ease;
  }
  form.login-form button:hover {
    background: #005fa3;
  }
  .error-msg {
    color: #e74c3c;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
  }

  /* Responsive */
  @media (max-width: 900px) {
    .container {
      margin: 20px auto;
      padding: 15px;
    }
    .cards {
      flex-direction: column;
      align-items: center;
    }
    .card {
      width: 90%;
    }
    .layout {
      flex-direction: column;
    }
    .sidebar {
      width: 100%;
      flex-direction: row;
      justify-content: space-around;
      padding: 15px 10px;
      border-radius: 15px;
      gap: 8px;
    }
    .sidebar a {
      flex: 1;
      padding: 12px 10px;
      font-size: 1rem;
      justify-content: center;
    }
    main.content {
      margin-top: 20px;
      padding: 25px 15px;
      border-radius: 15px;
      min-height: auto;
    }
    form.login-form {
      margin: 70px 15px;
      padding: 30px 25px;
    }
  }
</style>
</head>
<body>
<?php if (!isset($_SESSION['faculty_id'])): ?>
  <form class="login-form" method="POST" novalidate>
    <h2>Faculty Login</h2>
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
    <h1 class="welcome-msg"><span class="emoji">👋</span> Welcome, <?= htmlspecialchars($faculty['name']) ?></h1>

    <div class="cards">
      <a href="faculty.php?profile=true" class="card">
        <div class="card-icon">👤</div>
        Profile
        <div class="card-desc">View and update your profile info</div>
      </a>
      <a href="faculty.php?courses=true" class="card">
        <div class="card-icon">📚</div>
        Courses
        <div class="card-desc">See courses you're teaching</div>
      </a>
      <a href="faculty.php?notice=true" class="card">
        <div class="card-icon">📢</div>
        Notices
        <div class="card-desc">View latest university notices</div>
      </a>
      <a href="faculty.php?account=true" class="card">
        <div class="card-icon">💰</div>
        Account
        <div class="card-desc">View your account details</div>
      </a>
      <a href="faculty.php?logout=true" class="card">
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
        <a href="faculty.php?profile=true" class="<?= isset($_GET['profile']) ? 'active' : '' ?>">
          <span class="icon">👤</span> Profile
        </a>
        <a href="faculty.php?courses=true" class="<?= isset($_GET['courses']) ? 'active' : '' ?>">
          <span class="icon">📚</span> Courses
        </a>
        <a href="faculty.php?notice=true" class="<?= isset($_GET['notice']) ? 'active' : '' ?>">
          <span class="icon">📢</span> Notices
        </a>
        <a href="faculty.php?account=true" class="<?= isset($_GET['account']) ? 'active' : '' ?>">
          <span class="icon">💰</span> Account
        </a>
        <a href="faculty.php?logout=true">
          <span class="icon">🚪</span> Logout
        </a>
      </nav>

      <main class="content">

        <?php if (isset($_GET['profile'])): ?>
          <img src="<?= htmlspecialchars($faculty['profile_picture']) ?>" alt="Profile Picture" class="profile-pic" />
          <div class="profile-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($faculty['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($faculty['email']) ?></p>
            <p><strong>Department:</strong> <?= htmlspecialchars($faculty['department']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($faculty['address'] ?? '') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($faculty['phone']) ?></p>
            <p><strong>Room Number:</strong> <?= htmlspecialchars($faculty['room_number'] ?? '') ?></p>
            <p><strong>Salary:</strong> <?= htmlspecialchars($faculty['salary'] ?? '') ?></p>
            <p><strong>Last Login:</strong> <?= htmlspecialchars($faculty['last_login'] ?: "First time login") ?></p>
          </div>

        <?php elseif (isset($_GET['courses'])): ?>
          <!--
          You can add your DB connection and course queries here later.
          Example:
          <?php
          /*
          $conn = new mysqli($host, $user, $pass, $db);
          if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);
          $result = $conn->query("SELECT * FROM courses WHERE faculty_id = $fid");
          if ($result && $result->num_rows > 0):
          ?>
            <ul class="course-list">
            <?php while ($course = $result->fetch_assoc()): ?>
              <li><?= htmlspecialchars($course['course_name'] ?? $course['course_title'] ?? 'Course') ?></li>
            <?php endwhile; ?>
            </ul>
          <?php
          else:
            echo "<p>No courses found.</p>";
          endif;
          $conn->close();
          */
          ?>
          -->
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