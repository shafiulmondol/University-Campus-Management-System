<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "skst_university");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set default timezone
date_default_timezone_set('Asia/Dhaka');

// Handle GET requests for notices
 if (isset($_GET['get_notices'])) {
    $query = "SELECT * FROM notice ORDER BY created_at DESC";
    $result = mysqli_query($con, $query);
    
    $notices = [];
    while ($row = mysqli_fetch_assoc($result)) {
       
            $id = $row['id'];
            $title = $row['title'];
            $section = $row['section'];
            $content = $row['content'];
            $author = $row['author'];
            $created_at = date('F j, Y h:i A', strtotime($row['created_at']));
    
    }
    
    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode($notices);
    exit;
 }

// Handle POST requests to add notices
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $section = mysqli_real_escape_string($con, $_POST['section']);
    $content = mysqli_real_escape_string($con, $_POST['content']);
    $author = mysqli_real_escape_string($con, $_POST['author']);
    
    $stmt = $con->prepare("INSERT INTO notice (title, section, content, author) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $section, $content, $author);
    
    if ($stmt->execute()) {
        echo "Notice added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit;
}

// Create table if not exists (initial setup)
$create_table = "CREATE TABLE IF NOT EXISTS notice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    section VARCHAR(100),
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

mysqli_query($con, $create_table);

// Close connection
mysqli_close($con);
?>