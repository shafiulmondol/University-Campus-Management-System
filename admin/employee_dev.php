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
$staff_id = $_POST['staff_id'] ?? '';

// Add or Update staff
if ($action === 'save') {
    // Collect all form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $sector = $_POST['sector'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $guardian_phone = $_POST['guardian_phone'] ?? '';
    $stuff_phone = $_POST['stuff_phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $position = $_POST['position'] ?? '';
    $last_exam = $_POST['last_exam'] ?? '';
    $board = $_POST['board'] ?? '';
    $other_board = $_POST['other_board'] ?? '';
    $year_of_passing = $_POST['year_of_passing'] ?? '';
    $institution_name = $_POST['institution_name'] ?? '';
    $result = $_POST['result'] ?? '';
    $subject_group = $_POST['subject_group'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $present_address = $_POST['present_address'] ?? '';
    $permanent_address = $_POST['permanent_address'] ?? '';
    $department = $_POST['department'] ?? '';
    
    if ($staff_id) {
        // Update existing staff
        $sql = "UPDATE stuf SET 
                first_name=?, last_name=?, sector=?, father_name=?, mother_name=?, date_of_birth=?, 
                guardian_phone=?, stuff_phone=?, email=?, password=?, position=?, 
                last_exam=?, board=?, other_board=?, year_of_passing=?, institution_name=?, 
                result=?, subject_group=?, gender=?, blood_group=?, nationality=?, 
                religion=?, present_address=?, permanent_address=?, department=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name, $last_name, $sector, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $stuff_phone, $email, $password, $position,
            $last_exam, $board, $other_board, $year_of_passing, $institution_name,
            $result, $subject_group, $gender, $blood_group, $nationality,
            $religion, $present_address, $permanent_address, $department, $staff_id
        ]);
    } else {
        // Insert new staff
        $sql = "INSERT INTO stuf (
                first_name, last_name, sector, father_name, mother_name, date_of_birth, 
                guardian_phone, stuff_phone, email, password, position, 
                last_exam, board, other_board, year_of_passing, institution_name, 
                result, subject_group, gender, blood_group, nationality, 
                religion, present_address, permanent_address, department, submission_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name, $last_name, $sector, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $stuff_phone, $email, $password, $position,
            $last_exam, $board, $other_board, $year_of_passing, $institution_name,
            $result, $subject_group, $gender, $blood_group, $nationality,
            $religion, $present_address, $permanent_address, $department
        ]);
    }
}

