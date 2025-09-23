<?php
session_start();

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

// Initialize variables
$success_message = "";
$error_message = "";
$student_exists = false;
$student_data = null;

// Check if student ID is provided (via GET or POST)
if (isset($_GET['student_id']) || isset($_POST['student_id'])) {
    $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : intval($_POST['student_id']);
    
    // Check if student exists in student_registration table
    $check_stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, department FROM student_registration WHERE id = ?");
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student_exists = true;
        $student_data = $result->fetch_assoc();
    } else {
        $error_message = "Student ID $student_id is not registered in the system. Please register as a student first.";
    }
    $check_stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    if (!$student_exists) {
        $error_message = "Cannot submit application: Student registration not verified.";
    } else {
        // Validate password
        $user_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($user_password) < 6) {
            $error_message = "Password must be at least 6 characters long!";
        } elseif ($user_password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            // Check if student ID already exists in volunteers table
            $check_volunteer_stmt = $mysqli->prepare("SELECT * FROM volunteers WHERE student_id = ?");
            $check_volunteer_stmt->bind_param("i", $student_id);
            $check_volunteer_stmt->execute();
            $volunteer_result = $check_volunteer_stmt->get_result();
            
            if ($volunteer_result->num_rows > 0) {
                $error_message = "This Student ID is already registered as a volunteer!";
                $check_volunteer_stmt->close();
            } else {
                $check_volunteer_stmt->close();
                
                // Prepare and bind parameters for insertion - CORRECTED: removed profile_picture column
                $stmt = $mysqli->prepare("INSERT INTO volunteers (student_id, student_name, department, email, phone, activity_name, activity_date, role, hours, remarks, stratus, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', ?)");
                
                if ($stmt) {
                    // Sanitize and validate input
                    $student_name = $student_data['first_name'] . ' ' . $student_data['last_name'];
                    $department = $student_data['department'];
                    $email = $student_data['email'];
                    $phone = htmlspecialchars($_POST['phone']);
                    $activity_name = htmlspecialchars($_POST['activity_name']);
                    $activity_date = $_POST['activity_date'];
                    $role = htmlspecialchars($_POST['role']);
                    $hours = !empty($_POST['hours']) ? intval($_POST['hours']) : 0;
                    $remarks = htmlspecialchars($_POST['remarks']);
                    
                    // Hash the password for security
                    $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
                    
                    // Bind parameters - CORRECTED: removed profile_picture parameter
                    $stmt->bind_param("isssssssiss", $student_id, $student_name, $department, $email, $phone, $activity_name, $activity_date, $role, $hours, $remarks, $hashed_password);
                    
                    // Execute statement
                    if ($stmt->execute()) {
                        $success_message = "Volunteer registration submitted successfully!";
                        // Reset form
                        $student_exists = true; // Keep student data visible
                    } else {
                        $error_message = "Error: " . $stmt->error;
                    }
                    
                    $stmt->close();
                } else {
                    $error_message = "Error preparing statement: " . $mysqli->error;
                }
            }
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration - SKST University</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        header {
            background: linear-gradient(135deg, maroon, #1a2530);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 12px;
        }
        
        .student-verification {
            background: #e8f4ff;
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .student-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .student-details p {
            margin: 5px 0;
            font-size: 1rem;
        }
        
        .verification-status {
            display: flex;
            align-items: center;
            font-weight: bold;
        }
        
        .verified {
            color: #27ae60;
        }
        
        .not-verified {
            color: #e74c3c;
        }
        
        .verification-form {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .verification-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .verify-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .verify-btn:hover {
            background: #2980b9;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 calc(50% - 20px);
            margin: 0 10px 20px;
        }
        
        .form-group-full {
            flex: 1 0 calc(100% - 20px);
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-reset {
            background: #95a5a6;
        }
        
        .btn-reset:hover {
            background: #7f8c8d;
        }
        
        .btn-submit {
            background: #2ecc71;
        }
        
        .btn-submit:hover {
            background: #27ae60;
        }
        
        .btn-verify {
            background: #f39c12;
        }
        
        .btn-verify:hover {
            background: #e67e22;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .icon {
            margin-right: 8px;
            color: #3498db;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #27ae60; }
        
        .password-match {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #27ae60;
        }
        
        .password-mismatch {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #e74c3c;
        }
        
        .file-upload {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            display: none; /* Hidden since we don't have profile_picture column */
        }
        
        .file-upload:hover {
            border-color: #3498db;
            background-color: #f8f9fa;
        }
        
        .file-upload i {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(100% - 20px);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .student-info {
                flex-direction: column;
                text-align: center;
            }
            
            .verification-form {
                flex-direction: column;
            }
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .info-message {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message i {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .error-message i {
            font-size: 2rem;
            color: #dc3545;
            margin-bottom: 10px;
        }
        
        .form-section {
            display: <?php echo $student_exists ? 'block' : 'none'; ?>;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SKST University Volunteer Program</h1>
            <p class="subtitle">Join us in making a difference through community service</p>
        </header>
        
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-hand-holding-heart icon"></i>Volunteer Registration Form</h2>
            
            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Registration Error</h3>
                    <p><?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3>Thank You for Registering!</h3>
                    <p><?php echo $success_message; ?></p>
                    <p>You can now <a href="volunteer.php">login to the volunteer portal</a>.</p>
                </div>
            <?php endif; ?>
            
            <!-- Student Verification Section -->
            <div class="student-verification">
                <?php if ($student_exists && $student_data): ?>
                    <div class="student-info">
                        <div class="student-details">
                            <h3>Student Verification</h3>
                            <p><strong>Student ID:</strong> <?php echo $student_data['id']; ?></p>
                            <p><strong>Name:</strong> <?php echo $student_data['first_name'] . ' ' . $student_data['last_name']; ?></p>
                            <p><strong>Department:</strong> <?php echo $student_data['department']; ?></p>
                            <p><strong>Email:</strong> <?php echo $student_data['email']; ?></p>
                        </div>
                        <div class="verification-status verified">
                            <i class="fas fa-check-circle"></i> Verified Student
                        </div>
                    </div>
                    <p>You are verified as a registered student. You may proceed with the volunteer application.</p>
                <?php else: ?>
                    <div class="student-info">
                        <div>
                            <h3>Student Verification Required</h3>
                            <p>Please verify your student registration before applying as a volunteer.</p>
                        </div>
                        <div class="verification-status not-verified">
                            <i class="fas fa-times-circle"></i> Not Verified
                        </div>
                    </div>
                    
                    <form method="get" action="" class="verification-form">
                        <input type="text" name="student_id" class="verification-input" placeholder="Enter your Student ID" required value="<?php echo isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : ''; ?>">
                        <button type="submit" class="verify-btn">Verify Student ID</button>
                    </form>
                    
                    <p class="info-message">
                        <i class="fas fa-info-circle"></i> 
                        You must be a registered student to apply as a volunteer. 
                        If you haven't registered yet, please complete your student registration first.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Volunteer Application Form (only show if student is verified) -->
            <form id="volunteerForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" class="form-section">
                <input type="hidden" name="student_id" value="<?php echo $student_data['id'] ?? ''; ?>">
                <input type="hidden" name="submit_application" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="required">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="activity_name" class="required">Activity Name</label>
                        <select id="activity_name" name="activity_name" required>
                            <option value="">Select Activity</option>
                            <option value="Blood Donation Camp" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Blood Donation Camp') ? 'selected' : ''; ?>>Blood Donation Camp</option>
                            <option value="Tree Plantation Drive" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Tree Plantation Drive') ? 'selected' : ''; ?>>Tree Plantation Drive</option>
                            <option value="Campus Clean-up" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Campus Clean-up') ? 'selected' : ''; ?>>Campus Clean-up</option>
                            <option value="Fundraising Event" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Fundraising Event') ? 'selected' : ''; ?>>Fundraising Event</option>
                            <option value="Cultural Festival" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Cultural Festival') ? 'selected' : ''; ?>>Cultural Festival</option>
                            <option value="Student Mentorship" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Student Mentorship') ? 'selected' : ''; ?>>Student Mentorship</option>
                            <option value="Community Outreach" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Community Outreach') ? 'selected' : ''; ?>>Community Outreach</option>
                            <option value="Health Awareness Campaign" <?php echo (isset($_POST['activity_name']) && $_POST['activity_name'] == 'Health Awareness Campaign') ? 'selected' : ''; ?>>Health Awareness Campaign</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="activity_date" class="required">Activity Date</label>
                        <input type="date" id="activity_date" name="activity_date" required value="<?php echo isset($_POST['activity_date']) ? htmlspecialchars($_POST['activity_date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="required">Preferred Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Volunteer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Volunteer') ? 'selected' : ''; ?>>Volunteer</option>
                            <option value="Organizer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Organizer') ? 'selected' : ''; ?>>Organizer</option>
                            <option value="Leader" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Leader') ? 'selected' : ''; ?>>Leader</option>
                            <option value="Coordinator" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                            <option value="Support Staff" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Support Staff') ? 'selected' : ''; ?>>Support Staff</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hours">Expected Hours</label>
                        <input type="number" id="hours" name="hours" min="1" max="50" placeholder="How many hours can you contribute?" value="<?php echo isset($_POST['hours']) ? htmlspecialchars($_POST['hours']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Previous Experience</label>
                        <select id="experience" name="experience">
                            <option value="">Select Experience Level</option>
                            <option value="None" <?php echo (isset($_POST['experience']) && $_POST['experience'] == 'None') ? 'selected' : ''; ?>>None</option>
                            <option value="Beginner" <?php echo (isset($_POST['experience']) && $_POST['experience'] == 'Beginner') ? 'selected' : ''; ?>>Beginner (1-4 events)</option>
                            <option value="Intermediate" <?php echo (isset($_POST['experience']) && $_POST['experience'] == 'Intermediate') ? 'selected' : ''; ?>>Intermediate (5-10 events)</option>
                            <option value="Experienced" <?php echo (isset($_POST['experience']) && $_POST['experience'] == 'Experienced') ? 'selected' : ''; ?>>Experienced (10+ events)</option>
                        </select>
                    </div>
                </div>
                
                <!-- Password Fields -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Create a password">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="required">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                        <div class="password-match" id="passwordMatch"></div>
                    </div>
                </div>
                
                <!-- Profile Picture Upload Section Removed since column doesn't exist -->
                
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="remarks">Remarks / Special Skills</label>
                        <textarea id="remarks" name="remarks" placeholder="Please share any special skills, comments, or preferences..."><?php echo isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane"></i> Submit Application</button>
                    <button type="reset" class="btn btn-reset"><i class="fas fa-redo"></i> Reset Form</button>
                    <button type="button" class="btn btn-reset" onclick="history.back();"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="button" class="btn btn-reset" onclick="window.location.href='../index.html';"><i class="fas fa-home"></i> Home</button>
                </div>
            </form>
            
            <?php if (!$student_exists): ?>
                <div class="info-message">
                    <i class="fas fa-info-circle"></i>
                    <p>The volunteer application form will appear here after successful student verification.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        if (document.getElementById('activity_date')) {
            document.getElementById('activity_date').setAttribute('min', today);
        }
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthElement = document.getElementById('passwordStrength');
                
                if (password.length === 0) {
                    strengthElement.textContent = '';
                    strengthElement.className = 'password-strength';
                    return;
                }
                
                let strength = 0;
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;
                
                let strengthText = '';
                let strengthClass = '';
                
                switch(strength) {
                    case 0:
                    case 1:
                        strengthText = 'Weak';
                        strengthClass = 'strength-weak';
                        break;
                    case 2:
                    case 3:
                        strengthText = 'Medium';
                        strengthClass = 'strength-medium';
                        break;
                    case 4:
                        strengthText = 'Strong';
                        strengthClass = 'strength-strong';
                        break;
                }
                
                strengthElement.textContent = `Password strength: ${strengthText}`;
                strengthElement.className = `password-strength ${strengthClass}`;
            });
        }
        
        // Password confirmation checker
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPassword = this.value;
                const matchElement = document.getElementById('passwordMatch');
                
                if (confirmPassword.length === 0) {
                    matchElement.textContent = '';
                    matchElement.className = 'password-match';
                    return;
                }
                
                if (password === confirmPassword) {
                    matchElement.textContent = '✓ Passwords match';
                    matchElement.className = 'password-match';
                } else {
                    matchElement.textContent = '✗ Passwords do not match';
                    matchElement.className = 'password-mismatch';
                }
            });
        }
        
        // Form validation
        const volunteerForm = document.getElementById('volunteerForm');
        if (volunteerForm) {
            volunteerForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    document.getElementById('password').focus();
                    return;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    document.getElementById('confirm_password').focus();
                    return;
                }
            });
        }
        
        // Add input event listeners to remove error styles when typing
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#ddd';
            });
        });

        // If there was a success message, scroll to it
        <?php if ($success_message): ?>
            document.querySelector('.success-message').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
        
        // Client-side validation for student ID in verification form
        const studentIdInput = document.querySelector('input[name="student_id"]');
        if (studentIdInput) {
            studentIdInput.addEventListener('blur', function() {
                if (this.value && !/^\d+$/.test(this.value)) {
                    this.style.borderColor = '#e74c3c';
                    alert('Student ID should contain only numbers.');
                }
            });
        }
    </script>
</body>
</html>