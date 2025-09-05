<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'skst_university');

// Check if student is logged in
if (!isset($_SESSION['student_data'])) {
    header("Location: studentlogin.php");
    exit();
}
require_once '../library/notice.php';

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
$show_result_section = false;
$show_course_section = false;
$show_enrollment_section = false;
$show_routine_section = false;
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
 if (isset($_POST['routine'])) {
        $show_routine_section = true;
        $show_result_section = false;
        $show_course_section = false;
        $show_enrollment_section = false;
        $show_request_form = false;
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
        $category = mysqli_real_escape_string($conn, $_POST['change_category']);
        if (!in_array($category, ['Student', 'Staff', 'Faculty'])) {
            $errors[] = "Invalid update type selected.";
        }
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
            $query = "INSERT INTO update_requests (admin_email,applicant_id,category, update_type, current_value, new_value, comments, request_time, action) 
                      VALUES ('$admin_email','$id','$category', '$update_type', '$current_value', '$new_value', '$comments', '$request_time', '$action')";

            if (mysqli_query($conn, $query)) {
                $success = "Your update request has been submitted successfully.";
                $show_request_form = false;
            } else {
                $errors[] = "Error submitting request: " . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['result'])) {
        $show_result_section = true;
        $show_course_section = false;
        $show_enrollment_section = false;
        $show_routine_section = false;
    } elseif (isset($_POST['course'])) {
        $show_course_section = true;
        $show_result_section = false;
        $show_enrollment_section = false;
        $show_routine_section = false;
    } elseif (isset($_POST['dashboard'])) {
        $show_result_section = false;
        $show_request_form = false;
        $show_course_section = false;
        $show_enrollment_section = false;
        $show_routine_section = false;
    } elseif (isset($_POST['biodata'])) {
        $show_result_section = false;
        $show_request_form = false;
        $show_course_section = false;
        $show_enrollment_section = false;
        $show_routine_section = false;
    } elseif (isset($_POST['enrolment'])) {
        $show_enrollment_section = true;
        $show_result_section = false;
        $show_course_section = false;
        $show_routine_section = false;
    } elseif (isset($_POST['routine'])) {
        $show_routine_section = true;
        $show_result_section = false;
        $show_course_section = false;
        $show_enrollment_section = false;
    } elseif (isset($_POST['enroll_course'])) {
        // Handle course enrollment
        $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
        $faculty_id = mysqli_real_escape_string($conn, $_POST['faculty_id']);
        $enrollment_date = date('Y-m-d H:i:s');
        
        // Check if already enrolled
        $check_query = "SELECT * FROM enrollments WHERE student_id = '$id' AND course_id = '$course_id'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $errors[] = "You are already enrolled in this course.";
        } else {
            // Insert enrollment
            $enroll_query = "INSERT INTO enrollments (student_id, course_id, faculty_id, enrollment_date, status) 
                             VALUES ('$id', '$course_id', '$faculty_id', '$enrollment_date', 'enrolled')";
            
            if (mysqli_query($conn, $enroll_query)) {
                $success = "Successfully enrolled in the course!";
                $show_enrollment_section = true;
            } else {
                $errors[] = "Error enrolling in course: " . mysqli_error($conn);
            }
        }
    } elseif (isset($_POST['cancel_enrollment'])) {
        $course_id = mysqli_real_escape_string($conn, $_POST['course_id']);
        
        $cancel_query = "DELETE FROM enrollments WHERE student_id = '$id' AND course_id = '$course_id'";
        
        if (mysqli_query($conn, $cancel_query)) {
            $success = "Enrollment canceled successfully!";
            $show_enrollment_section = true;
        } else {
            $errors[] = "Error canceling enrollment: " . mysqli_error($conn);
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

// Handle result search
$results = [];
$all_semesters = [];
$selected_semester = '';

if (isset($_POST['search_result']) || isset($_POST['show_all_results'])) {
    $show_result_section = true;

    if (isset($_POST['semester'])) {
        $selected_semester = mysqli_real_escape_string($conn, $_POST['semester']);
    }

    // Fetch available semesters for this student
    $semester_query = "SELECT DISTINCT semister FROM student_result WHERE st_id = '$id' ORDER BY semister";
    $semester_result = mysqli_query($conn, $semester_query);
    if ($semester_result && mysqli_num_rows($semester_result) > 0) {
        while ($row = mysqli_fetch_assoc($semester_result)) {
            $all_semesters[] = $row['semister'];
        }
    }

    // Build query based on search criteria
    if (isset($_POST['show_all_results'])) {
        $query = "SELECT * FROM student_result WHERE st_id = '$id' ORDER BY semister, course";
    } else {
        if (!empty($selected_semester)) {
            $query = "SELECT * FROM student_result WHERE st_id = '$id' AND semister = '$selected_semester' ORDER BY course";
        } else {
            $query = "SELECT * FROM student_result WHERE st_id = '$id' ORDER BY semister, course";
        }
    }

    $result_query = mysqli_query($conn, $query);
    if ($result_query && mysqli_num_rows($result_query) > 0) {
        while ($row = mysqli_fetch_assoc($result_query)) {
            $results[] = $row;
        }
    }
}

// Handle course display
$courses = [];
$course_semesters = [];
$selected_course_semester = '';

if (isset($_POST['course']) || isset($_POST['search_course']) || isset($_POST['show_all_courses'])) {
    $show_course_section = true;
    
    if (isset($_POST['semester'])) {
        $selected_course_semester = mysqli_real_escape_string($conn, $_POST['semester']);
    }
    
    // Get all available semesters from course table
    $sem_query = "SELECT DISTINCT semester FROM course ORDER BY semester ASC";
    $sem_result = mysqli_query($conn, $sem_query);
    if ($sem_result && mysqli_num_rows($sem_result) > 0) {
        while ($row = mysqli_fetch_assoc($sem_result)) {
            $course_semesters[] = $row['semester'];
        }
    }
    
    // Fetch courses based on selection
    if (isset($_POST['search_course']) && !empty($selected_course_semester)) {
        // Show only selected semester courses
        $query = "SELECT * FROM course WHERE semester = '$selected_course_semester' ORDER BY course_code ASC";
    } else {
        // Show all courses
        $query = "SELECT * FROM course ORDER BY semester ASC, course_code ASC";
    }
    
    $course_result = mysqli_query($conn, $query);
    if ($course_result && mysqli_num_rows($course_result) > 0) {
        while ($row = mysqli_fetch_assoc($course_result)) {
            $courses[] = $row;
        }
    }
}

// Calculate current semester based on student results
$current_semester = 1;
$max_semester_query = "SELECT MAX(semister) as max_semester FROM student_result WHERE st_id = '$id'";
$max_semester_result = mysqli_query($conn, $max_semester_query);

if ($max_semester_result && mysqli_num_rows($max_semester_result) > 0) {
    $row = mysqli_fetch_assoc($max_semester_result);
    if ($row['max_semester'] !== null) {
        $current_semester = $row['max_semester'] + 1;
    }
}

// Fetch available courses for the current semester with faculty information
$available_courses = [];
$enrolled_courses = [];

// Get current academic year
$current_year = date('Y');

// Check if course_instructor table has academic_year column
$check_academic_year = mysqli_query($conn, "SHOW COLUMNS FROM course_instructor LIKE 'academic_year'");
$has_academic_year = mysqli_num_rows($check_academic_year) > 0;

// Check if class_days column exists in course_instructor table
$check_class_days = mysqli_query($conn, "SHOW COLUMNS FROM course_instructor LIKE 'class_days'");
$has_class_days = mysqli_num_rows($check_class_days) > 0;

// Build query based on database structure
if ($has_academic_year) {
    $query = "SELECT c.*, f.faculty_id, f.name as instructor_name, 
                     ci.class_time, ci.room_number";
    
    if ($has_class_days) {
        $query .= ", ci.class_days";
    }
    
    $query .= " FROM course as c 
              LEFT JOIN course_instructor ci ON c.course_id = ci.course_id 
              LEFT JOIN faculty as f ON ci.faculty_id = f.faculty_id
              WHERE c.semester = '$current_semester' 
              AND ci.academic_year = '$current_year'
              ORDER BY c.course_code";
} else {
    $query = "SELECT c.*, f.faculty_id, f.name as instructor_name, 
                     ci.class_time, ci.room_number";
    
    if ($has_class_days) {
        $query .= ", ci.class_days";
    }
    
    $query .= " FROM course as c 
              LEFT JOIN course_instructor ci ON c.course_id = ci.course_id 
              LEFT JOIN faculty as f ON ci.faculty_id = f.faculty_id
              WHERE c.semester = '$current_semester' 
              ORDER BY c.course_code";
}

$course_query = mysqli_query($conn, $query);
if ($course_query && mysqli_num_rows($course_query) > 0) {
    while ($row = mysqli_fetch_assoc($course_query)) {
        // Check if student is already enrolled
        $course_id = $row['course_id'];
        $enrollment_query = "SELECT * FROM enrollments WHERE student_id = '$id' AND course_id = '$course_id'";
        $enrollment_result = mysqli_query($conn, $enrollment_query);
        $row['is_enrolled'] = mysqli_num_rows($enrollment_result) > 0;
        
        $available_courses[] = $row;
    }
}

// Get enrolled courses for routine
$enrolled_query = "SELECT c.*, f.name as instructor_name, ci.class_time, ci.room_number";
if ($has_class_days) {
    $enrolled_query .= ", ci.class_days";
}
$enrolled_query .= " FROM enrollments e
                   JOIN course c ON e.course_id = c.course_id
                   LEFT JOIN course_instructor ci ON c.course_id = ci.course_id 
                   LEFT JOIN faculty f ON ci.faculty_id = f.faculty_id
                   WHERE e.student_id = '$id' AND e.status = 'enrolled'";

$enrolled_result = mysqli_query($conn, $enrolled_query);
if ($enrolled_result && mysqli_num_rows($enrolled_result) > 0) {
    while ($row = mysqli_fetch_assoc($enrolled_result)) {
        $enrolled_courses[] = $row;
    }
}

// Check if required tables exist, create them if not
$check_tables = mysqli_query($conn, "SHOW TABLES LIKE 'course_instructor'");
if (mysqli_num_rows($check_tables) == 0) {
    // Create course_instructor table
    $create_table = "CREATE TABLE course_instructor (
        ci_id INT(11) PRIMARY KEY AUTO_INCREMENT,
        course_id INT(11) NOT NULL,
        faculty_id INT(11) NOT NULL,
        class_time VARCHAR(50) NOT NULL,
        room_number VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES course(course_id),
        FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
    )";
    
    mysqli_query($conn, $create_table);
    
    // Insert sample data - link courses with faculty
    $sample_data = [
        [1, 1, '10:00-11:30', 'Room 101'],
        [2, 2, '14:00-15:30', 'Room 202'],
        [3, 3, '11:00-12:30', 'Room 303'],
        [4, 4, '09:00-10:30', 'Room 404'],
        [5, 5, '13:00-14:30', 'Room 505'],
    ];
    
    foreach ($sample_data as $data) {
        $insert = "INSERT INTO course_instructor (course_id, faculty_id, class_time, room_number) 
                   VALUES ('$data[0]', '$data[1]', '$data[2]', '$data[3]')";
        mysqli_query($conn, $insert);
    }
}

$check_tables = mysqli_query($conn, "SHOW TABLES LIKE 'enrollments'");
if (mysqli_num_rows($check_tables) == 0) {
    // Create enrollments table with faculty_id column
    $create_table = "CREATE TABLE enrollments (
        enrollment_id INT(11) PRIMARY KEY AUTO_INCREMENT,
        student_id INT(11) NOT NULL,
        course_id INT(11) NOT NULL,
        faculty_id INT(11) NOT NULL,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('enrolled', 'completed', 'dropped') DEFAULT 'enrolled',
        FOREIGN KEY (student_id) REFERENCES student_registration(id),
        FOREIGN KEY (course_id) REFERENCES course(course_id),
        FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
    )";
    
    mysqli_query($conn, $create_table);
}

