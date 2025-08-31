<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'skst_university');

// Check if student is logged in
if (!isset($_SESSION['student_data'])) {
    header("Location: studentlogin.php");
    exit();
} 

// Get the student data from session
$stdata = $_SESSION['student_data'];

// Extract values for easier access
$id = $stdata['id'] ?? '';
$full_name = $stdata['full_name'] ?? '';
$email = $stdata['email'] ?? '';
$phone = $stdata['student_phone'] ?? '';
$father_name = $stdata['father_name'] ?? '';
$mother_name = $stdata['mother_name'] ?? '';
$guardian_phone = $stdata['guardian_phone'] ?? '';
$student_phone = $stdata['student_phone'] ?? '';
$password = $stdata['password'] ?? '';
$last_exam = $stdata['last_exam'] ?? '';
$board = $stdata['board'] ?? '';
$other_board = $stdata['other_board'] ?? '';
$year_of_passing = $stdata['year_of_passing'] ?? '';
$institution_name = $stdata['institution_name'] ?? '';
$result = $stdata['result'] ?? '';
$subject_group = $stdata['subject_group'] ?? '';
$gender = $stdata['gender'] ?? '';
$nationality = $stdata['nationality'] ?? '';
$religion = $stdata['religion'] ?? '';
$present_address = $stdata['present_address'] ?? '';
$permanent_address = $stdata['permanent_address'] ?? '';
$department = $stdata['department'] ?? '';
$submission_date = $stdata['submission_date'] ?? '';
$date_of_birth = $stdata['date_of_birth'] ?? '';
$student_key = $stdata['student_key'] ?? '';
$role = $stdata['role'] ?? 'Student';
$login_time = $stdata['login_time'] ?? '';

// Initialize variables
$show_request_form = false;
$errors = [];
$success = '';

