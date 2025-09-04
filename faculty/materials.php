<?php
include 'config.php';
checkFacultyLogin();

$faculty_id = $_SESSION['faculty_id'];

// Get courses taught by this faculty
$courses_sql = "SELECT c.course_id, c.course_code, c.course_name 
                FROM course c
                JOIN course_instructor ci ON c.course_id = ci.course_id
                WHERE ci.faculty_id = ?";
$stmt = $mysqli->prepare($courses_sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$courses_result = $stmt->get_result();
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Create materials table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS course_materials (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT(11) NOT NULL,
    course_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) DEFAULT 'document',
    file_size VARCHAR(20) DEFAULT '0 KB',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id),
    FOREIGN KEY (course_id) REFERENCES course(course_id)
)";
$mysqli->query($create_table_sql);

$selected_course = null;
$materials = [];

// If a course is selected, get the materials
if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $course_id = $_GET['course_id'];
    
    // Get course details
    foreach ($courses as $course) {
        if ($course['course_id'] == $course_id) {
            $selected_course = $course;
            break;
        }
    }
    
    // Get materials for this course
    $materials_sql = "SELECT * FROM course_materials 
                      WHERE course_id = ? AND faculty_id = ?
                      ORDER BY upload_date DESC";
    $stmt = $mysqli->prepare($materials_sql);
    $stmt->bind_param("ii", $course_id, $faculty_id);
    $stmt->execute();
    $materials_result = $stmt->get_result();
    $materials = $materials_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_material'])) {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    $uploadDir = 'uploads/course_materials/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['material_file']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $fileSize = $_FILES['material_file']['size'];
    
    // Format file size
    $sizeFormatted = formatFileSize($fileSize);
    
    // Upload file to server
    if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetFilePath)) {
        // Insert record into database
        $insert_sql = "INSERT INTO course_materials (faculty_id, course_id, title, description, file_path, file_type, file_size) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert_sql);
        $stmt->bind_param("iisssss", $faculty_id, $course_id, $title, $description, $targetFilePath, $fileType, $sizeFormatted);
        
        if ($stmt->execute()) {
            $success_message = "Material uploaded successfully!";
            // Refresh the page to show the new material
            header("Location: materials.php?course_id=" . $course_id);
            exit();
        } else {
            $error = "Failed to save material information to database.";
        }
        
        $stmt->close();
    } else {
        $error = "Sorry, there was an error uploading your file.";
    }
}

