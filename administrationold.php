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
    $id = intval($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT full_name, password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['password'] === $password) {
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $row['full_name'];
            header("Location: administration.php");
            exit();
        } else $error = "Incorrect password.";
    } else $error = "ID not found.";
}

// Faculty Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facultylogin'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $stmt = $conn->prepare("SELECT faculty_id, name FROM faculty WHERE faculty_id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['faculty_id'] = $row['faculty_id'];
        $_SESSION['faculty_name'] = $row['name'];
        // Redirect to originally requested action or default faculty dashboard
        $redirect = isset($_SESSION['faculty_redirect']) ? $_SESSION['faculty_redirect'] : 'facultylogin=true';
        unset($_SESSION['faculty_redirect']);
        header("Location: administration.php?$redirect");
        exit();
    } else {
        $error = "Faculty ID not found.";
    }
}

// Student Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['studentlogin'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT id, first_name FROM student_registration WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['student_id'] = $row['id'];
        $_SESSION['student_name'] = $row['first_name'];
        header("Location: administration.php?studentlogin=true");
        exit();
    } else {
        $error = "Student ID not found.";
    }
}

// Store requested faculty action if coming from faculty section
if (isset($_GET['faculty_biodata']) || isset($_GET['edit_faculty_biodata']) || isset($_GET['remove_faculty'])) {
    $_SESSION['faculty_redirect'] = $_SERVER['QUERY_STRING'];
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
            font-family: 'Segoe UI';
            background: linear-gradient(135deg, #32465fff, #566fdcff);
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
        .heading{
            background-color: #00bfff;
            padding:0;
            width: 1000px;
            height: 60px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 9px 10px #1b2c46ff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border: 5px solid #1b2c46ff;
        }
        .heading h2{
            margin: 0;
            padding: 0;
            font-size: 35px;
            color: #3f043eff;
        }
        .notice {
           background-color: #1b2c46ff;
            padding:0;
            width: 800px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 9px 10px rgba(250, 2, 2, 0.8);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .notice h1{
            color: #00bfff;
        }
        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }
        .cards a button{
            color: #16c3fdff;
            font-size: 15px;
        }
        .card button {
            background-color: #1b2c46ff;
            padding: 30px;
            width: 230px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.5s, box-shadow 0.3s;
            box-shadow: 0 4px 10px rgba(216, 254, 4, 0.99);
            text-decoration: none;
            color: #2c3e50;
            font-size: 13px;
            font-weight: 500;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
       .cards a :hover {
        box-shadow: 0 10px 14px rgba(4, 216, 254, 0.99);
       }
        .card span {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }
        .sidebar {
            width: 280px;
            background: linear-gradient(145deg, #1a273a, #22364d);
            color: white;
            padding: 30px;
            min-height: 100vh;
            box-shadow: 8px 8px 15px #141c28, -8px -8px 15px #22344c;
        }
        .sidebar h2 {
            text-align: center;
            color: #00bfff;
            text-shadow: 1px 1px 3px black;
        }
        .sidebar a {
            text-decoration: none;
        }
        .sidebar button {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: none;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: inset -2px -2px 5px rgba(255,255,255,0.1), 
                        inset 2px 2px 8px rgba(0,0,0,0.4);
            background-color: #2c3e50;
        }
        .sidebar button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.3);
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #f4f4f4;
            min-height: 100vh;
            overflow-y: auto;
        }
        .flex-container {
            display: flex;
            flex-direction: row;
            height: 100vh;
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['id'])): ?>
    <!-- Admin Login Form -->
    <div class="container">
        <h1>SKST University Admin Login</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="number" name="id" placeholder="Enter your Email" required autofocus />
            <input type="password" name="password" placeholder="Enter your Password" required />
            <button type="submit" name="login">Login</button>
        </form>
        <a href="index.html" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
    </div>

<?php else: ?>
    <?php if (isset($_GET['biodata'])): ?>
        <!-- Admin Biodata View -->
        <?php
        $adminid = $_SESSION['id'];
        $stmt = $conn->prepare("SELECT full_name, username, password, email, phone FROM admin_users WHERE id = ?");
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
                    <a href="administration.php?info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No biodata found for your ID.</p>
                    <a href="administration.php?info=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>
    
    <?php elseif (isset($_GET['faculty_info'])): ?>
        <!-- Faculty Login Form - Only shown when faculty_info is clicked -->
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
    
    <?php elseif (isset($_SESSION['faculty_id']) || isset($_GET['facultylogin'])): ?>
        <!-- Faculty Dashboard - Shown after successful login -->
        <div class="flex-container">
            <!-- Left Sidebar -->
            <div class="sidebar">
                <h2>SKST FACULTY PORTAL</h2>
                <div style="display: flex; flex-direction: column; gap: 17px; margin-top: 30px;">
                    <a href="?facultylogin=true&faculty_biodata=true">
                        <button style="background-color: <?= isset($_GET['faculty_biodata']) ? '#3498db' : '#2c3e50' ?>;">
                            <span style="margin-right: 10px;">👤</span>View Biodata
                        </button>
                    </a>
                    <a href="?facultylogin=true&add_faculty=true">
                        <button style="background-color: <?= isset($_GET['add_faculty']) ? '#3498db' : '#2c3e50' ?>;">
                            <span style="margin-right: 10px;">➕</span>Add Faculty
                        </button>
                    </a>
                    <a href="?facultylogin=true&edit_faculty_biodata=true">
                        <button style="background-color: <?= isset($_GET['edit_faculty_biodata']) ? '#3498db' : '#2c3e50' ?>;">
                            <span style="margin-right: 10px;">✏️</span>Edit Biodata
                        </button>
                    </a>
                    <a href="?facultylogin=true&remove_faculty=true">
                        <button style="background-color: <?= isset($_GET['remove_faculty']) ? '#3498db' : '#2c3e50' ?>;">
                            <span style="margin-right: 10px;">🗑️</span>Remove Faculty
                        </button>
                    </a>
                    <a href="administration.php">
                        <button>
                            <span style="margin-right: 10px;">🔙</span>Back to Dashboard
                        </button>
                    </a>
                </div>
            </div>

            <!-- Right Content Area -->
            <div class="main-content">
                <?php if (isset($_GET['faculty_biodata'])): ?>
                    <!-- Faculty Biodata Content -->
                    <?php
                    $faculty_id = $_SESSION['faculty_id'];
                    $stmt = $conn->prepare("SELECT faculty_id, name, email, password, department, address, phone, room_number, salary FROM faculty WHERE faculty_id = ?");
                    $stmt->bind_param("i", $faculty_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $biodata = $result->fetch_assoc();
                    ?>
                    <div class="routine-page">
                        <h2>Faculty Biodata</h2>
                        <?php if ($biodata): ?>
                            <div class="container">
                                <p><strong>ID:</strong> <?= htmlspecialchars($biodata['faculty_id']) ?></p>
                                <p><strong>Name:</strong> <?= htmlspecialchars($biodata['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($biodata['email']) ?></p>
                                <p><strong>Password:</strong> <?= htmlspecialchars($biodata['password']) ?></p>
                                <p><strong>Department:</strong> <?= htmlspecialchars($biodata['department']) ?></p>
                                <p><strong>Address:</strong> <?= htmlspecialchars($biodata['address']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($biodata['phone']) ?></p>
                                <p><strong>Room Number:</strong> <?= htmlspecialchars($biodata['room_number']) ?></p>
                                <p><strong>Salary:</strong> <?= htmlspecialchars($biodata['salary']) ?></p>
                                <a href="administration.php?facultylogin=true" type="back"><button><span>🔙</span>Back</button></a>
                            </div>
                        <?php else: ?>
                            <div class="container">
                                <p>No biodata found for your ID.</p>
                                <a href="administration.php?facultylogin=true" type="back"><button><span>🔙</span>Back</button></a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif (isset($_GET['add_faculty'])): ?>
                    <!-- Add Faculty Content -->
                    <?php
                    $message = "";
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faculty'])) {
                        $faculty_id = $_POST['faculty_id'];
                        $name = $_POST['name'];
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        $department = $_POST['department'];
                        $address = $_POST['address'];
                        $phone = $_POST['phone'];
                        $room_number = $_POST['room_number'];
                        $salary = $_POST['salary'];

                        $check_stmt = $conn->prepare("SELECT * FROM faculty WHERE faculty_id = ? OR email = ?");
                        $check_stmt->bind_param("ss", $faculty_id, $email);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();

                        if ($check_result->num_rows > 0) {
                            $message = "<span style='color: red;'>❌ Faculty ID or Email already exists.</span>";
                        } else {
                            $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, name, email, password, department, address, phone, room_number, salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssssss", $faculty_id, $name, $email, $password, $department, $address, $phone, $room_number, $salary);

                            if ($stmt->execute()) {
                                $message = "<span style='color: green;'>✅ Faculty added successfully.</span>";
                            } else {
                                $message = "<span style='color: red;'>❌ Error adding faculty.</span>";
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
                                <div style="display: flex; align-items: center; gap: 12px; margin-top: 10px;">
                                    <button type="submit" name="add_faculty">➕ Add Faculty</button>
                                    <?= $message ?>
                                </div>
                            </form>
                            <a href="administration.php?facultylogin=true" type="back"><button><span>🔙</span>Back</button></a>
                        </div>
                    </div>

                <?php elseif (isset($_GET['edit_faculty_biodata'])): ?>
                    <!-- Edit Faculty Biodata -->
                    <?php
                    $faculty_id = $_SESSION['faculty_id'];
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
                        $updated_faculty_id = $_POST['faculty_id'];
                        $name = $_POST['name'];
                        $email = $_POST['email'];
                        $password = $_POST['password'];
                        $department = $_POST['department'];
                        $address = $_POST['address'];
                        $phone = $_POST['phone'];
                        $room_number = $_POST['room_number'];
                        $salary = $_POST['salary'];

                        $stmt = $conn->prepare("UPDATE faculty SET faculty_id=?, name=?, email=?, password=?, department=?, address=?, phone=?, room_number=?, salary=? WHERE faculty_id=?");
                        $stmt->bind_param("ssssssssss", $updated_faculty_id, $name, $email, $password, $department, $address, $phone, $room_number, $salary, $faculty_id);

                        if ($stmt->execute()) {
                            $_SESSION['faculty_id'] = $updated_faculty_id;
                            echo "<script>alert('Biodata updated successfully.'); window.location='?facultylogin=true&edit_faculty_biodata=true';</script>";
                        } else {
                            echo "<script>alert('Failed to update biodata.');</script>";
                        }
                    }

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
                                <a href="administration.php?facultylogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                            </form>
                        </div>
                    </div>

                <?php elseif (isset($_GET['remove_faculty'])): ?>
                    <!-- Remove Faculty -->
                    <?php
                    $faculty_id = $_SESSION['faculty_id'];
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faculty'])) {
                        $stmt = $conn->prepare("DELETE FROM faculty WHERE faculty_id = ?");
                        $stmt->bind_param("s", $faculty_id);
                        if ($stmt->execute()) {
                            session_destroy();
                            echo "<script>alert('Faculty removed successfully.'); window.location='administration.php';</script>";
                        } else {
                            echo "<script>alert('Error removing faculty.');</script>";
                        }
                    }

                    $stmt = $conn->prepare("SELECT faculty_id, name FROM faculty WHERE faculty_id = ?");
                    $stmt->bind_param("s", $faculty_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $data = $result->fetch_assoc();
                    ?>
                    <div class="routine-page">
                        <h2>Confirm Remove Faculty</h2>
                        <?php if ($data): ?>
                            <div class="container">
                                <p><strong>ID:</strong> <?= htmlspecialchars($data['faculty_id']) ?></p>
                                <p><strong>Name:</strong> <?= htmlspecialchars($data['name']) ?></p>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this faculty?');">
                                    <button type="submit" name="delete_faculty" style="background-color: #e74c3c; color: white;">
                                        🗑️ Delete Faculty
                                    </button>
                                </form>
                                <a href="administration.php?facultylogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                            </div>
                        <?php else: ?>
                            <div class="container">
                                <p>No faculty found for deletion.</p>
                                <a href="administration.php?facultylogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Default Faculty Dashboard -->
                    <div class="routine-page">
                        <h2>Welcome <?= isset($_SESSION['faculty_name']) ? htmlspecialchars($_SESSION['faculty_name']) : 'Faculty' ?></h2>
                        <p>Select an action from the left menu.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif (isset($_GET['student_Intro'])): ?>
        <!-- Student Login Form -->
        <div class="container">
            <h1>SKST University Student Login</h1>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="number" name="id" placeholder="Enter Student ID" required autofocus />
                <button type="submit" name="studentlogin">Login</button>
            </form>
            <a href="administration.php" type="back"><button><span>🔙</span>Back to Dashboard</button></a>
        </div>

    <?php elseif (isset($_GET['studentlogin'])): ?>
        <!-- Student Dashboard -->
        <div class="routine-page">
            <h2>Student Information</h2>
            <div class="cards">
                <a href="?student_biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?edit_student_biodata=true" class="card"><button><span>✏️</span>Edit Biodata</button></a>
                <a href="?remove_student=true" class="card"><button><span>⚠️</span>Remove Student</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>

    <?php elseif (isset($_GET['student_biodata'])): ?>
        <!-- Student Biodata View -->
        <?php
        $student_id = $_SESSION['student_id'];
        $stmt = $conn->prepare("SELECT * FROM student_registration WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $biodata = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Student Biodata</h2>
            <?php if ($biodata): ?>
                <div class="container">
                    <p><strong>ID:</strong> <?= htmlspecialchars($biodata['id']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($biodata['first_name'] . ' ' . $biodata['last_name']) ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($biodata['date_of_birth']) ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($biodata['gender']) ?></p>
                    <p><strong>Blood Group:</strong> <?= htmlspecialchars($biodata['blood_group']) ?></p>
                    <p><strong>Nationality:</strong> <?= htmlspecialchars($biodata['nationality']) ?></p>
                    <p><strong>Religion:</strong> <?= htmlspecialchars($biodata['religion']) ?></p>
                    <p><strong>Father's Name:</strong> <?= htmlspecialchars($biodata['father_name']) ?></p>
                    <p><strong>Mother's Name:</strong> <?= htmlspecialchars($biodata['mother_name']) ?></p>
                    <p><strong>Guardian Phone:</strong> <?= htmlspecialchars($biodata['guardian_phone']) ?></p>
                    <p><strong>Student Phone:</strong> <?= htmlspecialchars($biodata['student_phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($biodata['email']) ?></p>
                    <p><strong>Present Address:</strong> <?= nl2br(htmlspecialchars($biodata['present_address'])) ?></p>
                    <p><strong>Permanent Address:</strong> <?= nl2br(htmlspecialchars($biodata['permanent_address'])) ?></p>
                    <p><strong>Department:</strong> <?= htmlspecialchars($biodata['department']) ?></p>
                    <?php if (!empty($biodata['photo_path'])): ?>
                        <p><strong>Photo:</strong><br>
                            <img src="uploads/<?= htmlspecialchars($biodata['photo_path']) ?>" width="150" style="border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.2);" alt="Student Photo" />
                        </p>
                    <?php else: ?>
                        <p><strong>Photo:</strong> No photo uploaded.</p>
                    <?php endif; ?>
                    <a href="administration.php?studentlogin=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No biodata found for your ID.</p>
                    <a href="administration.php?studentlogin=true" type="back"><button><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (isset($_GET['edit_student_biodata'])): ?>
        <!-- Edit Student Biodata -->
        <?php
        $student_id = $_SESSION['student_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $father_name = $_POST['father_name'];
            $mother_name = $_POST['mother_name'];
            $date_of_birth = $_POST['date_of_birth'];
            $guardian_phone = $_POST['guardian_phone'];
            $student_phone = $_POST['student_phone'];
            $email = $_POST['email'];
            $present_address = $_POST['present_address'];
            $permanent_address = $_POST['permanent_address'];
            $department = $_POST['department'];
            $blood_group = $_POST['blood_group'];
            $photo_path = $_POST['photo_path'];

            $stmt = $conn->prepare("UPDATE student_registration SET first_name=?, last_name=?, father_name=?, mother_name=?, date_of_birth=?, guardian_phone=?, student_phone=?, email=?, present_address=?, permanent_address=?, department=?, blood_group=?, photo_path=? WHERE id=?");
            $stmt->bind_param("sssssssssssssi", $first_name, $last_name, $father_name, $mother_name, $date_of_birth, $guardian_phone, $student_phone, $email, $present_address, $permanent_address, $department, $blood_group, $photo_path, $student_id);

            if ($stmt->execute()) {
                echo "<script>alert('Biodata updated successfully.'); window.location='?student_biodata=true';</script>";
            } else {
                echo "<script>alert('Failed to update biodata.');</script>";
            }
        }

        $stmt = $conn->prepare("SELECT * FROM student_registration WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Edit Biodata</h2>
            <div class="container">
                <form method="POST">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($student_id) ?>">
                    <p><strong>First Name:</strong> <input type="text" name="first_name" value="<?= htmlspecialchars($data['first_name']) ?>" required></p>
                    <p><strong>Last Name:</strong> <input type="text" name="last_name" value="<?= htmlspecialchars($data['last_name']) ?>" required></p>
                    <p><strong>Father's Name:</strong> <input type="text" name="father_name" value="<?= htmlspecialchars($data['father_name']) ?>" required></p>
                    <p><strong>Mother's Name:</strong> <input type="text" name="mother_name" value="<?= htmlspecialchars($data['mother_name']) ?>" required></p>
                    <p><strong>Date of Birth:</strong> <input type="date" name="date_of_birth" value="<?= htmlspecialchars($data['date_of_birth']) ?>" required></p>
                    <p><strong>Guardian Phone:</strong> <input type="text" name="guardian_phone" value="<?= htmlspecialchars($data['guardian_phone']) ?>" required></p>
                    <p><strong>Student Phone:</strong> <input type="text" name="student_phone" value="<?= htmlspecialchars($data['student_phone']) ?>" required></p>
                    <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required></p>
                    <p><strong>Present Address:</strong><br>
                        <textarea name="present_address" required><?= htmlspecialchars($data['present_address']) ?></textarea>
                    </p>
                    <p><strong>Permanent Address:</strong><br>
                        <textarea name="permanent_address" required><?= htmlspecialchars($data['permanent_address']) ?></textarea>
                    </p>
                    <p><strong>Department:</strong> <input type="text" name="department" value="<?= htmlspecialchars($data['department']) ?>" required></p>
                    <p><strong>Blood Group:</strong> <input type="text" name="blood_group" value="<?= htmlspecialchars($data['blood_group']) ?>" required></p>
                    <p><strong>Photo Path:</strong> <input type="text" name="photo_path" value="<?= htmlspecialchars($data['photo_path']) ?>"></p>
                    <button type="submit" name="update">Update</button>
                    <a href="administration.php?studentlogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                </form>
            </div>
        </div>

    <?php elseif (isset($_GET['remove_student'])): ?>
        <!-- Remove Student -->
        <?php
        $student_id = $_SESSION['student_id'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
            $stmt = $conn->prepare("DELETE FROM student_registration WHERE id = ?");
            $stmt->bind_param("i", $student_id);
            if ($stmt->execute()) {
                session_destroy();
                echo "<script>alert('Student removed successfully.'); window.location='administration.php';</script>";
            } else {
                echo "<script>alert('Error removing student.');</script>";
            }
        }

        $stmt = $conn->prepare("SELECT id, first_name, last_name FROM student_registration WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        ?>
        <div class="routine-page">
            <h2>Confirm Remove Student</h2>
            <?php if ($data): ?>
                <div class="container">
                    <p><strong>ID:</strong> <?= htmlspecialchars($data['id']) ?></p>
                    <p><strong>Name:</strong> <?= htmlspecialchars($data['first_name']) . ' ' . htmlspecialchars($data['last_name']) ?></p>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
                        <button type="submit" name="delete_student" style="background-color: #e74c3c; color: white;">
                            🗑️ Delete Student
                        </button>
                    </form>
                    <a href="administration.php?studentlogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                </div>
            <?php else: ?>
                <div class="container">
                    <p>No student found for deletion.</p>
                    <a href="administration.php?studentlogin=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif (isset($_GET['student_info'])): ?>
        <!-- Student Information Dashboard -->
        <div class="routine-page">
            <h2>Student Information</h2>
            <div class="cards">
                <a href="?student_Intro=true" class="card"><button><span>👤</span>Student Intro</button></a>
                <a href="?add_student=true" class="card"><button><span>✏️</span>Add new</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>

    <?php elseif (isset($_GET['add_student'])): ?>
        <!-- Add Student -->
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
            $id = $_POST['id'];
            $password = $_POST['password'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $father_name = $_POST['father_name'];
            $mother_name = $_POST['mother_name'];
            $date_of_birth = $_POST['date_of_birth'];
            $guardian_phone = $_POST['guardian_phone'];
            $student_phone = $_POST['student_phone'];
            $email = $_POST['email'];
            $present_address = $_POST['present_address'];
            $permanent_address = $_POST['permanent_address'];
            $department = $_POST['department'];
            $blood_group = $_POST['blood_group'];
            $photo_path = $_POST['photo_path'];

            $check_stmt = $conn->prepare("SELECT * FROM student_registration WHERE id = ? OR email = ?");
            $check_stmt->bind_param("is", $id, $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                echo "<script>alert('Student ID or Email already exists.');</script>";
            } else {
                $stmt = $conn->prepare("INSERT INTO student_registration (id, password, first_name, last_name, father_name, mother_name, date_of_birth, guardian_phone, student_phone, email, present_address, permanent_address, department, blood_group, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssssssssss", $id, $password, $first_name, $last_name, $father_name, $mother_name, $date_of_birth, $guardian_phone, $student_phone, $email, $present_address, $permanent_address, $department, $blood_group, $photo_path);

                if ($stmt->execute()) {
                    echo "<script>alert('New student added successfully.'); window.location='?student_info=true';</script>";
                } else {
                    echo "<script>alert('Error adding student.');</script>";
                }
            }
        }
        ?>
        <div class="routine-page">
            <h2>Add New Student</h2>
            <div class="container">
                <form method="POST">
                    <input type="number" name="id" placeholder="Student ID" required />
                    <input type="password" name="password" placeholder="Password" required />
                    <input type="text" name="first_name" placeholder="First Name" required />
                    <input type="text" name="last_name" placeholder="Last Name" required />
                    <input type="text" name="father_name" placeholder="Father's Name" required />
                    <input type="text" name="mother_name" placeholder="Mother's Name" required />
                    <input type="date" name="date_of_birth" placeholder="Date of Birth" required />
                    <input type="text" name="guardian_phone" placeholder="Guardian Phone" required />
                    <input type="text" name="student_phone" placeholder="Student Phone" required />
                    <input type="email" name="email" placeholder="Email" required />
                    <textarea name="present_address" placeholder="Present Address" required></textarea>
                    <textarea name="permanent_address" placeholder="Permanent Address" required></textarea>
                    <input type="text" name="department" placeholder="Department" required />
                    <input type="text" name="blood_group" placeholder="Blood Group" required />
                    <input type="text" name="photo_path" placeholder="Photo Path" />
                    <button type="submit" name="add_student">➕ Add Student</button>
                    <a href="administration.php?student_info=true" type="back"><button type="button">🔙 Back</button></a>
                </form>
            </div>
        </div>

    <?php elseif (isset($_GET['info'])): ?>
        <!-- Admin Information Dashboard -->
        <div class="routine-page">
            <h2>Personal Information</h2>
            <div class="cards">
                <a href="?biodata=true" class="card"><button><span>👤</span>View Biodata</button></a>
                <a href="?edit_biodata=true" class="card"><button><span>✏️</span>Edit Biodata</button></a>
                <a href="administration.php" class="card"><button><span>🔙</span>Back to Dashboard</button></a>
            </div>
        </div>

    <?php elseif (isset($_GET['edit_biodata'])): ?>
        <!-- Edit Admin Biodata -->
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
                    <a href="administration.php?info=true" type="back"><button type="button"><span>🔙</span>Back</button></a>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Main Admin Dashboard -->
        <div class="dashboard">
            <div class="cards">
                <div class="heading"><h2>👨‍💼 Welcome <?= htmlspecialchars($_SESSION['name']) ?></h2></div>
                <a href="?info=true" class="card"><button><span>👤</span>Personal Information</button></a>
                <a href="?student_info=true" class="card"><button><span>🎓</span>Manage Students</button></a>
                <a href="?manage_courses=true" class="card"><button><span>📚</span>Manage Courses</button></a>
                <a href="?routine_setup=true" class="card"><button><span>📆</span>Setup Class Routine</button></a>
                <a href="?finance_reports=true" class="card"><button><span>💳</span>Finance Reports</button></a>
                <a href="?faculty_info=true" class="card"><button><span>👨‍🏫</span>Faculty Info</button></a>
                <a href="?manage_employees=true" class="card"><button><span>🧑‍💼</span>Manage Employees</button></a>
                <a href="?logout=true" class="card"><button><span>🚪</span>Logout</button></a>
                <div class="notice"><h1><i>Note: Please logout after managing the system</i></h1></div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>