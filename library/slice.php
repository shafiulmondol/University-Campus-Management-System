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