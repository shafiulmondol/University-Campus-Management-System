<?php
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
$borrow_id = $_POST['borrow_id'] ?? '';

// Add or Update borrowing record
if ($action === 'save') {
    $member_id = $_POST['member_id'] ?? '';
    $book_id = $_POST['book_id'] ?? '';
    $borrow_date = $_POST['borrow_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';
    $status = $_POST['status'] ?? 'borrowed';
    
    // Calculate fine if applicable
    $fine_amount = 0.00;
    if ($status === 'returned' && $return_date && $due_date) {
        $return_timestamp = strtotime($return_date);
        $due_timestamp = strtotime($due_date);
        if ($return_timestamp > $due_timestamp) {
            $days_late = ceil(($return_timestamp - $due_timestamp) / (60 * 60 * 24));
            $fine_amount = $days_late * 5.00; // $5 per day late
        }
    }
    
    if ($borrow_id) {
        // Update existing borrowing record
        $sql = "UPDATE book_borrowings SET member_id=?, book_id=?, borrow_date=?, due_date=?, return_date=?, fine_amount=?, status=? WHERE borrow_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $book_id, $borrow_date, $due_date, $return_date, $fine_amount, $status, $borrow_id]);
    } else {
        // Insert new borrowing record
        $sql = "INSERT INTO book_borrowings (member_id, book_id, borrow_date, due_date, return_date, fine_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, $book_id, $borrow_date, $due_date, $return_date, $fine_amount, $status]);
    }
}

// Delete borrowing record
if ($action === 'delete' && $borrow_id) {
    $sql = "DELETE FROM book_borrowings WHERE borrow_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$borrow_id]);
}

// Mark as returned
if ($action === 'return' && $borrow_id) {
    $return_date = date('Y-m-d');
    $sql = "UPDATE book_borrowings SET return_date=?, status='returned' WHERE borrow_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$return_date, $borrow_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$sort = $_GET['sort'] ?? 'borrow_id';
$order = $_GET['order'] ?? 'DESC';

// Build query with search and filters
$sql = "SELECT bb.*, lm.full_name as member_name, b.title as book_title 
        FROM book_borrowings bb
        JOIN library_members lm ON bb.member_id = lm.member_id
        JOIN books b ON bb.book_id = b.book_id
        WHERE (lm.full_name LIKE :search OR b.title LIKE :search)";
$params = ['search' => "%$search%"];

if ($status_filter) {
    $sql .= " AND bb.status = :status";
    $params['status'] = $status_filter;
}

$sql .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get borrowing data for editing
$edit_borrowing = null;
if ($action === 'edit' && $borrow_id) {
    $sql = "SELECT bb.*, lm.full_name as member_name, b.title as book_title 
            FROM book_borrowings bb
            JOIN library_members lm ON bb.member_id = lm.member_id
            JOIN books b ON bb.book_id = b.book_id
            WHERE bb.borrow_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$borrow_id]);
    $edit_borrowing = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all books for dropdown
$books_sql = "SELECT book_id, title FROM books ORDER BY title";
$books_stmt = $pdo->query($books_sql);
$books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all members for dropdown
$members_sql = "SELECT member_id, full_name FROM library_members WHERE membership_status = 'active' ORDER BY full_name";
$members_stmt = $pdo->query($members_sql);
$members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$stats_sql = "SELECT 
    COUNT(*) as total_borrowings,
    SUM(CASE WHEN status = 'borrowed' THEN 1 ELSE 0 END) as active_borrowings,
    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_books,
    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_books,
    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_books,
    SUM(fine_amount) as total_fines
    FROM book_borrowings";
$stats_stmt = $pdo->query($stats_sql);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get status distribution
$status_sql = "SELECT status, COUNT(*) as count FROM book_borrowings GROUP BY status";
$status_stmt = $pdo->query($status_sql);
$status_stats = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Borrowings Management - Developer View</title>
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
            max-width: 1600px;
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
        
        .form-group-full {
            flex: 1 0 calc(100% - 20px);
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
            text-decoration: none;
        }
        
        .btn:hover {
            opacity: 0.9;
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
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .btn-warning {
            background: #f39c12;
        }
        
        .btn-warning:hover {
            background: #e67e22;
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
            gap: 20px;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .filter-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        label {
            color: #2c3e50;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
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
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
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
            text-align: center;
        }
        
        .actions-cell form {
            display: inline-block;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .status-borrowed {
            color: #f39c12;
            font-weight: bold;
        }
        
        .status-returned {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-overdue {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .status-lost {
            color: #7f8c8d;
            font-weight: bold;
        }
        
        .error-message {
            background-color: #ffe6e6;
            color: #e74c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .success-message {
            background-color: #e6ffe6;
            color: #27ae60;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }
        
        .sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .fine-amount {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .overdue {
            background-color: #fff0f0;
        }
        
        @media (max-width: 1024px) {
            .form-group {
                flex: 1 0 calc(50% - 20px);
            }
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 100%;
            }
            
            .search-sort {
                flex-direction: column;
                gap: 15px;
            }
            
            .filter-options {
                flex-direction: column;
            }
            
            .sort-container {
                flex-direction: column;
                align-items: flex-start;
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
            <h1>Book Borrowings Management</h1>
            <p class="subtitle">Developer View - SKST University Library</p>
        </header>
        <button style="margin-bottom: 10px;" class="btn btn-secondary" onclick="window.location.href='http://localhost:8080/University-Campus-Management-System/library/librarylogin.php'">⬅ Back</button>
        
        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Total Borrowings</div>
                <div class="stat-value"><?php echo $stats['total_borrowings']; ?></div>
                <div class="stat-label">All Time Records</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Active Borrowings</div>
                <div class="stat-value"><?php echo $stats['active_borrowings']; ?></div>
                <div class="stat-label">Books Currently Borrowed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Overdue Books</div>
                <div class="stat-value"><?php echo $stats['overdue_books']; ?></div>
                <div class="stat-label">Past Due Date</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Total Fines</div>
                <div class="stat-value">৳<?php echo number_format($stats['total_fines'], 2); ?></div>
                <div class="stat-label">Outstanding Fees</div>
            </div>
        </div>
        
        <!-- Status Distribution -->
        <div class="card">
            <h2 class="card-title">Borrowing Status Distribution</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <?php foreach ($status_stats as $status): ?>
                    <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 6px; min-width: 120px;">
                        <div style="font-size: 1.2rem; font-weight: bold;"><?php echo $status['count']; ?></div>
                        <div style="font-size: 0.9rem; color: #6c757d;"><?php echo ucfirst($status['status']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Borrowing Form -->
        <div class="card">
            <h2 class="card-title">
                <?php echo $edit_borrowing ? 'Edit Borrowing Record' : 'Add New Borrowing'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="borrow_id" value="<?php echo $edit_borrowing ? $edit_borrowing['borrow_id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="member_id">Member *</label>
                        <input list="member_list" id="member_id" name="member_id" placeholder="Select or type Member ID" required class="form-control"
                            value="<?php echo ($edit_borrowing) ? $edit_borrowing['member_id'] : ''; ?>">

                        <datalist id="member_list">
                            <?php foreach ($members as $member): ?>
                                <option>
                                    <?php echo $member['member_id'] . ' - ' . htmlspecialchars($member['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    
                   <div class="form-group">
                        <label for="book_id">Book *</label>
                        <input list="book_list" id="book_id" name="book_id" placeholder="Select or type Book ID" required class="form-control"
                            value="<?php echo ($edit_borrowing) ? $edit_borrowing['book_id'] : ''; ?>">

                        <datalist id="book_list">
                            <?php foreach ($books as $book): ?>
                                <option>
                                    <?php echo $book['book_id'] . ' - ' . htmlspecialchars($book['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    
                    <div class="form-group">
                        <label for="borrow_date">Borrow Date *</label>
                        <input type="date" id="borrow_date" name="borrow_date" 
                            value="<?php echo $edit_borrowing ? $edit_borrowing['borrow_date'] : date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="due_date">Due Date *</label>
                        <input type="date" id="due_date" name="due_date" 
                            value="<?php echo $edit_borrowing ? $edit_borrowing['due_date'] : date('Y-m-d', strtotime('+14 days')); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" id="return_date" name="return_date" 
                            value="<?php echo $edit_borrowing ? $edit_borrowing['return_date'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="borrowed" <?php echo ($edit_borrowing && $edit_borrowing['status'] == 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                            <option value="returned" <?php echo ($edit_borrowing && $edit_borrowing['status'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                            <option value="overdue" <?php echo ($edit_borrowing && $edit_borrowing['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                            <option value="lost" <?php echo ($edit_borrowing && $edit_borrowing['status'] == 'lost') ? 'selected' : ''; ?>>Lost</option>
                        </select>
                    </div>
                </div>
                
                <!-- Fine Amount Field (Visible only when editing) -->
                <?php if ($edit_borrowing): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="fine_amount">Fine Amount (৳)</label>
                        <input type="number" id="fine_amount" name="fine_amount" step="0.01" min="0" 
                            value="<?php echo $edit_borrowing ? number_format($edit_borrowing['fine_amount'], 2) : '0.00'; ?>" 
                            readonly style="background-color: #f8f9fa;">
                        <small>Calculated automatically based on return date and due date</small>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_borrowing ? 'Update Record' : 'Add Borrowing'; ?></button>
                    <?php if ($edit_borrowing): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Borrowing Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Borrowings</label>
                        <input type="text" id="search" name="search" placeholder="Search by member name or book title..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>

                
                <div class="sort-container">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort" onchange="this.form.submit()">
                        <option value="borrow_id" <?php echo $sort === 'borrow_id' ? 'selected' : ''; ?>>ID</option>
                        <option value="borrow_date" <?php echo $sort === 'borrow_date' ? 'selected' : ''; ?>>Borrow Date</option>
                        <option value="due_date" <?php echo $sort === 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                        <option value="return_date" <?php echo $sort === 'return_date' ? 'selected' : ''; ?>>Return Date</option>
                        <option value="status" <?php echo $sort === 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                    
                    <button class="btn btn-primary" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&status_filter=<?php echo $status_filter; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
                    </button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Book</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine Amount</th>
                        <th>Status</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($borrowings) > 0): ?>
                        <?php foreach ($borrowings as $borrowing): 
                            $is_overdue = ($borrowing['status'] === 'borrowed' && strtotime($borrowing['due_date']) < time());
                            $row_class = $is_overdue ? 'overdue' : '';
                        ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $borrowing['borrow_id']; ?></td>
                                <td><?php echo htmlspecialchars($borrowing['member_name']); ?></td>
                                <td><?php echo htmlspecialchars($borrowing['book_title']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($borrowing['due_date'])); ?></td>
                                <td><?php echo $borrowing['return_date'] ? date('M j, Y', strtotime($borrowing['return_date'])) : 'Not returned'; ?></td>
                                <td class="fine-amount">৳<?php echo number_format($borrowing['fine_amount'], 2); ?></td>
                                <td class="status-<?php echo $borrowing['status']; ?>">
                                    <?php echo ucfirst($borrowing['status']); ?>
                                    <?php if ($is_overdue): ?>
                                        <br><small>(Overdue)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="borrow_id" value="<?php echo $borrowing['borrow_id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    
                                    <?php if ($borrowing['status'] === 'borrowed'): ?>
                                    <form method="POST" onsubmit="return confirm('Mark this book as returned?');">
                                        <input type="hidden" name="borrow_id" value="<?php echo $borrowing['borrow_id']; ?>">
                                        <input type="hidden" name="action" value="return">
                                        <button type="submit" class="btn btn-success">Return</button>
                                    </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this borrowing record?');">
                                        <input type="hidden" name="borrow_id" value="<?php echo $borrowing['borrow_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No borrowing records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Live search functionality
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this.delay);
            this.delay = setTimeout(function() {
                this.form.submit();
            }.bind(this), 800);
        });
        
        // Auto-set due date to 14 days after borrow date
        document.getElementById('borrow_date').addEventListener('change', function() {
            const borrowDate = new Date(this.value);
            if (!isNaN(borrowDate.getTime())) {
                const dueDate = new Date(borrowDate);
                dueDate.setDate(dueDate.getDate() + 14);
                
                const dueDateInput = document.getElementById('due_date');
                dueDateInput.value = dueDate.toISOString().split('T')[0];
            }
        });
        
        // Auto-calculate status when return date is set
        document.getElementById('return_date').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('status').value = 'returned';
            }
        });
    </script>
</body>
</html>