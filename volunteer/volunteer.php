<?php
session_start();

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "skst_university";

// Create connection
$conn = new mysqli($host, $username, $password);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $database");
$conn->select_db($database);

// Create users table (admin)
$conn->query("CREATE TABLE IF NOT EXISTS users (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
)");

// Create volunteers table
$conn->query("CREATE TABLE IF NOT EXISTS volunteers (
    id INT(11) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    affiliation ENUM('undergrad','grad','faculty','staff','alumni') NOT NULL,
    department VARCHAR(100),
    availability TEXT NOT NULL,
    skills TEXT,
    interests TEXT,
    registration_date DATETIME NOT NULL,
    password VARCHAR(255) NOT NULL
)");

// Add default admin
$default_password = password_hash("admin123", PASSWORD_DEFAULT);
$conn->query("INSERT IGNORE INTO users (username,password) VALUES ('admin','$default_password')");

// Handle logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location:?page=login");
    exit;
}

// Determine page
$page = isset($_GET['page']) ? $_GET['page'] : 'login';
if(!isset($_SESSION['user']) && $page != 'login' && $page != 'register') {
    $page = 'login';
}

// LOGIN HANDLER
$login_error = "";
if(isset($_POST['login'])){
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // Admin login
    $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();
    
    if($stmt->num_rows > 0){
        $stmt->bind_result($hashed);
        $stmt->fetch();
        if(password_verify($pass, $hashed)){
            $_SESSION['user'] = $user;
            $_SESSION['role'] = 'admin';
            header("Location: ?page=home");
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    }
    $stmt->close();

    // Volunteer login (by email or ID) - FIXED CODE
    if(empty($login_error)){
        if(is_numeric($user)){ // If input is numeric, treat as ID
            $stmt = $conn->prepare("SELECT id, name, password FROM volunteers WHERE id = ?");
            $stmt->bind_param("i", $user);
        } else { // Otherwise, treat as email
            $stmt = $conn->prepare("SELECT id, name, password FROM volunteers WHERE email = ?");
            $stmt->bind_param("s", $user);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $volunteers = $result->fetch_assoc();
            if(password_verify($pass, $s['password'])){
                $_SESSION['user'] = $volunteers['name'];
                $_SESSION['role'] = 'volunteers';
                $_SESSION['id'] = $volunteers['id'];
                header("Location: ?page=home");
                exit;
            } else {
                $login_error = "Invalid password.";
            }
        } else {
            $login_error = "Account not found.";
        }
        $stmt->close();
    }
    
    // If we get here, login failed
    if(empty($login_error)) {
        $login_error = "Invalid username or password.";
    }
}

// REGISTER VOLUNTEER
$success_message = "";
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_volunteers'])) {
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $affiliation = $_POST['affiliation'];
    $department = trim($_POST['department']);
    $availability = trim($_POST['availability']);
    $skills = trim($_POST['skills']);
    $interests = isset($_POST['interests']) ? implode(", ", $_POST['interests']) : "";
    $registration_date = date("Y-m-d H:i:s");
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (!empty($id) && !empty($name) && !empty($email) && !empty($affiliation) && !empty($availability)) {
        $check_id = $conn->prepare("SELECT id FROM volunteers WHERE id=? OR email=?");
        $check_id->bind_param("is", $id, $email);
        $check_id->execute();
        $check_id->store_result();
        if ($check_id->num_rows > 0) {
            $error_message = "This ID or Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO volunteers (id, name, email, phone, affiliation, department, availability, skills, interests, registration_date, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssssss", $id, $name, $email, $phone, $affiliation, $department, $availability, $skills, $interests, $registration_date, $hashed_password);
            if ($stmt->execute()) { 
                $success_message = "volunteers registered successfully! You can now log in."; 
                $_POST = array(); 
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_id->close();
    } else {
        $error_message = "Please fill all required fields.";
    }
}

// Fetch volunteers (for admin)
$volunteers = [];
if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'){
    $result = $conn->query("SELECT * FROM volunteers ORDER BY registration_date DESC");
    if($result) while($row = $result->fetch_assoc()) $volunteers[] = $row;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SKST Volunteer System</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
    --primary: #1E3A8A;
    --secondary: #3B82F6;
    --light: #F3F4F6;
    --dark: #111827;
    --accent: #2563EB;
    --text: #111827;
    --text-light: #6B7280;
    --success: #10B981;
    --error: #EF4444;
}
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}
body {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.container {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
}
header {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
    margin-bottom: 2px;
}
header .logo {
    font-size: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}
header .logo i {
    margin-right: 10px;
    color: var(--secondary);
}
nav a {
    color: white;
    text-decoration: none;
    margin-left: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: 0.3s;
}
nav a.active, nav a:hover {
    background: var(--secondary);
}
.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    padding: 2rem;
    margin-bottom: 2rem;
    transition: transform 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
}
.card h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    text-align: center;
    position: relative;
    padding-bottom: 10px;
}
.card h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: var(--secondary);
    border-radius: 3px;
}
.btn {
    display: inline-block;
    background: var(--accent);
    color: white;
    padding: 0.8rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    transition: 0.3s;
    margin: 0.5rem 0;
    text-align: center;
    border: none;
    cursor: pointer;
    font-weight: 500;
    width: 100%;
}
.btn:hover {
    background: var(--secondary);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
}
.btn-secondary {
    background: var(--text-light);
}
.btn-secondary:hover {
    background: #4B5563;
}
form input, form select, form textarea {
    width: 100%;
    padding: 0.8rem 1.2rem;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}
