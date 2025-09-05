<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$search = "";
$page = 1;
$limit = 20;

// Handle search
if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
}

// Handle pagination
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = max(1, intval($_GET['page']));
}

// Calculate offset
$offset = ($page - 1) * $limit;

// Get total transactions count
$count_sql = "SELECT COUNT(*) as total FROM transaction_history";
if (!empty($search)) {
    $count_sql .= " WHERE description LIKE '%$search%' OR amount LIKE '%$search%'";
}
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Get transaction data with pagination
$sql = "SELECT * FROM transaction_history";
if (!empty($search)) {
    $sql .= " WHERE description LIKE '%$search%' OR amount LIKE '%$search%'";
}
$sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Get statistics
$stats_sql = "SELECT 
    SUM(CASE WHEN transaction_type = 'incoming' THEN amount ELSE 0 END) as total_credit,
    SUM(CASE WHEN transaction_type = 'outgoing' THEN amount ELSE 0 END) as total_debit,
    COUNT(*) as total_transactions
    FROM transaction_history";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$total_credit = $stats['total_credit'] ?? 0;
$total_debit = $stats['total_debit'] ?? 0;
$total_balance = $total_credit - $total_debit;
$total_transactions = $stats['total_transactions'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Payment Transactions</title>
    <link rel="icon" href="../../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo i {
            font-size: 32px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 600;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        nav a:hover {
            color: #ffd43b;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-5px);
        }

        .stat-box.credit {
            border-top: 5px solid #2ec27e;
        }

        .stat-box.debit {
            border-top: 5px solid #e01b24;
        }

        .stat-box.balance {
            border-top: 5px solid #1c71d8;
        }

        .stat-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .stat-value.credit {
            color: #2ec27e;
        }

        .stat-value.debit {
            color: #e01b24;
        }

        .stat-value.balance {
            color: #1c71d8;
        }

        .search-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 15px;
        }

        .search-input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e6e9ef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #1c71d8;
        }

        .search-btn {
            background: #1c71d8;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-btn:hover {
            background: #1a5fb4;
        }

        .history-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .history-header {
            padding: 20px;
            background: #f8f9fc;
            border-bottom: 1px solid #e6e9ef;
        }

        .history-title {
            font-size: 22px;
            font-weight: 600;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e6e9ef;
        }

        th {
            background: #f8f9fc;
            font-weight: 600;
            color: #4e4e4e;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background: #f8f9fc;
        }

        .transaction-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .incoming {
            background: #e4f5ee;
            color: #2ec27e;
        }

        .outgoing {
            background: #fbe6e7;
            color: #e01b24;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }

        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: white;
            color: #1c71d8;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .pagination a:hover, .pagination a.active {
            background: #1c71d8;
            color: white;
        }

        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
            <div class="logo">
             <img src="../../picture/SKST.png" alt="SKST Logo" style="height: 60px; border-radius: 50%;">
             <h1>SKST University</h1>
            </div>

            <nav>
                <ul>
                    <li><a href="../../index.html"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="http://localhost:8080/University-Campus-Management-System/account/bank/account_officer.php"><i class="fas fa-arrow-left"></i> Back</a></li>

                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- Statistics Boxes -->
        <div class="stats-container">
            <div class="stat-box credit">
                <div class="stat-title">Total Credit (Incoming)</div>
                <div class="stat-value credit"><?php echo number_format($total_credit, 2); ?> Taka</div>
            </div>
            <div class="stat-box debit">
                <div class="stat-title">Total Debit (Outgoing)</div>
                <div class="stat-value debit"><?php echo number_format($total_debit, 2); ?> Taka</div>
            </div>
            <div class="stat-box balance">
                <div class="stat-title">Net Balance</div>
                <div class="stat-value balance"><?php echo number_format($total_balance, 2); ?> Taka</div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Search transactions..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i> Search</button>
            </form>
        </div>

        <!-- Transaction History -->
        <div class="history-container">
            <div class="history-header">
                <h2 class="history-title">Transaction History</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Amount (Taka)</th>
                        <th>User Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($row['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td>
                                    <span class="transaction-type <?php echo $row['transaction_type']; ?>">
                                        <?php echo ucfirst($row['transaction_type']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo number_format($row['amount'], 2); ?></strong></td>
                                <td><?php echo ucfirst($row['related_user_type']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 SKST University. All rights reserved.</p>
    </footer>
</body>
</html>