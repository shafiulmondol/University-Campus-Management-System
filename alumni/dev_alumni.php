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
$alumni_id = $_POST['alumni_id'] ?? '';

// Add or Update alumni
if ($action === 'save') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $graduation_year = $_POST['graduation_year'] ?? '';
    $degree = $_POST['degree'] ?? '';
    $major = $_POST['major'] ?? '';
    $current_job = $_POST['current_job'] ?? '';
    $company = $_POST['company'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if ($alumni_id) {
        // Update existing alumni
        $sql = "UPDATE alumni SET name=?, email=?, password=?, graduation_year=?, degree=?, major=?, current_job=?, company=?, phone=?, address=? WHERE alumni_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $password, $graduation_year, $degree, $major, $current_job, $company, $phone, $address, $alumni_id]);
    } else {
        // Insert new alumni
        $sql = "INSERT INTO alumni (name, email, password, graduation_year, degree, major, current_job, company, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $password, $graduation_year, $degree, $major, $current_job, $company, $phone, $address]);
    }
}

// Delete alumni
if ($action === 'delete' && $alumni_id) {
    $sql = "DELETE FROM alumni WHERE alumni_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$alumni_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'alumni_id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM alumni WHERE name LIKE :search OR email LIKE :search OR company LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get alumni data for editing
$edit_alumni = null;
if ($action === 'edit' && $alumni_id) {
    $sql = "SELECT * FROM alumni WHERE alumni_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$alumni_id]);
    $edit_alumni = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Management System - Developer View</title>
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
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            
        }
        
        .btn:hover {
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
            column-gap: 50px;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            margin-right: 20px;
        }
        
        .sort-options {
            display: flex;
            align-items:self-end;
            gap: 10px;
            margin-top: 10px;
        }
        label{
          color: green;
          font-weight: bold;
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
            color: black;
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
            <h1>Alumni Management System</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Trigger button -->
<button id="toggleFormBtn" class="btn btn-primary" style="margin-bottom: 10px; background:#2ecc71">+ Add New Alumni</button>

<!-- Hidden Alumni Form -->
<div id="alumniForm" class="card" style="display: none;">
    <h2 class="card-title">
        <?php echo $edit_alumni ? 'Edit Alumni Record' : 'Add New Alumni'; ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="alumni_id" value="<?php echo $edit_alumni ? $edit_alumni['alumni_id'] : ''; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo $edit_alumni ? $edit_alumni['name'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo $edit_alumni ? $edit_alumni['email'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="text" id="password" name="password" value="<?php echo $edit_alumni ? $edit_alumni['password'] : ''; ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="graduation_year">Graduation Year</label>
                <input type="number" id="graduation_year" name="graduation_year" min="1900" max="2099" value="<?php echo $edit_alumni ? $edit_alumni['graduation_year'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="degree">Degree</label>
                <input type="text" id="degree" name="degree" value="<?php echo $edit_alumni ? $edit_alumni['degree'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="major">Major</label>
                <input type="text" id="major" name="major" value="<?php echo $edit_alumni ? $edit_alumni['major'] : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="current_job">Current Job</label>
                <input type="text" id="current_job" name="current_job" value="<?php echo $edit_alumni ? $edit_alumni['current_job'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" id="company" name="company" value="<?php echo $edit_alumni ? $edit_alumni['company'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo $edit_alumni ? $edit_alumni['phone'] : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group" style="flex: 1 0 100%;">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo $edit_alumni ? $edit_alumni['address'] : ''; ?></textarea>
            </div>
        </div>
        
        <div class="action-buttons">
            <button type="submit" class="btn btn-success"><?php echo $edit_alumni ? 'Update Record' : 'Add Alumni'; ?></button>
            <?php if ($edit_alumni): ?>
                <a href="?" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- JavaScript to toggle form -->
<script>
    document.getElementById("toggleFormBtn").addEventListener("click", function() {
        var form = document.getElementById("alumniForm");
        if (form.style.display === "none") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    });
</script>

        
        <div class="card">
            <h2 class="card-title">Alumni Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Alumni</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or company..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
    <label for="sort">Sort by:</label>
    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
        <option value="graduation_year" <?php echo $sort === 'graduation_year' ? 'selected' : ''; ?>>Graduation Year</option>
        <option value="degree" <?php echo $sort === 'degree' ? 'selected' : ''; ?>>Degree</option>
        <option value="registration_date" <?php echo $sort === 'registration_date' ? 'selected' : ''; ?>>Registration Date</option>
    </select>

    <button class="btn" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
    </button>
</div>

            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Graduation Year</th>
                        <th>Degree</th>
                        <th>Company</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($alumni) > 0): ?>
                        <?php foreach ($alumni as $a): ?>
                            <tr>
                                <td><?php echo $a['alumni_id']; ?></td>
                                <td><?php echo htmlspecialchars($a['name']); ?></td>
                                <td><?php echo htmlspecialchars($a['email']); ?></td>
                                <td><?php echo $a['graduation_year']; ?></td>
                                <td><?php echo htmlspecialchars($a['degree']); ?></td>
                                <td><?php echo htmlspecialchars($a['company']); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="alumni_id" value="<?php echo $a['alumni_id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                        <input type="hidden" name="alumni_id" value="<?php echo $a['alumni_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No alumni records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    </script>

 
</body>
</html>