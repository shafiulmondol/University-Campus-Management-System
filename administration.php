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
//  -- ==================== Connection FACULTY BIODATA ==================== --
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facultylogin'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['faculty_id'] = $row['faculty_id'];  // ✅ FIXED
        header("Location: administration.php?facultylogin=true");
        exit();
    } else {
        $error = "Faculty ID not found.";
    }
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
//  -- ==================== Admin Login ==================== --
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
//  -- ==================== Admin Biodata Check ==================== --
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
       
//  -- ==================== Admin Login Faculty Section ==================== --
    <?php elseif (isset($_GET['Faculty_Intro'])): ?>
        <div class="container">
        <h1>SKST University Faculty Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="number" name="faculty_id" placeholder="Enter Faculty ID" required autofocus />
            <button type="submit" name="facultylogin">Login</button>
        </form>
        <a href="administration.php" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
    </div>
   
//  -- ==================== FACULTY BIODATA ==================== --       
    <?php elseif (isset($_GET['faculty_biodata'])): ?>
        <?php
        $adminid = $_SESSION['faculty_id'];
        $stmt = $conn->prepare("SELECT  `faculty_id`, `name`, `email`, `password`, `department`, `address`, `phone`, `room_number`, `salary` FROM faculty WHERE faculty_id = ?");
        $stmt->bind_param("i", $adminid);
        $stmt->execute();
        $result = $stmt->get_result();
        $biodata = $result->fetch_assoc();
        ?>

        <div class="routine-page">
            <h2>Faculty Biodata</h2>
            <?php if ($biodata): ?>
                <div class="container">
                    <p><strong>ID:</strong> <?= htmlspecialchars($adminid) ?></p>
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($biodata['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($biodata['email']) ?></p>
                    <p><strong>Password:</strong> <?= htmlspecialchars($biodata['password']) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($biodata['department']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($biodata['phone']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($biodata['address']) ?></p>
                    <p><strong>Room Number:</strong> <?= htmlspecialchars($biodata['room_number']) ?></p>
                    <p><strong>Salary:</strong> <?= htmlspecialchars($biodata['salary']) ?></p>
                    <a href="?faculty_info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No biodata found for your ID.</p>
                    <a href="?faculty_info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>
//  -- ==================== FACULTY Dashboard ==================== --
    <?php elseif (isset($_GET['facultylogin'])): ?>
        <div class="routine-page">
            <h2>Faculty Information</h2>
            <div class="cards">
                <a href="?faculty_biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?edit_faculty_biodata=true" class="card"><button><span>✏️</span>Edit Biodata</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>
//  -- ==================== Individual FACULTY Dashboard ==================== --
    <?php elseif (isset($_GET['faculty_info'])): ?>
        <div class="routine-page">
            <h2>Faculty Information</h2>
            <div class="cards">
                <a href="?Faculty_Intro=true" class="card"><button><span>👤</span>Faculty Intro</button></a>
                <a href="?add_faculty=true" class="card"><button><span>✏️</span>Add new</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>
//  -- ==================== Add FACULTY ==================== --       
<?php elseif (isset($_GET['add_faculty'])): ?>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
        $faculty_id = $_POST['faculty_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password']; // In real applications, use password_hash()!
        $department = $_POST['department'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $room_number = $_POST['room_number'];
        $salary = $_POST['salary'];

        // Check if faculty_id or email already exists
        $check_stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ? OR email = ?");
        $check_stmt->bind_param("ss", $faculty_id, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Faculty ID or Email already exists.');</script>";
        } else {
            // Insert new faculty
            $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, name, email, password, department, address, phone, room_number, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $faculty_id, $name, $email, $password, $department, $address, $phone, $room_number, $salary);

            if ($stmt->execute()) {
                echo "<script>alert('New faculty added successfully.'); window.location='?add_faculty=true';</script>";
            } else {
                echo "<script>alert('Error adding faculty.');</script>";
            }
        }
    }
    ?>
    <div class="routine-page">
        <h2>Add New Faculty</h2>
        <div class="container">
            <form method="POST">
                <input type="text" name="faculty_id" placeholder="Faculty ID" required />
                <input type="text" name="name" placeholder="Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="text" name="password" placeholder="Password" required />
                <input type="text" name="department" placeholder="Department" required />
                <input type="text" name="address" placeholder="Address" required />
                <input type="text" name="phone" placeholder="Phone" required />
                <input type="text" name="room_number" placeholder="Room Number" required />
                <input type="text" name="salary" placeholder="Salary" required />
                <button type="submit" name="add_faculty">➕ Add Faculty</button>
                <a href="?faculty_info=true" type="back"><button type="button">🔙 Back</button></a>
            </form>
        </div>
    </div>
//  -- ==================== Edit FACULTY Biodata ==================== --
<?php elseif (isset($_GET['edit_faculty_biodata'])): ?>
    <?php
    $faculty_id = $_SESSION['faculty_id']; // Use the correct faculty_id from session

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        // Get posted form values
        $updated_faculty_id = $_POST['faculty_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $department = $_POST['department'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $room_number = $_POST['room_number'];
        $salary = $_POST['salary'];

        // Prepare UPDATE statement
        $stmt = $conn->prepare("UPDATE faculty SET faculty_id=?, name=?, email=?, password=?, department=?, address=?, phone=?, room_number=?, salary=? WHERE faculty_id=?");

        $stmt->bind_param("ssssssssss", $updated_faculty_id, $name, $email, $password, $department, $address, $phone, $room_number, $salary, $faculty_id);

        if ($stmt->execute()) {
            // Update session variable if faculty_id changed
            $_SESSION['faculty_id'] = $updated_faculty_id;

            echo "<script>alert('Biodata updated successfully.'); window.location='?edit_faculty_biodata=true';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to update biodata.');</script>";
        }
    }

    // Fetch current faculty biodata
    $stmt = $conn->prepare("SELECT faculty_id, name, email, password, department, address, phone, room_number, salary FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    ?>
    <div class="routine-page">
        <h2>Edit Biodata</h2>
        <div class="container">
            <form method="POST">
                <input type="text" name="faculty_id" value="<?= htmlspecialchars($data['faculty_id']) ?>" required />
                <input type="text" name="name" value="<?= htmlspecialchars($data['name']) ?>" required />
                <input type="text" name="email" value="<?= htmlspecialchars($data['email']) ?>" required />
                <input type="text" name="password" value="<?= htmlspecialchars($data['password']) ?>" required />
                <input type="text" name="department" value="<?= htmlspecialchars($data['department']) ?>" required />
                <input type="text" name="address" value="<?= htmlspecialchars($data['address']) ?>" required />
                <input type="text" name="phone" value="<?= htmlspecialchars($data['phone']) ?>" required />
                <input type="text" name="room_number" value="<?= htmlspecialchars($data['room_number']) ?>" required />
                <input type="text" name="salary" value="<?= htmlspecialchars($data['salary']) ?>" required />
                <button type="submit" name="update">Update</button>
                <a href="?faculty_info=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
            </form>
        </div>
    </div>

//  -- ==================== Admin Dashboard ==================== --
    <?php elseif (isset($_GET['info'])): ?>
        <div class="routine-page">
            <h2>Personal Information</h2>
            <div class="cards">
                <a href="?biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?edit_biodata=true" class="card"><button><span>✏️</span>Edit Biodata</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>
//  -- ==================== Edit Admin Biodata ==================== --
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