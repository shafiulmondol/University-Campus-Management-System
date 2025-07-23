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
    header("Location: student.php");
    exit();
}

// Login check
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $id = intval($_POST['id']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT password FROM student_biodata WHERE id = ?");
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
        input[type=number], input[type=password],input[type=text] {
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
    <!-- Login Page -->
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
        <a href="index.html" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
    </div>

<?php else: ?>

    <?php if (isset($_GET['routine'])): ?>
        <!-- Routine Page with 3 Buttons -->
        <div class="routine-page">
            <h2>Class & Exam Routine</h2>
            <div class="cards">
                <a href="#" class="card"><button><span>📅</span>View Class Routine</button></a>
                <a href="#" class="card"><button><span>📝</span>View Exam Routine</button></a>
                <a href="student.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>
        
    <?php elseif (isset($_GET['result'])): ?>
        <!-- Routine Page with 3 Buttons -->
        <div class="result">
            <div class="container">
                <h1>View Result</h1>
                <form method="POST">
                    <input type="number" name="id" placeholder="Enter your ID" required autofocus />
                    <input type="password" name="password" placeholder="Enter your Password" required />
                    <input type="text" name="text" placeholder="Enter Semister" required />
                    <button type="submit" name="login">Login</button>
                </form>
                <a href="student.php" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>
        
    <?php elseif (isset($_GET['info'])): ?>
        <!-- Info Page -->
        <div class="routine-page">
            <h2>Personal Information</h2>
            <div class="cards">
                <a href="?biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?result=true" class="card"><button><span>👤</span>View Result</button></a>
                <a href="student.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>

    <?php elseif (isset($_GET['biodata'])): ?>
        <!-- Biodata Page -->
        <?php
            $studentId = $_SESSION['id'];
            $stmt = $conn->prepare("SELECT full_name, date_of_birth, gender, blood_group, nationality, religion, father_name, mother_name, phone, email, present_address, permanent_address, photo FROM student_biodata WHERE id = ?");
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $biodata = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Student Biodata</h2>
            <?php if ($biodata): ?>
                <div class="container">
                    <p><strong>ID:</strong> <?= htmlspecialchars($studentId) ?></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($biodata['full_name']) ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($biodata['date_of_birth']) ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($biodata['gender']) ?></p>
                    <p><strong>Blood Group:</strong> <?= htmlspecialchars($biodata['blood_group']) ?></p>
                    <p><strong>Nationality:</strong> <?= htmlspecialchars($biodata['nationality']) ?></p>
                    <p><strong>Religion:</strong> <?= htmlspecialchars($biodata['religion']) ?></p>
                    <p><strong>Father's Name:</strong> <?= htmlspecialchars($biodata['father_name']) ?></p>
                    <p><strong>Mother's Name:</strong> <?= htmlspecialchars($biodata['mother_name']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($biodata['phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($biodata['email']) ?></p>
                    <p><strong>Present Address:</strong> <?= nl2br(htmlspecialchars($biodata['present_address'])) ?></p>
                    <p><strong>Permanent Address:</strong> <?= nl2br(htmlspecialchars($biodata['permanent_address'])) ?></p>
                    <?php if (!empty($biodata['photo'])): ?>
                        <p><strong>Photo:</strong><br>
                            <img src="uploads/<?= htmlspecialchars($biodata['photo']) ?>" width="150" style="border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.2);" alt="Student Photo" />
                        </p>
                    <?php else: ?>
                        <p><strong>Photo:</strong> No photo uploaded.</p>
                    <?php endif; ?>
                    <a href="?info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No biodata found for your ID.</p>
                    
                    <a href="?info=true" class="card"><button><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <!-- Main Dashboard -->
        <div class="dashboard">
            <h2>Welcome Student ID: <?= htmlspecialchars($_SESSION['id']) ?></h2>
            <div class="cards">
                <a href="?info=true" class="card"><button><span>👤</span>Personal Information</button></a>
                <a href="#" class="card"><button><span>✅</span>View Courses</button></a>
                <a href="#" class="card"><button><span>📚</span>Course Offering</button></a>
                <a href="#" class="card"><button><span>💳</span>Bank History</button></a>
                <a href="?routine=true" class="card"><button><span>📆</span>Routine</button></a>
                <a href="?logout=true" class="card" style="background-color:#e74c3c; color:white;"><button><span>🚪</span>Logout</button></a>
            </div>
            <h1 style="color: red;"><i>Note: Please logout after visiting</i></h1>
        </div>
    <?php endif; ?>

<?php endif; ?>
</body>
</html>
