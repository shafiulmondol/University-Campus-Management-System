<?php
session_start();
ob_start();

// Database configuration
$host = 'localhost';
$dbname = 'skst_university';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$error = '';
$success = '';
$student = null;
$payments = array();

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Check if student exists with this email and password
    $sql = "SELECT id, first_name, last_name, email, department FROM student_registration WHERE email = ? AND password = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt) {
        $stmt->execute([$email, $password]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($student) {
            // Set session variables
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['student_email'] = $student['email'];
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Database error: " . $pdo->errorInfo()[2];
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// If student is logged in, get payment information
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    
    // Fetch student details
    $sql = "SELECT * FROM student_registration WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch payment details
    $sql = "SELECT * FROM university_bank_payments WHERE student_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $total_paid = 0;
    $total_due = 0;
    foreach ($payments as $payment) {
        if ($payment['status'] == 'verified') {
            $total_paid += $payment['amount'];
        } else {
            $total_due += $payment['amount'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Payment Portal - SKST University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .login-container {
            
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: white;
            
        }
        
        .login-box {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
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
            color: var(--primary-color);
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 2px rgba(26, 35, 126, 0.2);
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
        }
        
        .login-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .error-msg {
            color: var(--danger-color);
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #ffecec;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }
        
        .payment-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
        }
        
        .btn-primary {
            background-color: #3949ab;
            border-color: whitesmoke;
        }
        
        .btn-primary:hover {
            background-color: #1a237e;
            border-color: #1a237e;
        }
        
        .student-info {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-container input {
            padding-right: 40px;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        footer {
            color: black;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        .stats-card {
            text-align: center;
            padding: 20px;
        }
        
        .stats-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['student_id'])): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Student Payment Portal</h1>
                <p>SKST University - Access Your Payment Information</p>
            </div>
            
            <form class="login-form" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                
                <div class="form-group password-container">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="login-btn">Login to Payment Portal</button>
                <button class= "login-btn" onclick="location.href='../index.html'"> Sign Out</button>                


                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php else: ?>
    <!-- Dashboard Page -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><img src="../picture/SKST.png" alt="SKST University Logo" style="border-radius: 50%; height: 60px;"> SKST University</h1>
                    <h2 class="mb-0" style="margin-left: 70px; margin-top: -25px; margin-bottom: 20px;">Student Payment Portal</h2>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../index.php" class="btn btn-light me-2">
                        <i class="fas fa-home"></i> Home </a>      
                    <a href="?logout=1" class="btn btn-light">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="student-info">
            <div class="row">
                <div class="col-md-6" style="margin-bottom: 10px;">
                    <h4>Welcome, <?php echo htmlspecialchars($_SESSION['student_name']); ?></h4>
                    <p class="mb-0">Student ID: <?php echo htmlspecialchars($_SESSION['student_id']); ?></p>
                    <p class="mb-0">Department: <?php echo htmlspecialchars($student['department']); ?></p>
                    <p class="mb-0">Email: <?php echo htmlspecialchars($_SESSION['student_email']); ?></p>
                    <p class="mb-0">Phone: <?php echo htmlspecialchars($student['student_phone']); ?></p>
                
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Paid</h5>
                        <div class="stats-number">৳<?php echo number_format($total_paid, 2); ?></div>
                        <p class="stats-label">Verified Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Due</h5>
                        <div class="stats-number">৳<?php echo number_format($total_due, 2); ?></div>
                        <p class="stats-label">Pending Payments</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-history"></i> Your Payment History</h4>
                
                <?php if (count($payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Payment Type</th>
                                <th>Semester</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                <td>৳<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo ucfirst($payment['payment_type']); ?></td>
                                <td><?php echo htmlspecialchars($payment['semester']); ?></td>
                                <td><?php echo htmlspecialchars($payment['academic_year']); ?></td>
                                <td>
                                    <span class="payment-status status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    No payment records found.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p class="mb-0">© 2025 SKST University. All rights reserved.</p>
        </div>
    </footer>
    <?php endif; ?>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>