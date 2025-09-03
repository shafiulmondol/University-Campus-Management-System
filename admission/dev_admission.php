<?php
// Database connection
$host = "127.0.0.1";
$dbname = "skst_university";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle actions (delete, edit, add, search, sort)
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'ASC';

// Delete student
if ($action === 'delete' && !empty($id)) {
    $stmt = $pdo->prepare("DELETE FROM student_registration WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ".str_replace("&action=delete&id=$id", "", $_SERVER['REQUEST_URI']));
    exit();
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $guardian_phone = $_POST['guardian_phone'] ?? '';
    $student_phone = $_POST['student_phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
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
    
    if (!empty($id)) {
        // Update existing student
        $stmt = $pdo->prepare("UPDATE student_registration SET 
            first_name=?, last_name=?, father_name=?, mother_name=?, date_of_birth=?, 
            guardian_phone=?, student_phone=?, email=?, password=?, last_exam=?, 
            board=?, other_board=?, year_of_passing=?, institution_name=?, result=?, 
            subject_group=?, gender=?, blood_group=?, nationality=?, religion=?, 
            present_address=?, permanent_address=?, department=?
            WHERE id=?
        ");
        
        $stmt->execute([
            $first_name, $last_name, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $student_phone, $email, $password, $last_exam,
            $board, $other_board, $year_of_passing, $institution_name, $result,
            $subject_group, $gender, $blood_group, $nationality, $religion,
            $present_address, $permanent_address, $department, $id
        ]);
    } else {
        // Insert new student
        $stmt = $pdo->prepare("INSERT INTO student_registration 
            (first_name, last_name, father_name, mother_name, date_of_birth, 
            guardian_phone, student_phone, email, password, last_exam, 
            board, other_board, year_of_passing, institution_name, result, 
            subject_group, gender, blood_group, nationality, religion, 
            present_address, permanent_address, department) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $first_name, $last_name, $father_name, $mother_name, $date_of_birth,
            $guardian_phone, $student_phone, $email, $password, $last_exam,
            $board, $other_board, $year_of_passing, $institution_name, $result,
            $subject_group, $gender, $blood_group, $nationality, $religion,
            $present_address, $permanent_address, $department
        ]);
    }
    
    header("Location: student_management.php");
    exit();
}

// Fetch student data for editing
$edit_student = null;
if ($action === 'edit' && !empty($id)) {
    $stmt = $pdo->prepare("SELECT * FROM student_registration WHERE id = ?");
    $stmt->execute([$id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Build query for fetching students
$query = "SELECT * FROM student_registration";
$params = [];

if (!empty($search)) {
    $query .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_phone LIKE ?";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

$query .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get sort link
function getSortLink($column, $label) {
    global $sort, $order;
    $newOrder = ($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC';
    $icon = ($sort === $column) ? ($order === 'ASC' ? ' ▲' : ' ▼') : '';
    return "<a href='?sort=$column&order=$newOrder&search=" . urlencode($_GET['search'] ?? '') . "'>$label$icon</a>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Management</title>
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
            width: 100%;
            max-width: 1800px;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            flex: 1;
            max-width: 500px;
        }
        
        .search-box input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }
        
        .search-box button {
            padding: 12px 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        .btn {
            padding: 12px 25px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #219653;
        }
        
        .btn i {
            font-size: 14px;
        }
        
        .student-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .student-table th, .student-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .student-table th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .student-table th:hover {
            background: #1a2530;
        }
        
        .student-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .student-table tr:hover {
            background-color: #f1f7ff;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .edit-btn {
            background: #3498db;
            color: white;
        }
        
        .edit-btn:hover {
            background: #2980b9;
        }
        
        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 18px;
            color: #7f8c8d;
        }
        
        @media (max-width: 1200px) {
            .container {
                overflow-x: auto;
            }
            
            .student-table {
                min-width: 1000px;
            }
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-title {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Student Registration Management</h1>
            <p>Developer Admin Panel - SKST University</p>
        </header>
        
        <div class="controls">
            <div class="search-box">
                <form method="GET" action="">
                    <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
            
            <button class="btn" onclick="openModal()">
                <i class="fas fa-plus"></i> Add New Student
            </button>
        </div>
        
        <?php if (count($students) > 0): ?>
        <div class="table-container">
            <table class="student-table">
                <thead>
                    <tr>
                        <th><?php echo getSortLink('id', 'ID'); ?></th>
                        <th><?php echo getSortLink('first_name', 'First Name'); ?></th>
                        <th><?php echo getSortLink('last_name', 'Last Name'); ?></th>
                        <th>Father's Name</th>
                        <th>Mother's Name</th>
                        <th><?php echo getSortLink('date_of_birth', 'DOB'); ?></th>
                        <th>Guardian Phone</th>
                        <th>Student Phone</th>
                        <th>Email</th>
                        <th>Last Exam</th>
                        <th>Board</th>
                        <th>Year of Passing</th>
                        <th>Institution</th>
                        <th>Result</th>
                        <th>Subject Group</th>
                        <th>Gender</th>
                        <th>Blood Group</th>
                        <th>Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['id']; ?></td>
                        <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['father_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['mother_name']); ?></td>
                        <td><?php echo $student['date_of_birth']; ?></td>
                        <td><?php echo htmlspecialchars($student['guardian_phone']); ?></td>
                        <td><?php echo htmlspecialchars($student['student_phone']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['last_exam']); ?></td>
                        <td><?php echo htmlspecialchars($student['board']); ?></td>
                        <td><?php echo $student['year_of_passing']; ?></td>
                        <td><?php echo htmlspecialchars($student['institution_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['result']); ?></td>
                        <td><?php echo htmlspecialchars($student['subject_group']); ?></td>
                        <td><?php echo ucfirst($student['gender']); ?></td>
                        <td><?php echo $student['blood_group']; ?></td>
                        <td><?php echo htmlspecialchars($student['department']); ?></td>
                        <td class="action-buttons">
                            <a href="?action=edit&id=<?php echo $student['id']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $student['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this student?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="no-results">
            <i class="fas fa-user-slash" style="font-size: 48px; margin-bottom: 20px;"></i>
            <p>No students found. <?php if (!empty($search)) echo 'Try a different search term.'; ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Student Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="form-title"><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
            
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $edit_student['id'] ?? ''; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo $edit_student['first_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo $edit_student['last_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="father_name">Father's Name</label>
                        <input type="text" id="father_name" name="father_name" value="<?php echo $edit_student['father_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mother_name">Mother's Name</label>
                        <input type="text" id="mother_name" name="mother_name" value="<?php echo $edit_student['mother_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo $edit_student['date_of_birth'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="guardian_phone">Guardian Phone</label>
                        <input type="text" id="guardian_phone" name="guardian_phone" value="<?php echo $edit_student['guardian_phone'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="student_phone">Student Phone</label>
                        <input type="text" id="student_phone" name="student_phone" value="<?php echo $edit_student['student_phone'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $edit_student['email'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="text" id="password" name="password" value="<?php echo $edit_student['password'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_exam">Last Exam</label>
                        <select id="last_exam" name="last_exam" required>
                            <option value="">Select Exam</option>
                            <option value="SSC" <?php if (($edit_student['last_exam'] ?? '') === 'SSC') echo 'selected'; ?>>SSC</option>
                            <option value="HSC" <?php if (($edit_student['last_exam'] ?? '') === 'HSC') echo 'selected'; ?>>HSC</option>
                            <option value="Diploma" <?php if (($edit_student['last_exam'] ?? '') === 'Diploma') echo 'selected'; ?>>Diploma</option>
                            <option value="Bachelor" <?php if (($edit_student['last_exam'] ?? '') === 'Bachelor') echo 'selected'; ?>>Bachelor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="board">Board</label>
                        <select id="board" name="board" required>
                            <option value="">Select Board</option>
                            <option value="Dhaka" <?php if (($edit_student['board'] ?? '') === 'Dhaka') echo 'selected'; ?>>Dhaka</option>
                            <option value="Rajshahi" <?php if (($edit_student['board'] ?? '') === 'Rajshahi') echo 'selected'; ?>>Rajshahi</option>
                            <option value="Comilla" <?php if (($edit_student['board'] ?? '') === 'Comilla') echo 'selected'; ?>>Comilla</option>
                            <option value="Jessore" <?php if (($edit_student['board'] ?? '') === 'Jessore') echo 'selected'; ?>>Jessore</option>
                            <option value="Chittagong" <?php if (($edit_student['board'] ?? '') === 'Chittagong') echo 'selected'; ?>>Chittagong</option>
                            <option value="Barisal" <?php if (($edit_student['board'] ?? '') === 'Barisal') echo 'selected'; ?>>Barisal</option>
                            <option value="Sylhet" <?php if (($edit_student['board'] ?? '') === 'Sylhet') echo 'selected'; ?>>Sylhet</option>
                            <option value="Dinajpur" <?php if (($edit_student['board'] ?? '') === 'Dinajpur') echo 'selected'; ?>>Dinajpur</option>
                            <option value="Other" <?php if (($edit_student['board'] ?? '') === 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="otherBoardGroup" style="display: none;">
                        <label for="other_board">Other Board</label>
                        <input type="text" id="other_board" name="other_board" value="<?php echo $edit_student['other_board'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="year_of_passing">Year of Passing</label>
                        <input type="number" id="year_of_passing" name="year_of_passing" min="1900" max="2099" value="<?php echo $edit_student['year_of_passing'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="institution_name">Institution Name</label>
                        <input type="text" id="institution_name" name="institution_name" value="<?php echo $edit_student['institution_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="result">Result</label>
                        <input type="text" id="result" name="result" value="<?php echo $edit_student['result'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject_group">Subject Group</label>
                        <select id="subject_group" name="subject_group" required>
                            <option value="">Select Group</option>
                            <option value="science" <?php if (($edit_student['subject_group'] ?? '') === 'science') echo 'selected'; ?>>Science</option>
                            <option value="arts" <?php if (($edit_student['subject_group'] ?? '') === 'arts') echo 'selected'; ?>>Arts</option>
                            <option value="commerce" <?php if (($edit_student['subject_group'] ?? '') === 'commerce') echo 'selected'; ?>>Commerce</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php if (($edit_student['gender'] ?? '') === 'male') echo 'selected'; ?>>Male</option>
                            <option value="female" <?php if (($edit_student['gender'] ?? '') === 'female') echo 'selected'; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="blood_group">Blood Group</label>
                        <select id="blood_group" name="blood_group" required>
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?php if (($edit_student['blood_group'] ?? '') === 'A+') echo 'selected'; ?>>A+</option>
                            <option value="A-" <?php if (($edit_student['blood_group'] ?? '') === 'A-') echo 'selected'; ?>>A-</option>
                            <option value="B+" <?php if (($edit_student['blood_group'] ?? '') === 'B+') echo 'selected'; ?>>B+</option>
                            <option value="B-" <?php if (($edit_student['blood_group'] ?? '') === 'B-') echo 'selected'; ?>>B-</option>
                            <option value="O+" <?php if (($edit_student['blood_group'] ?? '') === 'O+') echo 'selected'; ?>>O+</option>
                            <option value="O-" <?php if (($edit_student['blood_group'] ?? '') === 'O-') echo 'selected'; ?>>O-</option>
                            <option value="AB+" <?php if (($edit_student['blood_group'] ?? '') === 'AB+') echo 'selected'; ?>>AB+</option>
                            <option value="AB-" <?php if (($edit_student['blood_group'] ?? '') === 'AB-') echo 'selected'; ?>>AB-</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo $edit_student['nationality'] ?? 'Bangladeshi'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="religion">Religion</label>
                        <select id="religion" name="religion" required>
                            <option value="">Select Religion</option>
                            <option value="Islam" <?php if (($edit_student['religion'] ?? '') === 'Islam') echo 'selected'; ?>>Islam</option>
                            <option value="Hinduism" <?php if (($edit_student['religion'] ?? '') === 'Hinduism') echo 'selected'; ?>>Hinduism</option>
                            <option value="Christianity" <?php if (($edit_student['religion'] ?? '') === 'Christianity') echo 'selected'; ?>>Christianity</option>
                            <option value="Buddhism" <?php if (($edit_student['religion'] ?? '') === 'Buddhism') echo 'selected'; ?>>Buddhism</option>
                            <option value="Other" <?php if (($edit_student['religion'] ?? '') === 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="present_address">Present Address</label>
                        <textarea id="present_address" name="present_address" required><?php echo $edit_student['present_address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="permanent_address">Permanent Address</label>
                        <textarea id="permanent_address" name="permanent_address" required><?php echo $edit_student['permanent_address'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="<?php echo $edit_student['department'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> <?php echo $edit_student ? 'Update Student' : 'Add Student'; ?>
                    </button>
                    <button type="button" class="btn" style="background: #7f8c8d;" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('studentModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('studentModal').style.display = 'none';
            window.location.href = window.location.pathname;
        }
        
        // Close modal if clicked outside
        window.onclick = function(event) {
            const modal = document.getElementById('studentModal');
            if (event.target === modal) {
                closeModal();
            }
        };
        
        // Show/hide other board field
        document.getElementById('board').addEventListener('change', function() {
            document.getElementById('otherBoardGroup').style.display = this.value === 'Other' ? 'block' : 'none';
        });
        
        // Trigger change event on page load
        window.onload = function() {
            document.getElementById('board').dispatchEvent(new Event('change'));
            
            <?php if ($action === 'edit' && !empty($id)): ?>
                openModal();
            <?php endif; ?>
        };
        
        // Add confirmation for delete actions
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this student?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>