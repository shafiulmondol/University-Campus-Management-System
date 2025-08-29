<?php
// Database connection
$host = '127.0.0.1';
$db = 'skst_university';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle form actions
$action = $_POST['action'] ?? '';
$message = '';

if ($action === 'insert') {
    // Use student ID as application ID
    $application_id = $_POST['student_id'];
    
    $stmt = $pdo->prepare("INSERT INTO scholarship_application 
        (application_id, name, department, semester, mobile_number, email, 
        current_semester_sgpa, cgpa, previous_semester_cgpa, scholarship_percentage) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([
            $application_id,
            $_POST['name'],
            $_POST['department'],
            $_POST['semester'],
            $_POST['mobile_number'],
            $_POST['email'],
            $_POST['current_semester_sgpa'],
            $_POST['cgpa'],
            $_POST['previous_semester_cgpa'],
            $_POST['scholarship_percentage']
        ]);
        $message = "Record inserted successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
} elseif ($action === 'update') {
    $stmt = $pdo->prepare("UPDATE scholarship_application SET 
        name = ?, department = ?, semester = ?, mobile_number = ?, email = ?,
        current_semester_sgpa = ?, cgpa = ?, previous_semester_cgpa = ?, 
        scholarship_percentage = ? WHERE id = ?");
    
    try {
        $stmt->execute([
            $_POST['name'],
            $_POST['department'],
            $_POST['semester'],
            $_POST['mobile_number'],
            $_POST['email'],
            $_POST['current_semester_sgpa'],
            $_POST['cgpa'],
            $_POST['previous_semester_cgpa'],
            $_POST['scholarship_percentage'],
            $_POST['id']
        ]);
        $message = "Record updated successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
} elseif ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM scholarship_application WHERE id = ?");
    
    try {
        $stmt->execute([$_POST['id']]);
        $message = "Record deleted successfully!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Handle search and sort
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'application_date';
$order = $_GET['order'] ?? 'DESC';

$allowed_sorts = ['id', 'application_id', 'name', 'department', 'semester', 
                 'mobile_number', 'email', 'current_semester_sgpa', 'cgpa', 
                 'previous_semester_cgpa', 'scholarship_percentage', 'application_date'];
$sort = in_array($sort, $allowed_sorts) ? $sort : 'application_date';
$order = $order === 'ASC' ? 'ASC' : 'DESC';

$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE name LIKE ? OR application_id LIKE ? OR email LIKE ? OR mobile_number LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
}

$stmt = $pdo->prepare("SELECT * FROM scholarship_application $where ORDER BY $sort $order");
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get record to edit (if any)
$edit_id = $_GET['edit'] ?? '';
$edit_record = null;
if (!empty($edit_id)) {
    $stmt = $pdo->prepare("SELECT * FROM scholarship_application WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_record = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Application Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background-color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #3498db;
        }
        h1 {
            color: #2c3e50;
            font-size: 32px;
        }
        h2 {
            color: #3498db;
            margin: 20px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            font-weight: 500;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        button, .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            background-color: maroon;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        button:hover, .btn:hover {
            background-color: darkred;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: darkgreen;
        }
        .btn-success:hover {
            background-color: green;
        }
        .btn-secondary {
            background-color: darkgreen;
        }
        .btn-secondary:hover {
            background-color: green;
        }
        .form-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input[type="text"], input[type="number"], input[type="email"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .search-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
        }
        .search-container input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            white-space: nowrap;
            text-decoration: none;
        }
        th:hover {
            background-color: #2980b9;
        }
        tr {
            transition: background-color 0.2s ease;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e3f2fd;
        }
        .action-buttons {
            
            display: flex;
            gap: 8px;
        }
        .action-buttons form {
            margin: 0;
        }
        .action-buttons button {
            padding: 8px 12px;
            font-size: 14px;
        }
        .sort-indicator {
            margin-left: 5px;
            font-size: 12px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .stat-card p {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
        }
        @media (max-width: 992px) {
            .form-container {
                grid-template-columns: 1fr;
            }
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Scholarship Application Management</h1>
            <div class="btn-group">
                <a href="../working.html" class="btn btn-secondary"> <i class="fas fa-arrow-left"></i> Back </a>
                <a href="../index.html" class="btn"> <i class="fas fa-home"></i> Home </a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <i class="<?php echo strpos($message, 'Error') === false ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Applications</h3>
                <p><?php echo count($applications); ?></p>
            </div>
            <div class="stat-card">
                <h3>Departments</h3>
                <p><?php echo count(array_unique(array_column($applications, 'department'))); ?></p>
            </div>
            <div class="stat-card">
                <h3>Highest Scholarship</h3>
                <p><?php echo count($applications) > 0 ? max(array_column($applications, 'scholarship_percentage')) . '%' : 'N/A'; ?></p>
            </div>
        </div>
        
        <h2><i class="fas fa-plus-circle"></i> <?php echo $edit_record ? 'Edit Record' : 'Add New Record'; ?></h2>
        <form method="post">
            <div class="form-container">
                <input type="hidden" name="action" value="<?php echo $edit_record ? 'update' : 'insert'; ?>">
                <?php if ($edit_record): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_record['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="student_id"><i class="fas fa-id-card"></i> Student ID:</label>
                    <input type="text" id="student_id" name="student_id" required 
                        value="<?php echo $edit_record ? $edit_record['application_id'] : ''; ?>" 
                        placeholder="Enter student ID" <?php echo $edit_record ? 'readonly' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Name:</label>
                    <input type="text" id="name" name="name" required 
                        value="<?php echo $edit_record['name'] ?? ''; ?>" placeholder="Enter full name">
                </div>
                
                <div class="form-group">
                    <label for="department"><i class="fas fa-building"></i> Department:</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="BBA" <?php if (($edit_record['department'] ?? '') === 'BBA') echo 'selected'; ?>>BBA</option>
                        <option value="BSCE" <?php if (($edit_record['department'] ?? '') === 'BSCE') echo 'selected'; ?>>BSCE</option>
                        <option value="BSAg" <?php if (($edit_record['department'] ?? '') === 'BSAg') echo 'selected'; ?>>BSAg</option>
                        <option value="BSME" <?php if (($edit_record['department'] ?? '') === 'BSME') echo 'selected'; ?>>BSME</option>
                        <option value="BATHM" <?php if (($edit_record['department'] ?? '') === 'BATHM') echo 'selected'; ?>>BATHM</option>
                        <option value="BSN" <?php if (($edit_record['department'] ?? '') === 'BSN') echo 'selected'; ?>>BSN</option>
                        <option value="BCSE" <?php if (($edit_record['department'] ?? '') === 'BCSE') echo 'selected'; ?>>BCSE</option>
                        <option value="BSEEE" <?php if (($edit_record['department'] ?? '') === 'BSEEE') echo 'selected'; ?>>BSEEE</option>
                        <option value="BA Econ" <?php if (($edit_record['department'] ?? '') === 'BA Econ') echo 'selected'; ?>>BA Econ</option>
                        <option value="BA Eng" <?php if (($edit_record['department'] ?? '') === 'BA Eng') echo 'selected'; ?>>BA Eng</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="semester"><i class="fas fa-book"></i> Semester:</label>
                    <input type="number" id="semester" name="semester" min="1" max="12" required 
                        value="<?php echo $edit_record['semester'] ?? ''; ?>" placeholder="Enter semester">
                </div>
                
                <div class="form-group">
                    <label for="mobile_number"><i class="fas fa-phone"></i> Mobile Number:</label>
                    <input type="text" id="mobile_number" name="mobile_number" required 
                        value="<?php echo $edit_record['mobile_number'] ?? ''; ?>" placeholder="Enter mobile number">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="email" name="email" required 
                        value="<?php echo $edit_record['email'] ?? ''; ?>" placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label for="current_semester_sgpa"><i class="fas fa-chart-line"></i> Current Semester SGPA:</label>
                    <input type="number" id="current_semester_sgpa" name="current_semester_sgpa" 
                        step="0.01" min="0" max="4.00" required 
                        value="<?php echo $edit_record['current_semester_sgpa'] ?? ''; ?>" placeholder="0.00 - 4.00">
                </div>
                
                <div class="form-group">
                    <label for="cgpa"><i class="fas fa-chart-bar"></i> CGPA:</label>
                    <input type="number" id="cgpa" name="cgpa" step="0.01" min="0" max="4.00" required 
                        value="<?php echo $edit_record['cgpa'] ?? ''; ?>" placeholder="0.00 - 4.00">
                </div>
                
                <div class="form-group">
                    <label for="previous_semester_cgpa"><i class="fas fa-chart-area"></i> Previous Semester CGPA:</label>
                    <input type="number" id="previous_semester_cgpa" name="previous_semester_cgpa" 
                        step="0.01" min="0" max="4.00" required 
                        value="<?php echo $edit_record['previous_semester_cgpa'] ?? ''; ?>" placeholder="0.00 - 4.00">
                </div>
                
                <div class="form-group">
                    <label for="scholarship_percentage"><i class="fas fa-percent"></i> Scholarship Percentage:</label>
                    <input type="number" id="scholarship_percentage" name="scholarship_percentage" 
                        step="0.01" min="0" max="100" required 
                        value="<?php echo $edit_record['scholarship_percentage'] ?? ''; ?>" placeholder="0 - 100%">
                </div>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="<?php echo $edit_record ? 'btn-success' : ''; ?>">
                    <i class="<?php echo $edit_record ? 'fas fa-sync' : 'fas fa-plus'; ?>"></i> 
                    <?php echo $edit_record ? 'Update' : 'Add'; ?> Record
                </button>
                <?php if ($edit_record): ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <h2><i class="fas fa-search"></i> Search Applications</h2>
        <div class="search-container">
            <form method="get" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="search" placeholder="Search by name, student ID, email or phone" 
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit"><i class="fas fa-search"></i> Search</button>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
            </form>
        </div>
        
        <h2><i class="fas fa-list"></i> Applications List (<?php echo count($applications); ?>)</h2>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="?sort=id&order=<?php echo $sort === 'id' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                ID <?php echo $sort === 'id' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=application_id&order=<?php echo $sort === 'application_id' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                Student ID <?php echo $sort === 'application_id' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=name&order=<?php echo $sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                Name <?php echo $sort === 'name' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=department&order=<?php echo $sort === 'department' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                Department <?php echo $sort === 'department' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?sort=semester&order=<?php echo $sort === 'semester' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                Semester <?php echo $sort === 'semester' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>SGPA</th>
                        <th>CGPA</th>
                        <th>Prev. CGPA</th>
                        <th>Scholarship %</th>
                        <th>
                            <a href="?sort=application_date&order=<?php echo $sort === 'application_date' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&search=<?php echo urlencode($search); ?>">
                                Application Date <?php echo $sort === 'application_date' ? '<span class="sort-indicator">' . ($order === 'ASC' ? '▲' : '▼') . '</span>' : ''; ?>
                            </a>
                        </th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo $app['id']; ?></td>
                        <td><?php echo $app['application_id']; ?></td>
                        <td><?php echo htmlspecialchars($app['name']); ?></td>
                        <td><?php echo $app['department']; ?></td>
                        <td><?php echo $app['semester']; ?></td>
                        <td><?php echo $app['mobile_number']; ?></td>
                        <td><?php echo $app['email']; ?></td>
                        <td><?php echo $app['current_semester_sgpa']; ?></td>
                        <td><?php echo $app['cgpa']; ?></td>
                        <td><?php echo $app['previous_semester_cgpa']; ?></td>
                        <td><?php echo $app['scholarship_percentage']; ?>%</td>
                        <td><?php echo date('M j, Y g:i A', strtotime($app['application_date'])); ?></td>
                        <td class="action-buttons">
                            <a href="?edit=<?php echo $app['id']; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">
                                <button><i class="fas fa-edit"></i> Edit</button>
                            </a>
                            <form method="post">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($applications)): ?>
            <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                <i class="fas fa-inbox" style="font-size: 50px; margin-bottom: 15px;"></i>
                <h3>No records found</h3>
                <p>Try adjusting your search or add a new record</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Confirm before deleting
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[action="delete"]');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Auto-adjust input widths
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.style.width = '100%';
            });
        });
    </script>
</body>
</html>