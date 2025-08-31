<!-- function add_notice($title, $section, $content, $author) {
    global $con;
    
    // Validate inputs
    if(empty($title) || empty($content) || empty($section)) {
        return "All fields are required";
    }
    
    // Prepare the SQL statement
    $query = "INSERT INTO notice (title, section, content, author) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);
    
    if(!$stmt) {
        return "Database error: " . mysqli_error($con);
    }
    
    // Bind parameters and execute
    mysqli_stmt_bind_param($stmt, "ssss", $title, $section, $content, $author);
    $result = mysqli_stmt_execute($stmt);
    
    if($result) {
        return true; // Success
    } else {
        return "Failed to add notice: " . mysqli_error($con);
    }
}
<div class="notice-form-container">
    <h2>Add New Notice</h2>
    <form action="" method="post" class="notice-form">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required 
                   value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
        </div>
        
        <div class="form-group">
            <label for="section">Section:</label>
            <select id="section" name="section" required>
                <option value="">Select Section</option>
                <option value="Student" <?= (isset($_POST['section']) && $_POST['section'] === 'Student') ? 'selected' : '' ?>>Student</option>
                <option value="Faculty" <?= (isset($_POST['section']) && $_POST['section'] === 'Faculty') ? 'selected' : '' ?>>Faculty</option>
                <option value="Alumni" <?= (isset($_POST['section']) && $_POST['section'] === 'Alumni') ? 'selected' : '' ?>>Alumni</option>
                <option value="Library" <?= (isset($_POST['section']) && $_POST['section'] === 'Library') ? 'selected' : '' ?>>Library</option>
                <option value="Staff" <?= (isset($_POST['section']) && $_POST['section'] === 'Staff') ? 'selected' : '' ?>>Staff</option>
                <option value="Others" <?= (isset($_POST['section']) && $_POST['section'] === 'Others') ? 'selected' : '' ?>>Others</option>
            </select>
            
            <?php if(isset($_POST['section']) && $_POST['section'] === 'Others'): ?>
            <div id="other-section-container">
                <label for="other-section">Specify Section:</label>
                <input type="text" id="other-section" name="other_section" required
                       value="<?= isset($_POST['other_section']) ? htmlspecialchars($_POST['other_section']) : '' ?>">
            </div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="5" required><?= 
                isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' 
            ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required
                   value="<?= isset($_POST['author']) ? htmlspecialchars($_POST['author']) : '' ?>">
        </div>
        
        <button type="submit" name="submit_notice" class="submit-btn">Add Notice</button>
    </form>
</div>
if(isset($_POST['submit_notice'])) {
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $author = mysqli_real_escape_string($con, $_POST['author']);
    
    // Handle section selection
    $section = $_POST['section'];
    if($section === 'Others') {
        if(!empty($_POST['other_section'])) {
            $section = mysqli_real_escape_string($con, $_POST['other_section']);
        } else {
            echo '<div class="error-message">Please specify the section</div>';
            // Keep the form displayed with submitted values
            include 'notice_form.php';
            exit();
        }
    }
    
    $result = add_notice($title, $section, $content, $author);
    
    if($result === true) {
        echo '<div class="success-message">Notice added successfully!</div>';
        // Clear the form by not including POST values
        unset($_POST);
    } else {
        echo '<div class="error-message">Error: ' . htmlspecialchars($result) . '</div>';
    }
}
.notice-form-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.notice-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-weight: bold;
}

.form-group input[type="text"],
.form-group textarea,
.form-group select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.submit-btn {
    background: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.submit-btn:hover {
    background: #45a049;
}

.success-message {
    color: green;
    padding: 10px;
    background: #e6ffe6;
    border: 1px solid green;
    border-radius: 4px;
    margin: 10px 0;
}

.error-message {
    color: red;
    padding: 10px;
    background: #ffebeb;
    border: 1px solid red;
    border-radius: 4px;
    margin: 10px 0;
}

#other-section-container {
    margin-top: 10px;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 4px;
} -->
CREATE DATABASE IF NOT EXISTS skst_university;
USE skst_university;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    student_faculty_id VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    role ENUM('student','faculty','staff','researcher','alumni') NOT NULL,
    program ENUM('engineering','cse','medicine','business','law','arts','science','social') NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_student_faculty_id (student_faculty_id)
);

-- Insert default Account Officer (staff role)
INSERT INTO users (first_name, last_name, email, student_faculty_id, phone, role, program, password)
VALUES (
    'Account',
    'Officer',
    'accountofficer@skst.edu',
    'AO-1001',
    '01700000000',
    'staff',
    NULL,  -- Staff may not belong to a program
    '$2y$10$4V0B9pSThW7Ep5V92rSd7eK9K2xDx0AvlHkeOeG9vrLzdIc9zY4xG'
);

-- Example Student (with unique password hash)
INSERT INTO users (first_name, last_name, email, student_faculty_id, phone, role, program, password)
VALUES (
    'Rahim',
    'Uddin',
    'rahim.cse@skst.edu',
    'CSE-2025-001',
    '01711111111',
    'student',
    'cse',
    -- Hash for a unique password (e.g., "student123")
    '$2y$10$ANOTHER_RANDOM_HASHED_PASSWORD_EXAMPLE'
);

-- Example Faculty (with unique password hash)
INSERT INTO users (first_name, last_name, email, student_faculty_id, phone, role, program, password)
VALUES (
    'Dr. Anika',
    'Karim',
    'anika.faculty@skst.edu',
    'FAC-301',
    '01722222222',
    'faculty',
    'engineering',
    -- Hash for a unique password (e.g., "faculty123")
    '$2y$10$YET_ANOTHER_RANDOM_HASHED_PASSWORD'
);

