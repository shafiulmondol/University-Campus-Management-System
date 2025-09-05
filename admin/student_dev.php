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
$student_id = $_POST['student_id'] ?? '';

// Add or Update student
if ($action === 'save') {
    // Collect all form data
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $guardian_phone = $_POST['guardian_phone'] ?? '';
    $student_phone = $_POST['student_phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $key = $_POST['key'] ?? '';
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
    
    if ($student_id) {
        // Update existing student
        $sql = "UPDATE student_registration SET 
                first_name=?, last_name=?, father_name=?, mother_name=?, date_of_birth=?, 
                guardian_phone=?, student_phone=?, email=?, password=?, `key`=?, 
                last_exam=?, board=?, other_board=?, year_of_passing=?, institution_name=?, 
                result=?, subject_group=?, gender=?, blood_group=?, nationality=?, 
                religion=?, present_address=?, permanent_address=?, department=? 
                WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name, $last_name, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $student_phone, $email, $password, $key,
            $last_exam, $board, $other_board, $year_of_passing, $institution_name,
            $result, $subject_group, $gender, $blood_group, $nationality,
            $religion, $present_address, $permanent_address, $department, $student_id
        ]);
    } else {
        // Insert new student
        $sql = "INSERT INTO student_registration (
                first_name, last_name, father_name, mother_name, date_of_birth, 
                guardian_phone, student_phone, email, password, `key`, 
                last_exam, board, other_board, year_of_passing, institution_name, 
                result, subject_group, gender, blood_group, nationality, 
                religion, present_address, permanent_address, department, submission_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $first_name, $last_name, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $student_phone, $email, $password, $key,
            $last_exam, $board, $other_board, $year_of_passing, $institution_name,
            $result, $subject_group, $gender, $blood_group, $nationality,
            $religion, $present_address, $permanent_address, $department
        ]);
    }
}

