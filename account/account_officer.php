<?php
session_start();

$host = 'localhost';
$db = 'skst_university';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create accountofficer table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS accountofficer (
    officer_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    avatar_initials VARCHAR(5) NOT NULL,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$mysqli->query($createTable)) {
    die("Error creating table: " . $mysqli->error);
}

// Create account officer if empty
$result = $mysqli->query("SELECT COUNT(*) AS count FROM accountofficer");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $hashedPassword = password_hash('university123', PASSWORD_DEFAULT);
    $insert = $mysqli->prepare("INSERT INTO accountofficer (username, password, full_name, email, avatar_initials) VALUES (?, ?, ?, ?, ?)");
    $username = 'accountofficer';
    $full_name = 'Account Officer';
    $email = 'accountofficer@skst.edu';
    $avatar = 'AO';
    $insert->bind_param("sssss", $username, $hashedPassword, $full_name, $email, $avatar);
    $insert->execute();
    $insert->close();
}

// Logout logic
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Handle login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT * FROM accountofficer WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['officer_id'] = $user['officer_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['avatar_initials'] = $user['avatar_initials'];

            $update_sql = $mysqli->prepare("UPDATE accountofficer SET last_login = NOW() WHERE officer_id = ?");
            $update_sql->bind_param("i", $user['officer_id']);
            $update_sql->execute();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $loginError = 'Invalid username or password.';
        }
    } else {
        $loginError = 'Invalid username or password.';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Account Officer Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: #1a5e63;
            --secondary: #28a745;
            --accent: #e74c3c;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --gold: #ffd700;
        }

        body {
            background: #f4f6f8;
        }

        .container {
            width: 100%;
            min-height: 100vh;
        }

        header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-icon {
            font-size: 40px;
            margin-right: 10px;
        }

        .logo-text h1 {
            font-size: 22px;
            margin-bottom: 2px;
        }

        .login-container {
            display: flex;
            padding: 40px;
            background: white;
            margin: 30px auto;
            width: 80%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .login-left, .login-right {
            flex: 1;
            padding: 20px;
        }

        .login-left h2 {
            margin-bottom: 20px;
            color: var(--dark);
        }

        .feature {
            margin: 10px 0;
            color: #333;
        }

        .feature i {
            color: var(--secondary);
            margin-right: 10px;
        }

        .login-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--dark);
        }

        .login-links {
            margin-top: 15px;
        }

        .login-links a {
            display: inline-block;
            margin-right: 15px;
            color: var(--primary);
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .success-message {
            color: green;
            margin-bottom: 10px;
        }

        .dashboard {
            padding: 30px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--primary);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            background: var(--gold);
            color: black;
            padding: 10px;
            border-radius: 50%;
            font-weight: bold;
        }

        .logout-btn {
            background: var(--accent);
            padding: 8px 12px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }

        .tab {
            padding: 10px 15px;
            background: #ddd;
            cursor: pointer;
            border-radius: 4px;
        }

        .tab.active {
            background: var(--primary);
            color: white;
        }

        .tab-content {
            background: white;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }

        .stat-card {
            flex: 1;
            background: #f1f1f1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .taka-symbol {
            font-weight: bold;
        }

        .account-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .account-table th, .account-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: gray;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Login -->
    <div class="login-container" id="loginScreen" style="<?= isset($_SESSION['officer_id']) ? 'display:none;' : 'display:flex;' ?>">
        <div class="login-left">
            <h2>Account Officer Dashboard</h2>
            <p>Manage university finances, student fees, and budgets.</p>
            <div class="features">
                <div class="feature"><i class="fas fa-check"></i> Student Fee Management</div>
                <div class="feature"><i class="fas fa-check"></i> Department Budget Allocation</div>
                <div class="feature"><i class="fas fa-check"></i> Financial Reporting</div>
                <div class="feature"><i class="fas fa-check"></i> Transaction History</div>
                <div class="feature"><i class="fas fa-check"></i> Salary & Expense Management</div>
            </div>
        </div>
        <div class="login-right">
            <form class="login-form" method="POST">
                <h3><i class="fas fa-lock"></i> Login</h3>

                <?php if ($loginError): ?>
                    <div class="error-message"><?= $loginError ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['logout'])): ?>
                    <div class="success-message">Successfully logged out.</div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>

                <div class="login-links">
                    <a href="#"><i class="fas fa-question-circle"></i> Forgot Password?</a>
                    <a href="#"><i class="fas fa-user-plus"></i> Request Access</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="dashboard" id="dashboard" style="<?= isset($_SESSION['officer_id']) ? 'display:block;' : 'display:none;' ?>">
        <div class="dashboard-header">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-chart-line"></i></div>
                <div class="logo-text">
                    <h1>Account Officer Dashboard</h1>
                    <span>SKST University</span>
                </div>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= $_SESSION['avatar_initials'] ?? 'AO' ?></div>
                <div>
                    <div><?= $_SESSION['full_name'] ?? 'Account Officer' ?></div>
                    <div><?= $_SESSION['email'] ?? 'accountofficer@skst.edu' ?></div>
                </div>
                <a href="?logout=true" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="nav-tabs">
            <div class="tab active" data-tab="dashboard">Dashboard</div>
            <div class="tab" data-tab="accounts">Account Management</div>
            <div class="tab" data-tab="transactions">Transactions</div>
            <div class="tab" data-tab="reports">Financial Reports</div>
        </div>

        <div class="tab-content">
            <div class="tab-pane active" id="dashboardTab">
                <h2>Welcome, <?= $_SESSION['full_name'] ?? 'Account Officer' ?></h2>
                <p>You have access to financial tools for SKST University.</p>

                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Balance</h3>
                        <div class="value"><span class="taka-symbol">৳</span> 1,24,58,900</div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Transactions</h3>
                        <div class="value">42</div>
                    </div>
                    <div class="stat-card">
                        <h3>Accounts</h3>
                        <div class="value">28</div>
                    </div>
                    <div class="stat-card">
                        <h3>Departments</h3>
                        <div class="value">14</div>
                    </div>
                </div>

                <h3>Recent Transactions (BDT)</h3>
                <table class="account-table">
                    <tr><th>Date</th><th>Description</th><th>Account</th><th>Amount</th></tr>
                    <tr><td>2023-10-15</td><td>Student Fees</td><td>Student Fees Account</td><td><span class="taka-symbol">৳</span> 8,52,000</td></tr>
                    <tr><td>2023-10-14</td><td>Science Equipment</td><td>Departmental</td><td><span class="taka-symbol">৳</span> 1,25,000</td></tr>
                </table>
            </div>
        </div>

        <div class="footer">
            SKST University © 2025 | All amounts in BDT
        </div>
    </div>
</div>

<script>
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            document.getElementById(this.getAttribute('data-tab') + 'Tab').classList.add('active');
        });
    });
</script>
</body>
</html>