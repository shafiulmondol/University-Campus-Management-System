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