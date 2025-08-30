<?php
// login.php
session_start();


// Function to transfer admin data to session
function transferAdminData($adminData) {
    $_SESSION['admin_data'] = [
        'id' => $adminData['id'],
        'full_name' => $adminData['full_name'],
        'email' => $adminData['email'],
        'phone' => $adminData['phone'],
        'admin_key' => $adminData['key'],
        'role' => $adminData['role'] ?? 'Administrator',
        'login_time' => date('Y-m-d H:i:s')
    ];
    
    return true;
}


$conn = mysqli_connect("localhost", "root", "", "skst_university");
    

      // Set default timezone
      date_default_timezone_set('Asia/Dhaka');

$error = "";

if (isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM admin_users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // For demonstration, using direct comparison
        if ($password == $admin['password'] && $email == $admin['email']) {
            
            // Transfer admin data using our function
            if (transferAdminData($admin)) {
                // Redirect to admin dashboard
                header("Location: adminbody.php");
                exit();
            } else {
                $error = "Failed to transfer admin data!";
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Admin not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="admin.css">
  <style>
      * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
       body {
    background: #f5f7fa;
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
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a2a6c 0%, #2b5876 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .login-header p {
            margin-top: 0.5rem;
            opacity: 0.9;
        }
        
        .login-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
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
            padding: 12px 15px 12px 45px;
            border: 2px solid #e1e5eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .input-with-icon input:focus {
            border-color: #2b5876;
            outline: none;
            box-shadow: 0 0 0 3px rgba(43, 88, 118, 0.2);
        }
        
        .login-btn {
            background: linear-gradient(135deg, #1a2a6c 0%, #2b5876 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 42, 108, 0.3);
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .footer-links a {
            color: #2b5876;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: #1a2a6c;
            text-decoration: underline;
        }
.bodysection {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            border-radius: 20px;
        }
        
        .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      height: 80px;
    }
        
      
  </style>
</head>
<body>
    <!-- ==========================login form =================== -->
    <div class="login-container">
        <div class="login-header">
            <img src="../picture/SKST.png" alt="SKST Logo" style="border-radius: 50%; height: 80px; width: 80px;">
            <h1><i class="fas fa-university"></i> SKST University</h1>
            <p>Administration Portal Login</p>
        </div>
        
        <div class="login-form">
            <?php
            // Display error message if exists
            if (!empty($error)) {
                echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . $error . '</div>';
            }
            ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="admin@skstuniversity.edu" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                </button>
                <button style="margin-top:10px" type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> <a style="text-decoration: none;color:aliceblue;" href="administration.html">Sign Out</a>
                </button>
            </form>
            <div class="footer-links">
               
                <p><a  href="forgert_pass.php"><i class="fas fa-key"></i> Forgot Password?</a> •
                 <a href="#"><i class="fas fa-question-circle"></i> Help</a></p>
            </div>
            
        </div>
    </div>
</body>
</html>