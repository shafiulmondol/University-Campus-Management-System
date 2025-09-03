<?php
// Start session and check authentication
session_start();

// Database connection
$host = 'localhost';
$dbname = 'skst_portal';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Check if user is logged in, otherwise redirect to login
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Get student data
$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get payment data
$stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY payment_date DESC");
$stmt->execute([$student_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate financial summary
$total_paid = 0;
foreach ($payments as $payment) {
    if ($payment['status'] === 'completed') {
        $total_paid += $payment['amount'];
    }
}

$program_cost = 350000; // This would come from database based on department
$remaining_balance = $program_cost - $total_paid;
$completion_percentage = ($total_paid / $program_cost) * 100;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>SKST Student Dashboard · Scholarship & Payment System</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* All the CSS from the original HTML remains here */
:root {
  --bg: #f8fafc;
  --card: #ffffff;
  --muted: #64748b;
  --text: #1e293b;
  --accent: #4f46e5;
  --accent-light: #6366f1;
  --accent-2: #0d9488;
  --danger: #ef4444;
  --success: #10b981;
  --warning: #f59e0b;
  --registration: #8b5cf6;
  --cse: #4f46e5;
  --eee: #f59e0b;
  --civil: #10b981;
  --mech: #ef4444;
  --textile: #a855f7;
  --radius: 16px;
  --pad: 24px;
  --shadow: rgba(0,0,0,0.05);
  --gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
}
/* ... (all the CSS styles from the original HTML) ... */
</style>
</head>
<body>
<div class="wrap">
<header>
<div class="title">
  <div class="logo">৳</div>
  <div>
    <h1>SKST Student Dashboard</h1>
    <div class="subtitle">Welcome back, <?php echo htmlspecialchars($student['full_name']); ?></div>
  </div>
</div>
<div>
  <button class="btn">
    <i class="fas fa-bell"></i>
    Notifications
  </button>
  <a href="logout.php" class="btn primary">
    <i class="fas fa-sign-out-alt"></i>
    Logout
  </a>
</div>
</header>

<!-- Student Info Section -->
<div class="student-info">
  <div class="student-avatar"><?php echo substr($student['full_name'], 0, 1); ?></div>
  <div class="student-details">
    <h2 class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></h2>
    <div class="student-id">ID: <?php echo htmlspecialchars($student['student_id']); ?></div>
    <div class="student-contact">
      <div class="contact-item">
        <i class="fas fa-envelope" style="color: var(--muted);"></i>
        <?php echo htmlspecialchars($student['email']); ?>
      </div>
      <div class="contact-item">
        <i class="fas fa-phone" style="color: var(--muted);"></i>
        <?php echo htmlspecialchars($student['phone']); ?>
      </div>
      <div class="contact-item">
        <i class="fas fa-calendar" style="color: var(--muted);"></i>
        Joined: <?php echo date('F Y', strtotime($student['enrollment_date'])); ?>
      </div>
    </div>
  </div>
</div>

<!-- Dashboard Menu -->
<div class="dashboard-menu">
  <div class="menu-item">
    <div class="menu-icon">
      <i class="fas fa-book"></i>
    </div>
    <div class="menu-label">My Courses</div>
  </div>
  <div class="menu-item">
    <div class="menu-icon">
      <i class="fas fa-credit-card"></i>
    </div>
    <div class="menu-label">Make Payment</div>
  </div>
  <div class="menu-item">
    <div class="menu-icon">
      <i class="fas fa-file-invoice"></i>
    </div>
    <div class="menu-label">Payment History</div>
  </div>
  <div class="menu-item">
    <div class="menu-icon">
      <i class="fas fa-graduation-cap"></i>
    </div>
    <div class="menu-label">Scholarship</div>
  </div>
  <div class="menu-item">
    <div class="menu-icon">
      <i class="fas fa-user-cog"></i>
    </div>
    <div class="menu-label">Profile Settings</div>
  </div>
</div>

<!-- Department Information -->
<div class="department-section">
  <div class="department-header">
    <i class="fas fa-graduation-cap" style="color: var(--accent);"></i>
    <h3>My Department</h3>
  </div>
  <div class="department-grid">
    <div class="department-option cse selected">
      <div class="department-icon" style="color: var(--cse);">
        <i class="fas fa-laptop-code"></i>
      </div>
      <div class="department-name"><?php echo htmlspecialchars($student['department']); ?></div>
      <div class="department-cost">৳700,000</div>
    </div>
  </div>
</div>

<section class="grid">
<div>
  <!-- Registration Fee Section -->
  <div class="registration-section">
    <div class="registration-header">
      <div>
        <div style="font-size: 18px; font-weight: 600; color: var(--registration);">Registration Fee</div>
        <div style="font-size: 14px; color: var(--muted);">One-time payment at the beginning of your program</div>
      </div>
      <div class="registration-status">
        <span>Status:</span>
        <span class="status-badge status-paid">Paid</span>
      </div>
    </div>
    <div class="registration-fee">৳15,000</div>
    <div style="margin-top: 16px;">
      <div class="breakdown-item">
        <span>Registration Fee</span>
        <span>৳15,000</span>
      </div>
      <div class="breakdown-item">
        <span>Payment Date</span>
        <span>January 10, 2023</span>
      </div>
      <div class="breakdown-item">
        <span>Payment Method</span>
        <span>bKash</span>
      </div>
    </div>
  </div>

  <!-- Scholarship Information Section -->
  <div class="scholarship-section scholarship-50">
    <div class="scholarship-header">
      <div>
        <div style="font-size: 18px; font-weight: 600; color: var(--success);">Scholarship Benefits</div>
        <div style="font-size: 14px; color: var(--muted);">Your current scholarship details</div>
      </div>
    </div>
    <div class="scholarship-details">
      <div class="scholarship-detail">
        <div class="label">Scholarship Type</div>
        <div class="value">50% Scholarship</div>
      </div>
      <div class="scholarship-detail">
        <div class="label">Discount on Tuition</div>
        <div class="value">50%</div>
      </div>
      <div class="scholarship-detail">
        <div class="label">Total Savings</div>
        <div class="value">৳325,000</div>
      </div>
      <div class="scholarship-detail">
        <div class="label">Final Program Cost</div>
        <div class="value">৳350,000</div>
      </div>
    </div>
  </div>

  <!-- Program Cost Highlight -->
  <div class="program-cost">
    Total Program Cost: ৳350,000
    <span class="department-badge cse-badge">CSE</span>
  </div>

  <!-- Payment History -->
  <div class="card">
    <div class="card-head">Recent Payments</div>
    <div class="card-body">
      <div class="payment-history">
        <table class="history-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $payment): ?>
            <tr>
              <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
              <td><?php echo htmlspecialchars($payment['description']); ?></td>
              <td class="amount-paid">৳<?php echo number_format($payment['amount']); ?></td>
              <td class="status-<?php echo $payment['status']; ?>">
                <?php echo ucfirst($payment['status']); ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div>
  <!-- Financial Summary -->
  <div class="card">
    <div class="card-head">Financial Summary</div>
    <div class="card-body">
      <div class="summary">
        <div class="metric metric--registration">
          <div class="label">Registration Fee</div>
          <div class="value">৳15,000</div>
        </div>
        <div class="metric metric--payable">
          <div class="label">Total Program Payable</div>
          <div class="value">৳<?php echo number_format($program_cost); ?></div>
        </div>
        <div class="metric metric--paid">
          <div class="label">Total Paid</div>
          <div class="value">৳<?php echo number_format($total_paid); ?></div>
        </div>
        <div class="metric metric--due">
          <div class="label">Remaining Balance</div>
          <div class="value">৳<?php echo number_format($remaining_balance); ?></div>
        </div>
      </div>
      
      <div class="progress-bar">
        <div class="progress-fill" style="width: <?php echo $completion_percentage; ?>%"></div>
      </div>
      <div class="amount-details">
        <div>Completed: <span><?php echo round($completion_percentage, 1); ?>%</span></div>
        <div>Remaining: <span><?php echo round(100 - $completion_percentage, 1); ?>%</span></div>
      </div>

      <div class="fee-breakdown" style="margin-top: 24px;">
        <div class="breakdown-item">
          <span>Semesters Completed</span>
          <span>3/12</span>
        </div>
        <div class="breakdown-item">
          <span>Next Payment Due</span>
          <span>January 1, 2024</span>
        </div>
        <div class="breakdown-item">
          <span>Avg. Semester Cost</span>
          <span>৳29,167</span>
        </div>
        <div class="breakdown-item breakdown-total">
          <span>Year 1 Total</span>
          <span>৳102,500</span>
        </div>
      </div>
    </div>
  </div>
  
  <div class="spacer"></div>
  
  <!-- Upcoming Payments -->
  <div class="card">
    <div class="card-head">Upcoming Payments</div>
    <div class="card-body">
      <div class="payment-entry">
        <div>
          <div>Semester 4 Tuition</div>
          <div class="payment-date">Due: January 1, 2024</div>
        </div>
        <div class="payment-amount">৳29,167</div>
      </div>
      <div class="payment-entry">
        <div>
          <div>Semester 5 Tuition</div>
          <div class="payment-date">Due: April 1, 2024</div>
        </div>
        <div class="payment-amount">৳29,167</div>
      </div>
      <div class="payment-entry">
        <div>
          <div>Semester 6 Tuition</div>
          <div class="payment-date">Due: July 1, 2024</div>
        </div>
        <div class="payment-amount">৳29,167</div>
      </div>
      
      <div style="margin-top: 16px; padding: 12px; background: #f1f5f9; border-radius: 8px;">
        <div style="font-size: 13px; color: var(--muted);">Next payment amount:</div>
        <div style="font-size: 16px; font-weight: 600;">৳29,167</div>
      </div>
      
      <button class="btn primary" style="width: 100%; margin-top: 16px;">
        <i class="fas fa-credit-card"></i>
        Pay Now
      </button>
    </div>
  </div>

  <div class="spacer"></div>

  <!-- Academic Progress -->
  <div class="card">
    <div class="card-head">Academic Progress</div>
    <div class="card-body">
      <div class="breakdown-item">
        <span>Current Semester</span>
        <span>Semester 4</span>
      </div>
      <div class="breakdown-item">
        <span>CGPA</span>
        <span>3.75</span>
      </div>
      <div class="breakdown-item">
        <span>Credits Completed</span>
        <span>45/144</span>
      </div>
      <div class="breakdown-item">
        <span>Expected Graduation</span>
        <span>December 2026</span>
      </div>
      
      <div class="progress-bar" style="margin-top: 20px;">
        <div class="progress-fill" style="width: 31.3%; background: var(--success);"></div>
      </div>
      <div class="amount-details">
        <div>Completed: <span>31.3%</span></div>
        <div>Remaining: <span>68.7%</span></div>
      </div>
    </div>
  </div>
</div>
</section>

<footer>SKST Student Portal — © 2023 All rights reserved</footer>
</div>

<script>
// Menu item click handlers
document.querySelectorAll('.menu-item').forEach(item => {
  item.addEventListener('click', function() {
    const label = this.querySelector('.menu-label').textContent;
    alert(`Navigating to ${label} section...`);
  });
});
</script>
</body>
</html>