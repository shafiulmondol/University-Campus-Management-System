<?php
session_start();

// ======================
// Database Connection
// ======================
$host = "localhost";
$db   = "skst_university";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ======================
    // Create employees table if not exists
    // ======================
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            emp_id VARCHAR(20) UNIQUE NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            position VARCHAR(100) NOT NULL,
            department VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            status ENUM('active','onleave','inactive') DEFAULT 'active',
            last_attendance_date DATE DEFAULT NULL,
            attendance_status ENUM('Present','Absent','Leave') DEFAULT NULL,
            base_salary DECIMAL(10,2) DEFAULT 0,
            bonus DECIMAL(10,2) DEFAULT 0,
            deductions DECIMAL(10,2) DEFAULT 0,
            net_salary DECIMAL(10,2) GENERATED ALWAYS AS (base_salary + bonus - deductions) STORED,
            last_pay_date DATE DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // ======================
    // Insert sample data
    // ======================
    $sampleEmployees = [
        ['FAC001','Ahmed','Rahman','Professor','Engineering','ahmed.rahman@skst.edu','01710000001','password123',120000,5000,2000,'2025-09-01','2025-09-02','Present'],
        ['FAC002','Fatima','Khan','Associate Professor','Medicine','fatima.khan@skst.edu','01710000005','password123',110000,4000,1500,'2025-09-01','2025-09-02','Present']
    ];

    foreach ($sampleEmployees as $emp) {
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
        $stmt->execute([$emp[0]]);
        if ($stmt->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO employees 
                (emp_id, first_name, last_name, position, department, email, phone, password, base_salary, bonus, deductions, last_pay_date, last_attendance_date, attendance_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], password_hash($emp[7], PASSWORD_DEFAULT),
                $emp[8], $emp[9], $emp[10], $emp[11], $emp[12], $emp[13]
            ]);
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ======================
// Handle Login
// ======================
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM employees WHERE email = ?");
    $stmt->execute([$email]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee && password_verify($password, $employee['password'])) {
        $_SESSION['emp_id'] = $employee['emp_id'];
    } else {
        $message = "Invalid email or password.";
    }
}

// ======================
// If logged in, fetch employee data
// ======================
$profile = null;
if (isset($_SESSION['emp_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
    $stmt->execute([$_SESSION['emp_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Portal - SKST University</title>
</head>
<body>
<h1>SKST University - Employee Portal</h1>

<?php if (!$profile): ?>
    <h2>Login</h2>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" name="login">Login</button>
    </form>
    <p style="color:red;"><?php echo $message; ?></p>
<?php else: ?>
    <h2>Welcome, <?php echo $profile['first_name'] . " " . $profile['last_name']; ?></h2>
    <p><b>Employee ID:</b> <?php echo $profile['emp_id']; ?></p>
    <p><b>Position:</b> <?php echo $profile['position']; ?></p>
    <p><b>Department:</b> <?php echo $profile['department']; ?></p>
    <p><b>Status:</b> <?php echo $profile['status']; ?></p>
    <hr>
    <h3>Payroll Info</h3>
    <p><b>Base Salary:</b> <?php echo $profile['base_salary']; ?></p>
    <p><b>Bonus:</b> <?php echo $profile['bonus']; ?></p>
    <p><b>Deductions:</b> <?php echo $profile['deductions']; ?></p>
    <p><b>Net Salary:</b> <?php echo $profile['net_salary']; ?></p>
    <p><b>Last Pay Date:</b> <?php echo $profile['last_pay_date'] ?? 'N/A'; ?></p>
    <hr>
    <form method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>

    <?php
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    ?>
<?php endif; ?>

</body>
</html>
