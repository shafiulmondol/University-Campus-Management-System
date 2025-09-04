<?php
session_start();
ob_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'skst_university');

// Create connection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize variables
$error = '';
$email = '';
$student_id = '';
$student_name = '';
$payments = array();
$total_due = 0;
$total_paid = 0;

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Check if student exists with this email and password
    $sql = "SELECT id, first_name, last_name FROM student_registration WHERE email = ? AND password = ?";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $first_name, $last_name);
            $stmt->fetch();
            
            // Set session variables
            $_SESSION['student_id'] = $id;
            $_SESSION['student_name'] = $first_name . ' ' . $last_name;
            $_SESSION['student_email'] = $email;
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $error = "Database error: " . $mysqli->error;
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
    $student_name = $_SESSION['student_name'];
    
    // Fetch payment details
    $sql = "SELECT * FROM student_payments WHERE student_id = ? ORDER BY due_date";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
            
            if ($row['status'] == 'completed') {
                $total_paid += $row['amount'];
            } else {
                $total_due += $row['amount'];
            }
        }
        $stmt->close();
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
        :root {
            --primary-color: maroon;
            --secondary-color: #3949ab;
            --accent-color: #7986cb;
            --light-color: #f5f7fa;
            --dark-color: #121858;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
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
            background: white
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
            background: linear-gradient(135deg, #2b5876, #4e4376);;
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
            color: #2b5876;
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
            border-color: #2b5876;
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
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background: #ffecec;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .header {
            background: #2b5876;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
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
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .summary-card {
            background: maroon
            color: white;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .due-soon {
            border-left: 4px solid var(--warning-color);
        }
        
        .payment-type {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 3px solid var(--primary-color);
        }
        
        .stats-card {
            text-align: center;
            padding: 20px;
        }
        
        .stats-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stats-label {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        .student-info {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
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
                    <input type="email" id="email" name="email" required placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                
                <div class="form-group password-container">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                    
                </div>
                
                <button type="submit" class="login-btn">Login to Payment Portal</button>
                
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
                    <h1><i class="fas fa-university"></i> SKST University</h1>
                    <p class="mb-0">Student Payment Portal</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../index.html" class="btn btn-light">
                        <i class="fas fa-home"></i> Home
                    </a>
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
                <div class="col-md-6">
                    <h4>Welcome, <?php echo htmlspecialchars($student_name); ?></h4>
                    <p class="mb-0">Student ID: <?php echo htmlspecialchars($student_id); ?></p>
                    <p class="mb-0">Email: <?php echo htmlspecialchars($_SESSION['student_email']); ?></p>
                </div>
                
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Paid</h5>
                        <div class="stats-number">৳<?php echo number_format($total_paid, 2); ?></div>
                        <p class="stats-label">Completed Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Due</h5>
                        <div class="stats-number">৳<?php echo number_format($total_due, 2); ?></div>
                        <p class="stats-label">Pending Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Amount</h5>
                        <div class="stats-number">৳<?php echo number_format($total_paid + $total_due, 2); ?></div>
                        <p class="stats-label">All Payments</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4">Your Payment Transactions</h4>
                
                <?php if (count($payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Semester</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): 
                                $due_soon = (strtotime($payment['due_date']) - time() < 5 * 24 * 60 * 60 && $payment['status'] == 'pending');
                            ?>
                            <tr class="<?php echo $due_soon ? 'due-soon' : ''; ?>">
                                <td class="payment-type"><?php echo ucfirst($payment['payment_type']); ?></td>
                                <td>৳<?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y', strtotime($payment['due_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['semester']); ?></td>
                                <td><?php echo htmlspecialchars($payment['academic_year']); ?></td>
                                <td>
                                    <span class="payment-status status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
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