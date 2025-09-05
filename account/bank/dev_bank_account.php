<?php
// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'skst_university');
define('DB_USER', 'root'); // Change as needed
define('DB_PASS', ''); // Change as needed

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_transaction'])) {
        addTransaction($pdo);
    }
}

// Add a new transaction
function addTransaction($pdo) {
    $transaction_type = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $reference_id = $_POST['reference_id'] ?? null;
    $reference_table = $_POST['reference_table'] ?? null;
    $processed_by = $_POST['processed_by'] ?? null;
    
    // Calculate current balance
    $current_balance = getCurrentBalance($pdo);
    if ($transaction_type === 'credit') {
        $current_balance += $amount;
    } else {
        $current_balance -= $amount;
    }
    
    // Insert transaction
    $sql = "INSERT INTO university_bank_holdings 
            (transaction_type, amount, current_balance, description, reference_id, reference_table, processed_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$transaction_type, $amount, $current_balance, $description, $reference_id, $reference_table, $processed_by]);
        
        $_SESSION['message'] = "Transaction added successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        die("ERROR: Could not execute $sql. " . $e->getMessage());
    }
}

// Get current balance
function getCurrentBalance($pdo) {
    $sql = "SELECT current_balance FROM university_bank_holdings ORDER BY holding_id DESC LIMIT 1";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['current_balance'] : 0;
}

// Get all transactions
function getTransactions($pdo) {
    $sql = "SELECT * FROM university_bank_holdings ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Bank Holdings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .transaction-credit {
            background-color: #d4edda;
        }
        .transaction-debit {
            background-color: #f8d7da;
        }
        .current-balance {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">University Bank Holdings Management</h1>
                
                <!-- Balance Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Current Balance</h5>
                    </div>
                    <div class="card-body text-center">
                        <span class="current-balance text-<?php echo (getCurrentBalance($pdo) >= 0) ? 'success' : 'danger'; ?>">
                            $<?php echo number_format(getCurrentBalance($pdo), 2); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Add Transaction Form -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Add New Transaction</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="transaction_type" class="form-label">Transaction Type</label>
                                        <select class="form-select" id="transaction_type" name="transaction_type" required>
                                            <option value="credit">Credit</option>
                                            <option value="debit">Debit</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="description" name="description" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="reference_id" class="form-label">Reference ID (Optional)</label>
                                        <input type="text" class="form-control" id="reference_id" name="reference_id">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="reference_table" class="form-label">Reference Table (Optional)</label>
                                        <input type="text" class="form-control" id="reference_table" name="reference_table">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="processed_by" class="form-label">Processed By (Optional)</label>
                                        <input type="number" class="form-control" id="processed_by" name="processed_by">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="add_transaction" class="btn btn-success">
                                <i class="fas fa-plus-circle me-1"></i> Add Transaction
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Transaction History -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Transaction History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th>Processed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $transactions = getTransactions($pdo); ?>
                                    <?php if (count($transactions) > 0): ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr class="transaction-<?php echo $transaction['transaction_type']; ?>">
                                                <td><?php echo $transaction['holding_id']; ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $transaction['transaction_type'] === 'credit' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($transaction['transaction_type']); ?>
                                                    </span>
                                                </td>
                                                <td class="fw-bold">$<?php echo number_format($transaction['amount'], 2); ?></td>
                                                <td class="fw-bold">$<?php echo number_format($transaction['current_balance'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                                <td>
                                                    <?php if ($transaction['reference_id'] && $transaction['reference_table']): ?>
                                                        <?php echo $transaction['reference_table'] . ' #' . $transaction['reference_id']; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $transaction['processed_by'] ? 'User #' . $transaction['processed_by'] : '<span class="text-muted">N/A</span>'; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">No transactions found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>