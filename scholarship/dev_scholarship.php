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
$id = $_POST['id'] ?? '';

// Add or Update scholarship application
if ($action === 'save') {
    $application_id = $_POST['application_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $department = $_POST['department'] ?? '';
    $semester = $_POST['semester'] ?? '';
    $mobile_number = $_POST['mobile_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_semester_sgpa = $_POST['current_semester_sgpa'] ?? '';
    $cgpa = $_POST['cgpa'] ?? '';
    $previous_semester_cgpa = $_POST['previous_semester_cgpa'] ?? '';
    $scholarship_percentage = $_POST['scholarship_percentage'] ?? '';
    
    if ($id) {
        // Update existing application
        $sql = "UPDATE scholarship_application SET application_id=?, name=?, department=?, semester=?, mobile_number=?, email=?, current_semester_sgpa=?, cgpa=?, previous_semester_cgpa=?, scholarship_percentage=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$application_id, $name, $department, $semester, $mobile_number, $email, $current_semester_sgpa, $cgpa, $previous_semester_cgpa, $scholarship_percentage, $id]);
    } else {
        // Insert new application
        $sql = "INSERT INTO scholarship_application (application_id, name, department, semester, mobile_number, email, current_semester_sgpa, cgpa, previous_semester_cgpa, scholarship_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$application_id, $name, $department, $semester, $mobile_number, $email, $current_semester_sgpa, $cgpa, $previous_semester_cgpa, $scholarship_percentage]);
    }
}

// Delete application
if ($action === 'delete' && $id) {
    $sql = "DELETE FROM scholarship_application WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'application_date';
$order = $_GET['order'] ?? 'DESC';

// Build query with search and sort
$sql = "SELECT * FROM scholarship_application WHERE name LIKE :search OR email LIKE :search OR application_id LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get application data for editing
$edit_application = null;
if ($action === 'edit' && $id) {
    $sql = "SELECT * FROM scholarship_application WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $edit_application = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Application Management - Developer View</title>
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
            background: linear-gradient(135deg, #2c3e50, #1a2530);
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
            flex: 1 0 100%;
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
        
        textarea {
            min-height: 100px;
            resize: vertical;
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
            text-align: center;
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
            gap: 20px;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
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
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
            background-color: #f8f9fa;
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
            margin: 0 5px;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
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
        
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(50% - 20px);
            }
            
            .search-sort {
                flex-direction: column;
                gap: 15px;
            }
            
            .stat-card {
                min-width: 150px;
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
            
            .stat-card {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Scholarship Application Management</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($applications); ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $approved = array_filter($applications, function($app) {
                        return $app['scholarship_percentage'] > 0;
                    });
                    echo count($approved);
                    ?>
                </div>
                <div class="stat-label">Approved Applications</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php
                    $avgCgpa = 0;
                    if (count($applications) > 0) {
                        $totalCgpa = array_sum(array_column($applications, 'cgpa'));
                        $avgCgpa = round($totalCgpa / count($applications), 2);
                    }
                    echo $avgCgpa;
                    ?>
                </div>
                <div class="stat-label">Average CGPA</div>
            </div>
        </div>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-success" style="margin-bottom: 10px;">+ Add New Application</button>
        <button class="btn btn-secondary" onclick="history.back()">⬅ Back</button>
        
        <!-- Hidden Application Form -->
        <div id="applicationForm" class="card" style="display: <?php echo $edit_application ? 'block' : 'none'; ?>;">
            <h2 class="card-title">
                <?php echo $edit_application ? 'Edit Application Record' : 'Add New Application'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?php echo $edit_application ? $edit_application['id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="application_id">Application ID *</label>
                        <input type="text" id="application_id" name="application_id" value="<?php echo $edit_application ? $edit_application['application_id'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo $edit_application ? $edit_application['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="BBA" <?php echo ($edit_application && $edit_application['department'] == 'BBA') ? 'selected' : ''; ?>>BBA</option>
                            <option value="BSCE" <?php echo ($edit_application && $edit_application['department'] == 'BSCE') ? 'selected' : ''; ?>>BSCE</option>
                            <option value="BSAg" <?php echo ($edit_application && $edit_application['department'] == 'BSAg') ? 'selected' : ''; ?>>BSAg</option>
                            <option value="BSME" <?php echo ($edit_application && $edit_application['department'] == 'BSME') ? 'selected' : ''; ?>>BSME</option>
                            <option value="BATHM" <?php echo ($edit_application && $edit_application['department'] == 'BATHM') ? 'selected' : ''; ?>>BATHM</option>
                            <option value="BSN" <?php echo ($edit_application && $edit_application['department'] == 'BSN') ? 'selected' : ''; ?>>BSN</option>
                            <option value="BCSE" <?php echo ($edit_application && $edit_application['department'] == 'BCSE') ? 'selected' : ''; ?>>BCSE</option>
                            <option value="BSEEE" <?php echo ($edit_application && $edit_application['department'] == 'BSEEE') ? 'selected' : ''; ?>>BSEEE</option>
                            <option value="BA Econ" <?php echo ($edit_application && $edit_application['department'] == 'BA Econ') ? 'selected' : ''; ?>>BA Econ</option>
                            <option value="BA Eng" <?php echo ($edit_application && $edit_application['department'] == 'BA Eng') ? 'selected' : ''; ?>>BA Eng</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="semester">Semester *</label>
                        <input type="number" id="semester" name="semester" min="1" max="12" value="<?php echo $edit_application ? $edit_application['semester'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number *</label>
                        <input type="text" id="mobile_number" name="mobile_number" value="<?php echo $edit_application ? $edit_application['mobile_number'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_application ? $edit_application['email'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_semester_sgpa">Current Semester SGPA *</label>
                        <input type="number" id="current_semester_sgpa" name="current_semester_sgpa" step="0.01" min="0" max="4.00" value="<?php echo $edit_application ? $edit_application['current_semester_sgpa'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cgpa">CGPA *</label>
                        <input type="number" id="cgpa" name="cgpa" step="0.01" min="0" max="4.00" value="<?php echo $edit_application ? $edit_application['cgpa'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="previous_semester_cgpa">Previous Semester CGPA *</label>
                        <input type="number" id="previous_semester_cgpa" name="previous_semester_cgpa" step="0.01" min="0" max="4.00" value="<?php echo $edit_application ? $edit_application['previous_semester_cgpa'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="scholarship_percentage">Scholarship Percentage *</label>
                        <input type="number" id="scholarship_percentage" name="scholarship_percentage" step="0.01" min="0" max="100" value="<?php echo $edit_application ? $edit_application['scholarship_percentage'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_application ? 'Update Record' : 'Add Application'; ?></button>
                    <?php if ($edit_application): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Scholarship Applications</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Applications</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or application ID..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="department" <?php echo $sort === 'department' ? 'selected' : ''; ?>>Department</option>
                        <option value="cgpa" <?php echo $sort === 'cgpa' ? 'selected' : ''; ?>>CGPA</option>
                        <option value="scholarship_percentage" <?php echo $sort === 'scholarship_percentage' ? 'selected' : ''; ?>>Scholarship %</option>
                        <option value="application_date" <?php echo $sort === 'application_date' ? 'selected' : ''; ?>>Application Date</option>
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
                        <th>Application ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>CGPA</th>
                        <th>Scholarship %</th>
                        <th>Application Date</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo $app['id']; ?></td>
                                <td><?php echo htmlspecialchars($app['application_id']); ?></td>
                                <td><?php echo htmlspecialchars($app['name']); ?></td>
                                <td><?php echo htmlspecialchars($app['department']); ?></td>
                                <td><?php echo $app['semester']; ?></td>
                                <td><?php echo $app['cgpa']; ?></td>
                                <td><?php echo $app['scholarship_percentage']; ?>%</td>
                                <td><?php echo date('M j, Y', strtotime($app['application_date'])); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this application?');">
                                        <input type="hidden" name="id" value="<?php echo $app['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No scholarship applications found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Toggle form visibility
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("applicationForm");
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        });

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