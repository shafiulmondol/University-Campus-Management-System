<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);



// Include your database connection file here
// require_once 'db_connection.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.html?logout=1");
    exit();
}

$error = "";
if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin_users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // FIX: Use password_verify() if passwords are hashed
        // For now, using direct comparison as in your code
        if ($password == $user['password'] && $email == $user['email']) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_phone'] = $user['phone'];
            $_SESSION['admin_key'] = $user['key'];
            
            // FIX: Proper redirect after successful login
            header("Location: ../notice.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKST University Admin Portal</title>
    <link rel="icon" href="picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            box-sizing: border-box; 
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #32465fff, #566fdcff);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 450px;
            width: 100%;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #2980b9, #00bfff, #08ed91ff);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            position: relative;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #2980b9, #00bfff);
            border-radius: 3px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo i {
            font-size: 40px;
            color: #2980b9;
            background: #e8f4fc;
            padding: 15px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
        
        input[type="text"], 
        input[type="password"], 
        input[type="email"],
        input[type="number"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus {
            border-color: #2980b9;
            box-shadow: 0 0 0 3px rgba(41, 128, 185, 0.2);
            outline: none;
        }
        
        button[type="submit"] {
            background: linear-gradient(90deg, #2980b9, #2c3e50);
            color: white;
            padding: 15px;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            background: linear-gradient(90deg, #1c598a, #1e2a3a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .back-btn {
            display: block;
            text-align: center;
            background: linear-gradient(90deg, #08ed91ff, #00b894);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 15px;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .back-btn:hover {
            background: linear-gradient(90deg, #00b894, #019d7a);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .error {
            background: #ffeaa7;
            color: #d63031;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: <?php echo $error ? 'block' : 'none'; ?>;
            border-left: 4px solid #d63031;
        }
        
        .success {
            background: #55efc4;
            color: #00b894;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
            border-left: 4px solid #00b894;
        }
        
        .additional-links {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }
        
        .additional-links a {
            color: #2980b9;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .additional-links a:hover {
            color: #1c598a;
            text-decoration: underline;
        }
        
        @media (max-width: 500px) {
            .container {
                padding: 25px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-university"></i>
        </div>
        
        <h1>SKST University Admin Login</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your Email" required autofocus />
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Enter your Password" required />
            </div>
            
            <button type="submit" name="login">Login</button>
        </form>
        
        <a href="../index.html" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="additional-links">
            <p><a href="#">Forgot Password?</a> | <a href="#">Contact Support</a></p>
        </div>
    </div>
</body>
</html>