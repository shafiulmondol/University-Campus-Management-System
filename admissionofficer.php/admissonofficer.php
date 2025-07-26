<?php
session_start(); // Start session

$message = "";

// Database connection
$conn = new mysqli("localhost", "root", "", "your_database_name");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Register logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($password !== $confirm) {
            $message = "❌ Passwords do not match!";
        } else {
            $check = $conn->query("SELECT * FROM users WHERE email='$email'");
            if ($check->num_rows > 0) {
                $message = "❌ Email already registered!";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashed);
                if ($stmt->execute()) {
                    $message = "✅ Registration successful! Please login.";
                } else {
                    $message = "❌ Error: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }

    // Login logic
    if (isset($_POST['login'])) {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        $result = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "❌ Invalid password!";
            }
        } else {
            $message = "❌ No user found with this email!";
        }
    }
}

$conn->close();
?>
