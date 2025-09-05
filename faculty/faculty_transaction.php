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
$faculty = null;
$payments = array();

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Check if faculty exists with this email and password
    $sql = "SELECT faculty_id, name, email, department FROM faculty WHERE email = ? AND password = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt) {
        $stmt->execute([$email, $password]);
        $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($faculty) {
            // Set session variables
            $_SESSION['faculty_id'] = $faculty['faculty_id'];
            $_SESSION['faculty_name'] = $faculty['name'];
            $_SESSION['faculty_email'] = $faculty['email'];
            
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

// If faculty is logged in, get payment information
if (isset($_SESSION['faculty_id'])) {
    $faculty_id = $_SESSION['faculty_id'];
    
    // Fetch faculty details
    $sql = "SELECT * FROM faculty WHERE faculty_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Fetch payment details from faculty_payments table
    $sql = "SELECT * FROM faculty_payments WHERE faculty_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$faculty_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $total_processed = 0;
    $total_pending = 0;
    $total_failed = 0;
    
    foreach ($payments as $payment) {
        if ($payment['status'] == 'processed') {
            $total_processed += $payment['amount'];
        } else if ($payment['status'] == 'pending') {
            $total_pending += $payment['amount'];
        } else if ($payment['status'] == 'failed') {
            $total_failed += $payment['amount'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Payment Portal - SKST University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2b5876;
            --secondary-color: #4e4376;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            box-shadow: 0 0 0 2px rgba(43, 88, 118, 0.2);
        }
        
        .login-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .payment-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .summary-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1a3c52;
            border-color: #1a3c52;
        }
        
        .faculty-info {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            padding: 20px;
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
            color: #6c757d;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            border-top: 1px solid #e9ecef;
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
        
        .payment-type-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .type-salary {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .type-bonus {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .type-allowance {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .type-reimbursement {
            background-color: #f3e5f5;
            color: #7b1fa2;
        }
        
        .type-other {
            background-color: #f5f5f5;
            color: #424242;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo {
            height: 60px;
            margin-right: 15px;
            border-radius: 50%;
        }
        
        @media (max-width: 768px) {
            .login-container {
                padding: 10px;
            }
            
            .stats-number {
                font-size: 24px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .header h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <?php if (!isset($_SESSION['faculty_id'])): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1>Faculty Payment Portal</h1>
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
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="mt-3 text-center">
                    <p class="text-muted">Use your faculty email and password to login</p>
                </div>
            </form>
        </div>
    </div>

    <?php else: ?>
    <!-- Dashboard Page -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 logo-container">
                    <img src="../picture/SKST.png" alt="SKST University Logo" class="logo">
                    <div>
                        <h1>SKST University</h1>
                        <h2 class="mb-0">Faculty Payment Portal</h2>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../index.html" class="btn btn-light me-2">
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
        <div class="faculty-info">
            <div class="row">
                <div class="col-md-8">
                    <h4>Welcome, <?php echo htmlspecialchars($_SESSION['faculty_name']); ?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Faculty ID:</strong> <?php echo htmlspecialchars($_SESSION['faculty_id']); ?></p>
                            <p class="mb-1"><strong>Department:</strong> <?php echo htmlspecialchars($faculty['department']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['faculty_email']); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($faculty['phone']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="mt-3">
                        <span class="badge bg-primary p-2">Room: <?php echo htmlspecialchars($faculty['room_number']); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Processed</h5>
                        <div class="stats-number">৳<?php echo number_format($total_processed, 2); ?></div>
                        <p class="stats-label">Completed Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Pending</h5>
                        <div class="stats-number">৳<?php echo number_format($total_pending, 2); ?></div>
                        <p class="stats-label">Pending Payments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card summary-card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Failed</h5>
                        <div class="stats-number">৳<?php echo number_format($total_failed, 2); ?></div>
                        <p class="stats-label">Failed Payments</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="card-title mb-4"><i class="fas fa-history me-2"></i>Your Payment History</h4>
                
                <?php if (count($payments) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Payment Type</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_id']); ?></td>
                                <td><strong>৳<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                <td>
                                    <span class="payment-type-badge type-<?php echo $payment['payment_type']; ?>">
                                        <?php echo ucfirst($payment['payment_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($payment['payment_month']); ?> <?php echo htmlspecialchars($payment['payment_year']); ?></td>
                                <td>
                                    <span class="payment-status status-<?php echo $payment['status']; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['description']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No payment records found.
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
        
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to stats cards
            const statsCards = document.querySelectorAll('.summary-card');
            statsCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
            
            // Add hover effect to table rows
            const tableRows = document.querySelectorAll('table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
ob_end_flush();
?>