form input:focus, form select:focus, form textarea:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}
form textarea {
    min-height: 120px;
    resize: vertical;
}
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}
.alert i {
    margin-right: 10px;
    font-size: 1.2rem;
}
.alert-success {
    background: #D1FAE5;
    color: #065F46;
}
.alert-error {
    background: #FEE2E2;
    color: #B91C1C;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #E5E7EB;
}
th {
    background: #F3F4F6;
    color: var(--primary);
}
tr:hover {
    background: #F9FAFB;
}
.checkbox-group {
    margin-bottom: 1rem;
}
.checkbox-group p {
    margin-bottom: 0.5rem;
    font-weight: 500;
}
.checkbox-group label {
    display: inline-flex;
    align-items: center;
    margin-right: 1rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
}
.checkbox-group input[type="checkbox"] {
    width: auto;
    margin-right: 0.5rem;
    margin-bottom: 0;
}
.login-container {
    max-width: 450px;
    margin: 2rem auto;
}
.login-card {
    text-align: center;
}
.demo-accounts {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
    color: white;
}
.demo-accounts h3 {
    margin-bottom: 1rem;
    text-align: center;
}
.demo-account {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.8rem;
    border-radius: 8px;
    margin-bottom: 0.8rem;
}
.demo-account:last-child {
    margin-bottom: 0;
}
.welcome-message {
    text-align: center;
    margin-bottom: 2rem;
}
.welcome-message i {
    font-size: 4rem;
    color: var(--secondary);
    margin-bottom: 1rem;
}
.card-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .card-container {
        grid-template-columns: 1fr;
    }
    header {
        flex-direction: column;
        text-align: center;
    }
    nav {
        margin-top: 1rem;
    }
    nav a {
        margin: 0 0.5rem;
    }
}
</style>
</head>
<body>

<?php if($page != 'login' && $page != 'register'): ?>
<header>
<div class="logo"><i class="fas fa-hands-helping"></i> SKST Volunteers</div>
<nav>
    <a href="?page=home" class="<?php echo $page=='home'?'active':'';?>">Home</a>
    <?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
    <a href="?page=volunteers" class="<?php echo $page=='volunteers'?'active':'';?>">Volunteers</a>
    <?php endif;?>
    <a href="?logout=1" style="background:#B91C1C;">Logout</a>
</nav>
</header>
<?php endif;?>

<div class="container">

<?php if($page == 'login'): ?>
<div class="login-container">
    <div class="card login-card">
        <h2><i class="fas fa-hands-helping"></i><br>SKST Volunteer Portal</h2>
        <?php if($login_error):?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $login_error; ?></span>
        </div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="ID / Email / Admin Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        <p style="margin-top: 1rem; color: var(--text-light);">
            <a href="?page=register" style="color: var(--secondary); text-decoration: none;">Forgot Password?</a>
        </p>
    </div>
    
    <div class="demo-accounts">
        <h3>Demo Accounts</h3>
        <div class="demo-account">
            <strong>Admin:</strong> admin / admin123
        </div>
        <div class="demo-account">
            <strong>Volunteer (ID):</strong> 1001 / volunteer123
        </div>
        <div class="demo-account">
            <strong>Volunteer (Email):</strong> john@skst.edu / volunteer123
        </div>
        <p style="margin-top: 1rem; text-align: center;">
            <a href="?page=register" style="color: white; text-decoration: none;">
                New Volunteer? Register here <i class="fas fa-arrow-right"></i>
            </a>
        </p>
    </div>
</div>

