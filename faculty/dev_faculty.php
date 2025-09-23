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
$faculty_id = $_POST['faculty_id'] ?? '';

// File upload handling
$upload_dir = 'uploads/faculty/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Add or Update faculty
if ($action === 'save') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $department = $_POST['department'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $room_number = $_POST['room_number'] ?? '';
    $salary = $_POST['salary'] ?? '';
    
    // Handle file upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            $profile_picture = $file_path;
        }
    }
    
    if ($faculty_id) {
        // Update existing faculty
        if ($profile_picture) {
            $sql = "UPDATE faculty SET name=?, email=?, password=?, department=?, address=?, phone=?, room_number=?, salary=?, profile_picture=? WHERE faculty_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $password, $department, $address, $phone, $room_number, $salary, $profile_picture, $faculty_id]);
        } else {
            $sql = "UPDATE faculty SET name=?, email=?, password=?, department=?, address=?, phone=?, room_number=?, salary=? WHERE faculty_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $password, $department, $address, $phone, $room_number, $salary, $faculty_id]);
        }
    } else {
        // Insert new faculty
        $sql = "INSERT INTO faculty (name, email, password, department, address, phone, room_number, salary, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $password, $department, $address, $phone, $room_number, $salary, $profile_picture]);
    }
}

// Delete faculty
if ($action === 'delete' && $faculty_id) {
    // Get profile picture path before deleting
    $sql = "SELECT profile_picture FROM faculty WHERE faculty_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Delete the record
    $sql = "DELETE FROM faculty WHERE faculty_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$faculty_id]);
    
    // Delete the profile picture file if it exists
    if ($faculty && $faculty['profile_picture'] && file_exists($faculty['profile_picture'])) {
        unlink($faculty['profile_picture']);
    }
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'faculty_id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM faculty WHERE name LIKE :search OR email LIKE :search OR department LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$faculty = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get faculty data for editing
$edit_faculty = null;
if ($action === 'edit' && $faculty_id) {
    $sql = "SELECT * FROM faculty WHERE faculty_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$faculty_id]);
    $edit_faculty = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle view profile picture
$view_profile = null;
if (isset($_GET['view_profile']) && $_GET['view_profile']) {
    $sql = "SELECT * FROM faculty WHERE faculty_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['view_profile']]);
    $view_profile = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <title>Faculty Management System - Developer View</title>
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
        
        .profile-picture {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 80%;
            max-height: 80%;
        }
        
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
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
            <h1>Faculty Management System</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-primary" style="margin-bottom: 10px; background:#2ecc71">+ Add Faculty</button>
        <button class="btn btn-secondary" onclick="history.back()">⬅ Back</button>




        <!-- Hidden Faculty Form -->
        <div id="facultyForm" class="card" style="display: none;">
            <h2 class="card-title">
                <?php echo $edit_faculty ? 'Edit Faculty Record' : 'Add New Faculty'; ?>
            </h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="faculty_id" value="<?php echo $edit_faculty ? $edit_faculty['faculty_id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo $edit_faculty ? $edit_faculty['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_faculty ? $edit_faculty['email'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="text" id="password" name="password" value="<?php echo $edit_faculty ? $edit_faculty['password'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="<?php echo $edit_faculty ? $edit_faculty['department'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $edit_faculty ? $edit_faculty['phone'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="room_number">Room Number</label>
                        <input type="text" id="room_number" name="room_number" value="<?php echo $edit_faculty ? $edit_faculty['room_number'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary">Salary</label>
                        <input type="number" id="salary" name="salary" step="0.01" value="<?php echo $edit_faculty ? $edit_faculty['salary'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                    
                    <?php if ($edit_faculty && $edit_faculty['profile_picture']): ?>
                    <div class="form-group">
                        <label>Current Profile Picture</label>
                        <img src="<?php echo $edit_faculty['profile_picture']; ?>" alt="Profile Picture" class="profile-picture" onclick="viewProfilePicture('<?php echo $edit_faculty['profile_picture']; ?>')">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1 0 100%;">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"><?php echo $edit_faculty ? $edit_faculty['address'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_faculty ? 'Update Record' : 'Add Faculty'; ?></button>
                    <?php if ($edit_faculty): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Faculty Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Faculty</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or department..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="department" <?php echo $sort === 'department' ? 'selected' : ''; ?>>Department</option>
                        <option value="salary" <?php echo $sort === 'salary' ? 'selected' : ''; ?>>Salary</option>
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
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Room No.</th>
                        <th>Salary</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($faculty) > 0): ?>
                        <?php foreach ($faculty as $f): ?>
                            <tr>
                                <td><?php echo $f['faculty_id']; ?></td>
                                <td>
                                    <?php if ($f['profile_picture']): ?>
                                        <img src="<?php echo $f['profile_picture']; ?>" alt="Profile Picture" class="profile-picture" onclick="viewProfilePicture('<?php echo $f['profile_picture']; ?>')">
                                    <?php else: ?>
                                        <span>No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($f['name']); ?></td>
                                <td><?php echo htmlspecialchars($f['email']); ?></td>
                                <td><?php echo htmlspecialchars($f['department']); ?></td>
                                <td><?php echo htmlspecialchars($f['room_number']); ?></td>
                                <td>৳<?php echo number_format($f['salary'], 2); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="faculty_id" value="<?php echo $f['faculty_id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                        <input type="hidden" name="faculty_id" value="<?php echo $f['faculty_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                    
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No faculty records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Profile Picture Modal -->
    <div id="profileModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        // Toggle form functionality
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("facultyForm");
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        });

        // Live search functionality
        document.getElementById('search').addEventListener('input', function() {
            // Submit the form after a short delay
            clearTimeout(this.delay);
            this.delay = setTimeout(function() {
                this.form.submit();
            }.bind(this), 800);
        });

        // Profile picture modal functionality
        function viewProfilePicture(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('profileModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('profileModal').style.display = 'none';
        }

        // Close modal when clicking outside the image
        window.onclick = function(event) {
            var modal = document.getElementById('profileModal');
            if (event.target == modal) {
                closeModal();
            }
        }

   
    </script>

</body>

</html>