// Handle navigation
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'show_request_form') {
        $show_request_form = true;
    } elseif ($_GET['action'] === 'show_biodata') {
        $show_request_form = false;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editreq'])) {
        // Show the request form
        $show_request_form = true;
    } elseif (isset($_POST['submit_request'])) {
        // Process the request form submission
        $show_request_form = true;
        
        // Validate admin email
        $admin_email = mysqli_real_escape_string($conn, $_POST['admin_email']);
        if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid admin email format.";
        }
        
        // Validate update type
        $update_type = mysqli_real_escape_string($conn, $_POST['change_type']);
        if (!in_array($update_type, ['password', 'email'])) {
            $errors[] = "Invalid update type selected.";
        }
        
        // Validate current value
        $current_value = mysqli_real_escape_string($conn, $_POST['current_value']);
        if ($update_type === 'email') {
            if ($current_value !== $email) {
                $errors[] = "Current email does not match your account email.";
            }
        }
        
        // Validate new value
        $new_value = mysqli_real_escape_string($conn, $_POST['new_value']);
        if ($update_type === 'email') {
            if (!filter_var($new_value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid new email format.";
            }
        }
        
        // Validate comments
        $comments = mysqli_real_escape_string($conn, $_POST['reason']);
        if (empty($comments)) {
            $errors[] = "Please provide a reason for the change.";
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            // Set action to 0 (Pending) and current timestamp
            $action = 0;
            $request_time = date('Y-m-d H:i:s');
            
            // Fixed query to match your database structure
            $query = "INSERT INTO update_requests (admin_email, update_type, current_value, new_value, comments, request_time, action) 
                      VALUES ('$admin_email', '$update_type', '$current_value', '$new_value', '$comments', '$request_time', '$action')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Your update request has been submitted successfully.";
                $show_request_form = false;
            } else {
                $errors[] = "Error submitting request: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch admin emails for dropdown
$admin_emails = [];
$admin_query = "SELECT email FROM admin_users";
$admin_result = mysqli_query($conn, $admin_query);
if ($admin_result && mysqli_num_rows($admin_result) > 0) {
    while ($row = mysqli_fetch_assoc($admin_result)) {
        $admin_emails[] = $row['email'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="../Design/buttom_bar.css">
    <link rel="stylesheet" href="../admin/admin.css">
    <link rel="stylesheet" href="student.css">
    <style>
        /* Additional styles for better form presentation */
     
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/logo.gif" alt="SKST Logo">
            <h1><i class="fas fa-university"></i> SKST University || Student Portal</h1>
        </div>
       
        <div class="nav-buttons">
            <button><i class="fas fa-home"></i><a style="text-decoration: none;color:aliceblue" href="student.html">Home</a></button>
            <button><i class="fas fa-bell"></i> Notifications</button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <form method="post" style="display: contents;">
                    <li><button type="submit" name="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</button></li>
                    <li><button type="submit" name="biodata"><i class="fas fa-id-card"></i> Biodata</button></li>
                    <li><button type="submit" name="result"><i class="fas fa-poll"></i> Result</button></li>
                    <li><button type="submit" name="course"><i class="fas fa-book-open"></i> Courses</button></li>
                    <li><button type="submit" name="account"><i class="fas fa-exchange-alt"></i> Transaction</button></li>
                </form>
                <li><a href="studentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main content -->
        <?php if (isset($_POST['biodata']) || $show_request_form || isset($_GET['action'])): ?>
            <?php if ($show_request_form): ?>
                <!-- Request Form -->
                <div class="content-area">
                    <div class="page-header">
                        <h2 class="page-title"><i class="fas fa-edit"></i> Request Biodata Update</h2>
                        <a href="?action=show_biodata" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Biodata</a>
                    </div>
                    
                    <div class="request-form-container">
                        <?php if (!empty($errors)): ?>
                            <div class="error-message">
                                <h3><i class="fas fa-exclamation-circle"></i> Errors:</h3>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="success-message">
                                <h3><i class="fas fa-check-circle"></i> Success!</h3>
                                <p><?php echo $success; ?></p>
                                <p><a href="?action=show_biodata" class="btn-cancel">Return to Biodata</a></p>
                            </div>
                        <?php else: ?>
                            <form method="post" class="request-form">
                                <div class="form-group">
                                    <label for="admin_email">Admin Email *</label>
                                    <select id="admin_email" name="admin_email" required>
                                        <option value="">Select Admin Email</option>
                                        <?php foreach ($admin_emails as $admin_email_option): ?>
                                            <option value="<?php echo $admin_email_option; ?>" <?php if (isset($admin_email) && $admin_email === $admin_email_option) echo 'selected'; ?>>
                                                <?php echo $admin_email_option; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="change_type">Update Type *</label>
                                    <select id="change_type" name="change_type" required>
                                        <option value="">Select Update Type</option>
                                        <option value="password" <?php if (isset($update_type) && $update_type === 'password') echo 'selected'; ?>>Change Password</option>
                                        <option value="email" <?php if (isset($update_type) && $update_type === 'email') echo 'selected'; ?>>Change Email</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="current_value">Current Value *</label>
                                    <input type="text" id="current_value" name="current_value" value="<?php echo isset($current_value) ? $current_value : ''; ?>" required>
                                    <small>Enter your current email or password depending on what you're changing</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_value">New Value *</label>
                                    <input type="text" id="new_value" name="new_value" value="<?php echo isset($new_value) ? $new_value : ''; ?>" required>
                                    <small>Enter your new email or password</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reason">Reason for Change *</label>
                                    <textarea id="reason" name="reason" rows="4" required><?php echo isset($comments) ? $comments : ''; ?></textarea>
                                </div>
                                
                                <div class="form-buttons">
                                    <button type="submit" name="submit_request" class="btn-submit">Submit Request</button>
                                    <a href="?action=show_biodata" class="btn-cancel">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Student Profile Section -->
                <div class="content-area">
                    <div class="page-header">
                        <h2 class="page-title"><i class="fas fa-user-circle"></i> Student Profile</h2>
                        <form action="" method="post">
                            <button type="submit" name="editreq" class="btn-edit"><i class="fas fa-edit"></i> Request Biodata Update</button>
                        </form>
                    </div>

                    <!-- Profile Card -->
                    <div class="profile-card">
                        <img src="../picture/profilepicture.png" alt="Student" class="profile-img">
                        <div class="profile-info">
                            <h2><?= htmlspecialchars($full_name) ?></h2>
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></p>
                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($student_phone) ?></p>
                            <p><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($role) ?></p>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="info-cards">
                        <!-- Personal Information -->
                        <div class="detail-card">
                            <h3><i class="fas fa-id-card"></i> Personal Information</h3>
                            <div class="info-group"><div class="info-label">Student ID</div><div class="info-value"><?= $id ?></div></div>
                            <div class="info-group"><div class="info-label">Full Name</div><div class="info-value"><?= htmlspecialchars($full_name) ?></div></div>
                            <div class="info-group"><div class="info-label">Father's Name</div><div class="info-value"><?= htmlspecialchars($father_name) ?></div></div>
                            <div class="info-group"><div class="info-label">Mother's Name</div><div class="info-value"><?= htmlspecialchars($mother_name) ?></div></div>
                            <div class="info-group"><div class="info-label">Gender</div><div class="info-value"><?= htmlspecialchars($gender) ?></div></div>
                            <div class="info-group"><div class="info-label">Date of Birth</div><div class="info-value"><?= htmlspecialchars($date_of_birth) ?></div></div>
                            <div class="info-group"><div class="info-label">Nationality</div><div class="info-value"><?= htmlspecialchars($nationality) ?></div></div>
                            <div class="info-group"><div class="info-label">Religion</div><div class="info-value"><?= htmlspecialchars($religion) ?></div></div>
                        </div>

                        <!-- Contact & Address -->
                        <div class="detail-card">
                            <h3><i class="fas fa-address-book"></i> Contact & Address</h3>
                            <div class="info-group"><div class="info-label">Student Phone</div><div class="info-value"><?= htmlspecialchars($student_phone) ?></div></div>
                            <div class="info-group"><div class="info-label">Guardian Phone</div><div class="info-value"><?= htmlspecialchars($guardian_phone) ?></div></div>
                            <div class="info-group"><div class="info-label">Present Address</div><div class="info-value"><?= htmlspecialchars($present_address) ?></div></div>
                            <div class="info-group"><div class="info-label">Permanent Address</div><div class="info-value"><?= htmlspecialchars($permanent_address) ?></div></div>
                        </div>

                        <!-- Academic Information -->
                        <div class="detail-card">
                            <h3><i class="fas fa-book-open"></i> Academic Information</h3>
                            <div class="info-group"><div class="info-label">Last Exam</div><div class="info-value"><?= htmlspecialchars($last_exam) ?></div></div>
                            <div class="info-group"><div class="info-label">Board</div><div class="info-value"><?= htmlspecialchars($board ?: $other_board) ?></div></div>
                            <div class="info-group"><div class="info-label">Year of Passing</div><div class="info-value"><?= htmlspecialchars($year_of_passing) ?></div></div>
                            <div class="info-group"><div class="info-label">Institution</div><div class="info-value"><?= htmlspecialchars($institution_name) ?></div></div>
                            <div class="info-group"><div class="info-label">Result</div><div class="info-value"><?= htmlspecialchars($result) ?></div></div>
                            <div class="info-group"><div class="info-label">Subject Group</div><div class="info-value"><?= htmlspecialchars($subject_group) ?></div></div>
                            <div class="info-group"><div class="info-label">Department</div><div class="info-value"><?= htmlspecialchars($department) ?></div></div>
                            <div class="info-group"><div class="info-label">Submission Date</div><div class="info-value"><?= htmlspecialchars($submission_date) ?></div></div>
                        </div>

                        <!-- Security Information -->
                        <div class="detail-card">
                            <h3><i class="fas fa-shield-alt"></i> Security Information</h3>
                            <div class="info-group"><div class="info-label">Student Key</div><div class="info-value"><?= htmlspecialchars($student_key) ?></div></div>
                            <div class="info-group"><div class="info-label">Last Login</div><div class="info-value"><?= htmlspecialchars($login_time) ?></div></div>
                            <div class="info-group"><div class="info-label">Account Status</div><div class="info-value"><span style="color: #28a745;">Active</span></div></div>
                            <div class="info-group"><div class="info-label">Two-Factor Authentication</div><div class="info-value">Enabled</div></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Default Dashboard View -->
            <div class="content-area">
                <div class="page-header">
                    <h2 class="page-title">Student Profile</h2>
                </div>
                
                <div class="profile-card">
                    <img src="../picture/profilepicture.png" alt="Student" class="profile-img">
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($full_name) ?></h2>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($email) ?></p>
                        <p><i class="fas fa-phone"></i> <?= htmlspecialchars($phone) ?></p>
                        <p><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($role) ?></p>
                    </div>
                </div>
                
                <div class="info-cards">
                    <div class="detail-card">
                        <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                        <div class="info-group">
                            <div class="info-label">Student ID</div>
                            <div class="info-value"><?= $id ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?= htmlspecialchars($full_name) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?= htmlspecialchars($email) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Phone Number</div>
                            <div class="info-value"><?= htmlspecialchars($phone) ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3><i class="fas fa-key"></i> Security Information</h3>
                        <div class="info-group">
                            <div class="info-label">Student Key</div>
                            <div class="info-value"><?= htmlspecialchars($student_key) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Last Login</div>
                            <div class="info-value"><?= htmlspecialchars($login_time) ?></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Account Status</div>
                            <div class="info-value"><span style="color: #28a745;">Active</span></div>
                        </div>
                        <div class="info-group">
                            <div class="info-label">Two-Factor Authentication</div>
                            <div class="info-value">Enabled</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>