<?php elseif($page == 'home' && isset($_SESSION['user'])): ?>
<div class="card">
    <div class="welcome-message">
        <i class="fas fa-check-circle"></i>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
        <p>You are logged in as <b><?php echo $_SESSION['role'] == 'admin' ? 'Administrator' : 'Volunteer'; ?></b></p>
    </div>
    
    <?php if($_SESSION['role'] == 'admin'): ?>
    <div class="card-container">
        <div class="card">
            <h2>Volunteer Stats</h2>
            <p><i class="fas fa-users"></i> <strong>Total Volunteers:</strong> <?php echo count($volunteers); ?></p>
            <p><i class="fas fa-calendar-check"></i> <strong>Active This Month:</strong> <?php echo min(42, count($volunteers)); ?></p>
            <p><i class="fas fa-clock"></i> <strong>Total Hours:</strong> 1,245</p>
            <a href="?page=volunteers" class="btn">View Volunteers</a>
        </div>
        
        <div class="card">
            <h2>Quick Actions</h2>
            <a href="?page=register" class="btn">Register New Volunteer</a>
            <button class="btn">Schedule Event</button>
            <button class="btn">Send Announcement</button>
            <button class="btn btn-secondary">View Calendar</button>
        </div>
    </div>
    <?php else: ?>
    <div style="text-align: center;">
        <p>Thank you for volunteering with SKST University. Your contribution makes a difference!</p>
        <div style="margin-top: 2rem;">
            <button class="btn">View Upcoming Events</button>
            <button class="btn btn-secondary">Update Availability</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php elseif($page == 'register'): ?>
<div class="card">
    <h2>Volunteer Registration</h2>
    <?php if($success_message):?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $success_message; ?></span>
    </div>
    <?php endif; ?>
    <?php if($error_message):?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $error_message; ?></span>
    </div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="add_volunteer" value="1">
        <input type="number" name="id" placeholder="Student ID *" required value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>">
        <input type="text" name="name" placeholder="Full Name *" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
        <input type="email" name="email" placeholder="Email *" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <input type="tel" name="phone" placeholder="Phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
        <select name="affiliation" required>
            <option value="">Select Affiliation</option>
            <option value="undergrad" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'undergrad') ? 'selected' : ''; ?>>Undergraduate</option>
            <option value="grad" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'grad') ? 'selected' : ''; ?>>Graduate</option>
            <option value="faculty" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'faculty') ? 'selected' : ''; ?>>Faculty</option>
            <option value="staff" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
            <option value="alumni" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
        </select>
        <input type="text" name="department" placeholder="Department" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
        <textarea name="availability" placeholder="Availability *" required><?php echo isset($_POST['availability']) ? htmlspecialchars($_POST['availability']) : ''; ?></textarea>
        <textarea name="skills" placeholder="Skills"><?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?></textarea>
        <div class="checkbox-group">
            <p>Select Interests:</p>
            <label><input type="checkbox" name="interests[]" value="events" <?php echo (isset($_POST['interests']) && in_array('events', $_POST['interests'])) ? 'checked' : ''; ?>> Campus Events</label>
            <label><input type="checkbox" name="interests[]" value="community" <?php echo (isset($_POST['interests']) && in_array('community', $_POST['interests'])) ? 'checked' : ''; ?>> Community Service</label>
            <label><input type="checkbox" name="interests[]" value="orientation" <?php echo (isset($_POST['interests']) && in_array('orientation', $_POST['interests'])) ? 'checked' : ''; ?>> Student Orientation</label>
            <label><input type="checkbox" name="interests[]" value="sustainability" <?php echo (isset($_POST['interests']) && in_array('sustainability', $_POST['interests'])) ? 'checked' : ''; ?>> Sustainability</label>
            <label><input type="checkbox" name="interests[]" value="fundraising" <?php echo (isset($_POST['interests']) && in_array('fundraising', $_POST['interests'])) ? 'checked' : ''; ?>> Fundraising</label>
            <label><input type="checkbox" name="interests[]" value="tutoring" <?php echo (isset($_POST['interests']) && in_array('tutoring', $_POST['interests'])) ? 'checked' : ''; ?>> Tutoring</label>
        </div>
        <input type="password" name="password" placeholder="Password *" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password *" required>
        <button type="submit" class="btn">Submit</button>
        <a href="?page=login" class="btn btn-secondary">Login</a>
    </form>
</div>

<?php elseif($page == 'volunteers' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
<div class="card">
    <h2>Registered Volunteers</h2>
    <?php if(count($volunteers) > 0):?>
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Affiliation</th><th>Department</th><th>Registered On</th></tr>
        </thead>
        <tbody>
            <?php foreach($volunteers as $v):?>
            <tr>
                <td><?php echo htmlspecialchars($v['id']);?></td>
                <td><?php echo htmlspecialchars($v['name']);?></td>
                <td><?php echo htmlspecialchars($v['email']);?></td>
                <td><?php echo ucfirst($v['affiliation']);?></td>
                <td><?php echo htmlspecialchars($v['department']);?></td>
                <td><?php echo date('M j, Y', strtotime($v['registration_date']));?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <?php else:?>
    <p>No volunteers registered yet.</p>
    <?php endif;?>
</div>
<?php endif;?>

</div>
</body>
</html>