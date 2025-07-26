<?php
session_start(); // Session শুরু

// Database connection
$conn = new mysqli("localhost", "root", "", "your_database_name"); // <-- DB name ঠিক করো
if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

// Session message variable
$msg = $_SESSION['message'] ?? "";
unset($_SESSION['message']); // Show once, then remove

// ================= REGISTER =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['register'])) {
    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password)) {
        $_SESSION['message'] = "❌ All fields are required!";
    } elseif ($password !== $confirm) {
        $_SESSION['message'] = "❌ Passwords do not match!";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $_SESSION['message'] = "❌ Email already registered!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed);
            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ Registration successful! Please login.";
            } else {
                $_SESSION['message'] = "❌ Registration error: " . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
    header("Location: auth.php"); // Same page redirect
    exit();
}

// ================= LOGIN =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['message'] = "";
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['message'] = "❌ Invalid password!";
        }
    } else {
        $_SESSION['message'] = "❌ No user found with this email!";
    }
    $stmt->close();
    header("Location: auth.php");
    exit();
}

$conn->close();
?>

<!-- ============== HTML FORM PART ============== -->
<!DOCTYPE html>
<html>
<head>
    <title>Login/Register Form</title>
    <style>
        body {
            font-family: Arial;
            text-align: center;
            margin-top: 50px;
        }
        form {
            border: 1px solid #ccc;
            padding: 20px;
            display: inline-block;
        }
        input {
            margin: 5px;
            padding: 8px;
            width: 250px;
        }
        .msg {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- ======= Message Show ======= -->
<?php if (!empty($msg)): ?>
    <div class="msg"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- ======= Registration Form ======= -->
<h2>Register</h2>
<form method="POST">
    <input type="text" name="name" placeholder="Your Name" required><br>
    <input type="email" name="email" placeholder="Your Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
    <button type="submit" name="register">Register</button>
</form>

<br><br>

<!-- ======= Login Form ======= -->
<h2>Login</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Your Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="login">Login</button>
</form>

</body>
</html>
