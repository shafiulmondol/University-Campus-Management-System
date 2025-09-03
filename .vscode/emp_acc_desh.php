<?php
// =======================
// Database Connection
// =======================
$host = "localhost";
$db   = "skst_university";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// =======================
// Fetch employee info
// =======================
$emp_id = "FAC001"; // Example: logged-in employee
$sql = "SELECT * FROM employees WHERE emp_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();
} else {
    die("Employee not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SKST University - Employee Portal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {font-family: Arial, sans-serif; margin:0; padding:0; background:#f4f4f4;}
.container {width:90%; margin:auto;}
header {background:#004080; color:white; padding:1rem;}
header .logo {display:flex; align-items:center;}
header .logo i {font-size:2rem; margin-right:10px;}
header .user-info {float:right; display:flex; align-items:center;}
header .user-info img {border-radius:50%; width:40px; height:40px; margin-right:10px;}
.main-content {display:flex; margin-top:20px;}
.sidebar {width:20%; background:#fff; padding:1rem; border-radius:8px;}
.sidebar-menu {list-style:none; padding:0;}
.sidebar-menu li {margin:10px 0;}
.sidebar-menu li a {text-decoration:none; color:#004080;}
.sidebar-menu li a.active {font-weight:bold;}
.content {flex:1; background:#fff; margin-left:20px; padding:1rem; border-radius:8px;}
.profile-header {display:flex; align-items:center; margin-bottom:20px;}
.profile-img img {width:100px; border-radius:50%; margin-right:20px;}
.info-grid {display:flex; gap:20px; flex-wrap:wrap;}
.info-card {background:#e6f2ff; padding:1rem; border-radius:8px; flex:1; min-width:200px;}
.info-item {margin:5px 0;}
.info-label {font-weight:bold;}
.action-buttons button {margin:5px; padding:10px 15px; border:none; border-radius:5px; cursor:pointer;}
.btn-primary {background:#004080; color:white;}
.btn-secondary {background:#6c757d; color:white;}
.btn-success {background:#28a745; color:white;}
.footer {background:#004080; color:white; text-align:center; padding:10px; margin-top:20px;}
.section {margin-bottom:30px;}
.section h3 {border-bottom:1px solid #004080; padding-bottom:5px;}
</style>
</head>
<body>
<header>
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-university"></i>
                <div>
                    <h1>SKST University</h1>
                    <div>Employee Self-Service Portal</div>
                </div>
            </div>
            <div class="user-info">
                <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Employee">
                <div>
                    <div><?php echo $employee['first_name'] . " " . $employee['last_name']; ?></div>
                    <div style="font-size: 0.8rem;"><?php echo $employee['position']; ?></div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container">
    <div class="main-content">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#" class="active"><i class="fas fa-user"></i> My Profile</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Attendance</a></li>
                <li><a href="#"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> Leave Requests</a></li>
                <li><a href="#"><i class="fas fa-tasks"></i> Tasks</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="#"><i class="fas fa-question-circle"></i> Help</a></li>
                <li><a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="content">
            <!-- Profile Section -->
            <div class="section profile-section">
                <h3>My Employee Profile</h3>
                <div class="profile-header">
                    <div class="profile-img">
                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Employee">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo $employee['first_name'] . " " . $employee['last_name']; ?></h2>
                        <p>Employee ID: <?php echo $employee['emp_id']; ?></p>
                        <p><?php echo $employee['position'] . " - " . $employee['department']; ?> Department</p>
                        <p>Status: <span style="color:green;"><?php echo ucfirst($employee['status']); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Attendance Section -->
            <div class="section attendance-section">
                <h3>Attendance</h3>
                <div class="info-card">
                    <div class="info-item"><span class="info-label">Last Attendance Date:</span> <?php echo $employee['last_attendance_date'] ?? 'N/A'; ?></div>
                    <div class="info-item"><span class="info-label">Attendance Status:</span> <?php echo $employee['attendance_status'] ?? 'N/A'; ?></div>
                </div>
            </div>

            <!-- Payroll Section -->
            <div class="section payroll-section">
                <h3>Payroll</h3>
                <div class="info-card">
                    <div class="info-item"><span class="info-label">Base Salary:</span> <?php echo $employee['base_salary']; ?></div>
                    <div class="info-item"><span class="info-label">Bonus:</span> <?php echo $employee['bonus']; ?></div>
                    <div class="info-item"><span class="info-label">Deductions:</span> <?php echo $employee['deductions']; ?></div>
                    <div class="info-item"><span class="info-label">Net Salary:</span> <?php echo $employee['net_salary']; ?></div>
                    <div class="info-item"><span class="info-label">Last Pay Date:</span> <?php echo $employee['last_pay_date'] ?? 'N/A'; ?></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-primary"><i class="fas fa-download"></i> Download Profile</button>
                <button class="btn btn-secondary"><i class="fas fa-print"></i> Print Profile</button>
                <button class="btn btn-success"><i class="fas fa-edit"></i> Request Update</button>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <div class="container">
        <p>© 2025 SKST University Employee Portal. All rights reserved.</p>
    </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
