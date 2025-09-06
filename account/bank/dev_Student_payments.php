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

// Handle form actions
$action = $_POST['action'] ?? '';
$bank_payment_id = $_POST['bank_payment_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';

// Add or Update payment
if ($action === 'save') {
    $transaction_id = $_POST['transaction_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $payment_type = $_POST['payment_type'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if ($bank_payment_id) {
        // Update existing payment
        $sql = "UPDATE university_bank_payments SET student_id=?, transaction_id=?, amount=?, payment_type=?, semester=?, academic_year=?, status=? WHERE bank_payment_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $transaction_id, $amount, $payment_type, $semester, $academic_year, $status, $bank_payment_id]);
    } else {
        // Insert new payment
        $sql = "INSERT INTO university_bank_payments (student_id, transaction_id, amount, payment_type, semester, academic_year, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $transaction_id, $amount, $payment_type, $semester, $academic_year, $status]);
    }
}

// Delete payment
if ($action === 'delete' && $bank_payment_id) {
    $sql = "DELETE FROM university_bank_payments WHERE bank_payment_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bank_payment_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'bank_payment_id';
$order = $_GET['order'] ?? 'DESC';

// Build query with search and sort
$sql = "SELECT ubp.*, sr.first_name, sr.last_name 
        FROM university_bank_payments ubp 
        JOIN student_registration sr ON ubp.student_id = sr.id 
        WHERE ubp.transaction_id LIKE :search OR sr.first_name LIKE :search OR sr.last_name LIKE :search 
        ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment data for editing
$edit_payment = null;
if ($action === 'edit' && $bank_payment_id) {
    $sql = "SELECT * FROM university_bank_payments WHERE bank_payment_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bank_payment_id]);
    $edit_payment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get student data for dropdown
$students_sql = "SELECT id, first_name, last_name FROM student_registration ORDER BY first_name, last_name";
$students_stmt = $pdo->query($students_sql);
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Payments Management - Developer View</title>
    <link rel="icon" href="../../picture/SKST.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 1400px;
        }

        header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 calc(33.333% - 20px);
            margin: 0 10px 20px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #2ecc71;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .search-sort {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            margin-right: 20px;
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        label {
            color: #1a237e;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            font-weight: 600;
            color: #1a237e;
            cursor: pointer;
        }
        
        th:hover {
            background-color: #e9ecef;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions-cell {
            white-space: nowrap;
        }
        
        .actions-cell form {
            display: inline-block;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
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
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(50% - 20px);
            }
            
            .search-sort {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-box {
                margin-right: 0;
            }
        }
        
        @media (max-width: 576px) {
            .form-group {
                flex: 1 0 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>University Bank Payments Management</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>


                    <!-- Back Button -->
                  <button class="btn btn-secondary" type="button" style="margin-bottom: 10px;" onclick="goBack()">
                      Back
                  </button>

                  <script>
                  function goBack() {
                      window.history.back();
                  }
                  </script>
        <a href="http://localhost:8080/University-Campus-Management-System/account/bank/account_officer.php" class="btn btn-secondary" style="margin-bottom: 10px;">Dashboard</a>



        <!-- Payment Form -->
        <div class="card">
            <h2 class="card-title">
                <?php echo $edit_payment ? 'Edit Payment Record' : 'Add New Payment'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="bank_payment_id" value="<?php echo $edit_payment ? $edit_payment['bank_payment_id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                      <label for="student_id">Student ID *</label>
                      <input list="students_list" id="student_id" name="student_id" placeholder="Select or type Student ID" required class="form-control"
                          value="<?php echo ($edit_payment) ? $edit_payment['student_id'] : ''; ?>">

                      <datalist id="students_list">
                          <?php foreach ($students as $student): ?>
                              <option value="<?php echo $student['id']; ?>">
                                  <?php echo $student['id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']; ?>
                              </option>
                          <?php endforeach; ?>
                      </datalist>
                  </div>

                    <div class="form-group">
                        <label for="transaction_id">Transaction ID *</label>
                        <input type="text" id="transaction_id" name="transaction_id" 
                               value="<?php echo $edit_payment ? $edit_payment['transaction_id'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount *</label>
                        <input type="number" step="0.01" id="amount" name="amount" 
                               value="<?php echo $edit_payment ? $edit_payment['amount'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_type">Payment Type *</label>
                        <select id="payment_type" name="payment_type" required>
                            <option value="registration" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'registration') ? 'selected' : ''; ?>>Registration</option>
                            <option value="tuition" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'tuition') ? 'selected' : ''; ?>>Tuition</option>
                            <option value="exam" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'exam') ? 'selected' : ''; ?>>Exam</option>
                            <option value="library" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'library') ? 'selected' : ''; ?>>Library</option>
                            <option value="hostel" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'hostel') ? 'selected' : ''; ?>>Hostel</option>
                            <option value="other" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="semester">Semester *</label>
                        <input type="text" id="semester" name="semester" 
                               value="<?php echo $edit_payment ? $edit_payment['semester'] : 'Fall 2025'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="academic_year">Academic Year *</label>
                        <input type="number" id="academic_year" name="academic_year" 
                               value="<?php echo $edit_payment ? $edit_payment['academic_year'] : '2025'; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo ($edit_payment && $edit_payment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="verified" <?php echo ($edit_payment && $edit_payment['status'] == 'verified') ? 'selected' : ''; ?>>Verified</option>
                            <option value="rejected" <?php echo ($edit_payment && $edit_payment['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $edit_payment ? 'Update Payment' : 'Add Payment'; ?>
                    
                    <?php if ($edit_payment): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Payment Records -->
        <div class="card">
            <h2 class="card-title">Payment Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Payments</label>
                        <input type="text" id="search" name="search" placeholder="Search by transaction ID or student name..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="bank_payment_id" <?php echo $sort === 'bank_payment_id' ? 'selected' : ''; ?>>Payment ID</option>
                        <option value="student_id" <?php echo $sort === 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                        <option value="amount" <?php echo $sort === 'amount' ? 'selected' : ''; ?>>Amount</option>
                        <option value="created_at" <?php echo $sort === 'created_at' ? 'selected' : ''; ?>>Date</option>
                    </select>

                    <button class="btn btn-primary" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <?php echo $order === 'ASC' ? 'ASC' : 'DESC'; ?>
                    </button>
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo $payment['bank_payment_id']; ?></td>
                                    <td><?php echo $payment['student_id']; ?></td>
                                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                    <td>৳<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo ucfirst($payment['payment_type']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['semester']); ?></td>
                                    <td><?php echo $payment['academic_year']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <form method="POST">
                                            <input type="hidden" name="bank_payment_id" value="<?php echo $payment['bank_payment_id']; ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                            <input type="hidden" name="bank_payment_id" value="<?php echo $payment['bank_payment_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" style="text-align: center;">No payment records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Live search functionality
        document.getElementById('search').addEventListener('input', function() {
            // Submit the form after a short delay
            clearTimeout(this.delay);
            this.delay = setTimeout(function() {
                this.form.submit();
            }.bind(this), 800);
        });
        
        // Focus on search input on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('search').focus();
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>