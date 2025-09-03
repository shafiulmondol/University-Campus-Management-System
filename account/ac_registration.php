<?php
// Database configuration
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "university_portal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    role ENUM('student', 'faculty', 'librarian', 'employee') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Initialize response array
$response = [
    'status' => '',
    'message' => '',
    'errors' => []
];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $userIdPrefix = $_POST['userIdPrefix'] ?? '';
    $userIdSuffix = $_POST['userIdSuffix'] ?? '';
    $userId = $userIdPrefix . $userIdSuffix;
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $terms = isset($_POST['terms']) ? true : false;

    // Validation
    $isValid = true;

    // Validate University ID
    $userIdPatterns = [
        'ST' => '/^ST\d{5}$/i',
        'FAC' => '/^FAC\d{3}$/i',
        'LIB' => '/^LIB\d{3}$/i',
        'EMP' => '/^EMP\d{3,5}$/i'
    ];

    if (empty($userIdPrefix) || empty($userIdSuffix)) {
        $response['errors']['userId'] = "University ID is required";
        $isValid = false;
    } elseif (!array_key_exists($userIdPrefix, $userIdPatterns)) {
        $response['errors']['userId'] = "Invalid ID prefix";
        $isValid = false;
    } elseif (!preg_match($userIdPatterns[$userIdPrefix], $userId)) {
        $response['errors']['userId'] = "Invalid ID format for selected prefix";
        $isValid = false;
    } else {
        // Check if user ID already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $response['errors']['userId'] = "This University ID is already registered";
            $isValid = false;
        }
        $stmt->close();
    }

    // Validate Email
    if (empty($email)) {
        $response['errors']['email'] = "Email is required";
        $isValid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format";
        $isValid = false;
    } elseif (!preg_match('/@skst\.edu$/i', $email)) {
        $response['errors']['email'] = "Must be a valid university email (@skst.edu)";
        $isValid = false;
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $response['errors']['email'] = "This email is already registered";
            $isValid = false;
        }
        $stmt->close();
    }

    // Validate Phone
    if (empty($phone)) {
        $response['errors']['phone'] = "Phone number is required";
        $isValid = false;
    } elseif (!preg_match('/^\d{10}$/', preg_replace('/\D/', '', $phone))) {
        $response['errors']['phone'] = "Please enter a valid 10-digit phone number";
        $isValid = false;
    }

    // Validate Role
    if (empty($role)) {
        $response['errors']['role'] = "Please select your role";
        $isValid = false;
    }

    // Validate Password
    if (empty($password)) {
        $response['errors']['password'] = "Password is required";
        $isValid = false;
    } elseif (strlen($password) < 8) {
        $response['errors']['password'] = "Password must be at least 8 characters";
        $isValid = false;
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $response['errors']['password'] = "Password must contain both letters and numbers";
        $isValid = false;
    } elseif ($password !== $confirmPassword) {
        $response['errors']['confirmPassword'] = "Passwords do not match";
        $isValid = false;
    }

    // Validate Terms
    if (!$terms) {
        $response['errors']['terms'] = "You must agree to the terms and conditions";
        $isValid = false;
    }

    // If all validations pass, insert into database
    if ($isValid) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (user_id, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $userId, $email, $phone, $role, $hashedPassword);
        
        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Registration successful! You will be redirected to login.';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error: ' . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Please correct the errors below.';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$conn->close();
?>