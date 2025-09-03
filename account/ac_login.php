<?php
// Database configuration
$host = 'localhost';
$dbname = 'university_portal';
$username = 'root';
$password = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create users table if it doesn't exist
$createTableSQL = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    user_id VARCHAR(20) UNIQUE NOT NULL,
    role ENUM('student', 'faculty', 'librarian', 'employee') NOT NULL,
    program VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    $pdo->exec($createTableSQL);
} catch (PDOException $e) {
    die("Table creation failed: " . $e->getMessage());
}

// Handle form submissions
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['formType']) && $_POST['formType'] === 'login') {
        // Login processing
        $email = filter_var($_POST['loginEmail'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['loginPassword'];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@skst.edu')) {
            $response['message'] = 'Please enter a valid university email';
        } elseif (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $response['success'] = true;
                    $response['message'] = 'Login successful! Redirecting to your dashboard...';
                    // In a real application, you would start a session here
                } else {
                    $response['message'] = 'Invalid email or password';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['formType']) && $_POST['formType'] === 'signup') {
        // Signup processing
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $userId = $_POST['userId'];
        $role = $_POST['role'];
        $program = $_POST['program'];
        
        // Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@skst.edu')) {
            $response['message'] = 'Please enter a valid university email ending with @skst.edu';
        } elseif (strlen($password) < 8) {
            $response['message'] = 'Password must be at least 8 characters';
        } elseif (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone)) {
            $response['message'] = 'Please enter a valid phone number';
        } elseif (!validateUserId($userId, $role)) {
            $response['message'] = 'Please enter a valid university ID';
        } elseif (empty($role)) {
            $response['message'] = 'Please select your role';
        } elseif (empty($program)) {
            $response['message'] = 'Please select your program/department';
        } else {
            try {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR user_id = ?");
                $stmt->execute([$email, $userId]);
                
                if ($stmt->rowCount() > 0) {
                    $response['message'] = 'Email or User ID already exists';
                } else {
                    // Hash password and insert user
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (email, password, phone, user_id, role, program) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$email, $hashedPassword, $phone, $userId, $role, $program]);
                    
                    $response['success'] = true;
                    $response['message'] = 'Account created successfully! You can now login.';
                }
            } catch (PDOException $e) {
                $response['message'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

// Validation functions
function validateUserId($userId, $role) {
    if (empty($userId)) return false;
    
    $patterns = [
        'student' => '/^ST\d{5}$/',
        'faculty' => '/^FAC\d{3}$/',
        'librarian' => '/^LIB\d{3}$/',
        'employee' => '/^EMP\d{3}$/'
    ];
    
    // If role is selected, validate against the pattern
    if ($role && isset($patterns[$role])) {
        return preg_match($patterns[$role], $userId);
    }
    
    // If no role selected, check if it matches any pattern
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $userId)) {
            return true;
        }
    }
    
    return false;
}
?>