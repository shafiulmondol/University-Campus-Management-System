<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_data'])) {
    header("Location: studentlogin.php");
    exit();
} 

// Get the student data from session
$stdata = $_SESSION['student_data'];

// Extract values for easier access
$id                = $stdata['id'] ?? '';
$full_name         = $stdata['full_name'] ?? '';
$email             = $stdata['email'] ?? '';
$phone             = $stdata['student_phone'] ?? '';
$father_name       = $stdata['father_name'] ?? '';
$mother_name       = $stdata['mother_name'] ?? '';
$guardian_phone    = $stdata['guardian_phone'] ?? '';
$student_phone     = $stdata['student_phone'] ?? '';
$password          = $stdata['password'] ?? '';
$last_exam         = $stdata['last_exam'] ?? '';
$board             = $stdata['board'] ?? '';
$other_board       = $stdata['other_board'] ?? '';
$year_of_passing   = $stdata['year_of_passing'] ?? '';
$institution_name  = $stdata['institution_name'] ?? '';
$result            = $stdata['result'] ?? '';
$subject_group     = $stdata['subject_group'] ?? '';
$gender            = $stdata['gender'] ?? '';
$nationality       = $stdata['nationality'] ?? '';
$religion          = $stdata['religion'] ?? '';
$present_address   = $stdata['present_address'] ?? '';
$permanent_address = $stdata['permanent_address'] ?? '';
$department        = $stdata['department'] ?? '';
$submission_date   = $stdata['submission_date'] ?? '';
$date_of_birth     = $stdata['date_of_birth'] ?? '';
$student_key       = $stdata['student_key'] ?? '';
$role              = $stdata['role'] ?? 'Student';
$login_time        = $stdata['login_time'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Biodata</title>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Add your CSS styles here (same as before) */
        .content-area { padding: 20px; font-family: 'Segoe UI', sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-title { font-size: 28px; font-weight: 600; color: #333; }
        .btn-edit { background: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
        .profile-card { display: flex; align-items: center; gap: 20px; background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff; }
        .profile-info h2 { margin: 0 0 10px; }
        .profile-info p { margin: 5px 0; color: #555; }
        .info-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .detail-card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .detail-card h3 { margin-bottom: 15px; color: #007bff; }
        .info-group { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .info-label { font-weight: 500; color: #555; }
        .info-value { font-weight: 600; color: #333; }
    </style>
</head>
<body><?php
function studentbiodata($stdata) {
    // Extract variables from $stdata
    extract($stdata);
    ?>
    <!-- Student Profile Section -->
    <div class="content-area">
        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-user-circle"></i> Student Profile</h2>
            <button class="btn-edit"><i class="fas fa-edit"></i> Edit Profile</button>
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
<?php
}
?>

</body>
</html>
