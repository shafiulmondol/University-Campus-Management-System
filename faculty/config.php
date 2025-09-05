
<?php
// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'skst_university');

// Create connection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Function to check if faculty is logged in
function checkFacultyLogin() {
    if (!isset($_SESSION['faculty_id'])) {
        header("Location: faculty1.php");
        exit();
    }
}

// Get faculty information
function getFacultyInfo($faculty_id, $mysqli) {
    $sql = "SELECT * FROM faculty WHERE faculty_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();

}

?>