// Delete staff
if ($action === 'delete' && $staff_id) {
    $sql = "DELETE FROM stuf WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$staff_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'ASC';
$position_filter = $_GET['position_filter'] ?? '';
$sector_filter = $_GET['sector_filter'] ?? '';

// Build query with search and sort
$sql = "SELECT * FROM stuf 
        WHERE (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search 
        OR father_name LIKE :search OR stuff_phone LIKE :search OR position LIKE :search)";
$params = ['search' => "%$search%"];

if ($position_filter) {
    $sql .= " AND position = :position_filter";
    $params['position_filter'] = $position_filter;
}

if ($sector_filter) {
    $sql .= " AND sector = :sector_filter";
    $params['sector_filter'] = $sector_filter;
}

$sql .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$staff_members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get staff data for editing
$edit_staff = null;
if ($action === 'edit' && $staff_id) {
    $sql = "SELECT * FROM stuf WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$staff_id]);
    $edit_staff = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get unique positions for filter
$positions_sql = "SELECT DISTINCT position FROM stuf WHERE position IS NOT NULL AND position != '' ORDER BY position";
$positions_stmt = $pdo->query($positions_sql);
$positions = $positions_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get unique sectors for filter
$sectors_sql = "SELECT DISTINCT sector FROM stuf WHERE sector IS NOT NULL AND sector != '' ORDER BY sector";
$sectors_stmt = $pdo->query($sectors_sql);
$sectors = $sectors_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System - Developer View</title>
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
            gap: 15px;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .filter-options {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
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
            color: black;
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
        
        .section-title {
            background: #f8f9fa;
            padding: 10px 15px;
            margin: 20px 0 15px;
            border-left: 4px solid #3498db;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .position-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .position-admin {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .position-faculty {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .position-staff {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .sector-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .sector-library {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .sector-bank {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .sector-another {
            background-color: #fce4ec;
            color: #c2185b;
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
            }
            
            .filter-options {
                flex-direction: column;
                align-items: stretch;
            }
        }
        
        @media (max-width: 576px) {
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
            <h1>Employee Management System</h1>
            <p class="subtitle">Developer View - SKST University Staff</p>
        </header>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-success" style="margin-bottom: 10px;">+ Add New Employee</button>
        <button class="btn btn-secondary" onclick="history.back()">⬅ Back</button>
        
        <!-- Hidden Employee Form -->
        <div id="employeeForm" class="card" style="display: none;">
            <h2 class="card-title">
                <?php echo $edit_staff ? 'Edit Employee Record' : 'Add New Employee'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="staff_id" value="<?php echo $edit_staff ? $edit_staff['id'] : ''; ?>">
                
                <div class="section-title">Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $edit_staff ? $edit_staff['first_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $edit_staff ? $edit_staff['last_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sector">Sector *</label>
                        <select id="sector" name="sector" required>
                            <option value="">Select Sector</option>
                            <option value="Library" <?php echo ($edit_staff && $edit_staff['sector'] == 'Library') ? 'selected' : ''; ?>>Library</option>
                            <option value="Bank" <?php echo ($edit_staff && $edit_staff['sector'] == 'Bank') ? 'selected' : ''; ?>>Bank</option>
                            <option value="Another" <?php echo ($edit_staff && $edit_staff['sector'] == 'Another') ? 'selected' : ''; ?>>Another</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="father_name">Father's Name *</label>
                        <input type="text" id="father_name" name="father_name" value="<?php echo $edit_staff ? $edit_staff['father_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mother_name">Mother's Name *</label>
                        <input type="text" id="mother_name" name="mother_name" value="<?php echo $edit_staff ? $edit_staff['mother_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $edit_staff ? $edit_staff['date_of_birth'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($edit_staff && $edit_staff['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($edit_staff && $edit_staff['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($edit_staff && $edit_staff['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo ($edit_staff && $edit_staff['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality *</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo $edit_staff ? $edit_staff['nationality'] : 'Bangladeshi'; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="religion">Religion *</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select Religion</option>
                            <option value="Islam" <?php echo ($edit_staff && $edit_staff['religion'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="Hinduism" <?php echo ($edit_staff && $edit_staff['religion'] == 'Hinduism') ? 'selected' : ''; ?>>Hinduism</option>
                            <option value="Christianity" <?php echo ($edit_staff && $edit_staff['religion'] == 'Christianity') ? 'selected' : ''; ?>>Christianity</option>
                            <option value="Buddhism" <?php echo ($edit_staff && $edit_staff['religion'] == 'Buddhism') ? 'selected' : ''; ?>>Buddhism</option>
                            <option value="Other" <?php echo ($edit_staff && $edit_staff['religion'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title">Contact Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_staff ? $edit_staff['email'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stuff_phone">Staff Phone *</label>
                        <input type="text" id="stuff_phone" name="stuff_phone" value="<?php echo $edit_staff ? $edit_staff['stuff_phone'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="guardian_phone">Guardian Phone *</label>
                        <input type="text" id="guardian_phone" name="guardian_phone" value="<?php echo $edit_staff ? $edit_staff['guardian_phone'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="present_address">Present Address *</label>
                        <textarea id="present_address" name="present_address" required><?php echo $edit_staff ? $edit_staff['present_address'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="permanent_address">Permanent Address *</label>
                        <textarea id="permanent_address" name="permanent_address" required><?php echo $edit_staff ? $edit_staff['permanent_address'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="section-title">Employment Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="position">Position/Role *</label>
                        <select id="position" name="position" required>
                            <option value="">Select Position</option>
                            <option value="Administrator" <?php echo ($edit_staff && $edit_staff['position'] == 'Administrator') ? 'selected' : ''; ?>>Administrator</option>
                            <option value="Professor" <?php echo ($edit_staff && $edit_staff['position'] == 'Professor') ? 'selected' : ''; ?>>Professor</option>
                            <option value="Associate Professor" <?php echo ($edit_staff && $edit_staff['position'] == 'Associate Professor') ? 'selected' : ''; ?>>Associate Professor</option>
                            <option value="Assistant Professor" <?php echo ($edit_staff && $edit_staff['position'] == 'Assistant Professor') ? 'selected' : ''; ?>>Assistant Professor</option>
                            <option value="Lecturer" <?php echo ($edit_staff && $edit_staff['position'] == 'Lecturer') ? 'selected' : ''; ?>>Lecturer</option>
                            <option value="Registrar" <?php echo ($edit_staff && $edit_staff['position'] == 'Registrar') ? 'selected' : ''; ?>>Registrar</option>
                            <option value="Accountant" <?php echo ($edit_staff && $edit_staff['position'] == 'Accountant') ? 'selected' : ''; ?>>Accountant</option>
                            <option value="Librarian" <?php echo ($edit_staff && $edit_staff['position'] == 'Librarian') ? 'selected' : ''; ?>>Librarian</option>
                            <option value="IT Support" <?php echo ($edit_staff && $edit_staff['position'] == 'IT Support') ? 'selected' : ''; ?>>IT Support</option>
                            <option value="Office Assistant" <?php echo ($edit_staff && $edit_staff['position'] == 'Office Assistant') ? 'selected' : ''; ?>>Office Assistant</option>
                            <option value="Cleaner" <?php echo ($edit_staff && $edit_staff['position'] == 'Cleaner') ? 'selected' : ''; ?>>Cleaner</option>
                            <option value="Security" <?php echo ($edit_staff && $edit_staff['position'] == 'Security') ? 'selected' : ''; ?>>Security</option>
                            <option value="Other" <?php echo ($edit_staff && $edit_staff['position'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="Administration" <?php echo ($edit_staff && $edit_staff['department'] == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                            <option value="CSE" <?php echo ($edit_staff && $edit_staff['department'] == 'CSE') ? 'selected' : ''; ?>>Computer Science & Engineering</option>
                            <option value="EEE" <?php echo ($edit_staff && $edit_staff['department'] == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronic Engineering</option>
                            <option value="BBA" <?php echo ($edit_staff && $edit_staff['department'] == 'BBA') ? 'selected' : ''; ?>>Business Administration</option>
                            <option value="English" <?php echo ($edit_staff && $edit_staff['department'] == 'English') ? 'selected' : ''; ?>>English</option>
                            <option value="Economics" <?php echo ($edit_staff && $edit_staff['department'] == 'Economics') ? 'selected' : ''; ?>>Economics</option>
                            <option value="Library" <?php echo ($edit_staff && $edit_staff['department'] == 'Library') ? 'selected' : ''; ?>>Library</option>
                            <option value="Accounts" <?php echo ($edit_staff && $edit_staff['department'] == 'Accounts') ? 'selected' : ''; ?>>Accounts</option>
                            <option value="IT" <?php echo ($edit_staff && $edit_staff['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                            <option value="Maintenance" <?php echo ($edit_staff && $edit_staff['department'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="Security" <?php echo ($edit_staff && $edit_staff['department'] == 'Security') ? 'selected' : ''; ?>>Security</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title">Academic Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="last_exam">Last Examination Passed</label>
                        <select id="last_exam" name="last_exam">
                            <option value="">Select Examination</option>
                            <option value="SSC" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'SSC') ? 'selected' : ''; ?>>SSC</option>
                            <option value="HSC" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'HSC') ? 'selected' : ''; ?>>HSC</option>
                            <option value="Diploma" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                            <option value="Bachelor" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'Bachelor') ? 'selected' : ''; ?>>Bachelor</option>
                            <option value="Masters" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'Masters') ? 'selected' : ''; ?>>Masters</option>
                            <option value="PhD" <?php echo ($edit_staff && $edit_staff['last_exam'] == 'PhD') ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="board">Board</label>
                        <select id="board" name="board">
                            <option value="">Select Board</option>
                            <option value="Dhaka" <?php echo ($edit_staff && $edit_staff['board'] == 'Dhaka') ? 'selected' : ''; ?>>Dhaka</option>
                            <option value="Chittagong" <?php echo ($edit_staff && $edit_staff['board'] == 'Chittagong') ? 'selected' : ''; ?>>Chittagong</option>
                            <option value="Rajshahi" <?php echo ($edit_staff && $edit_staff['board'] == 'Rajshahi') ? 'selected' : ''; ?>>Rajshahi</option>
                            <option value="Comilla" <?php echo ($edit_staff && $edit_staff['board'] == 'Comilla') ? 'selected' : ''; ?>>Comilla</option>
                            <option value="Jessore" <?php echo ($edit_staff && $edit_staff['board'] == 'Jessore') ? 'selected' : ''; ?>>Jessore</option>
                            <option value="Barisal" <?php echo ($edit_staff && $edit_staff['board'] == 'Barisal') ? 'selected' : ''; ?>>Barisal</option>
                            <option value="Sylhet" <?php echo ($edit_staff && $edit_staff['board'] == 'Sylhet') ? 'selected' : ''; ?>>Sylhet</option>
                            <option value="Dinajpur" <?php echo ($edit_staff && $edit_staff['board'] == 'Dinajpur') ? 'selected' : ''; ?>>Dinajpur</option>
                            <option value="Madrasah" <?php echo ($edit_staff && $edit_staff['board'] == 'Madrasah') ? 'selected' : ''; ?>>Madrasah</option>
                            <option value="Technical" <?php echo ($edit_staff && $edit_staff['board'] == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                            <option value="Other" <?php echo ($edit_staff && $edit_staff['board'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="other-board-group" style="<?php echo ($edit_staff && $edit_staff['board'] == 'Other') ? '' : 'display: none;'; ?>">
                        <label for="other_board">Other Board Name</label>
                        <input type="text" id="other_board" name="other_board" value="<?php echo $edit_staff ? $edit_staff['other_board'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="year_of_passing">Year of Passing</label>
                        <input type="number" id="year_of_passing" name="year_of_passing" min="1990" max="2099" value="<?php echo $edit_staff ? $edit_staff['year_of_passing'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="institution_name">Institution Name</label>
                        <input type="text" id="institution_name" name="institution_name" value="<?php echo $edit_staff ? $edit_staff['institution_name'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="result">Result (GPA/CGPA)</label>
                        <input type="number" id="result" name="result" step="0.01" min="1" max="5" value="<?php echo $edit_staff ? $edit_staff['result'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject_group">Subject/Group</label>
                        <select id="subject_group" name="subject_group">
                            <option value="">Select Group</option>
                            <option value="Science" <?php echo ($edit_staff && $edit_staff['subject_group'] == 'Science') ? 'selected' : ''; ?>>Science</option>
                            <option value="Commerce" <?php echo ($edit_staff && $edit_staff['subject_group'] == 'Commerce') ? 'selected' : ''; ?>>Commerce</option>
                            <option value="Arts" <?php echo ($edit_staff && $edit_staff['subject_group'] == 'Arts') ? 'selected' : ''; ?>>Arts</option>
                            <option value="Other" <?php echo ($edit_staff && $edit_staff['subject_group'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title">Account Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="text" id="password" name="password" value="<?php echo $edit_staff ? $edit_staff['password'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_staff ? 'Update Record' : 'Add Employee'; ?></button>
                    <?php if ($edit_staff): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Employee Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Employees</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or position..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
                
                <div class="filter-options">
                    <div class="filter-group">
                        <label for="position_filter">Filter by Position:</label>
                        <select id="position_filter" name="position_filter" onchange="updateFilters()">
                            <option value="">All Positions</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position; ?>" <?php echo $position_filter == $position ? 'selected' : ''; ?>>
                                    <?php echo $position; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sector_filter">Filter by Sector:</label>
                        <select id="sector_filter" name="sector_filter" onchange="updateFilters()">
                            <option value="">All Sectors</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?php echo $sector; ?>" <?php echo $sector_filter == $sector ? 'selected' : ''; ?>>
                                    <?php echo $sector; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Sort by:</label>
                        <select id="sort" onchange="updateFilters()">
                            <option value="first_name" <?php echo $sort === 'first_name' ? 'selected' : ''; ?>>First Name</option>
                            <option value="last_name" <?php echo $sort === 'last_name' ? 'selected' : ''; ?>>Last Name</option>
                            <option value="position" <?php echo $sort === 'position' ? 'selected' : ''; ?>>Position</option>
                            <option value="department" <?php echo $sort === 'department' ? 'selected' : ''; ?>>Department</option>
                            <option value="submission_date" <?php echo $sort === 'submission_date' ? 'selected' : ''; ?>>Registration Date</option>
                        </select>
                    </div>

                    <button class="btn" onclick="updateFilters()">
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
                        <th>Phone</th>
                        <th>Sector</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Registration Date</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($staff_members) > 0): ?>
                        <?php foreach ($staff_members as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['stuff_phone']); ?></td>
                                <td>
                                    <span class="sector-badge 
                                        <?php echo strtolower($s['sector']) == 'library' ? 'sector-library' : ''; ?>
                                        <?php echo strtolower($s['sector']) == 'bank' ? 'sector-bank' : ''; ?>
                                        <?php echo strtolower($s['sector']) == 'another' ? 'sector-another' : ''; ?>">
                                        <?php echo htmlspecialchars($s['sector']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="position-badge 
                                        <?php echo strtolower($s['position']) == 'administrator' ? 'position-admin' : ''; ?>
                                        <?php echo in_array(strtolower($s['position']), ['professor', 'associate professor', 'assistant professor', 'lecturer']) ? 'position-faculty' : ''; ?>
                                        <?php echo in_array(strtolower($s['position']), ['registrar', 'accountant', 'librarian', 'it support', 'office assistant', 'cleaner', 'security']) ? 'position-staff' : ''; ?>">
                                        <?php echo htmlspecialchars($s['position']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($s['department']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($s['submission_date'])); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this employee record?');">
                                        <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No employee records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Toggle form visibility
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("employeeForm");
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
                updateFilters();
            }.bind(this), 800);
        });
        
        // Show/hide other board field based on selection
        document.getElementById('board').addEventListener('change', function() {
            var otherBoardGroup = document.getElementById('other-board-group');
            otherBoardGroup.style.display = this.value === 'Other' ? 'block' : 'none';
        });
        
        // Auto-show form if editing
        <?php if ($edit_staff): ?>
            document.getElementById('employeeForm').style.display = 'block';
        <?php endif; ?>
        
        // Update filters function
        function updateFilters() {
            const search = document.getElementById('search').value;
            const positionFilter = document.getElementById('position_filter').value;
            const sectorFilter = document.getElementById('sector_filter').value;
            const sort = document.getElementById('sort').value;
            const order = '<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>';
            
            let url = `?search=${encodeURIComponent(search)}&position_filter=${encodeURIComponent(positionFilter)}&sector_filter=${encodeURIComponent(sectorFilter)}&sort=${sort}&order=${order}`;
            window.location.href = url;
        }
    </script>
</body>
</html>