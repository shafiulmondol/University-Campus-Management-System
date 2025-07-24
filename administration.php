<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: administration.php");
    exit();
}

// Login check
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $id = intval($_POST['id']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['password'] === $password) {
            $_SESSION['id'] = $id;
            header("Location: administration.php");
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
        * { box-sizing: border-box; }
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
        h1, h2 {
            color: #2c3e50;
            text-align: center;
        }
        input[type=number], input[type=password], input[type=text] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button[type=submit] {
            background-color: #2980b9;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        a[type=back] button {
            margin-top: 10px;
            background-color: #08ed91ff;
            color: white;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button[type=submit]:hover {
            background-color: #1c598a;
        }
        a[type=back] button:hover {
            background-color: #f31b1bff;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .dashboard, .routine-page {
            padding: 40px 20px;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }
        .card button {
            background: white;
            padding: 25px;
            width: 220px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #2c3e50;
            font-size: 13px;
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
        <h1>SKST University Admin Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="number" name="id" placeholder="Enter your ID" required autofocus />
            <input type="password" name="password" placeholder="Enter your Password" required />
            <button type="submit" name="login">Login</button>
        </form>
        <a href="index.html" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
    </div>
<?php else: ?>

    <?php if (isset($_GET['biodata'])): ?>
        <?php
        $adminid = $_SESSION['id'];
        $stmt = $conn->prepare("SELECT  full_name, username, password, email, phone FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();
        $biodata = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Administrator Biodata</h2>
            <?php if ($biodata): ?>
                <div class="container">
                    <p><strong>ID:</strong> <?= htmlspecialchars($adminid) ?></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($biodata['full_name']) ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($biodata['username']) ?></p>
                    <p><strong>Password:</strong> <?= htmlspecialchars($biodata['password']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($biodata['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($biodata['phone']) ?></p>
                    <a href="?info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No biodata found for your ID.</p>
                    <a href="?info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (isset($_GET['info'])): ?>
        <div class="routine-page">
            <h2>Personal Information</h2>
            <div class="cards">
                <a href="?biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?edit_biodata=true" class="card"><button><span>✏️</span>Edit Biodata</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>

    <?php elseif (isset($_GET['edit_biodata'])): ?>
        <?php
        $adminid = $_SESSION['id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
            $full_name = $_POST['full_name'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];

            $stmt = $conn->prepare("UPDATE admin_users SET full_name=?, username=?, password=?, email=?, phone=? WHERE id=?");
            $stmt->bind_param("sssssi", $full_name, $username, $password, $email, $phone, $adminid);
            $stmt->execute();
            echo "<script>alert('Biodata updated successfully.'); window.location='?biodata=true';</script>";
        }

        $stmt = $conn->prepare("SELECT full_name, username, password, email, phone FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Edit Biodata</h2>
            <div class="container">
                <form method="POST">
                    <input type="text" name="full_name" value="<?= htmlspecialchars($data['full_name']) ?>" required />
                    <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required />
                    <input type="text" name="password" value="<?= htmlspecialchars($data['password']) ?>" required />
                    <input type="text" name="email" value="<?= htmlspecialchars($data['email']) ?>" required />
                    <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" required />
                    <button type="submit" name="update">Update</button>
                    <a href="?info=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="dashboard">
            <h2>Welcome Administrator: <?= htmlspecialchars($_SESSION['id']) ?></h2>
            <div class="cards">
                <a href="?info=true" class="card"><button><span>👤</span>Personal Information</button></a>
                <a href="?manage_students=true" class="card"><button><span>🎓</span>Manage Students</button></a>
                <a href="?manage_courses=true" class="card"><button><span>📚</span>Manage Courses</button></a>
                <a href="?routine_setup=true" class="card"><button><span>📆</span>Setup Class Routine</button></a>
                <a href="?finance_reports=true" class="card"><button><span>💳</span>Finance Reports</button></a>
                <a href="?faculty_info=true" class="card"><button><span>👨‍🏫</span>Faculty Info</button></a>
                <a href="?manage_employees=true" class="card"><button><span>🧑‍💼</span>Manage Employees</button></a>
                <a href="?logout=true" class="card" style="background-color:#e74c3c; color:white;"><button><span>🚪</span>Logout</button></a>
            </div>
            <h1 style="color: red;"><i>Note: Please logout after managing the system</i></h1>
        </div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>