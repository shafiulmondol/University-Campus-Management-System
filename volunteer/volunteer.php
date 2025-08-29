<?php
session_start();

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: volunteer.php");
    exit();
}

// Login check
$error = "";
$success_message = "";
$signup_message = "";

// Handle Volunteer Registration Form
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $id = intval($_POST['id']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $affiliation = $conn->real_escape_string($_POST['affiliation']);
    $availability = $conn->real_escape_string($_POST['availability']);
    $skills = $conn->real_escape_string($_POST['skills']);

    $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
    $interests_str = implode(', ', $interests);

    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if ID or email already exists
    $check = $conn->prepare("SELECT id FROM volunteers WHERE id = ? OR email = ?");
    $check->bind_param("is", $id, $email);
    $check->execute();
    $check_result = $check->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "ID or Email already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO volunteers (id, name, email, phone, affiliation, availability, skills, interests, password, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issssssss", $id, $name, $email, $phone, $affiliation, $availability, $skills, $interests_str, $password);
        
        if ($stmt->execute()) {
            $success_message = "Volunteer registration successful! You can login now.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Handle Login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $id = intval($_POST['id']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM volunteers WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $id;
            $_SESSION['name'] = $row['name'];
            header("Location: volunteer.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "ID not found.";
    }
}

// Handle Sign-Up to Opportunity
if (isset($_GET['signup']) && isset($_SESSION['id'])) {
    $opp_id = intval($_GET['signup']);
    $vol_id = intval($_SESSION['id']);

    // Check if already signed up
    $check = $conn->prepare("SELECT * FROM volunteer_signups WHERE volunteer_id=? AND opportunity_id=?");
    $check->bind_param("ii", $vol_id, $opp_id);
    $check->execute();
    $check_res = $check->get_result();
    
    if ($check_res->num_rows > 0) {
        $signup_message = "You have already signed up for this opportunity!";
    } else {
        $stmt = $conn->prepare("INSERT INTO volunteer_signups (volunteer_id, opportunity_id, signup_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $vol_id, $opp_id);
        
        if ($stmt->execute()) {
            $signup_message = "Successfully signed up for the opportunity!";
        } else {
            $signup_message = "Error signing up: " . $conn->error;
        }
    }
}

// Fetch Volunteer Opportunities
$opportunities_result = $conn->query("SELECT * FROM volunteer_opportunities ORDER BY date DESC");

// Fetch Volunteer Hours
$total_hours = 0;
$hours_result = null;
if(isset($_SESSION['id'])){
    $volunteer_id = intval($_SESSION['id']);
    $hours_result = $conn->query("SELECT * FROM volunteer_hours WHERE volunteer_id = $volunteer_id");
    if ($hours_result && $hours_result->num_rows > 0) {
        while($row = $hours_result->fetch_assoc()) {
            $total_hours += $row['hours'];
        }
        $hours_result->data_seek(0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Volunteer Dashboard | University</title>
  <link rel="icon" href="../picture/SKST.png" type="image/png" />

<style>
   body { 
    font-family: Arial, sans-serif;
     margin:0; 
     padding:0; 
     background:#f4f4f9; 
     }
        header { 
            background:#2c3e50; 
            color:#fff; 
            padding:15px; 
            text-align:center; 
            }
        nav a { 
            color:#fff; 
            margin:0 15px; 
            text-decoration:none; 
            font-weight:bold; 
            }
        section { 
            padding:40px; 
            text-align:center; 
            }
        form { 
            max-width:400px; 
            margin:20px auto; 
            background:#fff; 
            padding:20px; 
            border-radius:10px; 
            box-shadow:0 2px 6px rgba(0,0,0,0.1); 
            }
        form h2 { 
            margin-bottom:15px; 
            }
        .form-group { 
            margin-bottom:15px; 
            text-align:left; 
            }
        label { 
            display:block; 
            -bottom:5px; 
            }
        input { 
            width:100%; 
            :10px; 
            border:1px solid #ccc; border-radius:5px; 
            }
        button { 
            padding:10px 15px;
             background:#2c3e50; 
             color:#fff; 
             :none; 
             border-radius:5px; 
             cursor:pointer; 
             }
        button:hover { 
            background:#1a252f; 
            }
        .message { 
            color:red; 
            font-weight:bold; 
            text-align:center; 
            margin:10px;
            }
        footer { 
            background:#2c3e50; 
            color:#fff; 
            text-align:center; 
            padding:10px; 
            margin-top:40px; 
            }
body{
    font-family:sans-serif;
    background:#f4f6fb;
    margin:0;padding:0;
    }
header{
    background:#4361ee;
    color:white;
    padding:15px;
    text-align:center;
    }
.container{
    width:90%;
    max-width:1000px;
    margin:30px auto;
    }
.cards{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    justify-content:center;
    }
.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 3px 10px rgba(0,0,0,0.1);
    text-align:center;
    transition:0.3s;
    cursor:pointer;
    }
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
    }
.card span{
    font-size:30px;
    display:block;
    margin-bottom:10px;
    }
input,textarea,select,button{
    padding:10px;
    margin:8px 0;
    border-radius:6px;
    border:1px solid #ccc;
    width:100%;
    }
button{
    background:#4361ee;
    color:white;
    border:none;
    cursor:pointer;
    }
button:hover{
    background:#3f37c9;
    }
.success{
    background:#d4edda;
    color:#155724;
    padding:10px;
    border-radius:5px;
    margin-bottom:15px;
    }
.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    border-radius:5px;
    margin-bottom:15px;
    }
table{
    width:100%;
    border-collapse:collapse;
    }
table,th,td{
    border:1px solid #ccc;
    }
th,td{
    padding:8px;
    text-align:center;
    }
</style>
</head>
<body>

<header>
    <h1>University Volunteer System</h1>
    <nav>
   <a href="../index.html" class="home-button">
          <i class="fas fa-home"></i> Home
        </a>
        <a href="#opportunities">Opportunities</a>
        <?php if(isset($_SESSION['id'])): ?>
            <a href="#myhours">My Hours</a>
            <a href="#profile">Profile</a>
            <a href="?logout=true">Logout</a>
        <?php else: ?>
            <a href="#signup">Sign Up</a>
            <a href="#signin">Sign In</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <!-- Hero / Home Section -->
    <section id="home" class="hero">
        <h2>Make a Difference Today</h2>
        <p>Join our volunteer program and be part of positive change at SKST University.</p>
    </section>

    <?php if(!isset($_SESSION['id'])): ?>
        <!-- Sign In Form -->
        <section id="signin">
            <h2>Volunteer Login</h2>
            <?php if($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
            <form method="POST">
                <input type="number" name="id" placeholder="Enter your ID" required>
                <input type="password" name="password" placeholder="Enter your Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </section>

        <!-- Registration Form -->
        <section id="signup">
            <h2>Register as New Volunteer</h2>
            <?php if($success_message): ?><div class="success"><?=htmlspecialchars($success_message)?></div><?php endif; ?>
            <form method="POST">
                <input type="number" name="id" placeholder="Your ID" required>
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone">
                <input type="text" name="affiliation" placeholder="Affiliation">
                <input type="text" name="availability" placeholder="Availability">
                <input type="text" name="skills" placeholder="Skills/Certifications">
                <input type="password" name="password" placeholder="Password" required>
                <label>Interests:</label><br>
                <input type="checkbox" name="interests[]" value="Community Service"> Community Service
                <input type="checkbox" name="interests[]" value="Events"> Events
                <input type="checkbox" name="interests[]" value="Orientation"> Orientation
                <button type="submit" name="register">Register</button>
            </form>
        </section>

    <?php else: ?>
        <h2>Welcome <?=htmlspecialchars($_SESSION['name'])?></h2>
        <?php if($signup_message): ?><div class="success"><?=htmlspecialchars($signup_message)?></div><?php endif; ?>
        <div class="cards">
            <a href="#opportunities" class="card"><span>📋</span>Opportunities</a>
            <a href="#myhours" class="card"><span>⏱️</span>My Hours</a>
            <a href="#profile" class="card"><span>👤</span>Profile</a>
            <a href="?logout=true" class="card" style="background:#e74c3c;color:white;"><span>🚪</span>Logout</a>
        </div>

        <section id="opportunities">
            <h2>Volunteer Opportunities</h2>
            <div class="cards">
            <?php if($opportunities_result && $opportunities_result->num_rows>0):
                while($opp = $opportunities_result->fetch_assoc()): ?>
                <div class="card">
                    <span>📌</span>
                    <strong><?=htmlspecialchars($opp['title'])?></strong><br>
                    <?=htmlspecialchars($opp['description'])?><br>
                    Date: <?=htmlspecialchars($opp['date'])?><br>
                    <a href="?signup=<?=$opp['id']?>"><button>Sign Up</button></a>
                </div>
                <?php endwhile; else: ?>
                <p>No opportunities available.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="myhours">
            <h2>My Volunteer Hours</h2>
            <p>Total Hours: <?=intval($total_hours)?></p>
            <?php if($hours_result && $hours_result->num_rows>0): ?>
            <table>
                <tr><th>Event</th><th>Date</th><th>Hours</th></tr>
                <?php while($h=$hours_result->fetch_assoc()): ?>
                <tr>
                    <td><?=htmlspecialchars($h['event_name'])?></td>
                    <td><?=htmlspecialchars($h['event_date'])?></td>
                    <td><?=htmlspecialchars($h['hours'])?></td>
                </tr>
                <?php endwhile; ?>
            </table>
            <?php else: ?>
            <p>No hours recorded yet.</p>
            <?php endif; ?>
        </section>

        <section id="profile">
            <h2>Profile / Biodata</h2>
            <?php
            $stmt = $conn->prepare("SELECT * FROM volunteers WHERE id=?");
            $stmt->bind_param("i", $_SESSION['id']);
            $stmt->execute();
            $res = $stmt->get_result();
            $profile = $res->fetch_assoc();
            ?>
            <p><strong>ID:</strong> <?=htmlspecialchars($profile['id'])?></p>
            <p><strong>Name:</strong> <?=htmlspecialchars($profile['name'])?></p>
            <p><strong>Email:</strong> <?=htmlspecialchars($profile['email'])?></p>
            <p><strong>Phone:</strong> <?=htmlspecialchars($profile['phone'])?></p>
            <p><strong>Affiliation:</strong> <?=htmlspecialchars($profile['affiliation'])?></p>
            <p><strong>Availability:</strong> <?=htmlspecialchars($profile['availability'])?></p>
            <p><strong>Skills:</strong> <?=htmlspecialchars($profile['skills'])?></p>
            <p><strong>Interests:</strong> <?=htmlspecialchars($profile['interests'])?></p>
        </section>

    <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>