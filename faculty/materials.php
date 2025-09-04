
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
    
    // Upload file to server
    if (move_uploaded_file($_FILES['material_file']['tmp_name'], $targetFilePath)) {
        // Insert record into database
        $insert_sql = "INSERT INTO course_materials (faculty_id, course_id, title, description, file_path) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($insert_sql);
        $stmt->bind_param("iisss", $faculty_id, $course_id, $title, $description, $targetFilePath);
        
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

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Materials - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse the CSS from faculty1.php with some additions */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .navbar {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        /* ... (other styles from faculty1.php) ... */
        
        .materials-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .form-group select, .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .materials-list {
            margin-top: 30px;
        }
        
        .material-item {
            background: #f8faff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .material-info h3 {
            margin: 0 0 5px 0;
            color: #2b5876;
        }
        
        .material-info p {
            margin: 0;
            color: #666;
        }
        
        .material-date {
            font-size: 14px;
            color: #888;
        }
        
        .material-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            background: #f0f5ff;
            border: none;
            border-radius: 5px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #2b5876;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: #2b5876;
            color: white;
        }
        
        .upload-form {
            background: #f8faff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center;">
                <span style="font-weight: bold; color: #2b5876;">SKST</span>
            </div>
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
            <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
            <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="faculty1.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-user-check"></i> Attendance</a></li>
                <li><a href="materials.php" class="active"><i class="fas fa-file-alt"></i> Materials</a></li>
                <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
            </ul>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-file-alt"></i> Course Materials</h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="materials-container">
                <h2>Select Course to Manage Materials</h2>
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
                    
                    <button type="submit" class="btn-upload">Load Materials</button>
                </form>
            </div>
            
            <?php if ($selected_course): ?>
            <div class="materials-container">
                <h2>Materials for <?php echo $selected_course['course_code'] . ' - ' . $selected_course['course_name']; ?></h2>
                
                <div class="upload-form">
                    <h3>Upload New Material</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="course_id" value="<?php echo $selected_course['course_id']; ?>">
                        
                        <div class="form-group">
                            <label for="title">Title:</label>
                            <input type="text" name="title" id="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea name="description" id="description"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="material_file">File:</label>
                            <input type="file" name="material_file" id="material_file" required>
                        </div>
                        
                        <button type="submit" name="upload_material" class="btn-upload">
                            <i class="fas fa-upload"></i> Upload Material
                        </button>
                    </form>
                </div>
                
                <div class="materials-list">
                    <h3>Existing Materials</h3>
                    
                    <?php if (count($materials) > 0): ?>
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
                            } elseif (in_array($file_ext, ['zip', 'rar'])) {
                                $file_icon = "fa-file-archive";
                            } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $file_icon = "fa-file-image";
                            }
                        ?>
                        <div class="material-item">
                            <div class="material-info">
                                <h3><i class="fas <?php echo $file_icon; ?>"></i> <?php echo $material['title']; ?></h3>
                                <p><?php echo $material['description']; ?></p>
                                <div class="material-date">
                                    Uploaded on <?php echo date('F j, Y', strtotime($material['upload_date'])); ?>
                                </div>
                            </div>
                            
                            <div class="material-actions">
                                <a href="<?php echo $material['file_path']; ?>" class="action-btn" download title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="materials.php?delete_id=<?php echo $material['id']; ?>&course_id=<?php echo $selected_course['course_id']; ?>" 
                                   class="action-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this material?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No materials uploaded for this course yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>