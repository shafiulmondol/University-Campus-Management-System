<?php
// Database connection
$host = 'localhost';
$dbname = 'skst_university';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_type = $_POST['user_type'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Validate user exists in the appropriate table
    if (validateUser($pdo, $user_type, $user_id, $email)) {
        // Check if user already has a library membership
        if (!isLibraryMember($pdo, $user_type, $user_id)) {
            // Create library membership
            if (createLibraryMember($pdo, $user_type, $user_id)) {
                $message = "Library membership application submitted successfully!";
            } else {
                $error = "Error creating library membership. Please try again.";
            }
        } else {
            $message = "You already have a library membership!";
        }
    } else {
        $error = "User validation failed. Please check your details.";
    }
}

// Function to validate user exists in the appropriate table
function validateUser($pdo, $user_type, $user_id, $email) {
    $table = '';
    $id_field = '';
    
    switch ($user_type) {
        case 'student':
            $table = 'student_registration';
            $id_field = 'id';
            break;
        case 'faculty':
            $table = 'faculty';
            $id_field = 'faculty_id';
            break;
        case 'staff':
            $table = 'stuf';
            $id_field = 'id';
            break;
        case 'alumni':
            $table = 'alumni';
            $id_field = 'alumni_id';
            break;
        case 'bank_officer':
            $table = 'bank_officers';
            $id_field = 'officer_id';
            break;
        case 'admin':
            $table = 'admin_users';
            $id_field = 'id';
            break;
        default:
            return false;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE $id_field = ? AND email = ?");
    $stmt->execute([$user_id, $email]);
    return $stmt->rowCount() > 0;
}

// Function to check if user is already a library member
function isLibraryMember($pdo, $user_type, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM library_members WHERE user_type = ? AND user_id = ?");
    $stmt->execute([$user_type, $user_id]);
    return $stmt->rowCount() > 0;
}

// Function to create a new library member
function createLibraryMember($pdo, $user_type, $user_id) {
    // Get user details based on type
    $user_details = getUserDetails($pdo, $user_type, $user_id);
    
    if (!$user_details) return false;
    
    $stmt = $pdo->prepare("INSERT INTO library_members 
                          (user_type, user_id, full_name, email, department, membership_start) 
                          VALUES (?, ?, ?, ?, ?, CURDATE())");
    
    return $stmt->execute([
        $user_type, 
        $user_id, 
        $user_details['name'], 
        $user_details['email'], 
        $user_details['department'] ?? null
    ]);
}

// Function to get user details based on type
function getUserDetails($pdo, $user_type, $user_id) {
    $table = '';
    $id_field = '';
    $name_field = '';
    
    switch ($user_type) {
        case 'student':
            $table = 'student_registration';
            $id_field = 'id';
            $name_field = 'CONCAT(first_name, " ", last_name) as name';
            break;
        case 'faculty':
            $table = 'faculty';
            $id_field = 'faculty_id';
            $name_field = 'name';
            break;
        case 'staff':
            $table = 'stuf';
            $id_field = 'id';
            $name_field = 'CONCAT(first_name, " ", last_name) as name';
            break;
        case 'alumni':
            $table = 'alumni';
            $id_field = 'alumni_id';
            $name_field = 'name';
            break;
        case 'bank_officer':
            $table = 'bank_officers';
            $id_field = 'officer_id';
            $name_field = 'name';
            break;
        case 'admin':
            $table = 'admin_users';
            $id_field = 'id';
            $name_field = 'full_name as name';
            break;
        default:
            return false;
    }
    
    $stmt = $pdo->prepare("SELECT $name_field, email, department FROM $table WHERE $id_field = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Member Card Application - SKST University</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #1a5fb4 0%, #3584e4 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .university-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .library-name {
            font-size: 20px;
            font-weight: 300;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        h2 {
            color: #1a5fb4;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e6e9ef;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d4d8e1;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        select:focus, input:focus {
            border-color: #1a5fb4;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 95, 180, 0.2);
        }
        
        button {
            background: #1a5fb4;
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        button:hover {
            background: #3584e4;
        }
        
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
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
        
        .info-box {
            background-color: #e8f4fd;
            border-left: 4px solid #1a5fb4;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            color: #1a5fb4;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            padding-left: 20px;
        }
        
        footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="university-name">SKST University</div>
            <div class="library-name">Library Member Card Application</div>
        </header>
        
        <div class="card">
            <h2>Apply for Library Membership</h2>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>Membership Benefits</h3>
                <ul>
                    <li>Borrow up to 3 books at a time</li>
                    <li>Access to digital resources</li>
                    <li>Study room reservations</li>
                    <li>Extended borrowing periods for faculty</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_type">I am a:</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="student">Student</option>
                        <option value="faculty">Faculty Member</option>
                        <option value="staff">Staff Member</option>
                        <option value="alumni">Alumni</option>
                        <option value="bank_officer">Bank Officer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <input type="text" id="user_id" name="user_id" required placeholder="Enter your user ID">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your registered email">
                </div>
                
                <button type="submit">Apply for Library Membership</button>
            </form>
        </div>
        
        <footer>
            <p>&copy; 2025 SKST University Library. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>