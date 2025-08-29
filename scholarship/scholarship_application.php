<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

// Initialize variables
$successMessage = $errorMessage = "";
$formData = [
    'id' => '', 'name' => '', 'department' => '', 'semester' => '',
    'mobile' => '', 'email' => '', 'sgpa' => '', 'cgpa' => '',
    'prev_cgpa' => '', 'scholarship_percentage' => ''
];

// Create database connection
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql)) {
    $conn->select_db($dbname);
    
    // Create table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Scholarship_application (
        id INT AUTO_INCREMENT PRIMARY KEY,
        application_id VARCHAR(20) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        department ENUM('BBA', 'BSCE', 'BSAg', 'BSME', 'BATHM', 'BSN', 'BCSE', 'BSEEE', 'BA Econ', 'BA Eng') NOT NULL,
        semester INT NOT NULL,
        mobile_number VARCHAR(15) NOT NULL,
        email VARCHAR(100) NOT NULL,
        current_semester_sgpa DECIMAL(3,2) NOT NULL,
        cgpa DECIMAL(3,2) NOT NULL,
        previous_semester_cgpa DECIMAL(3,2) NOT NULL,
        scholarship_percentage DECIMAL(5,2) NOT NULL,
        application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        $errorMessage = "Error creating table: " . $conn->error;
    }
    
    // Check if we need to alter the table to add new columns
    $result = $conn->query("SHOW COLUMNS FROM Scholarship_application LIKE 'previous_semester_cgpa'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE Scholarship_application ADD COLUMN previous_semester_cgpa DECIMAL(3,2) NOT NULL AFTER cgpa");
    }
    
    $result = $conn->query("SHOW COLUMNS FROM Scholarship_application LIKE 'scholarship_percentage'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE Scholarship_application ADD COLUMN scholarship_percentage DECIMAL(5,2) NOT NULL AFTER previous_semester_cgpa");
    }
} else {
    $errorMessage = "Error creating database: " . $conn->error;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $formData = [
        'id' => $_POST['id'] ?? '',
        'name' => $_POST['name'] ?? '',
        'department' => $_POST['department'] ?? '',
        'semester' => $_POST['semester'] ?? '',
        'mobile' => $_POST['mobile'] ?? '',
        'email' => $_POST['email'] ?? '',
        'sgpa' => $_POST['sgpa'] ?? '',
        'cgpa' => $_POST['cgpa'] ?? '',
        'prev_cgpa' => $_POST['prev_cgpa'] ?? '',
        'scholarship_percentage' => $_POST['scholarship_percentage'] ?? ''
    ];
    
    // Validate data
    $isValid = true;
    $errors = [];
    
    if (empty($formData['id'])) {
        $errors['id'] = "Student ID is required";
        $isValid = false;
    }
    
    if (empty($formData['name'])) {
        $errors['name'] = "Name is required";
        $isValid = false;
    }
    
    if (empty($formData['department'])) {
        $errors['department'] = "Department is required";
        $isValid = false;
    }
    
    if (empty($formData['semester']) || $formData['semester'] < 1 || $formData['semester'] > 12) {
        $errors['semester'] = "Valid semester (1-12) is required";
        $isValid = false;
    }
    
    if (empty($formData['mobile']) || !preg_match('/^[0-9]{10,15}$/', $formData['mobile'])) {
        $errors['mobile'] = "Valid mobile number is required";
        $isValid = false;
    }
    
    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Valid email is required";
        $isValid = false;
    }
    
    if (empty($formData['sgpa']) || $formData['sgpa'] < 0 || $formData['sgpa'] > 4) {
        $errors['sgpa'] = "Valid SGPA (0-4) is required";
        $isValid = false;
    }
    
    if (empty($formData['cgpa']) || $formData['cgpa'] < 0 || $formData['cgpa'] > 4) {
        $errors['cgpa'] = "Valid CGPA (0-4) is required";
        $isValid = false;
    }
    
    if (empty($formData['prev_cgpa']) || $formData['prev_cgpa'] < 0 || $formData['prev_cgpa'] > 4) {
        $errors['prev_cgpa'] = "Valid Previous Semester CGPA (0-4) is required";
        $isValid = false;
    }
    
    if (empty($formData['scholarship_percentage']) || $formData['scholarship_percentage'] < 0 || $formData['scholarship_percentage'] > 100) {
        $errors['scholarship_percentage'] = "Valid Scholarship Percentage (0-100) is required";
        $isValid = false;
    }
    
    // If valid, insert into database
    if ($isValid) {
        $stmt = $conn->prepare("INSERT INTO Scholarship_application (application_id, name, department, semester, mobile_number, email, current_semester_sgpa, cgpa, previous_semester_cgpa, scholarship_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissssss", $formData['id'], $formData['name'], $formData['department'], $formData['semester'], $formData['mobile'], $formData['email'], $formData['sgpa'], $formData['cgpa'], $formData['prev_cgpa'], $formData['scholarship_percentage']);
        
        if ($stmt->execute()) {
            $successMessage = "Application submitted successfully!";
            // Reset form data
            $formData = [
                'id' => '', 'name' => '', 'department' => '', 'semester' => '',
                'mobile' => '', 'email' => '', 'sgpa' => '', 'cgpa' => '',
                'prev_cgpa' => '', 'scholarship_percentage' => ''
            ];
        } else {
            if ($stmt->errno == 1062) {
                $errorMessage = "Application with this ID already exists.";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    } else {
        $errorMessage = "Please correct the errors below.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University || Scholarship Application</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {

            width: 100%;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
        }
        
        .university-name {
            color: #2c3e50;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .form-title {
            color: #3498db;
            font-size: 24px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
        }
        
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .submit-btn {
            background: linear-gradient(to right, #2c3e50, #3498db);
            color: white;
            border: none;
            padding: 16px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s, box-shadow 0.3s;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .required {
            color: #e74c3c;
        }
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
        <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
            <img src="../picture/SKST.png" alt="SKST University Logo" 
             style="height: 50px; width: 50px; border-radius: 50%;">
            <h1 class="university-name">SKST University</h1>
        </div>

        <h2 class="form-title">Scholarship Application Form</h2>
        </header>
        
        <?php if (!empty($successMessage)): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="message error-msg"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="id">Student ID <span class="required">*</span></label>
                    <input type="text" id="id" name="id" value="<?php echo htmlspecialchars($formData['id']); ?>" required>
                    <?php if (isset($errors['id'])): ?>
                        <div class="error"><?php echo $errors['id']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($formData['name']); ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="error"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department <span class="required">*</span></label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="BBA" <?php if ($formData['department'] == 'BBA') echo 'selected'; ?>>BBA</option>
                        <option value="BSCE" <?php if ($formData['department'] == 'BSCE') echo 'selected'; ?>>BSCE</option>
                        <option value="BSAg" <?php if ($formData['department'] == 'BSAg') echo 'selected'; ?>>BSAg</option>
                        <option value="BSME" <?php if ($formData['department'] == 'BSME') echo 'selected'; ?>>BSME</option>
                        <option value="BATHM" <?php if ($formData['department'] == 'BATHM') echo 'selected'; ?>>BATHM</option>
                        <option value="BSN" <?php if ($formData['department'] == 'BSN') echo 'selected'; ?>>BSN</option>
                        <option value="BCSE" <?php if ($formData['department'] == 'BCSE') echo 'selected'; ?>>BCSE</option>
                        <option value="BSEEE" <?php if ($formData['department'] == 'BSEEE') echo 'selected'; ?>>BSEEE</option>
                        <option value="BA Econ" <?php if ($formData['department'] == 'BA Econ') echo 'selected'; ?>>BA Econ</option>
                        <option value="BA Eng" <?php if ($formData['department'] == 'BA Eng') echo 'selected'; ?>>BA Eng</option>
                    </select>
                    <?php if (isset($errors['department'])): ?>
                        <div class="error"><?php echo $errors['department']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="semester">Semester <span class="required">*</span></label>
                    <input type="number" id="semester" name="semester" min="1" max="12" value="<?php echo htmlspecialchars($formData['semester']); ?>" required>
                    <?php if (isset($errors['semester'])): ?>
                        <div class="error"><?php echo $errors['semester']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="mobile">Mobile Number <span class="required">*</span></label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($formData['mobile']); ?>" required>
                    <?php if (isset($errors['mobile'])): ?>
                        <div class="error"><?php echo $errors['mobile']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error"><?php echo $errors['email']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sgpa">Current Semester SGPA <span class="required">*</span></label>
                    <input type="number" id="sgpa" name="sgpa" step="0.01" min="0" max="4" value="<?php echo htmlspecialchars($formData['sgpa']); ?>" required>
                    <?php if (isset($errors['sgpa'])): ?>
                        <div class="error"><?php echo $errors['sgpa']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="cgpa">CGPA <span class="required">*</span></label>
                    <input type="number" id="cgpa" name="cgpa" step="0.01" min="0" max="4" value="<?php echo htmlspecialchars($formData['cgpa']); ?>" required>
                    <?php if (isset($errors['cgpa'])): ?>
                        <div class="error"><?php echo $errors['cgpa']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="prev_cgpa">Previous Semester CGPA <span class="required">*</span></label>
                    <input type="number" id="prev_cgpa" name="prev_cgpa" step="0.01" min="0" max="4" value="<?php echo htmlspecialchars($formData['prev_cgpa']); ?>" required>
                    <?php if (isset($errors['prev_cgpa'])): ?>
                        <div class="error"><?php echo $errors['prev_cgpa']; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="scholarship_percentage">Scholarship Percentage <span class="required">*</span></label>
                    <input type="number" id="scholarship_percentage" name="scholarship_percentage" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($formData['scholarship_percentage']); ?>" required>
                    <?php if (isset($errors['scholarship_percentage'])): ?>
                        <div class="error"><?php echo $errors['scholarship_percentage']; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <button type="submit" class="submit-btn">Submit Application</button>
        </form>
    </div>

    <script>
        // Simple client-side validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#e74c3c';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>