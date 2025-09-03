<?php
session_start();
$error = '';

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

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, email, phone, password FROM admin_users WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($id, $full_name, $email, $phone, $hashed_password);
            if ($stmt->fetch()) {
                // In a real application, you should verify the password using password_verify()
                // For demo purposes, we'll do a simple comparison
                if ($password === $hashed_password) {
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_name'] = $full_name;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['admin_phone'] = $phone;

                    // Update last login time
                    $update_sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
                    $update_stmt = $mysqli->prepare($update_sql);
                    if ($update_stmt) {
                        $update_stmt->bind_param("i", $id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }

                    header("Location: admin.php");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            }
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    } else {
        $error = "SQL Prepare failed: " . $mysqli->error;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle profile picture upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $uploadDir = 'uploads/admin_pictures/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update database with file path
            $update_sql = "UPDATE admin_users SET profile_picture = ? WHERE id = ?";
            if ($update_stmt = $mysqli->prepare($update_sql)) {
                $update_stmt->bind_param("si", $targetFilePath, $admin_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Update session variable
                $_SESSION['admin_profile_picture'] = $targetFilePath;
                
                // Refresh page to show new image
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $key = trim($_POST['key']);
    
    $update_sql = "UPDATE admin_users SET full_name = ?, username = ?, email = ?, phone = ?, `key` = ? WHERE id = ?";
    if ($update_stmt = $mysqli->prepare($update_sql)) {
        $update_stmt->bind_param("sssssi", $full_name, $username, $email, $phone, $key, $admin_id);
        
        if ($update_stmt->execute()) {
            // Update session variables
            $_SESSION['admin_name'] = $full_name;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_phone'] = $phone;
            
            // Refresh page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Error updating profile: " . $update_stmt->error;
        }
        
        $update_stmt->close();
    }
}

// Check if admin is logged in
$is_logged_in = isset($_SESSION['admin_id']);

// Get admin data if logged in
if ($is_logged_in) {
    $admin_id = $_SESSION['admin_id'];
    $sql = "SELECT * FROM admin_users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();

    // Update session phone number if available
    if (!empty($admin['phone'])) {
        $_SESSION['admin_phone'] = $admin['phone'];
    }
    
    // Set profile picture in session if available
    if (!empty($admin['profile_picture'])) {
        $_SESSION['admin_profile_picture'] = $admin['profile_picture'];
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your CSS styles here (too long to include in full) */
        /* ... all your CSS styles ... */
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
                <h1>Admin Portal</h1>
                <p>SKST University - Administrator Access</p>
            </div>
            
            <form class="login-form" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email address">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">Login to Admin Dashboard</button>
                
                <div class="login-links">
                    <a href="#" id="forgotPasswordLink"><i class="fas fa-unlock-alt"></i> Forgot Password?</a>
                    <a href="#" id="helpLink"><i class="fas fa-question-circle"></i> Help</a>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="demo-credentials">
                    <p><strong>Demo Credentials:</strong></p>
                    <p>Email: admin@skst.edu</p>
                    <p>Password: admin123</p>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-unlock-alt"></i> Reset Password</h2>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-body">
                    <input type="hidden" name="reset_password" value="1">
                    <div class="form-group">
                        <label for="reset_email">Email Address</label>
                        <input type="email" id="reset_email" name="email" required placeholder="Enter your email address">
                    </div>
                    <p>Enter your email address and we'll send you instructions to reset your password.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelReset">Cancel</button>
                    <button type="submit" class="btn-save">Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Help Modal -->
    <div id="helpModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-question-circle"></i> Help & Support</h2>
            </div>
            <div class="modal-body">
                <h3>Administrator Login Assistance</h3>
                <p>If you're having trouble accessing your account, please follow these steps:</p>
                <ol>
                    <li>Ensure you're using the correct email and password (case sensitive)</li>
                    <li>Try resetting your password using the "Forgot Password" link</li>
                    <li>Clear your browser cache and cookies</li>
                    <li>Try using a different browser</li>
                </ol>
                
                <h3>Contact Support</h3>
                <p>If you continue to experience issues, please contact the SKST University IT support team:</p>
                <ul>
                    <li><strong>Email:</strong> support@skstuniversity.edu</li>
                    <li><strong>Phone:</strong> 01884273156; 01610343595</li>
                    <li><strong>Hours:</strong> Saturday-Wednesday, 8:00 AM - 5:00 PM</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="closeHelp">Close</button>
            </div>
        </div>
    </div>
    <?php else: ?>

    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <img src="../picture/SKST.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%;">
            <h1 style="color:white; text-align:center;margin:0px;">SKST University Admin Portal</h1>
        </div>
        
        <div class="nav-buttons">
            <span class="welcome"><i class="fas fa-user-shield"></i> Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <button onclick="location.href='../index.html'">
                <i class="fas fa-home"></i> Home
            </button>
            <button onclick="location.href='?logout=1'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="#" class="active">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="../student/dev_student.php">
                        <i class="fas fa-user"></i> Student
                    </a>
                </li>
                <li>
                    <a href="../admission/dev_admission.php">
                        <i class="fas fa-user"></i> Admission
                    </a>
                </li>
                <li>
                    <a href="../alumni/dev_alumni.php">
                        <i class="fas fa-user-graduate"></i> Alumni
                    </a>
                </li>
                <li>
                    <a href="../faculty/dev_faculty.php">
                        <i class="fas fa-chalkboard-teacher"></i> Faculty
                    </a>
                </li>
                <li>
                    <a href="../employee/employee_management.php">
                        <i class="fas fa-briefcase"></i> Employee
                    </a>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-book"></i> Course
                    </a>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                </li>
                <li>
                    <a href="../working.html">
                        <i class="fas fa-cog"></i> System Settings
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-user-shield"></i> Administrator Dashboard</h1>
                <button class="btn-edit" id="editProfileBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <!-- Profile Card with Picture Upload -->
            <div class="profile-card">
                <div class="profile-img-container">
                    <?php if (!empty($_SESSION['admin_profile_picture'])): ?>
                        <img id="profile-image" class="profile-img" src="<?php echo htmlspecialchars($_SESSION['admin_profile_picture']); ?>" alt="Profile Image">
                    <?php else: ?>
                        <div id="profile-placeholder" class="profile-placeholder">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    <?php endif; ?>
                    <div class="edit-overlay" onclick="document.getElementById('file-input').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                    <form id="upload-form" method="post" enctype="multipart/form-data">
                        <input type="file" id="file-input" name="profile_picture" accept="image/*">
                    </form>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin['username']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo (!empty($_SESSION['admin_phone'])) ? htmlspecialchars($_SESSION['admin_phone']) : 'N/A'; ?></p>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="info-cards">
                <div class="detail-card">
                    <h3><i class="fas fa-info-circle"></i> Account Information</h3>
                    <div class="info-group">
                        <div class="info-label">Admin ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['id']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['username']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security Key</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['key']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-user-tie"></i> Personal Information</h3>
                    <div class="info-group">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['email']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($admin['phone']); ?></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-calendar-alt"></i> Account Activity</h3>
                    <div class="info-group">
                        <div class="info-label">Registration Date</div>
                        <div class="info-value"><?php echo date('M j, Y', strtotime($admin['registration_date'])); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Last Login</div>
                        <div class="info-value"><?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'First login'; ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Status</div>
                        <div class="info-value"><span style="color: #00a651;"><?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?></span></div>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h3><i class="fas fa-shield-alt"></i> Admin Privileges</h3>
                    <div class="info-group">
                        <div class="info-label">User Level</div>
                        <div class="info-value">Super Administrator</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Access Rights</div>
                        <div class="info-value">Full System Access</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Security</div>
                        <div class="info-value">Two-Factor Authentication Recommended</div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="page-header">
                <h2 class="page-title"><i class="fas fa-chart-line"></i> System Statistics</h2>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">1,254</div>
                    <div class="stat-label">Total Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number">78</div>
                    <div class="stat-label">Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number">542</div>
                    <div class="stat-label">Students</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number">42</div>
                    <div class="stat-label">Faculty</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2 class="modal-title"><i class="fas fa-user-edit"></i> Edit Profile</h2>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="modal-body">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="key">Security Key</label>
                        <input type="text" id="key" name="key" value="<?php echo htmlspecialchars($admin['key']); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const password = document.getElementById('password');
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            // Handle profile picture upload
            const fileInput = document.getElementById('file-input');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        document.getElementById('upload-form').submit();
                    }
                });
            }
            
            // Edit Profile Modal functionality
            const modal = document.getElementById("editModal");
            const btn = document.getElementById("editProfileBtn");
            const span = document.getElementsByClassName("close")[0];
            const cancelBtn = document.getElementById("cancelEdit");
            
            if (btn) {
                btn.onclick = function() {
                    modal.style.display = "block";
                }
            }
            
            if (span) {
                span.onclick = function() {
                    modal.style.display = "none";
                }
            }
            
            if (cancelBtn) {
                cancelBtn.onclick = function() {
                    modal.style.display = "none";
                }
            }
            
            // Forgot Password Modal
            const forgotPasswordLink = document.getElementById("forgotPasswordLink");
            const forgotPasswordModal = document.getElementById("forgotPasswordModal");
            const cancelReset = document.getElementById("cancelReset");
            
            if (forgotPasswordLink && forgotPasswordModal) {
                forgotPasswordLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    forgotPasswordModal.style.display = "block";
                });
            }
            
            if (cancelReset) {
                cancelReset.onclick = function() {
                    forgotPasswordModal.style.display = "none";
                }
            }
            
            // Help Modal
            const helpLink = document.getElementById("helpLink");
            const helpModal = document.getElementById("helpModal");
            const closeHelp = document.getElementById("closeHelp");
            
            if (helpLink && helpModal) {
                helpLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    helpModal.style.display = "block";
                });
            }
            
            if (closeHelp) {
                closeHelp.onclick = function() {
                    helpModal.style.display = "none";
                }
            }
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
                if (event.target == forgotPasswordModal) {
                    forgotPasswordModal.style.display = "none";
                }
                if (event.target == helpModal) {
                    helpModal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>