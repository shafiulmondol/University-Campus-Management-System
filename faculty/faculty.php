<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "skst_university";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

/* --------- Ensure attendance table exists --------- */
$conn->query("
CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  faculty_id INT NOT NULL,
  course_code VARCHAR(50) NOT NULL,
  student_id INT NOT NULL,
  status ENUM('Present','Absent') NOT NULL,
  date DATE NOT NULL,
  INDEX idx_att (faculty_id, course_code, date),
  FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* --------- Logout --------- */
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: faculty.php");
  exit();
}

$error = "";

/* --------- Login --------- */
if (!isset($_SESSION['faculty_id']) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
  $email    = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT * FROM faculty WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($row = $res->fetch_assoc()) {
    if ($password === $row['password']) {
      $_SESSION['faculty_id']   = $row['faculty_id'];
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
  $stmt->close();
}

/* --------- Fetch faculty (if logged in) --------- */
$faculty = null;
if (isset($_SESSION['faculty_id'])) {
  $fid  = $_SESSION['faculty_id'];
  $stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ?");
  $stmt->bind_param("i", $fid);
  $stmt->execute();
  $result  = $stmt->get_result();
  $faculty = $result->fetch_assoc();
  $stmt->close();

  if (empty($faculty['profile_picture'])) {
    $faculty['profile_picture'] = 'picture/profilepicture.png';
  }
}

/* Helper: fetch courses of this faculty (used by Attendance & Report) */
function fetch_faculty_courses(mysqli $conn, int $fid) {
  $courses = [];
  $q = $conn->prepare("SELECT course_code, course_name FROM courses WHERE faculty_id = ?");
  $q->bind_param("i", $fid);
  $q->execute();
  $r = $q->get_result();
  while ($row = $r->fetch_assoc()) $courses[] = $row;
  $q->close();
  return $courses;
}

/* Helper: get students for a course (tries enrollment->student, else all students) */
function fetch_students_for_course(mysqli $conn, string $course_code) {
  $students = [];

  // Try enrollment table if it exists
  $hasEnrollment = $conn->query("SHOW TABLES LIKE 'enrollment'")->num_rows > 0;
  if ($hasEnrollment) {
    $sql = "
      SELECT s.id, s.name
      FROM enrollment e
      JOIN student s ON s.id = e.student_id
      WHERE e.course_code = ?
      ORDER BY s.id
    ";
    $st = $conn->prepare($sql);
    $st->bind_param("s", $course_code);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) $students[] = $row;
    $st->close();
  }

  // Fallback to all students if none found
  if (empty($students)) {
    $rs = $conn->query("SELECT id, name FROM student ORDER BY id");
    while ($row = $rs->fetch_assoc()) $students[] = $row;
  }
  return $students;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <title>Faculty Portal</title>
 <link rel="icon" href="picture/SKST.png" type="image/png">
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <style>
  /* Reset and base */
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Poppins', sans-serif; background: #273947; color: #f0f0f0; min-height: 100vh; }
  a { color: inherit; text-decoration: none; }

  /* Container */
  .container { max-width: 1100px; margin: 40px auto; padding: 0 15px; }

  /* Welcome Dashboard */
  .welcome-msg { text-align: center; font-size: 2rem; font-weight: 600; margin-bottom: 50px; position: relative; }
  .welcome-msg .emoji { font-size: 3rem; margin-right: 10px; }
  .cards { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; }
  .card {
    background: #0f2f53ff; border-radius: 15px; box-shadow: 0 8px 20px rgba(15,11,11,0.3);
    width: 260px; padding: 30px 25px; text-align: center; font-weight: 600; font-size: 1.3rem;
    cursor: pointer; transition: transform .3s ease, box-shadow .3s ease; color: #e1e8f0;
  }
  .card:hover { transform: translateY(-12px); box-shadow: 0 14px 35px rgba(0,0,0,0.5); }
  .card-icon { font-size: 4.5rem; margin-bottom: 20px; }
  .card-desc { font-weight: 400; font-size: 1rem; margin-top: 10px; color: #b0b7c1; }

  /* Layout Sidebar + Content */
  .layout { display: flex; gap: 25px; }
  .sidebar {
    background: #273947; border-radius: 20px; width: 260px; padding: 30px 20px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3); display: flex; flex-direction: column; gap: 18px;
  }
  .sidebar h2 { font-size: 2rem; font-weight: 700; margin-bottom: 25px; color: #1e90ff; text-align: center; }
  .sidebar a { padding: 15px 20px; border-radius: 15px; font-weight: 600; font-size: 1.1rem; background: #1b2735; display: flex; align-items: center; gap: 15px; transition: background-color .3s ease; }
  .sidebar a:hover, .sidebar a.active { background: #1e90ff; color: #1b2735; font-weight: 700; }
  .sidebar a .icon { font-size: 1.8rem; }
  main.content {
    flex-grow: 1; background: #f9f9f9; color: #333; border-radius: 20px; padding: 40px 35px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1); min-height: 520px;
  }

  /* Profile */
  .profile-pic { display: block; margin: 0 auto 25px; width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 5px solid #1e90ff; }
  .profile-info p { font-size: 1.15rem; margin: 12px 0; }
  .profile-info p strong { color: #1e90ff; font-weight: 700; font-size: 1.2rem; }

  /* Tables / forms */
  table { width: 100%; background: #fff; border-collapse: collapse; border-radius: 12px; overflow: hidden; }
  th, td { padding: 10px 12px; border: 1px solid #eee; text-align: left; }
  th { background: #f2f6fb; }
  select, input[type="date"] {
    padding: 10px 12px; border-radius: 10px; border: 1px solid #ddd; background: #fff; margin: 0 8px 0 0;
  }
  .section-title { font-size: 1.6rem; margin-bottom: 12px; color: #1e90ff; }

  /* Account / Notices little tweaks */
  .notice-item { border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 20px; }
  .notice-item h4 { margin-bottom: 8px; color: #1e90ff; }
  .notice-item small { color: #888; }

  /* Buttons */
  form.login-form { max-width: 400px; margin: 120px auto; background: #f9f9f9; border-radius: 25px; padding: 40px 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); color: #333; font-weight: 600; }
  form.login-form h2 { text-align: center; margin-bottom: 30px; font-weight: 700; font-size: 2rem; color: #1e90ff; }
  form.login-form input { width: 100%; padding: 15px 20px; border-radius: 18px; border: 1px solid #ddd; margin-bottom: 20px; font-size: 1rem; font-weight: 500; }
  form.login-form button { width: 100%; padding: 15px; background: #1e90ff; border: none; border-radius: 18px; color: white; font-size: 1.1rem; cursor: pointer; font-weight: 700; transition: background .3s ease; }
  form.login-form button:hover { background: #08ed91ff; }
  button[type=submit] { background-color: #2980b9; color: white; padding: 12px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; }
  a[type=back] button, .btn-back { margin-top: 10px; background-color: #005fa3; color: white; padding: 12px 14px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; display: inline-block; }
  .error-msg { color: #e74c3c; font-weight: 700; margin-bottom: 20px; text-align: center; }
  .success { color: #27ae60; font-weight: 700; margin: 10px 0 20px; }

  /* Responsive */
  @media (max-width: 900px) {
    .container { margin: 20px auto; padding: 15px; }
    .cards { flex-direction: column; align-items: center; }
    .card { width: 90%; }
    .layout { flex-direction: column; }
    .sidebar { width: 100%; flex-direction: row; justify-content: space-around; padding: 15px 10px; border-radius: 15px; gap: 8px; flex-wrap: wrap; }
    .sidebar a { flex: 1; padding: 12px 10px; font-size: 1rem; justify-content: center; }
    main.content { margin-top: 20px; padding: 25px 15px; border-radius: 15px; min-height: auto; }
    form.login-form { margin: 70px 15px; padding: 30px 25px; }
  }
 </style>
</head>
<body>
<?php if (!isset($_SESSION['faculty_id'])): ?>
  <!-- ---------- LOGIN ---------- -->
  <form class="login-form" method="POST" novalidate>
    <h2>Faculty Login</h2>
    <?php if ($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit" name="login">Login</button>
    <a href="index.html" type="back"><button type="button"><span>🔙</span> Back to Dashboard</button></a>
  </form>

<?php else: ?>
<?php 
$pageSelected = isset($_GET['profile']) || isset($_GET['courses']) || isset($_GET['notice']) || isset($_GET['account']) || isset($_GET['attendance']) || isset($_GET['report']);
?>
<div class="container">
  <?php if (!$pageSelected): ?>
    <!-- ---------- WELCOME DASHBOARD ---------- -->
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
      <a href="faculty.php?attendance=true" class="card">
        <div class="card-icon">📝</div>
        Attendance
        <div class="card-desc">Mark & view student attendance</div>
      </a>
      <a href="faculty.php?report=true" class="card">
        <div class="card-icon">📊</div>
        Report
        <div class="card-desc">Attendance percentages</div>
      </a>
      <a href="faculty.php?logout=true" class="card">
        <div class="card-icon">🚪</div>
        Logout
        <div class="card-desc">Logout safely</div>
      </a>
    </div>

  <?php else: ?>
    <!-- ---------- SIDEBAR + CONTENT ---------- -->
    <div class="layout">
      <nav class="sidebar">
        <h2>SKST Portal</h2>
        <a href="faculty.php?profile=true"    class="<?= isset($_GET['profile'])    ? 'active' : '' ?>"><span class="icon">👤</span> Profile</a>
        <a href="faculty.php?courses=true"    class="<?= isset($_GET['courses'])    ? 'active' : '' ?>"><span class="icon">📚</span> Courses</a>
        <a href="faculty.php?notice=true"     class="<?= isset($_GET['notice'])     ? 'active' : '' ?>"><span class="icon">📢</span> Notices</a>
        <a href="faculty.php?account=true"    class="<?= isset($_GET['account'])    ? 'active' : '' ?>"><span class="icon">💰</span> Account</a>
        <a href="faculty.php?attendance=true" class="<?= isset($_GET['attendance']) ? 'active' : '' ?>"><span class="icon">📝</span> Attendance</a>
        <a href="faculty.php?report=true"     class="<?= isset($_GET['report'])     ? 'active' : '' ?>"><span class="icon">📊</span> Report</a>
        <a href="faculty.php?logout=true"><span class="icon">🚪</span> Logout</a>
      </nav>

      <main class="content">

        <?php if (isset($_GET['profile'])): ?>
          <!-- ---------- PROFILE ---------- -->
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
            <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>
          </div>

        <?php elseif (isset($_GET['courses'])): ?>
          <!-- ---------- COURSES (example placeholder) ---------- -->
          <h2 class="section-title">📚 Your Courses</h2>
          <?php
            $mycourses = fetch_faculty_courses($conn, $fid);
            if (!empty($mycourses)) {
              echo '<ul class="course-list">';
              foreach ($mycourses as $c) {
                echo '<li>'.htmlspecialchars($c['course_name']).' ('.htmlspecialchars($c['course_code']).')</li>';
              }
              echo '</ul>';
            } else {
              echo "<p>No courses found.</p>";
            }
          ?>
          <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>

        <?php elseif (isset($_GET['notice'])): ?>
          <!-- ---------- NOTICES (placeholder) ---------- -->
          <h2 class="section-title">📢 Notices</h2>
          <div class="notice-item">
            <h4>Welcome to the SKST Faculty Portal</h4>
            <small><?= date('Y-m-d') ?></small>
            <p>Use the Attendance page to mark today’s class and Report page for percentages.</p>
          </div>
          <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>

        <?php elseif (isset($_GET['account'])): ?>
          <!-- ---------- ACCOUNT (placeholder) ---------- -->
          <h2 class="section-title">💼 Account</h2>
          <p>Account section coming soon…</p>
          <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>

        <?php elseif (isset($_GET['attendance'])): ?>
          <!-- ---------- ATTENDANCE ---------- -->
          <h2 class="section-title">📝 Attendance Management</h2>
          <?php
          $saveMsg = "";
          if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_attendance'])) {
            $course_code = $_POST['course_code'] ?? '';
            $date        = $_POST['date'] ?? date('Y-m-d');
            $statuses    = $_POST['status'] ?? [];

            if ($course_code && $date && !empty($statuses)) {
              // Clear existing records for this date/course/faculty to avoid duplicates
              $del = $conn->prepare("DELETE FROM attendance WHERE faculty_id = ? AND course_code = ? AND date = ?");
              $del->bind_param("iss", $fid, $course_code, $date);
              $del->execute();
              $del->close();

              $ins = $conn->prepare("INSERT INTO attendance (faculty_id, course_code, student_id, status, date) VALUES (?, ?, ?, ?, ?)");
              foreach ($statuses as $sid => $st) {
                $st = ($st === 'Absent') ? 'Absent' : 'Present';
                $ins->bind_param("isiss", $fid, $course_code, $sid, $st, $date);
                $ins->execute();
              }
              $ins->close();
              $saveMsg = "✅ Attendance saved for $course_code on $date";
            } else {
              $saveMsg = "⚠️ Please select a course and mark at least one student.";
            }
          }

          $courses = fetch_faculty_courses($conn, $fid);
          $selectedCourse = $_POST['course_code'] ?? ($courses[0]['course_code'] ?? '');
          $selectedDate   = $_POST['date'] ?? date('Y-m-d');

          // Students for selected course
          $students = $selectedCourse ? fetch_students_for_course($conn, $selectedCourse) : [];
          ?>

          <?php if (!empty($saveMsg)): ?><div class="success"><?= htmlspecialchars($saveMsg) ?></div><?php endif; ?>

          <form method="POST">
            <p style="margin-bottom:12px;">
              <label><strong>Course:</strong></label>
              <select name="course_code" required onchange="this.form.submit()">
                <?php foreach ($courses as $c): ?>
                  <li><option value="<?= htmlspecialchars($c['course_code']) ?>" <?= $selectedCourse===$c['course_code']?'selected':'' ?>>
                    <?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['course_code']) ?>)
                  </option></li>
                <?php endforeach; ?>
              </select>

              <label><strong>Date:</strong></label>
              <input type="date" name="date" value="<?= htmlspecialchars($selectedDate) ?>" required />
            </p>

            <?php if (!empty($students)): ?>
            <table>
              <tr>
                <th style="width:120px;">Student ID</th>
                <th>Name</th>
                <th style="width:160px;">Status</th>
              </tr>
              <?php foreach ($students as $s): ?>
              <tr>
                <td><?= htmlspecialchars($s['id']) ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td>
                  <select name="status[<?= (int)$s['id'] ?>]">
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                  </select>
                </td>
              </tr>
              <?php endforeach; ?>
            </table>
            <br>
            <button type="submit" name="mark_attendance">Save Attendance</button>
            <?php else: ?>
              <p>No students found.</p>
            <?php endif; ?>
          </form>

          <hr style="margin:24px 0;">
          <h3 class="section-title">🗂️ Recent Attendance</h3>
          <?php
          $rec = $conn->prepare("
            SELECT a.date, a.course_code, a.student_id, a.status, s.name AS student_name
            FROM attendance a
            LEFT JOIN student s ON s.id = a.student_id
            WHERE a.faculty_id = ?
            ORDER BY a.date DESC, a.course_code ASC, a.student_id ASC
            LIMIT 50
          ");
          $rec->bind_param("i", $fid);
          $rec->execute();
          $rows = $rec->get_result();
          if ($rows->num_rows > 0): ?>
            <table>
              <tr>
                <th style="width:110px;">Date</th>
                <th style="width:120px;">Course</th>
                <th style="width:120px;">Student ID</th>
                <th>Student</th>
                <th style="width:110px;">Status</th>
              </tr>
              <?php while ($r = $rows->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($r['date']) ?></td>
                <td><?= htmlspecialchars($r['course_code']) ?></td>
                <td><?= htmlspecialchars($r['student_id']) ?></td>
                <td><?= htmlspecialchars($r['student_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
              </tr>
              <?php endwhile; ?>
            </table>
          <?php else: ?>
            <p>No attendance records yet.</p>
          <?php endif; $rec->close(); ?>

          <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>

        <?php elseif (isset($_GET['report'])): ?>
          <!-- ---------- REPORT ---------- -->
          <h2 class="section-title">📊 Attendance Report</h2>
          <?php
          $courses = fetch_faculty_courses($conn, $fid);
          $reportCourse = $_POST['course_code'] ?? ($courses[0]['course_code'] ?? '');
          ?>

          <form method="POST" style="margin-bottom:14px;">
            <label><strong>Course:</strong></label>
            <select name="course_code" required>
              <?php foreach ($courses as $c): ?>
                <option value="<?= htmlspecialchars($c['course_code']) ?>" <?= $reportCourse===$c['course_code']?'selected':'' ?>>
                  <?= htmlspecialchars($c['course_name']) ?> (<?= htmlspecialchars($c['course_code']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <button type="submit" name="view_report">View Report</button>
          </form>

          <?php
          $result = null;
          if ($reportCourse) {
            $stmt = $conn->prepare("
              SELECT s.id, s.name, a.course_code,
                     COUNT(*) AS total_classes,
                     SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) AS presents,
                     ROUND((SUM(CASE WHEN a.status='Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(*),0)) * 100, 2) AS percentage
              FROM attendance a
              LEFT JOIN student s ON a.student_id = s.id
              WHERE a.faculty_id = ? AND a.course_code = ?
              GROUP BY s.id, s.name, a.course_code
              ORDER BY s.id
            ");
            $stmt->bind_param("is", $fid, $reportCourse);
            $stmt->execute();
            $result = $stmt->get_result();
          }

          if ($result && $result->num_rows > 0): ?>
            <table>
              <tr>
                <th style="width:120px;">Student ID</th>
                <th>Student</th>
                <th style="width:140px;">Total Classes</th>
                <th style="width:120px;">Presents</th>
                <th style="width:150px;">Attendance %</th>
              </tr>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['name'] ?? '-') ?></td>
                  <td><?= (int)$row['total_classes'] ?></td>
                  <td><?= (int)$row['presents'] ?></td>
                  <td><?= is_null($row['percentage']) ? '0.00' : number_format((float)$row['percentage'], 2) ?>%</td>
                </tr>
              <?php endwhile; ?>
            </table>
          <?php elseif ($reportCourse): ?>
            <p>No attendance records yet for <strong><?= htmlspecialchars($reportCourse) ?></strong>.</p>
          <?php endif; ?>

          <a href="faculty.php" class="btn-back">🔙 Back to Dashboard</a>

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
<?php
/* Close connection at very end */
$conn->close();
