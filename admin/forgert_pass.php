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
$show_password = false;
$recovered_password = "";

if (isset($_POST['forget_password'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $key = $_POST['key'];
    
    $query = "SELECT * FROM admin_users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // For demonstration, using direct comparison
        if ($key == $admin['key'] && $email == $admin['email']) {
            $show_password = true;
            $recovered_password = $admin['password'];
        } else {
            $error = "Invalid recovery key!";
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
    <title>SKST University - Admin Password Recovery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="admin.css">
   <style>
    
        .login-header img {
            border-radius: 50%;
            height: 80px;
            width: 80px;
            margin-bottom: 15px;
            border: 3px solid white;
        }
        
        
        
        .secondary-btn {
            background: #6c757d;
        }
        
        .secondary-btn:hover {
            background: #5a6268;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .error-message i {
            margin-right: 10px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .success-message i {
            margin-right: 10px;
        }
        
        .password-result {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .password-result h3 {
            color: #1a2a6c;
            margin-bottom: 10px;
        }
        
        .password-value {
            font-size: 24px;
            font-weight: bold;
            color: #b21f1f;
            margin: 15px 0;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
            border: 1px dashed #ccc;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        a.button-link {
            display: block;
            text-decoration: none;
            color: white;
            text-align: center;
        }
         
        
      
   </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="../picture/SKST.png" alt="SKST Logo" style="border-radius: 50%; height: 80px; width: 80px;">
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
                    <div class="password-value">' . $recovered_password . '</div>
                    <p>Please keep this password secure and consider changing it after login.</p>
                </div>
                <div class="action-buttons">
                    <a href="adminlogin.php" class="login-btn button-link">
                        <i class="fas fa-sign-in-alt"></i> Back to Login
                    </a>
                    <a href="administration.html" class="login-btn secondary-btn button-link">
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
                            <input type="email" id="email" name="email" placeholder="admin@skstuniversity.edu" required>
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
                    
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="administration.html" style="color: #1a2a6c; text-decoration: none;">
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