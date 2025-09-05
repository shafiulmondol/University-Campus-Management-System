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
$payment_id = $_POST['payment_id'] ?? '';
$faculty_id = $_POST['faculty_id'] ?? '';

// Add or Update payment
if ($action === 'save') {
    $amount = $_POST['amount'] ?? '';
    $payment_type = $_POST['payment_type'] ?? '';
    $payment_month = $_POST['payment_month'] ?? '';
    $payment_year = $_POST['payment_year'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? '';
    $bank_transaction_id = $_POST['bank_transaction_id'] ?? '';
    
    if ($payment_id) {
        // Update existing payment
        $sql = "UPDATE faculty_payments SET faculty_id=?, amount=?, payment_type=?, payment_month=?, payment_year=?, description=?, status=?, bank_transaction_id=? WHERE payment_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$faculty_id, $amount, $payment_type, $payment_month, $payment_year, $description, $status, $bank_transaction_id, $payment_id]);
    } else {
        // Insert new payment
        $sql = "INSERT INTO faculty_payments (faculty_id, amount, payment_type, payment_month, payment_year, description, status, bank_transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$faculty_id, $amount, $payment_type, $payment_month, $payment_year, $description, $status, $bank_transaction_id]);
    }
}

// Delete payment
if ($action === 'delete' && $payment_id) {
    $sql = "DELETE FROM faculty_payments WHERE payment_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$payment_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'payment_id';
$order = $_GET['order'] ?? 'DESC';

// Build query with search and sort
$sql = "SELECT fp.*, f.name 
        FROM faculty_payments fp 
        JOIN faculty f ON fp.faculty_id = f.faculty_id 
        WHERE fp.payment_id LIKE :search OR f.name LIKE :search OR fp.bank_transaction_id LIKE :search 
        ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get payment data for editing
$edit_payment = null;
if ($action === 'edit' && $payment_id) {
    $sql = "SELECT * FROM faculty_payments WHERE payment_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$payment_id]);
    $edit_payment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get faculty data for dropdown
$faculty_sql = "SELECT faculty_id, name FROM faculty ORDER BY name";
$faculty_stmt = $pdo->query($faculty_sql);
$faculty_members = $faculty_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Payments Management - SKST University</title>
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
            color: #2b5876;
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
            color: #2b5876;
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
        
        .status-processed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
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
            <h1>Faculty Payments Management</h1>
            <p class="subtitle">SKST University - Administrator View</p>
        </header>

        <!-- Back Button -->
        <button class="btn btn-secondary" type="button" style="margin-bottom: 10px;" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>

        <!-- Home Button as a link styled like button -->
        <a href="../../index.html" class="btn btn-secondary" style="margin-bottom: 10px;">
            <i class="fas fa-home"></i> Home
        </a>

        <script>
        function goBack() {
            window.history.back();
        }
        </script>

        <!-- Payment Form -->
        <div class="card">
            <h2 class="card-title">
                <?php echo $edit_payment ? 'Edit Payment Record' : 'Add New Payment'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="payment_id" value="<?php echo $edit_payment ? $edit_payment['payment_id'] : ''; ?>">
                
                <div class="form-row">
                  <div class="form-group">
                      <label for="faculty_id">Faculty Member *</label>
                      <input list="faculty_list" id="faculty_id" name="faculty_id" placeholder="Select or type Faculty ID" required class="form-control"
                          value="<?php echo ($edit_payment) ? $edit_payment['faculty_id'] : ''; ?>">

                      <datalist id="faculty_list">
                          <?php foreach ($faculty_members as $faculty): ?>
                              <option value="<?php echo $faculty['faculty_id']; ?>">
                                  <?php echo $faculty['faculty_id'] . ' - ' . $faculty['name']; ?>
                              </option>
                          <?php endforeach; ?>
                      </datalist>
                  </div>

                    <div class="form-group">
                        <label for="amount">Amount (৳) *</label>
                        <input type="number" step="0.01" id="amount" name="amount" 
                               value="<?php echo $edit_payment ? $edit_payment['amount'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_type">Payment Type *</label>
                        <select id="payment_type" name="payment_type" required>
                            <option value="salary" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'salary') ? 'selected' : ''; ?>>Salary</option>
                            <option value="bonus" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'bonus') ? 'selected' : ''; ?>>Bonus</option>
                            <option value="allowance" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'allowance') ? 'selected' : ''; ?>>Allowance</option>
                            <option value="reimbursement" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'reimbursement') ? 'selected' : ''; ?>>Reimbursement</option>
                            <option value="other" <?php echo ($edit_payment && $edit_payment['payment_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_month">Payment Month *</label>
                        <select id="payment_month" name="payment_month" required>
                            <option value="January" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'January') ? 'selected' : ''; ?>>January</option>
                            <option value="February" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'February') ? 'selected' : ''; ?>>February</option>
                            <option value="March" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'March') ? 'selected' : ''; ?>>March</option>
                            <option value="April" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'April') ? 'selected' : ''; ?>>April</option>
                            <option value="May" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'May') ? 'selected' : ''; ?>>May</option>
                            <option value="June" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'June') ? 'selected' : ''; ?>>June</option>
                            <option value="July" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'July') ? 'selected' : ''; ?>>July</option>
                            <option value="August" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'August') ? 'selected' : ''; ?>>August</option>
                            <option value="September" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'September') ? 'selected' : ''; ?>>September</option>
                            <option value="October" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'October') ? 'selected' : ''; ?>>October</option>
                            <option value="November" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'November') ? 'selected' : ''; ?>>November</option>
                            <option value="December" <?php echo ($edit_payment && $edit_payment['payment_month'] == 'December') ? 'selected' : ''; ?>>December</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_year">Payment Year *</label>
                        <input type="number" id="payment_year" name="payment_year" min="2020" max="2030"
                               value="<?php echo $edit_payment ? $edit_payment['payment_year'] : date('Y'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo ($edit_payment && $edit_payment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processed" <?php echo ($edit_payment && $edit_payment['status'] == 'processed') ? 'selected' : ''; ?>>Processed</option>
                            <option value="failed" <?php echo ($edit_payment && $edit_payment['status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="bank_transaction_id">Bank Transaction ID</label>
                        <input type="text" id="bank_transaction_id" name="bank_transaction_id" 
                               value="<?php echo $edit_payment ? $edit_payment['bank_transaction_id'] : ''; ?>">
                    </div>
                    
                    <div class="form-group" style="flex: 2;">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?php echo $edit_payment ? $edit_payment['description'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $edit_payment ? 'Update Payment' : 'Add Payment'; ?>
                    </button>
                    
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
                        <input type="text" id="search" name="search" placeholder="Search by payment ID, faculty name, or transaction ID..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="payment_id" <?php echo $sort === 'payment_id' ? 'selected' : ''; ?>>Payment ID</option>
                        <option value="faculty_id" <?php echo $sort === 'faculty_id' ? 'selected' : ''; ?>>Faculty ID</option>
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
                            <th>Faculty ID</th>
                            <th>Faculty Name</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?php echo $payment['payment_id']; ?></td>
                                    <td><?php echo $payment['faculty_id']; ?></td>
                                    <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                    <td>৳<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td>
                                        <span class="payment-type-badge type-<?php echo $payment['payment_type']; ?>">
                                            <?php echo ucfirst($payment['payment_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['payment_month']); ?> <?php echo $payment['payment_year']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $payment['bank_transaction_id'] ? htmlspecialchars($payment['bank_transaction_id']) : 'N/A'; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($payment['created_at'])); ?></td>
                                    <td class="actions-cell">
                                        <form method="POST">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this payment record?');">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
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
                                <td colspan="10" style="text-align: center;">No payment records found.</td>
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