// If enrollments table exists but doesn't have faculty_id column, add it
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM enrollments LIKE 'faculty_id'");
if (mysqli_num_rows($check_column) == 0) {
    $alter_table = "ALTER TABLE enrollments ADD COLUMN faculty_id INT(11) NOT NULL AFTER course_id";
    mysqli_query($conn, $alter_table);
    
    $add_foreign_key = "ALTER TABLE enrollments ADD FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)";
    mysqli_query($conn, $add_foreign_key);
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
    <!-- <link rel="stylesheet" href="../admin/admin.css"> -->
    <link rel="stylesheet" href="student.css">
    <style>
        /* =======       student body ===================== */
   .result-container, .course-container, .routine-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .form-group-row {
            display: flex;
            flex-direction: column;
        }
        
        .form-group-row label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group-row select, 
        .form-group-row input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-search {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-routine {
            background: #6f42c1;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-show-all {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 20px;
        }
        
        .result-table, .course-table, .routine-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .result-table th, 
        .result-table td,
        .course-table th, 
        .course-table td,
        .routine-table th, 
        .routine-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .result-table th,
        .course-table th,
        .routine-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .result-table tr:nth-child(even),
        .course-table tr:nth-child(even),
        .routine-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .no-results {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .semester-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .gpa-display {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        
        .btn-enroll {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-enroll:hover {
            background: #218838;
        }
        
        .btn-enroll:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .btn-enrolled {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: default;
        }
        
        .course-card {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .course-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .course-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .course-code {
            font-weight: bold;
            color: #007bff;
            font-size: 1.1rem;
        }
        
        .course-credits {
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .course-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #333;
        }
        
        .course-details {
            margin-bottom: 15px;
            color: #555;
        }
        
        .course-details div {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .course-details i {
            margin-right: 8px;
            width: 16px;
            color: #007bff;
        }
        
        .course-schedule {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 3px solid #007bff;
        }
        
        .course-schedule div {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .course-schedule i {
            margin-right: 8px;
            width: 16px;
            color: #28a745;
        }
        
        .course-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        
        .semester-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .semester-info h3 {
            margin: 0;
            color: #495057;
        }
        
        .semester-info p {
            margin: 5px 0 0 0;
            color: #6c757d;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .enrollment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .status-enrolled {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-available {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .instructor-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ddd;
        }
        
        .instructor-info div {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .instructor-info i {
            margin-right: 8px;
            width: 16px;
            color: #6f42c1;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        /* =================== search bar and search form====================== */
        *{
            padding: 0;
            margin: 0;
        }
        .content-area {
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-header {
            margin-bottom: 15px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

       

        .search-form input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-form button {
            padding: 8px 15px;
            border: none;
            background: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        /* ===================edit admin biodata form================= */
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: 600;
            display: block;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .btn-submit {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .success-msg {
            background: #d4edda;
            padding: 10px;
            border-radius: 8px;
            color: #155724;
            text-align: center;
            margin-bottom: 15px;
        }

        /* ================ Navbar Styles ============ */
        .navbar {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .logo h1 {
            font-size: 22px;
            font-weight: 600;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-buttons button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-buttons button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        /* ================ Main Layout ============ */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        /* ================ Sidebar Styles ============ */
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            color: #2b5876;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .description {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Statistics Button */
        .stats-button {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(43, 88, 118, 0.2);
        }

        .stats-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(43, 88, 118, 0.3);
        }

        .stats-button:active {
            transform: translateY(0);
        }

        .stats-button i {
            margin-right: 10px;
            font-size: 20px;
        }

        /* Stats Panel (initially hidden) */
        .stats-panel {
            background: #f8faff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            text-align: left;
            display: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #4e4376;
        }

        .stats-panel.visible {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #4e4376;
            font-weight: 500;
        }

        .stat-value {
            color: #2b5876;
            font-weight: 700;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .instructions {
            margin-top: 25px;
            padding: 15px;
            background: #f0f5ff;
            border-radius: 8px;
            font-size: 14px;
            color: #4e4376;
        }

        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 90;
            padding: 25px 0;
        }

        .sidebar-menu {
            list-style: none;
            width: 100%;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
            width: 100%;
            margin-bottom: 8px;
        }

        .sidebar-menu a,
        .sidebar-menu button {
            display: flex;
            align-items: center;
            color: #4e4376;
            text-decoration: none;
            padding: 12px 25px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active,
        .sidebar-menu button:hover {
            background-color: #f0f5ff;
            color: #2b5876;
            border-right: 4px solid #2b5876;
        }

        .sidebar-menu i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        /* ================ Content Area ============ */
        .content-area {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            height: calc(100vh - 80px);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title {
            color: #2b5876;
            font-size: 24px;
            font-weight: 600;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.3s;
        }

        .btn-edit:hover {
            opacity: 0.9;
        }

        /* ================ Profile Section ============ */
        .profile-card {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-right: 30px;
        }

        .profile-info h2 {
            color: #2b5876;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .profile-info p {
            color: #666;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .profile-info i {
            margin-right: 10px;
            color: #4e4376;
        }

        /* ================ Info Cards ============ */
        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .detail-card h3 {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
            display: flex;
            align-items: center;
        }

        .detail-card h3 i {
            margin-right: 10px;
            background: #f0f5ff;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #4e4376;
        }

        .info-group {
            margin-bottom: 18px;
        }

        .info-label {
            font-size: 14px;
            color: #888;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 18px;
            color: #444;
            font-weight: 500;
        }

        /* ================ Stats Section ============ */
        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 15px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex: 1;
            margin: 10px;
            min-width: 180px;
            transition: all 0.3s ease;
            border-top: 3px solid;
            height: 150px;
            /* Fixed height */
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }

        .stat-card:nth-child(1) {
            border-color: #4e4376;
        }

        .stat-card:nth-child(2) {
            border-color: #2b5876;
        }

        .stat-card:nth-child(3) {
            border-color: #4a90e2;
        }

        .stat-card:nth-child(4) {
            border-color: #f39c12;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #4e4376, #826ab4);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #2b5876, #4e8fa8);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #4a90e2, #6bb9ff);
        }

        .stat-card:nth-child(4) .stat-icon {
            background: linear-gradient(135deg, #f39c12, #f1c40f);
        }

        .stat-number {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 3px;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .stats {
                gap: 12px;
            }

            .stat-card {
                min-width: 160px;
                padding: 12px;
                height: 140px;
            }
        }

        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
            }

            .stat-card {
                min-width: auto;
                height: auto;
                padding: 15px;
            }
        }

        /* ================ Responsive Design ============ */
        @media (max-width: 1024px) {
            .info-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                top: 0;
            }

            .content-area {
                height: auto;
            }

            .profile-card {
                flex-direction: column;
                text-align: center;
            }

            .profile-img {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }

            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .info-cards {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        /* Additional styles for better form presentation */
        


       

    

       

    


       


    

 
    /* Grade value styling */
    .grade-a-plus {
        color: #2E7D32;
        font-weight: bold;
    }
    
    .grade-a {
        color: #2E7D32;
        font-weight: bold;
    }
    
    .grade-a-minus {
        color: #2E7D32;
        font-weight: bold;
    }
    
    .grade-b-plus {
        color: #5C6BC0;
        font-weight: bold;
    }
    
    .grade-b {
        color: #5C6BC0;
        font-weight: bold;
    }
    
    .grade-b-minus {
        color: #5C6BC0;
        font-weight: bold;
    }
    
    .grade-c-plus {
        color: #FF9800;
        font-weight: bold;
    }
    
    .grade-c {
        color: #FF9800;
        font-weight: bold;
    }
    
    .grade-c-minus {
        color: #FF9800;
        font-weight: bold;
    }
    
    .grade-d-plus {
        color: #EF6C00;
        font-weight: bold;
    }
    
    .grade-d {
        color: #EF6C00;
        font-weight: bold;
    }
    
    .grade-f {
        color: #C62828;
        font-weight: bold;
    }
    
    .grade-na {
        color: #757575;
        font-style: italic;
    }
      /* Improved styles for enrollment section */
        .enrollment-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .course-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .course-code {
            font-weight: bold;
            color: #2b5876;
            font-size: 1.2rem;
        }
        
        .course-credits {
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }
        
        .course-details {
            margin-bottom: 10px;
        }
        
        .course-schedule {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .enrollment-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .btn-enroll {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-enroll:hover {
            background: #218838;
        }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-cancel:hover {
            background: #c82333;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-enrolled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-available {
            background: #cce5ff;
            color: #004085;
        }
        
        .no-courses-message {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        
        .semester-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2b5876;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-routine {
            background: #6f42c1;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .bell-btn {
    position: relative;
    background: #fff;
    border: none;
    cursor: pointer;
    font-size: 22px;
    color: #333;
    padding: 10px 15px;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: 0.3s;
}

.bell-btn:hover {
    background: #f5f5f5;
    color: #007bff;
    transform: scale(1.05);
}

.bell-btn i {
    font-size: 26px;
}

.badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #ff3b3b;
    color: #fff !important;
    font-size: 13px;
    font-weight: bold;
    border-radius: 50%;
    padding: 4px 7px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.notice-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    margin: 12px 0;
    padding: 15px;
    transition: 0.3s;
}

.notice-card.unread {
    background: #91f13cff; /* light blue for unread */
    border-left: 5px solid #007bff;
}

.notice-card.read {
    background: #62cee9ff; /* light gray for read */
    border-left: 5px solid #ccc;
}
.notice-card.update {
    background: #f5d878ff; /* light yellow */
    border-left: 5px solid #ff9800;
}


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
            <!-- Bell Button -->


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
                    <li><button type="submit" name="enrolment"><i class="fas fa-user-plus"></i> Enrollment</button></li>
                    <li><button type="submit" name="routine"><i class="fas fa-calendar-alt"></i> Routine</button></li>
                    <li><button type="submit" name="account"><i class="fas fa-exchange-alt"></i> Transaction</button></li>
    <div class="notification-bell">
      <li>  <button type="submit" name="notice" class="bell-btn">
            <i class="fas fa-bell"></i>Notification
            <?php 
            $unread = get_unread_student_notification_count(); 
            if($unread > 0): ?>
                <span class="badge"><?= htmlspecialchars($unread) ?></span>
            <?php endif; ?>
        </button></li>
    </div>

                </form>
                <li><a href="studentlogin.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
      <?php
        // <!-- Main content -->
         if (isset($_POST['biodata']) || $show_request_form || isset($_GET['action'])): ?>
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
                                    <label for="change_category">Select Category *</label>
                                    <select id="change_category" name="change_category" required>
                                        <option value="">Select Category</option>
                                        <option value="Student" <?php if (isset($update_category) && $update_category === 'Student') echo 'selected'; ?>>Student</option>
                                        <option value="Staff" <?php if (isset($update_category) && $update_category === 'Staff') echo 'selected'; ?>>Staff</option>
                                        <option value="Faculty" <?php if (isset($update_category) && $update_category === 'Faculty') echo 'selected'; ?>>Faculty</option>
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
                            <div class="info-group">
                                <div class="info-label">Student ID</div>
                                <div class="info-value"><?= $id ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Full Name</div>
                                <div class="info-value"><?= htmlspecialchars($full_name) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Father's Name</div>
                                <div class="info-value"><?= htmlspecialchars($father_name) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Mother's Name</div>
                                <div class="info-value"><?= htmlspecialchars($mother_name) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Gender</div>
                                <div class="info-value"><?= htmlspecialchars($gender) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Date of Birth</div>
                                <div class="info-value"><?= htmlspecialchars($date_of_birth) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Nationality</div>
                                <div class="info-value"><?= htmlspecialchars($nationality) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Religion</div>
                                <div class="info-value"><?= htmlspecialchars($religion) ?></div>
                            </div>
                        </div>

                        <!-- Contact & Address -->
                        <div class="detail-card">
                            <h3><i class="fas fa-address-book"></i> Contact & Address</h3>
                            <div class="info-group">
                                <div class="info-label">Student Phone</div>
                                <div class="info-value"><?= htmlspecialchars($student_phone) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Guardian Phone</div>
                                <div class="info-value"><?= htmlspecialchars($guardian_phone) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Present Address</div>
                                <div class="info-value"><?= htmlspecialchars($present_address) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Permanent Address</div>
                                <div class="info-value"><?= htmlspecialchars($permanent_address) ?></div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="detail-card">
                            <h3><i class="fas fa-book-open"></i> Academic Information</h3>
                            <div class="info-group">
                                <div class="info-label">Last Exam</div>
                                <div class="info-value"><?= htmlspecialchars($last_exam) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Board</div>
                                <div class="info-value"><?= htmlspecialchars($board ?: $other_board) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Year of Passing</div>
                                <div class="info-value"><?= htmlspecialchars($year_of_passing) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Institution</div>
                                <div class="info-value"><?= htmlspecialchars($institution_name) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Result</div>
                                <div class="info-value"><?= htmlspecialchars($result) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Subject Group</div>
                                <div class="info-value"><?= htmlspecialchars($subject_group) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Department</div>
                                <div class="info-value"><?= htmlspecialchars($department) ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Submission Date</div>
                                <div class="info-value"><?= htmlspecialchars($submission_date) ?></div>
                            </div>
                        </div>

                        <!-- Security Information -->
                        <div class="detail-card">
                            <h3><i class="fas fa-shield-alt"></i> Security Information</h3>
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
        <?php elseif ($show_result_section || isset($_POST['result'])): ?>
    <!-- Result Section -->
    <div class="content-area">
        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-poll"></i> Academic Results</h2>
            <form method="post">
                <button type="submit" name="dashboard" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Dashboard</button>
            </form>
        </div>

        <div class="result-container">
            <form method="post" class="search-form">
                <div class="form-group-row">
                    <label for="semester">Select Semester:</label>
                    <select id="semester" name="semester">
                        <option value="">All Semesters</option>
                        <?php foreach ($all_semesters as $sem): ?>
                            <option value="<?= $sem ?>" <?= ($selected_semester == $sem) ? 'selected' : '' ?>>
                                Semester <?= $sem ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="search_result" class="btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="submit" name="show_all_results" class="btn-show-all">
                    <i class="fas fa-list"></i> Show All Results
                </button>
            </form>

            <?php if (!empty($results)): ?>
                <?php
                // Group results by semester and get SGPA from database
                $grouped_results = [];
                $semester_gpas = [];
                
                // First, get the SGPA for each semester from the database
                $sgpa_query = "SELECT DISTINCT semister, sgpa FROM student_result WHERE st_id = '$id'";
                $sgpa_result = mysqli_query($conn, $sgpa_query);
                if ($sgpa_result && mysqli_num_rows($sgpa_result) > 0) {
                    while ($row = mysqli_fetch_assoc($sgpa_result)) {
                        $semester_gpas[$row['semister']] = $row['sgpa'];
                    }
                }
                
                // Then group the results by semester
                foreach ($results as $result) {
                    $semester = $result['semister'];
                    $grouped_results[$semester][] = $result;
                }
                ?>

                <?php foreach ($grouped_results as $semester => $semester_results): ?>
                    <h3>Semester <?= $semester ?> Results</h3>
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Grade</th>
                                <th>Grade Value</th>
                                <th>Credit Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($semester_results as $result): 
                                // Get the grade from database - this should be the letter grade (A, B+, etc.)
                                $letter_grade = trim($result['grade']);
                                $grade_value = '';
                                $grade_class = '';
                                
                                // Convert letter grade to numeric value for display
                                switch (strtoupper($letter_grade)) {
                                    case 'A+':
                                        $grade_value = '4.00';
                                        $grade_class = 'grade-a-plus';
                                        break;
                                    case 'A':
                                        $grade_value = '4.00';
                                        $grade_class = 'grade-a';
                                        break;
                                    case 'A-':
                                        $grade_value = '3.70';
                                        $grade_class = 'grade-a-minus';
                                        break;
                                    case 'B+':
                                        $grade_value = '3.30';
                                        $grade_class = 'grade-b-plus';
                                        break;
                                    case 'B':
                                        $grade_value = '3.00';
                                        $grade_class = 'grade-b';
                                        break;
                                    case 'B-':
                                        $grade_value = '2.70';
                                        $grade_class = 'grade-b-minus';
                                        break;
                                    case 'C+':
                                        $grade_value = '2.30';
                                        $grade_class = 'grade-c-plus';
                                        break;
                                    case 'C':
                                        $grade_value = '2.00';
                                        $grade_class = 'grade-c';
                                        break;
                                    case 'C-':
                                        $grade_value = '1.70';
                                        $grade_class = 'grade-c-minus';
                                        break;
                                    case 'D+':
                                        $grade_value = '1.30';
                                        $grade_class = 'grade-d-plus';
                                        break;
                                    case 'D':
                                        $grade_value = '1.00';
                                        $grade_class = 'grade-d';
                                        break;
                                    case 'F':
                                    case 'FAIL':
                                        $grade_value = '0.00';
                                        $grade_class = 'grade-f';
                                        $letter_grade = 'F'; // Standardize to F
                                        break;
                                    case 'P':
                                    case 'PASS':
                                        $grade_value = 'N/A';
                                        $grade_class = 'grade-p';
                                        $letter_grade = 'P'; // Standardize to P
                                        break;
                                    case 'I':
                                    case 'INCOMPLETE':
                                        $grade_value = 'N/A';
                                        $grade_class = 'grade-na';
                                        $letter_grade = 'I'; // Standardize to I
                                        break;
                                    case 'W':
                                    case 'WITHDRAWN':
                                        $grade_value = 'N/A';
                                        $grade_class = 'grade-na';
                                        $letter_grade = 'W'; // Standardize to W
                                        break;
                                    default:
                                        // If it's already a numeric value, use it directly
                                        if (is_numeric($letter_grade)) {
                                            $grade_value = $letter_grade;
                                            $grade_class = 'grade-numeric';
                                            // Convert numeric value back to letter grade for display
                                            $numeric_val = (float)$letter_grade;
                                            if ($numeric_val >= 3.7) $letter_grade = 'A';
                                            else if ($numeric_val >= 3.3) $letter_grade = 'B+';
                                            else if ($numeric_val >= 3.0) $letter_grade = 'B';
                                            else if ($numeric_val >= 2.7) $letter_grade = 'B-';
                                            else if ($numeric_val >= 2.3) $letter_grade = 'C+';
                                            else if ($numeric_val >= 2.0) $letter_grade = 'C';
                                            else if ($numeric_val >= 1.7) $letter_grade = 'C-';
                                            else if ($numeric_val >= 1.3) $letter_grade = 'D+';
                                            else if ($numeric_val >= 1.0) $letter_grade = 'D';
                                            else $letter_grade = 'F';
                                        } else {
                                            $grade_value = 'N/A';
                                            $grade_class = 'grade-na';
                                        }
                                }
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($result['course']) ?></td>
                                    <td><?= htmlspecialchars($result['course_name'] ?? $result['course'] . ' Name') ?></td>
                                    <td class="<?= $grade_class ?>"><?= htmlspecialchars($letter_grade) ?></td>
                                    <td><?= $grade_value ?></td>
                                    <td><?= htmlspecialchars($result['credit_hours'] ?? '3') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="gpa-display">
                        <strong>Semester <?= $semester ?> SGPA: 
                            <?= isset($semester_gpas[$semester]) ? number_format($semester_gpas[$semester], 2) : 'N/A' ?>
                        </strong>
                    </div>
                <?php endforeach; ?>

                <!-- Display overall CGPA if showing all results -->
                <?php if (isset($_POST['show_all_results']) && !empty($semester_gpas)): ?>
                    <?php
                    // Calculate overall CGPA from semester SGPAs
                    $total_sgpa = 0;
                    $total_semesters = count($semester_gpas);
                    
                    foreach ($semester_gpas as $sgpa) {
                        $total_sgpa += $sgpa;
                    }
                    
                    $cgpa = $total_semesters > 0 ? $total_sgpa / $total_semesters : 0;
                    ?>
                    <div class="gpa-display" style="border-left-color: #28a745; margin-top: 30px;">
                        <strong>Overall CGPA: <?= number_format($cgpa, 2) ?></strong>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>No results found</h3>
                    <p>Please select a different semester or try again later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php
// Default: fetch all semeste

// Get all available semesters from course table
$sem_query = "SELECT DISTINCT semester FROM course ORDER BY semester ASC";
$sem_result = mysqli_query($conn, $sem_query);
while ($row = mysqli_fetch_assoc($sem_result)) {
    $all_semesters[] = $row['semester'];
}

// Fetch courses
$courses = [];
if (isset($_POST['search_course']) && !empty($selected_semester)) {
    // Show only selected semester courses
    $stmt = $conn->prepare("SELECT * FROM course WHERE semester = ?");
    $stmt->bind_param("i", $selected_semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Show all courses
    $query = "SELECT * FROM course ORDER BY semester ASC, course_code ASC";
    $result = mysqli_query($conn, $query);
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>


   <?php elseif ($show_course_section || isset($_POST['course'])): ?>
    <!-- Academic Courses Section -->
    <div class="content-area">
        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-book"></i> Academic Courses</h2>
            <form method="post">
                <button type="submit" name="dashboard" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </button>
            </form>
        </div>

        <div class="result-container">
            <!-- Filter Form -->
            <form method="post" class="search-form">
                <div class="form-group-row">
                    <label for="semester">Select Semester:</label>
                    <select id="semester" name="semester">
                        <option value="">All Semesters</option>
                        <?php foreach ($all_semesters as $sem): ?>
                            <option value="<?= htmlspecialchars($sem) ?>" <?= ($selected_semester == $sem) ? 'selected' : '' ?>>
                                Semester <?= htmlspecialchars($sem) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="search_course" class="btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="submit" name="show_all_courses" class="btn-show-all">
                    <i class="fas fa-list"></i> Show All Courses
                </button>
                <button type="submit" name="enrolment" class="btn-show-all">
                    <i class="fas fa-plus-circle"></i> Enrol Course
                </button>
            </form>

            <?php if (!empty($courses)): ?>
                <?php
                // Group courses by semester
                $grouped_courses = [];
                foreach ($courses as $course) {
                    $semester = $course['semester'];
                    $grouped_courses[$semester][] = $course;
                }
                ?>

                <?php foreach ($grouped_courses as $semester => $semester_courses): ?>
                    <h3>Semester <?= htmlspecialchars($semester) ?> Courses</h3>
                    <table class="result-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credit Hours</th>
                                <th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($semester_courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                    <td><?= htmlspecialchars($course['credit_hours']) ?></td>
                                    <td><?= htmlspecialchars($course['department']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-courses">
                    <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>No courses found</h3>
                    <p>Please select a different semester or try again later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
   <?php elseif ($show_enrollment_section || isset($_POST['enrolment'])): ?>
            <!-- Enrollment Section -->
            <div class="content-area">
                <div class="page-header">
                    <h2 class="page-title"><i class="fas fa-plus-circle"></i> Course Enrolment</h2>
                    <form method="post">
                        <button type="submit" name="course" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </button>
                    </form>
                </div>
                
                <div class="enrollment-container">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <h3><i class="fas fa-exclamation-circle"></i> Errors:</h3>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="semester-info">
                        <h3><i class="fas fa-info-circle"></i> Enrollment Information</h3>
                        <p>Based on your academic progress, you are eligible to enroll in <strong>Semester <?= $current_semester ?></strong> courses.</p>
                    </div>
                    
                    <div class="action-buttons">
                        <form method="post">
                            <button type="submit" name="routine" class="btn-routine">
                                <i class="fas fa-calendar-alt"></i> View My Routine
                            </button>
                        </form>
                    </div>
                    
                    <?php if (!empty($available_courses)): ?>
                        <h3>Available Courses for Semester <?= $current_semester ?></h3>
                        
                        <?php foreach ($available_courses as $course): ?>
                            <div class="course-card">
                                <div class="course-header">
                                    <span class="course-code"><?= htmlspecialchars($course['course_code']) ?></span>
                                    <span class="course-credits"><?= htmlspecialchars($course['credit_hours']) ?> Credits</span>
                                </div>
                                
                                <div class="course-details">
                                    <h4><?= htmlspecialchars($course['course_name']) ?></h4>
                                    <p><strong>Department:</strong> <?= htmlspecialchars($course['department']) ?></p>
                                    <p><strong>Instructor:</strong> <?= htmlspecialchars($course['instructor_name'] ?? 'Not Assigned') ?></p>
                                    
                                    <?php if (!empty($course['class_time'])): ?>
                                        <div class="course-schedule">
                                            <p><strong>Schedule:</strong> <?= htmlspecialchars($course['class_time']) ?></p>
                                            <?php if (!empty($course['room_number'])): ?>
                                                <p><strong>Room:</strong> <?= htmlspecialchars($course['room_number']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="status-badge <?= $course['is_enrolled'] ? 'status-enrolled' : 'status-available' ?>">
                                            <?= $course['is_enrolled'] ? 'Enrolled' : 'Available' ?>
                                        </span>
                                    </p>
                                </div>
                                
                                <div class="enrollment-actions">
                                    <?php if ($course['is_enrolled']): ?>
                                        <form method="post">
                                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                            <button type="submit" name="cancel_enrollment" class="btn-cancel">
                                                <i class="fas fa-times"></i> Cancel Enrollment
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post">
                                            <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                            <input type="hidden" name="faculty_id" value="<?= $course['faculty_id'] ?>">
                                            <button type="submit" name="enroll_course" class="btn-enroll">
                                                <i class="fas fa-user-plus"></i> Enroll
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-courses-message">
                            <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <h3>No courses available for enrollment</h3>
                            <p>There are no courses available for Semester <?= $current_semester ?> at this time.</p>
                            <p>Please contact your department for course availability.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
<?php elseif (isset($_POST['notice'])):

  

    echo "<div class='notices-container'>";
    echo "<h2 class='notices-heading'><i class='fas fa-bullhorn'></i> Latest Notifications</h2>";

    // -------------------
    // 1. Fetch Notices
    // -------------------
    $query1 = "SELECT id, title, content, author, created_at, viewed, 'notice' AS source 
               FROM notice 
               WHERE section='Student' AND id=$id
               ORDER BY created_at DESC";
    $result1 = mysqli_query($con, $query1);

    $notices = [];
    if ($result1) {
        while ($row = mysqli_fetch_assoc($result1)) {
            $notices[] = $row;
        }
    }

    // -------------------
    // 2. Fetch Update Requests (action != 0)
    // -------------------
    $query2 = "SELECT id,applicant_id admin_email, category, update_type, current_value, new_value, comments, request_time, action, 'update' AS source 
               FROM update_requests 
               WHERE action != 0 AND applicant_id=$id
               ORDER BY request_time DESC";
    $result2 = mysqli_query($con, $query2);

    if ($result2) {
        while ($row = mysqli_fetch_assoc($result2)) {
            $notices[] = $row;
        }
    }

    // -------------------
    // 3. Sort All Notifications by Date (newest first)
    // -------------------
    usort($notices, function($a, $b) {
        $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : strtotime($a['request_time']);
        $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : strtotime($b['request_time']);
        return $timeB - $timeA;
    });

    // -------------------
    // 4. Display Combined Notifications
    // -------------------
    if (count($notices) > 0) {
        foreach ($notices as $row) {
            if ($row['source'] == 'notice') {
                // Student Notices
                $noticeClass = ($row['viewed'] == 0) ? "notice-card unread" : "notice-card read";
                echo "<div class='{$noticeClass}'>";
                echo "<div class='notice-header'>";
                echo "<h3 class='notice-title'><i class='fas fa-chevron-circle-right'></i> " . htmlspecialchars($row['title']) . "</h3>";
                echo "<span class='notice-section'>Notice</span>";
                echo "</div>";
                echo "<div class='notice-content'>" . nl2br(htmlspecialchars($row['content'])) . "</div>";
                echo "<div class='notice-footer'>";
                echo "<span class='notice-author'><i class='fas fa-user'></i> " . htmlspecialchars($row['author']) . "</span>";
                echo "<span class='notice-date'><i class='far fa-calendar-alt'></i> " . date('F j, Y h:i A', strtotime($row['created_at'])) . "</span>";
                echo "</div></div>";
            } else {
                // Update Requests
                $statusText = ($row['action'] == 1 AND $row['applicant_id']=$id) ? "✅ Your update request has been Approved." : "❌ Your update request has been Rejected.";
                echo "<div class='notice-card update'>";
                echo "<div class='notice-header'>";
                echo "<h3 class='notice-title'><i class='fas fa-edit'></i> Update Request (" . htmlspecialchars($row['update_type']) . ")</h3>";
                echo "<span class='notice-section'>Profile Update</span>";
                echo "</div>";
                echo "<div class='notice-content'>" . $statusText . "<br>";
                echo "<b>Old Value:</b> " . htmlspecialchars($row['current_value']) . "<br>";
                echo "<b>New Value:</b> " . htmlspecialchars($row['new_value']) . "</div>";
                echo "<div class='notice-footer'>";
                echo "<span class='notice-author'><i class='fas fa-user'></i> Admin: " . htmlspecialchars($row['admin_email']) . "</span>";
                echo "<span class='notice-date'><i class='far fa-calendar-alt'></i> " . date('F j, Y h:i A', strtotime($row['request_time'])) . "</span>";
                echo "</div></div>";
            }
        }

        echo "<div class='back-button-container'>";?>
        <form method="post">
                <button type="submit" name="dashboard" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </form> <?php
        echo "</div>";

    } else {
        echo "<div class='no-notices'>";
        echo "<i class='far fa-folder-open'></i>";
        echo "<p>No notifications found at this time</p>";
        ?>
        <form method="post">
                <button type="submit" name="dashboard" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </form> <?php
        echo "</div>";
    }

    echo "</div>"; // Close notices-container

    // -------------------
    // 5. Mark Student Notices as Read
    // -------------------
    $update = "UPDATE notice SET viewed = 1 WHERE section='Student' AND viewed = 0";
    mysqli_query($con, $update);


    
    elseif ($show_routine_section || isset($_POST['routine'])): ?>
    <!-- Routine Section -->
    <div class="content-area">
        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-calendar-alt"></i> My Class Routine</h2>
            <form method="post">
                <button type="submit" name="enrolment" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Enrollment
                </button>
            </form>
        </div>
        
        <div class="routine-container">
            <?php if (!empty($enrolled_courses)): ?>
                <h3>Your Class Schedule for Semester <?= $current_semester ?></h3>
                
                <div class="table-responsive">
                    <table class="routine-table">
                        <thead>
                            <tr>
                                <th>SI</th>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Instructor</th>
                                <th>Time</th>
                                <th>Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $si = 1; ?>
                            <?php foreach ($enrolled_courses as $course): ?>
                                <tr>
                                    <td><?= $si++ ?></td>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                    <td><?= htmlspecialchars($course['instructor_name'] ?? 'Not Assigned') ?></td>
                                    <td><?= htmlspecialchars($course['class_time'] ?? 'Not Scheduled') ?></td>
                                    <td><?= htmlspecialchars($course['room_number'] ?? 'Not Assigned') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>No enrolled courses found</h3>
                    <p>You haven't enrolled in any courses yet. Please enroll in courses to view your routine.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php 

 else: ?>
 
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