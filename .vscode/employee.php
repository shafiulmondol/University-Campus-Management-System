<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$db   = "skst_university";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = sanitize($_POST['emp_id'] ?? '');
    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $status = sanitize($_POST['status'] ?? 'active');

    // Validate required fields
    if (empty($emp_id) || empty($first_name) || empty($last_name) || empty($position)) {
        $errors[] = "Employee ID, First Name, Last Name, and Position are required.";
    }

    if (empty($errors)) {
        // Check for duplicate emp_id or email
        $check_sql = "SELECT * FROM employees WHERE emp_id=? OR email=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $emp_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Employee ID or Email already exists.";
        } else {
            // Insert into database
            $insert_sql = "INSERT INTO employees (emp_id, first_name, last_name, position, department, email, phone, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssssssss", $emp_id, $first_name, $last_name, $position, $department, $email, $phone, $status);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Failed to save data: " . $stmt->error;
            }
        }
    }
}

// Fetch all employees for display
$employees = [];
$sql = "SELECT * FROM employees ORDER BY id ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $employees = $result->fetch_all(MYSQLI_ASSOC);
}

function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKST University - Employee Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {font-family: Arial, sans-serif; margin:20px;}
        table {border-collapse: collapse; width:100%;}
        th, td {border:1px solid #ccc; padding:8px; text-align:left;}
        th {background:#004080; color:white;}
        form input, form select, form button {padding:6px; margin:4px 0;}
        .success {color:green;}
        .error {color:red;}
    </style>
</head>
<body>
<h1>SKST University - Employee Management</h1>

<?php if ($success): ?>
    <div class="success">Employee added successfully!</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error">
        <?php foreach ($errors as $err) echo "<div>$err</div>"; ?>
    </div>
<?php endif; ?>

<h2>Employee Directory</h2>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Position</th><th>Department</th><th>Email</th><th>Phone</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?php echo $emp['emp_id']; ?></td>
                <td><?php echo $emp['first_name'] . ' ' . $emp['last_name']; ?></td>
                <td><?php echo $emp['position']; ?></td>
                <td><?php echo $emp['department']; ?></td>
                <td><?php echo $emp['email']; ?></td>
                <td><?php echo $emp['phone']; ?></td>
                <td><?php echo ucfirst($emp['status']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Add Employee</h2>
<form method="POST" action="">
    <input type="text" name="emp_id" placeholder="Employee ID" required><br>
    <input type="text" name="first_name" placeholder="First Name" required><br>
    <input type="text" name="last_name" placeholder="Last Name" required><br>
    <input type="text" name="position" placeholder="Position" required><br>
    <input type="text" name="department" placeholder="Department"><br>
    <input type="email" name="email" placeholder="Email"><br>
    <input type="tel" name="phone" placeholder="Phone"><br>
    <select name="status">
        <option value="active">Active</option>
        <option value="onleave">On Leave</option>
        <option value="inactive">Inactive</option>
    </select><br><br>
    <button type="submit">Add Employee</button>
</form>
</body>
</html>

<?php $conn->close(); ?>
