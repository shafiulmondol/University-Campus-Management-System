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
$member_id = $_POST['member_id'] ?? '';

// Add or Update member
if ($action === 'save') {
    $user_type = $_POST['user_type'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';
    $max_books = $_POST['max_books'] ?? 3;
    $membership_status = $_POST['membership_status'] ?? 'active';
    
    // Check for duplicate user
    $check_sql = "SELECT COUNT(*) FROM library_members WHERE user_type = ? AND user_id = ? AND member_id != ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$user_type, $user_id, $member_id ?: 0]);
    $user_exists = $check_stmt->fetchColumn();
    
    if ($user_exists) {
        $error = "This user already has a library membership.";
    } else {
        if ($member_id) {
            // Update existing member
            $sql = "UPDATE library_members SET user_type=?, user_id=?, full_name=?, email=?, department=?, max_books=?, membership_status=? WHERE member_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_type, $user_id, $full_name, $email, $department, $max_books, $membership_status, $member_id]);
        } else {
            // Insert new member
            $sql = "INSERT INTO library_members (user_type, user_id, full_name, email, department, max_books, membership_status, membership_start) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_type, $user_id, $full_name, $email, $department, $max_books, $membership_status]);
        }
    }
}

// Delete member
if ($action === 'delete' && $member_id) {
    $sql = "DELETE FROM library_members WHERE member_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$member_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$type_filter = $_GET['type_filter'] ?? '';
$sort = $_GET['sort'] ?? 'member_id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and filters
$sql = "SELECT * FROM library_members WHERE (full_name LIKE :search OR email LIKE :search OR department LIKE :search)";
$params = ['search' => "%$search%"];

if ($status_filter) {
    $sql .= " AND membership_status = :status";
    $params['status'] = $status_filter;
}

if ($type_filter) {
    $sql .= " AND user_type = :type";
    $params['type'] = $type_filter;
}

$sql .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get member data for editing
$edit_member = null;
if ($action === 'edit' && $member_id) {
    $sql = "SELECT * FROM library_members WHERE member_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$member_id]);
    $edit_member = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Calculate statistics
$stats_sql = "SELECT 
    COUNT(*) as total_members,
    SUM(CASE WHEN membership_status = 'active' THEN 1 ELSE 0 END) as active_members,
    SUM(CASE WHEN membership_status = 'suspended' THEN 1 ELSE 0 END) as suspended_members,
    SUM(CASE WHEN membership_status = 'graduated' THEN 1 ELSE 0 END) as graduated_members,
    SUM(CASE WHEN membership_status = 'retired' THEN 1 ELSE 0 END) as retired_members
    FROM library_members";
$stats_stmt = $pdo->query($stats_sql);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get user type distribution
$type_sql = "SELECT user_type, COUNT(*) as count FROM library_members GROUP BY user_type";
$type_stmt = $pdo->query($type_sql);
$type_stats = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Member Management - Developer View</title>
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
        
        .status-active {
            color: #27ae60;
            font-weight: bold;
        }
        
        .status-suspended {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .status-graduated, .status-retired {
            color: #95a5a6;
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
        
        .user-type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .type-student { background-color: #3498db; }
        .type-faculty { background-color: #9b59b6; }
        .type-staff { background-color: #f39c12; }
        .type-alumni { background-color: #16a085; }
        .type-bank_officer { background-color: #d35400; }
        .type-admin { background-color: #7f8c8d; }
        
        .sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(50% - 20px);
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
            <h1>Library Member Management</h1>
            <p class="subtitle">Developer View - SKST University Library</p>
        </header>
        
        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Total Members</div>
                <div class="stat-value"><?php echo $stats['total_members']; ?></div>
                <div class="stat-label">All Library Members</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Active Members</div>
                <div class="stat-value"><?php echo $stats['active_members']; ?></div>
                <div class="stat-label">Can Borrow Books</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Suspended</div>
                <div class="stat-value"><?php echo $stats['suspended_members']; ?></div>
                <div class="stat-label">Membership Suspended</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">User Types</div>
                <div class="stat-value"><?php echo count($type_stats); ?></div>
                <div class="stat-label">Different User Categories</div>
            </div>
        </div>
        
        <!-- User Type Distribution -->
        <div class="card">
            <h2 class="card-title">User Type Distribution</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <?php foreach ($type_stats as $type): ?>
                    <div style="text-align: center; padding: 10px; background: #f8f9fa; border-radius: 6px; min-width: 120px;">
                        <div style="font-size: 1.2rem; font-weight: bold;"><?php echo $type['count']; ?></div>
                        <div style="font-size: 0.9rem; color: #6c757d;"><?php echo ucfirst(str_replace('_', ' ', $type['user_type'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Member Form -->
        <div class="card">
            <h2 class="card-title">
                <?php echo $edit_member ? 'Edit Member Record' : 'Add New Member'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="member_id" value="<?php echo $edit_member ? $edit_member['member_id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="user_type">User Type *</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Select User Type</option>
                            <option value="student" <?php echo ($edit_member && $edit_member['user_type'] == 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="faculty" <?php echo ($edit_member && $edit_member['user_type'] == 'faculty') ? 'selected' : ''; ?>>Faculty</option>
                            <option value="staff" <?php echo ($edit_member && $edit_member['user_type'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                            <option value="alumni" <?php echo ($edit_member && $edit_member['user_type'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                            <option value="bank_officer" <?php echo ($edit_member && $edit_member['user_type'] == 'bank_officer') ? 'selected' : ''; ?>>Bank Officer</option>
                            <option value="admin" <?php echo ($edit_member && $edit_member['user_type'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_id">User ID *</label>
                        <input type="text" id="user_id" name="user_id" value="<?php echo $edit_member ? $edit_member['user_id'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo $edit_member ? htmlspecialchars($edit_member['full_name']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_member ? htmlspecialchars($edit_member['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="<?php echo $edit_member ? htmlspecialchars($edit_member['department']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="max_books">Max Books *</label>
                        <input type="number" id="max_books" name="max_books" min="1" max="10" value="<?php echo $edit_member ? $edit_member['max_books'] : 3; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="membership_status">Membership Status *</label>
                        <select id="membership_status" name="membership_status" required>
                            <option value="active" <?php echo ($edit_member && $edit_member['membership_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="suspended" <?php echo ($edit_member && $edit_member['membership_status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                            <option value="graduated" <?php echo ($edit_member && $edit_member['membership_status'] == 'graduated') ? 'selected' : ''; ?>>Graduated</option>
                            <option value="retired" <?php echo ($edit_member && $edit_member['membership_status'] == 'retired') ? 'selected' : ''; ?>>Retired</option>
                        </select>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_member ? 'Update Record' : 'Add Member'; ?></button>
                    <?php if ($edit_member): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Member Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Members</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or department..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-container">
                    <label for="sort">Sort by:</label>
                    <select id="sort" name="sort" onchange="this.form.submit()">
                        <option value="member_id" <?php echo $sort === 'member_id' ? 'selected' : ''; ?>>ID</option>
                        <option value="full_name" <?php echo $sort === 'full_name' ? 'selected' : ''; ?>>Name</option>
                        <option value="user_type" <?php echo $sort === 'user_type' ? 'selected' : ''; ?>>User Type</option>
                        <option value="membership_status" <?php echo $sort === 'membership_status' ? 'selected' : ''; ?>>Status</option>
                        <option value="membership_start" <?php echo $sort === 'membership_start' ? 'selected' : ''; ?>>Join Date</option>
                    </select>
                    
                    <button class="btn btn-primary" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
                    </button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>User Type</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Max Books</th>
                        <th>Status</th>
                        <th>Join Date</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($members) > 0): ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo $member['member_id']; ?></td>
                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td>
                                    <span class="user-type-badge type-<?php echo $member['user_type']; ?>">
                                        <?php echo ucfirst($member['user_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['department'] ?? 'N/A'); ?></td>
                                <td><?php echo $member['max_books']; ?></td>
                                <td class="status-<?php echo $member['membership_status']; ?>">
                                    <?php echo ucfirst($member['membership_status']); ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($member['membership_start'])); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                        <input type="hidden" name="member_id" value="<?php echo $member['member_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No member records found.</td>
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
    </script>
</body>
</html>