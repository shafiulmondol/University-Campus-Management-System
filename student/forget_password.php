<?php
// login.php
session_start();

// Function to transfer student data to session
function transferstdata($stdata) {
    $_SESSION['stdata'] = [
        'id' => $stdata['id'],
        'full_name' => $stdata['first_name'].$stdata['last_name'],
        'email' => $stdata['email'],
        'phone' => $stdata['phone'],
        'key' => $stdata['key'],
        'login_time' => date('Y-m-d H:i:s')
    ];
    
    return true;
}

$conn = mysqli_connect("localhost", "root", "", "skst_university");

// Set default timezone
date_default_timezone_set('Asia/Dhaka');

$error = "";
$show_password = false;
$recovered_password = "";

if (isset($_POST['forget_password'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $key = $_POST['key'];
    
    $query = "SELECT * FROM student_registration WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // For demonstration, using direct comparison
        if ($key == $student['key'] && $email == $student['email']) {
            $show_password = true;
            $recovered_password = $student['password'];
        } else {
            $error = "Invalid recovery key!";
        }
    } else {
        $error = "student not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Student Password Recovery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .login-header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #2b5876;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .input-with-icon input:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .login-btn {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .login-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .error-message {
            background: #ffecec;
            color: #e74c3c;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .password-result {
            text-align: center;
            padding: 20px;
            background: #f8faff;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #4e4376;
        }
        
        .password-result h3 {
            color: #2b5876;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .password-value {
            font-size: 24px;
            font-weight: bold;
            color: #2b5876;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 2px dashed #4e4376;
            word-break: break-all;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        
        .button-link {
            display: block;
            text-decoration: none;
            text-align: center;
        }
        
        .secondary-btn {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }
        
        .secondary-btn:hover {
            background: linear-gradient(135deg, #5a6268, #4e555b);
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #2b5876;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: color 0.3s;
        }
        
        .back-link a:hover {
            color: #4e4376;
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-container {
                border-radius: 10px;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-form {
                padding: 20px;
            }
            
            .password-value {
                font-size: 18px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../picture/SKST.png" alt="SKST Logo" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid white; margin-bottom: 15px;">
            <h1><i class="fas fa-university"></i> SKST University</h1>
            <p>Password Recovery Portal</p>
        </div>
        <div class="login-form">
            <?php
            // Display error message if exists
            if (!empty($error)) {
                echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $error . '</div>';
            }
            
            if ($show_password) {
                // Show the recovered password
                echo '
                <div class="password-result">
                    <h3><i class="fas fa-key"></i> Password Recovery Successful</h3>
                    <p>Your account password is:</p>
                    <div class="password-value">' . htmlspecialchars($recovered_password) . '</div>
                    <p>Please keep this password secure and consider changing it after login.</p>
                </div>
                <div class="action-buttons">
                    <a href="studentlogin.php" class="login-btn button-link">
                        <i class="fas fa-sign-in-alt"></i> Back to Login
                    </a>
                    <a href="student.html" class="login-btn secondary-btn button-link">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>';
            } else {
                // Show the recovery form
                echo '
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="student@skstuniversity.edu" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="key">Recovery Key</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="key" name="key" placeholder="Enter recovery key" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="forget_password" class="login-btn">
                        <i class="fas fa-key"></i> Recover Password
                    </button>
                    
                    <div class="back-link">
                        <a href="student.html">
                            <i class="fas fa-arrow-left"></i> Back to Main Portal
                        </a>
                    </div>
                </form>';
            }
            ?>
        </div>
    </div>
</body>
</html>