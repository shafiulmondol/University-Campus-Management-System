<?php
// login.php
session_start();

// Function to transfer admin data to session
function transferstdata($stdata) {
    $_SESSION['student_data'] = [
        'id' => $stdata['id'],
        'full_name' => $stdata['first_name'] . ' ' . $stdata['last_name'],
        'email' => $stdata['email'],
        'phone' => $stdata['student_phone'],
        'father_name' => $stdata['father_name'],
        'mother_name' => $stdata['mother_name'],
        'guardian_phone' => $stdata['guardian_phone'],
        'student_phone' => $stdata['student_phone'],
        'password' => $stdata['password'],
        'last_exam' => $stdata['last_exam'],
        'board' => $stdata['board'],
        'other_board' => $stdata['other_board'],
        'year_of_passing' => $stdata['year_of_passing'],
        'institution_name' => $stdata['institution_name'],
        'result' => $stdata['result'],
        'subject_group' => $stdata['subject_group'],
        'gender' => $stdata['gender'],
        'nationality' => $stdata['nationality'],
        'religion' => $stdata['religion'],
        'present_address' => $stdata['present_address'],
        'permanent_address' => $stdata['permanent_address'],
        'department' => $stdata['department'],
        'submission_date' => $stdata['submission_date'],
        'date_of_birth' => $stdata['date_of_birth'],
        'student_key' => $stdata['key'] ?? '',
        'role' => $stdata['role'] ?? 'Student',
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
    
    $query = "SELECT * FROM student_registration WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        
        // For demonstration, using direct comparison
        if ($password == $student['password'] && $email == $student['email']) {
            
            // Transfer student data using our function
            if (transferstdata($student)) {
                // Redirect to admin dashboard
                header("Location: studentbody.php");
                exit();
            } else {
                $error = "Failed to transfer student data!";
            }
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Student not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Student Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../admin/admin.css">
</head>
<body>
    <!-- ==========================login form =================== -->
    <div class="login-container">
        <div class="login-header">
            <img src="../picture/SKST.png" alt="SKST Logo" style="border-radius: 50%; height: 80px; width: 80px;">
            <h1><i class="fas fa-university"></i> SKST University</h1>
            <p>Student Portal Login</p>
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
                        <input type="email" id="email" name="email" placeholder="student@skstuniversity.edu" required>
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
                    <i class="fas fa-sign-in-alt"></i> <a style="text-decoration: none;color:aliceblue;" href="student.html">Sign Out</a>
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