// Delete student
if ($action === 'delete' && $student_id) {
    $sql = "DELETE FROM student_registration WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM student_registration 
        WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search 
        OR father_name LIKE :search OR student_phone LIKE :search 
        ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get student data for editing
$edit_student = null;
if ($action === 'edit' && $student_id) {
    $sql = "SELECT * FROM student_registration WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Developer View</title>
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
            align-items: self-end;
            gap: 10px;
            margin-top: 10px;
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
            
            .search-box {
                margin-right: 0;
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
            <h1>Student Management System</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-success" style="margin-bottom: 10px;">+ Add New Student</button>
        <button class="btn btn-secondary" onclick="history.back()">⬅ Back</button>
        
        <!-- Hidden Student Form -->
        <div id="studentForm" class="card" style="display: none;">
            <h2 class="card-title">
                <?php echo $edit_student ? 'Edit Student Record' : 'Add New Student'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="student_id" value="<?php echo $edit_student ? $edit_student['id'] : ''; ?>">
                
                <div class="section-title">Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $edit_student ? $edit_student['first_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $edit_student ? $edit_student['last_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="father_name">Father's Name *</label>
                        <input type="text" id="father_name" name="father_name" value="<?php echo $edit_student ? $edit_student['father_name'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mother_name">Mother's Name *</label>
                        <input type="text" id="mother_name" name="mother_name" value="<?php echo $edit_student ? $edit_student['mother_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth *</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $edit_student ? $edit_student['date_of_birth'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($edit_student && $edit_student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($edit_student && $edit_student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($edit_student && $edit_student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?php echo ($edit_student && $edit_student['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo ($edit_student && $edit_student['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo ($edit_student && $edit_student['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo ($edit_student && $edit_student['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo ($edit_student && $edit_student['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo ($edit_student && $edit_student['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo ($edit_student && $edit_student['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo ($edit_student && $edit_student['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality *</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo $edit_student ? $edit_student['nationality'] : 'Bangladeshi'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="religion">Religion *</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select Religion</option>
                            <option value="Islam" <?php echo ($edit_student && $edit_student['religion'] == 'Islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="Hinduism" <?php echo ($edit_student && $edit_student['religion'] == 'Hinduism') ? 'selected' : ''; ?>>Hinduism</option>
                            <option value="Christianity" <?php echo ($edit_student && $edit_student['religion'] == 'Christianity') ? 'selected' : ''; ?>>Christianity</option>
                            <option value="Buddhism" <?php echo ($edit_student && $edit_student['religion'] == 'Buddhism') ? 'selected' : ''; ?>>Buddhism</option>
                            <option value="Other" <?php echo ($edit_student && $edit_student['religion'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title">Contact Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_student ? $edit_student['email'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="student_phone">Student Phone *</label>
                        <input type="text" id="student_phone" name="student_phone" value="<?php echo $edit_student ? $edit_student['student_phone'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="guardian_phone">Guardian Phone *</label>
                        <input type="text" id="guardian_phone" name="guardian_phone" value="<?php echo $edit_student ? $edit_student['guardian_phone'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="present_address">Present Address *</label>
                        <textarea id="present_address" name="present_address" required><?php echo $edit_student ? $edit_student['present_address'] : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="permanent_address">Permanent Address *</label>
                        <textarea id="permanent_address" name="permanent_address" required><?php echo $edit_student ? $edit_student['permanent_address'] : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="section-title">Academic Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="last_exam">Last Examination Passed *</label>
                        <select id="last_exam" name="last_exam" required>
                            <option value="">Select Examination</option>
                            <option value="SSC" <?php echo ($edit_student && $edit_student['last_exam'] == 'SSC') ? 'selected' : ''; ?>>SSC</option>
                            <option value="HSC" <?php echo ($edit_student && $edit_student['last_exam'] == 'HSC') ? 'selected' : ''; ?>>HSC</option>
                            <option value="Diploma" <?php echo ($edit_student && $edit_student['last_exam'] == 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                            <option value="Bachelor" <?php echo ($edit_student && $edit_student['last_exam'] == 'Bachelor') ? 'selected' : ''; ?>>Bachelor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="board">Board *</label>
                        <select id="board" name="board" required>
                            <option value="">Select Board</option>
                            <option value="Dhaka" <?php echo ($edit_student && $edit_student['board'] == 'Dhaka') ? 'selected' : ''; ?>>Dhaka</option>
                            <option value="Chittagong" <?php echo ($edit_student && $edit_student['board'] == 'Chittagong') ? 'selected' : ''; ?>>Chittagong</option>
                            <option value="Rajshahi" <?php echo ($edit_student && $edit_student['board'] == 'Rajshahi') ? 'selected' : ''; ?>>Rajshahi</option>
                            <option value="Comilla" <?php echo ($edit_student && $edit_student['board'] == 'Comilla') ? 'selected' : ''; ?>>Comilla</option>
                            <option value="Jessore" <?php echo ($edit_student && $edit_student['board'] == 'Jessore') ? 'selected' : ''; ?>>Jessore</option>
                            <option value="Barisal" <?php echo ($edit_student && $edit_student['board'] == 'Barisal') ? 'selected' : ''; ?>>Barisal</option>
                            <option value="Sylhet" <?php echo ($edit_student && $edit_student['board'] == 'Sylhet') ? 'selected' : ''; ?>>Sylhet</option>
                            <option value="Dinajpur" <?php echo ($edit_student && $edit_student['board'] == 'Dinajpur') ? 'selected' : ''; ?>>Dinajpur</option>
                            <option value="Madrasah" <?php echo ($edit_student && $edit_student['board'] == 'Madrasah') ? 'selected' : ''; ?>>Madrasah</option>
                            <option value="Technical" <?php echo ($edit_student && $edit_student['board'] == 'Technical') ? 'selected' : ''; ?>>Technical</option>
                            <option value="Other" <?php echo ($edit_student && $edit_student['board'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="other-board-group" style="<?php echo ($edit_student && $edit_student['board'] == 'Other') ? '' : 'display: none;'; ?>">
                        <label for="other_board">Other Board Name</label>
                        <input type="text" id="other_board" name="other_board" value="<?php echo $edit_student ? $edit_student['other_board'] : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="year_of_passing">Year of Passing *</label>
                        <input type="number" id="year_of_passing" name="year_of_passing" min="1990" max="2099" value="<?php echo $edit_student ? $edit_student['year_of_passing'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="institution_name">Institution Name *</label>
                        <input type="text" id="institution_name" name="institution_name" value="<?php echo $edit_student ? $edit_student['institution_name'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="result">Result (GPA) *</label>
                        <input type="number" id="result" name="result" step="0.01" min="1" max="5" value="<?php echo $edit_student ? $edit_student['result'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject_group">Subject/Group *</label>
                        <select id="subject_group" name="subject_group" required>
                            <option value="">Select Group</option>
                            <option value="Science" <?php echo ($edit_student && $edit_student['subject_group'] == 'Science') ? 'selected' : ''; ?>>Science</option>
                            <option value="Commerce" <?php echo ($edit_student && $edit_student['subject_group'] == 'Commerce') ? 'selected' : ''; ?>>Commerce</option>
                            <option value="Arts" <?php echo ($edit_student && $edit_student['subject_group'] == 'Arts') ? 'selected' : ''; ?>>Arts</option>
                            <option value="Other" <?php echo ($edit_student && $edit_student['subject_group'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department at SKST *</label>
                        <select id="department" name="department" required>
                            <option value="">Select Department</option>
                            <option value="CSE" <?php echo ($edit_student && $edit_student['department'] == 'CSE') ? 'selected' : ''; ?>>Computer Science & Engineering</option>
                            <option value="EEE" <?php echo ($edit_student && $edit_student['department'] == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronic Engineering</option>
                            <option value="BBA" <?php echo ($edit_student && $edit_student['department'] == 'BBA') ? 'selected' : ''; ?>>Business Administration</option>
                            <option value="English" <?php echo ($edit_student && $edit_student['department'] == 'English') ? 'selected' : ''; ?>>English</option>
                            <option value="Economics" <?php echo ($edit_student && $edit_student['department'] == 'Economics') ? 'selected' : ''; ?>>Economics</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title">Account Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="text" id="password" name="password" value="<?php echo $edit_student ? $edit_student['password'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="key">Security Key *</label>
                        <input type="text" id="key" name="key" value="<?php echo $edit_student ? $edit_student['key'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_student ? 'Update Record' : 'Add Student'; ?></button>
                    <?php if ($edit_student): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Student Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Students</label>
                        <input type="text" id="search" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="first_name" <?php echo $sort === 'first_name' ? 'selected' : ''; ?>>First Name</option>
                        <option value="last_name" <?php echo $sort === 'last_name' ? 'selected' : ''; ?>>Last Name</option>
                        <option value="department" <?php echo $sort === 'department' ? 'selected' : ''; ?>>Department</option>
                        <option value="submission_date" <?php echo $sort === 'submission_date' ? 'selected' : ''; ?>>Registration Date</option>
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
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Registration Date</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['student_phone']); ?></td>
                                <td><?php echo htmlspecialchars($s['department']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($s['submission_date'])); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student record?');">
                                        <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Toggle form visibility
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("studentForm");
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
        
        // Show/hide other board field based on selection
        document.getElementById('board').addEventListener('change', function() {
            var otherBoardGroup = document.getElementById('other-board-group');
            otherBoardGroup.style.display = this.value === 'Other' ? 'block' : 'none';
        });
        
        // Auto-show form if editing
        <?php if ($edit_student): ?>
            document.getElementById('studentForm').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>