<?php
ob_start(); // Start output buffering to avoid header issues
session_start();

$error = '';
require_once '../library/notice.php';

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
if(isset($_POST['dashboard'])) { ?>
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-user-shield"></i> Administrator Dashboard</h1>

                <a href="#edit-form"><button class="btn-edit">
                    <i class="fas fa-edit"></i> Edit Profile
                </button></a>
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
                    <form id="upload-form" method="post" enctype="multipart/form-data" style="margin-top: 10px;">
                        <label for="file-input" class="edit-overlay">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" id="file-input" name="profile_picture" accept="image/*">
                        <button type="submit" style="margin-top: 10px; padding: 5px 10px;">Upload Photo</button>
                    </form>
                </div>

                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h2>
                    <p><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo (!empty($_SESSION['admin_phone'])) ? htmlspecialchars($_SESSION['admin_phone']) : 'N/A'; ?></p>
                </div>
            </div>
            
            <?php
            if (!empty($error)): ?>
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
                    <div class="stat-number"> </div>
                    <div class="stat-label">Courses</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-number">542</div>
                    <div class="stat-label">Students</div>
                </div>
                
                <?php 
                // Database connection
                $mysqli = new mysqli("localhost", "root", "", "skst_university");

                // Check connection
                if ($mysqli->connect_error) {
                    die("Connection failed: " . $mysqli->connect_error);
                }

                // Count faculty
                $result = $mysqli->query("SELECT COUNT(*) AS total FROM faculty");
                $row = $result->fetch_assoc();
                $faculty_count = $row['total'];
                ?>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number"><?php echo $faculty_count; ?></div>
                    <div class="stat-label">Faculty</div>
                </div>
            </div>
            
            <!-- Edit Profile Form (always visible) -->
            <div id="edit-form" class="detail-card" style="margin-top: 30px;">
                <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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
                    
                    <div class="form-group">
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