php

<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "skst_university";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Handle account creation
if (isset($_POST['register'])) {
    $firstName = trim($_POST['first_name']);
    $lastName  = trim($_POST['last_name']);
    $email     = trim($_POST['email']);
    $studentId = trim($_POST['student_id']);
    $phone     = trim($_POST['phone']);
    $role      = $_POST['role'];
    $program   = $_POST['program'];
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];

    // Check password match
    if ($password !== $confirm) {
        echo "<p style='color:red'>Passwords do not match!</p>";
    } else {
        // Hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO users
            (first_name, last_name, email, student_faculty_id, phone, role, program, password)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $studentId, $phone, $role, $program, $hashed);

        if ($stmt->execute()) {
            echo "<p style='color:green'>✅ Account created successfully! You can now log in.</p>";
        } else {
            echo "<p style='color:red'>❌ Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Handle login
if (isset($_POST['login'])) {
    $email    = trim($_POST['login_email']);
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['currentUser'] = $user;
            echo "<p style='color:green'>✅ Login successful! Welcome, " . $user['first_name'] . " (" . $user['role'] . ")</p>";

            // Example redirect
            // header("Location: dashboard.php");
            // exit();
        } else {
            echo "<p style='color:red'>❌ Incorrect password!</p>";
        }
    } else {
        echo "<p style='color:red'>❌ No account found with this email!</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SKST University - Notices</title>
  <link rel="icon" href="picture/SKST.png" type="image/png" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f9f9ff;
      color: #333;
      line-height: 1.6;
    }

    .navbar {
      background-color: #e0e7ff;
      padding: 10px 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      height: 80px;
    }

    .logo h1 {
      font-size: 26px;
      color: #333;
    }

    .home-button {
      background: gray;
      color: white;
      border: none;
      padding: 10px 16px;
      font-size: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .home-button:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #18bcae, #f3af02);
    }

    .menu-section {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-top: 15px;
    }

    .menu-section a {
      text-decoration: none;
    }

    .btn {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      border: none;
      padding: 12px 20px;
      font-size: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      min-width: 120px;
      cursor: pointer;
    }

    .btn:hover {
      transform: translateY(-3px);
      background: linear-gradient(135deg, #512da8, #1e88e5);
    }

    /* Notice Section Styles */
    .content {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .notices-container {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.08);
      padding: 25px;
      margin-bottom: 30px;
    }

    .notices-heading {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-size: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .notice-card {
      background: #f8f9ff;
      border-left: 5px solid #6a11cb;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .notice-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .notice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .notice-title {
      color: #2c3e50;
      font-size: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .notice-section {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 500;
    }

    .notice-content {
      color: #34495e;
      margin-bottom: 15px;
      line-height: 1.6;
      white-space: pre-line;
    }

    .notice-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 15px;
      font-size: 14px;
      color: #7f8c8d;
      border-top: 1px solid #eee;
      padding-top: 15px;
    }

    .notice-author, .notice-date {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .back-button-container {
      text-align: center;
      margin-top: 30px;
    }

    .back-button {
      background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
      color: white;
      text-decoration: none;
      padding: 12px 25px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }

    .back-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
    }

    .no-notices {
      text-align: center;
      padding: 50px 20px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    }

    .no-notices i {
      font-size: 60px;
      color: #ddd;
      margin-bottom: 20px;
    }

    .no-notices p {
      font-size: 18px;
      color: #7f8c8d;
      margin-bottom: 30px;
    }

    @media (max-width: 768px) {
      .navbar-top {
        flex-direction: column;
        align-items: center;
      }

      .btn {
        width: 80%;
      }

      .menu-section {
        flex-direction: column;
        align-items: center;
      }

      .home-button {
        margin-top: 10px;
      }
      
      .notice-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .notice-footer {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>
  <div class="navbar">
    <div class="navbar-top">
      <div class="logo">
        <img src="picture/logo.gif" alt="SKST Logo">
        <h1>SKST University || Notice</h1>
      </div>

      <!-- Home Button -->
      <a href="index.html" class="home-button">
        <i class="fas fa-home"></i> Home
      </a>
    </div>

    <div class="menu-section">
      <a href="student/student.html"><button class="btn">Student</button></a>
      <a href="faculty.html"><button class="btn">Faculty</button></a>
      <a href="administration.html"><button class="btn">Administration</button></a>
      <a href="alumni/alumni.html"><button class="btn">Alumni</button></a>
      <a href="campus.html"><button class="btn">Campus Life</button></a>
      <a href="iqac.html"><button class="btn">IQAC</button></a>
      <a href="notice.html"><button class="btn">Notice</button></a>
      <a href="news.html"><button class="btn">News</button></a>
      <a href="ranking.html"><button class="btn">Ranking</button></a>
      <a href="academic.html"><button class="btn">Academics</button></a>
      <a href="scholarship.html"><button class="btn">Scholarships</button></a>
      <a href="admission.html"><button class="btn">Admission</button></a>
      <a href="library/library1.html"><button class="btn">Library</button></a>
      <a href="volunteer.html"><button class="btn">Volunteer</button></a>
      <a href="account.html"><button class="btn">Account</button></a>
      <a href="about.html"><button class="btn">About US</button></a>
    </div>
  </div>
  
  <div class="content">
    <?php
    // Database connection
   require_once 'library/notice.php';
   echo see_notice();
    ?>
  </div>
</body>
</html>
    </body>
    </html>
    <?php
    exit();




?>




    </body>
 </html>