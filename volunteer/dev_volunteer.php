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
$sl = $_POST['sl'] ?? '';

// Add or Update volunteer
if ($action === 'save') {
    $student_id = $_POST['student_id'] ?? '';
    $student_name = $_POST['student_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $activity_name = $_POST['activity_name'] ?? '';
    $activity_date = $_POST['activity_date'] ?? '';
    $role = $_POST['role'] ?? '';
    $hours = $_POST['hours'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    
    if ($sl) {
        // Update existing volunteer
        $sql = "UPDATE volunteers SET student_id=?, student_name=?, department=?, email=?, phone=?, activity_name=?, activity_date=?, role=?, hours=?, remarks=? WHERE sl=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $student_name, $department, $email, $phone, $activity_name, $activity_date, $role, $hours, $remarks, $sl]);
    } else {
        // Insert new volunteer
        $sql = "INSERT INTO volunteers (student_id, student_name, department, email, phone, activity_name, activity_date, role, hours, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $student_name, $department, $email, $phone, $activity_name, $activity_date, $role, $hours, $remarks]);
    }
}

// Delete volunteer
if ($action === 'delete' && $sl) {
    $sql = "DELETE FROM volunteers WHERE sl=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sl]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'sl';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM volunteers WHERE student_name LIKE :search OR department LIKE :search OR activity_name LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get volunteer data for editing
$edit_volunteer = null;
if ($action === 'edit' && $sl) {
    $sql = "SELECT * FROM volunteers WHERE sl=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sl]);
    $edit_volunteer = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteers Management System - Developer View</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
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
            <h1>Volunteers Management System</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Trigger button -->
<button id="toggleFormBtn" class="btn btn-primary" style="margin-bottom: 10px; background:#2ecc71">+ Add New Volunteer</button>
<button class="btn btn-secondary" onclick="window.location.href='http://localhost:8080/University-Campus-Management-System/admin/admin.php'">⬅ Back</button>
<!-- Hidden Volunteer Form -->
<div id="volunteerForm" class="card" style="display: none;">
    <h2 class="card-title">
        <?php echo $edit_volunteer ? 'Edit Volunteer Record' : 'Add New Volunteer'; ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="sl" value="<?php echo $edit_volunteer ? $edit_volunteer['sl'] : ''; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input type="number" id="student_id" name="student_id" value="<?php echo $edit_volunteer ? $edit_volunteer['student_id'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="student_name">Student Name *</label>
                <input type="text" id="student_name" name="student_name" value="<?php echo $edit_volunteer ? $edit_volunteer['student_name'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" value="<?php echo $edit_volunteer ? $edit_volunteer['department'] : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo $edit_volunteer ? $edit_volunteer['email'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo $edit_volunteer ? $edit_volunteer['phone'] : ''; ?>">
            </div>
            
            <div class="form-group">
                        <label for="activity_name" class="required">Activity Name</label>
                        <select id="activity_name" name="activity_name" required>
                            <option value="">Select Activity</option>
                            <option value="Blood Donation Camp" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Blood Donation Camp') ? 'selected' : ''; ?>>Blood Donation Camp</option>
                            <option value="Tree Plantation Drive" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Tree Plantation Drive') ? 'selected' : ''; ?>>Tree Plantation Drive</option>
                            <option value="Campus Clean-up" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Campus Clean-up') ? 'selected' : ''; ?>>Campus Clean-up</option>
                            <option value="Fundraising Event" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Fundraising Event') ? 'selected' : ''; ?>>Fundraising Event</option>
                            <option value="Cultural Festival" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Cultural Festival') ? 'selected' : ''; ?>>Cultural Festival</option>
                            <option value="Student Mentorship" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Student Mentorship') ? 'selected' : ''; ?>>Student Mentorship</option>
                            <option value="Community Outreach" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Community Outreach') ? 'selected' : ''; ?>>Community Outreach</option>
                            <option value="Health Awareness Campaign" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Health Awareness Campaign') ? 'selected' : ''; ?>>Health Awareness Campaign</option>
                        </select>
                    </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="activity_date">Activity Date *</label>
                <input type="date" id="activity_date" name="activity_date" value="<?php echo $edit_volunteer ? $edit_volunteer['activity_date'] : ''; ?>" required>
            </div>
            
            <div class="form-group">
                        <label for="role" class="required">Preferred Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Volunteer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                            <option value="Organizer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Organizer') ? 'selected' : ''; ?>>Organizer</option>
                            <option value="Leader" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Leader') ? 'selected' : ''; ?>>Leader</option>
                            <option value="Coordinator" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                            <option value="Support Staff" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Support Staff') ? 'selected' : ''; ?>>Support Staff</option>
                        </select>
                    </div>
            
            <div class="form-group">
                <label for="hours">Hours</label>
                <input type="number" id="hours" name="hours" min="0" value="<?php echo $edit_volunteer ? $edit_volunteer['hours'] : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group" style="flex: 1 0 100%;">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks"><?php echo $edit_volunteer ? $edit_volunteer['remarks'] : ''; ?></textarea>
            </div>
        </div>
        
        <div class="action-buttons">
            <button type="submit" class="btn btn-success"><?php echo $edit_volunteer ? 'Update Record' : 'Add Volunteer'; ?></button>
            <?php if ($edit_volunteer): ?>
                <a href="?" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- JavaScript to toggle form -->
<script>
    document.getElementById("toggleFormBtn").addEventListener("click", function() {
        var form = document.getElementById("volunteerForm");
        if (form.style.display === "none") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    });
</script>

        
        <div class="card">
            <h2 class="card-title">Volunteer Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Volunteers</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, department, or activity..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
    <label for="sort">Sort by:</label>
    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
        <option value="student_name" <?php echo $sort === 'student_name' ? 'selected' : ''; ?>>Name</option>
        <option value="department" <?php echo $sort === 'department' ? 'selected' : ''; ?>>Department</option>
        <option value="activity_name" <?php echo $sort === 'activity_name' ? 'selected' : ''; ?>>Activity</option>
        <option value="activity_date" <?php echo $sort === 'activity_date' ? 'selected' : ''; ?>>Date</option>
    </select>

    <button class="btn" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
    </button>
</div>

            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Hours</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($volunteers) > 0): ?>
                        <?php foreach ($volunteers as $v): ?>
                            <tr>
                                <td><?php echo $v['sl']; ?></td>
                                <td><?php echo $v['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($v['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($v['department']); ?></td>
                                <td><?php echo htmlspecialchars($v['activity_name']); ?></td>
                                <td><?php echo $v['activity_date']; ?></td>
                                <td><?php echo $v['hours']; ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="sl" value="<?php echo $v['sl']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                        <input type="hidden" name="sl" value="<?php echo $v['sl']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No volunteer records found.</td>
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
        
        // Show form if editing
        <?php if ($edit_volunteer): ?>
            document.getElementById('volunteerForm').style.display = 'block';
        <?php endif; ?>
    </script>

 
</body>
</html>