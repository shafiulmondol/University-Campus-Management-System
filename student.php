<?php
session_start();

// Database config
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";

// Connect to MySQL
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle logout request
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: student.php");
    exit();
}

// Initialize error message
$error = "";

// Handle login POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $id = intval($_POST['id']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['password'] === $password) {
            $_SESSION['id'] = $id;
            header("Location: student.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "ID not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>SKST University Login</title>
<link rel="icon" href="picture/SKST.png" type="image/png" />
<style>
    body {
        margin: 0; padding: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .container {
        background: #fff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        width: 350px;
        text-align: center;
    }
    h1 {
        color: #009879;
        margin-bottom: 20px;
    }
    input[type=number], input[type=password] {
        width: 100%;
        padding: 12px;
        margin: 10px 0 20px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
    }
    button {
        background-color: #009879;
        color: white;
        padding: 12px;
        border: none;
        width: 100%;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background-color: #007f63;
    }
    .error {
        color: red;
        margin-bottom: 15px;
    }
    .portal-links {
        list-style: none;
        padding: 0;
        margin-top: 20px;
        text-align: left;
    }
    .portal-links li {
        margin-bottom: 10px;
    }
    .logout-btn {
        margin-top: 25px;
        background-color: #e74c3c;
    }
    .logout-btn:hover {
        background-color: #c0392b;
    }
</style>
</head>
<body>
<div class="container">

<?php if (!isset($_SESSION['id'])): ?>
    <h1>SKST University Login</h1>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="number" name="id" placeholder="Enter your ID" required autofocus />
        <input type="password" name="password" placeholder="Enter your Password" required />
        <button type="submit" name="login">Login</button>
    </form>
<?php else: ?>
    <h1>Welcome to SKST University</h1>
    <p>You are logged in as ID: <strong><?= htmlspecialchars($_SESSION['id']) ?></strong></p>

    <ul class="portal-links">
        <li><a href="#">📚 View Courses</a></li>
        <li><a href="#">📥 Download Resources</a></li>
        <li><a href="#">📊 Check Results</a></li>
    </ul>

    <form method="GET" action="">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
<?php endif; ?>

</div>
</body>
</html>
