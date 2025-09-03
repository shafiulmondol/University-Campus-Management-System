<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // MySQL username
$password = "";     // MySQL password
$dbname = "skst_university";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Handle Login
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $demo_user = 'accountofficer';
    $demo_pass = 'university123';

    if ($user === $demo_user && $pass === $demo_pass) {
        $_SESSION['username'] = $user;
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password']);
    }
    exit;
}

// Handle Logout
if (isset($_POST['action']) && $_POST['action'] == 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

// Fetch Dashboard Data
if (isset($_GET['action']) && $_GET['action'] == 'fetch_data') {
    if (!isset($_SESSION['username'])) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    $sql = "SELECT * FROM account_officer_data ORDER BY transaction_date DESC";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>University Account Officer Portal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Same CSS as your original code (login + dashboard) */
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f0f2f5;margin:0;padding:0}
.container{max-width:1200px;margin:20px auto;background:#fff;padding:0;border-radius:15px;overflow:hidden;box-shadow:0 15px 30px rgba(0,0,0,0.25)}
header{background:#1a5e63;color:#fff;padding:20px 30px;display:flex;justify-content:space-between;align-items:center}
.logo{display:flex;align-items:center;gap:15px}.logo-icon{background:#ffd700;color:#1a5e63;width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:bold}.login-container{display:flex;min-height:500px}.login-left{flex:1;background:linear-gradient(to bottom right,#1a5e63,#2c7744);color:#fff;padding:40px;display:flex;flex-direction:column;justify-content:center;position:relative;overflow:hidden}.login-right{flex:1;padding:40px;display:flex;flex-direction:column;justify-content:center;background:#f8f9fa}.login-form{max-width:400px;margin:0 auto;padding:30px;background:#fff;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.1)}.form-group{margin-bottom:20px}.form-group label{display:block;margin-bottom:8px;font-weight:500;color:#333}.form-control{width:100%;padding:12px;border:2px solid #ddd;border-radius:8px;font-size:16px}.btn{display:block;width:100%;padding:14px;background:#1a5e63;color:#fff;border:none;border-radius:8px;font-size:18px;font-weight:600;cursor:pointer}.btn:hover{background:#14474c}.error-message{color:#e74c3c;font-size:14px;margin-top:5px;display:none}.dashboard{display:none;min-height:600px}.account-table{width:100%;border-collapse:collapse;margin-top:20px;background:#fff;box-shadow:0 5px 15px rgba(0,0,0,0.05);border-radius:10px;overflow:hidden}.account-table th{background:#1a5e63;color:#fff;padding:15px;text-align:left;font-weight:500}.account-table td{padding:15px;border-bottom:1px solid #eee}.account-table tr:last-child td{border-bottom:none}.account-table tr:nth-child(even){background:#f9f9f9}.account-table tr:hover{background:#f1f7ff}
</style>
</head>
<body>
<div class="container">
    <!-- Login -->
    <header>
        <div class="logo">
            <div class="logo-icon"><i class="fas fa-university"></i></div>
            <div class="logo-text">
                <h1>SKST University</h1>
                <span>Account Officer Portal</span>
            </div>
        </div>
    </header>

    <div class="login-container" id="loginScreen">
        <div class="login-left">
            <h2>Account Officer Dashboard</h2>
            <p>Manage university finances, student fees, and department budgets.</p>
        </div>
        <div class="login-right">
            <form class="login-form" id="loginForm">
                <h3><i class="fas fa-lock"></i> Login</h3>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="username" class="form-control" required>
                    <div class="error-message" id="usernameError"></div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" class="form-control" required>
                    <div class="error-message" id="passwordError"></div>
                </div>
                <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            </form>
        </div>
    </div>

    <!-- Dashboard -->
    <div class="dashboard" id="dashboard">
        <div style="padding:20px;">
            <button class="btn" id="logoutBtn" style="width:auto;margin-bottom:20px;"><i class="fas fa-sign-out-alt"></i> Logout</button>
            <h2>Recent Transactions</h2>
            <table class="account-table" id="dataTable">
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th>Amount (BDT)</th>
                    <th>Status</th>
                </tr>
            </table>
        </div>
    </div>
</div>

<script>
// Login
document.getElementById('loginForm').addEventListener('submit', function(e){
    e.preventDefault();
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value.trim();
    fetch('', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'action=login&username='+encodeURIComponent(user)+'&password='+encodeURIComponent(pass)
    }).then(res=>res.json()).then(data=>{
        if(data.status==='success'){
            document.getElementById('loginScreen').style.display='none';
            document.getElementById('dashboard').style.display='block';
            loadData();
        } else {
            document.getElementById('usernameError').textContent=data.message;
            document.getElementById('usernameError').style.display='block';
        }
    });
});

// Logout
document.getElementById('logoutBtn').addEventListener('click', function(){
    fetch('', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=logout'})
    .then(res=>res.json()).then(data=>{
        if(data.status==='success'){
            document.getElementById('loginScreen').style.display='flex';
            document.getElementById('dashboard').style.display='none';
            document.getElementById('username').value='';
            document.getElementById('password').value='';
            document.getElementById('usernameError').style.display='none';
        }
    });
});

// Fetch Data
function loadData(){
    fetch('?action=fetch_data').then(res=>res.json()).then(data=>{
        if(data.status==='success'){
            const table = document.getElementById('dataTable');
            data.data.forEach(row=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.transaction_date || ''}</td>
                                <td>${row.student_name || ''}</td>
                                <td>${row.account_name || ''}</td>
                                <td>${row.transaction_description || ''}</td>
                                <td>৳ ${row.transaction_amount || ''}</td>
                                <td>${row.transaction_status || ''}</td>`;
                table.appendChild(tr);
            });
        }
    });
}
</script>
</body>
</html>