// Handle file deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $course_id = $_GET['course_id'];
    
    // Get file path first
    $file_sql = "SELECT file_path FROM course_materials WHERE id = ? AND faculty_id = ?";
    $stmt = $mysqli->prepare($file_sql);
    $stmt->bind_param("ii", $delete_id, $faculty_id);
    $stmt->execute();
    $file_result = $stmt->get_result();
    
    if ($file_result->num_rows > 0) {
        $file_data = $file_result->fetch_assoc();
        $file_path = $file_data['file_path'];
        
        // Delete file from server
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Delete record from database
        $delete_sql = "DELETE FROM course_materials WHERE id = ? AND faculty_id = ?";
        $delete_stmt = $mysqli->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $delete_id, $faculty_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        $success_message = "Material deleted successfully!";
        // Refresh the page
        header("Location: materials.php?course_id=" . $course_id);
        exit();
    }
    
    $stmt->close();
}

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f9c74f;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            --hover-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 25px 0;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary);
            font-size: 18px;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            background: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 15px;
            gap: 12px;
        }
        
        .sidebar-menu a:hover, .sidebar-menu button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 30px;
        }
        
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left: 4px solid var(--success);
        }
        
        .sidebar-menu i {
            width: 20px;
            font-size: 16px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Top Navigation */
        .top-navbar {
            background: var(--white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-buttons {
            display: flex;
            gap: 12px;
        }
        
        .nav-buttons button {
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            background: var(--light);
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-buttons button:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }
        
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }
        
        .card-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group select, .form-group input, .form-group textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--light-gray);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: var(--white);
        }
        
        .form-group select:focus, .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #d90429);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(249, 65, 68, 0.3);
        }
        
        /* Materials Grid */
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        
        .material-card {
            background: var(--white);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .material-card:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-3px);
        }
        
        .material-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .material-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(67, 97, 238, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 20px;
        }
        
        .material-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }
        
        .material-description {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .material-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--light-gray);
        }
        
        .material-date {
            font-size: 13px;
            color: var(--gray);
        }
        
        .material-size {
            font-size: 13px;
            color: var(--gray);
            background: var(--light);
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .action-btn {
            background: var(--light);
            border: none;
            border-radius: 8px;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary);
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .action-btn.delete {
            color: var(--danger);
        }
        
        .action-btn.delete:hover {
            background: var(--danger);
            color: white;
        }
        
        /* Upload Form */
        .upload-form {
            background: var(--light);
            padding: 25px;
            border-radius: 12px;
            margin-top: 25px;
        }
        
        .upload-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: var(--light-gray);
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .message.success {
            background: rgba(76, 201, 240, 0.15);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .message.error {
            background: rgba(249, 65, 68, 0.15);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header, .sidebar-menu span {
                display: none;
            }
            
            .sidebar-menu a, .sidebar-menu button {
                justify-content: center;
                padding: 15px;
            }
            
            .sidebar-menu a:hover, .sidebar-menu button:hover {
                padding-left: 15px;
            }
        }
        
        @media (max-width: 768px) {
            .top-navbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .nav-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .content-area {
                padding: 20px;
            }
            
            .materials-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">SKST</div>
                <div class="logo-text">SKST Faculty</div>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="faculty1.php"><i class="fas fa-user"></i> <span>Profile</span></a></li>
            <li><a href="courses.php"><i class="fas fa-book"></i> <span>Courses</span></a></li>
            <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a></li>
            <li><a href="students.php"><i class="fas fa-users"></i> <span>Students</span></a></li>
            <li><a href="attendance.php"><i class="fas fa-user-check"></i> <span>Attendance</span></a></li>
            <li><a href="materials.php" class="active"><i class="fas fa-file-alt"></i> <span>Materials</span></a></li>
            <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></button></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <div class="top-navbar">
            <h1 class="page-title"><i class="fas fa-file-alt"></i> Course Materials</h1>
            <div class="nav-buttons">
                <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
                <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
                <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <?php if (isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Course Selection Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-book"></i> Select Course to Manage Materials</h2>
                </div>
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="course">Course:</label>
                        <select name="course_id" id="course" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['course_id']; ?>" 
                                    <?php if (isset($_GET['course_id']) && $_GET['course_id'] == $course['course_id']) echo 'selected'; ?>>
                                    <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary"><i class="fas fa-folder-open"></i> Load Materials</button>
                </form>
            </div>
            
            <?php if ($selected_course): ?>
            <!-- Materials Management Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-file-alt"></i> Materials for <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?></h2>
                    <p>Total Materials: <span class="badge"><?php echo count($materials); ?></span></p>
                </div>
                
                <!-- Upload Form -->
                <div class="upload-form">
                    <h3 class="upload-title"><i class="fas fa-cloud-upload-alt"></i> Upload New Material</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="course_id" value="<?php echo $selected_course['course_id']; ?>">
                        
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" name="title" id="title" required placeholder="Enter material title">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" id="description" placeholder="Enter material description (optional)"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="material_file">File:</label>
                            <input type="file" name="material_file" id="material_file" required>
                        </div>
                        
                        <button type="submit" name="upload_material" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload Material
                        </button>
                    </form>
                </div>
                
                <!-- Materials List -->
                <div class="materials-list">
                    <h3 class="card-title" style="margin-top: 30px;"><i class="fas fa-file-download"></i> Available Materials</h3>
                    
                    <?php if (count($materials) > 0): ?>
                        <div class="materials-grid">
                            <?php foreach ($materials as $material): 
                                $file_ext = pathinfo($material['file_path'], PATHINFO_EXTENSION);
                                $file_icon = "fa-file";
                                
                                if (in_array($file_ext, ['pdf'])) {
                                    $file_icon = "fa-file-pdf";
                                } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                    $file_icon = "fa-file-word";
                                } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
                                    $file_icon = "fa-file-excel";
                                } elseif (in_array($file_ext, ['ppt', 'pptx'])) {
                                    $file_icon = "fa-file-powerpoint";
                                } elseif (in_array($file_ext, ['zip', 'rar', '7z'])) {
                                    $file_icon = "fa-file-archive";
                                } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                                    $file_icon = "fa-file-image";
                                } elseif (in_array($file_ext, ['mp4', 'mov', 'avi', 'wmv'])) {
                                    $file_icon = "fa-file-video";
                                } elseif (in_array($file_ext, ['mp3', 'wav', 'ogg'])) {
                                    $file_icon = "fa-file-audio";
                                }
                            ?>
                            <div class="material-card">
                                <div class="material-header">
                                    <div class="material-icon">
                                        <i class="fas <?php echo $file_icon; ?>"></i>
                                    </div>
                                </div>
                                
                                <h3 class="material-title"><?php echo $material['title']; ?></h3>
                                
                                <?php if (!empty($material['description'])): ?>
                                    <p class="material-description"><?php echo $material['description']; ?></p>
                                <?php endif; ?>
                                
                                <div class="material-meta">
                                    <div class="material-date">
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php echo date('M j, Y', strtotime($material['upload_date'])); ?>
                                    </div>
                                    <div class="material-size">
                                        <i class="fas fa-weight-hanging"></i> 
                                        <?php echo $material['file_size']; ?>
                                    </div>
                                </div>
                                
                                <div class="material-actions">
                                    <a href="<?php echo $material['file_path']; ?>" class="action-btn" download title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="materials.php?delete_id=<?php echo $material['id']; ?>&course_id=<?php echo $selected_course['course_id']; ?>" 
                                       class="action-btn delete" title="Delete" onclick="return confirm('Are you sure you want to delete this material?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-excel"></i>
                            <h3>No Materials Available</h3>
                            <p>There are no materials uploaded for this course yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Simple file type validation
        document.getElementById('material_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = file.size;
                const maxSize = 10 * 1024 * 1024; // 10MB
                
                if (fileSize > maxSize) {
                    alert('File size exceeds 10MB. Please choose a smaller file.');
                    e.target.value = '';
                }
            }
        });
    </script>
</body>
</html>