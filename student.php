<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: student.php");
    exit();
}

$error = "";
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
        } else $error = "Incorrect password.";
    } else $error = "ID not found.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKST University Portal</title>
    <link rel="icon" href="picture/SKST.png" type="image/png" />
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #acb6e5);
            min-height: 100vh;
        }
        .container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        input[type=number], input[type=password] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background-color: #2980b9;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1c598a;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .dashboard {
            padding: 40px 20px;
        }
        .dashboard h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }
        .card {
            background: white;
            padding: 25px;
            width: 220px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 500;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .card span {
            font-size: 24px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<?php if (!isset($_SESSION['id'])): ?>
    <div class="container">
        <h1>SKST University Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="number" name="id" placeholder="Enter your ID" required autofocus />
            <input type="password" name="password" placeholder="Enter your Password" required />
            <button type="submit" name="login">Login</button>
        </form>
    </div>
<?php else: ?>
    <div class="dashboard">
        <h2>Welcome Student ID: <?= htmlspecialchars($_SESSION['id']) ?></h2>
        <div class="cards">
            <a href="#" class="card"><span>👤</span>Personal Information</a>
            <a href="#" class="card"><span>✅</span>View Completed Courses</a>
            <a href="#" class="card"><span>📚</span>Course Offering</a>
            <a href="#" class="card"><span>💳</span>Bank History</a>
            <a href="#" class="card"><span>📆</span>Class Routine</a>
            <a href="#" class="card"><span>📝</span>Exam Routine</a>
            <a href="?logout=true" class="card" style="background-color:#e74c3c; color:white;"><span>🚪</span>Logout</a>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
