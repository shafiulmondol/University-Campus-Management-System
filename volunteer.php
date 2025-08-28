<?php
session_start();

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "skst_university";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql)) {
    // Select the database
    $conn->select_db($database);
    
    // Create volunteers table if it doesn't exist (matching your SQL structure)
    $sql = "CREATE TABLE IF NOT EXISTS volunteers (
        id INT(11) NOT NULL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) DEFAULT NULL,
        affiliation ENUM('undergrad','grad','faculty','staff','alumni') NOT NULL,
        department VARCHAR(100) DEFAULT NULL,
        availability TEXT DEFAULT NULL,
        skills TEXT DEFAULT NULL,
        interests TEXT DEFAULT NULL,
        registration_date DATETIME NOT NULL,
        FOREIGN KEY (id) REFERENCES student_registration(id)
    )";
    
    if (!$conn->query($sql)) {
        // If foreign key constraint fails, create without it
        $sql = "CREATE TABLE IF NOT EXISTS volunteers (
            id INT(11) NOT NULL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20) DEFAULT NULL,
            affiliation ENUM('undergrad','grad','faculty','staff','alumni') NOT NULL,
            department VARCHAR(100) DEFAULT NULL,
            availability TEXT DEFAULT NULL,
            skills TEXT DEFAULT NULL,
            interests TEXT DEFAULT NULL,
            registration_date DATETIME NOT NULL
        )";
        
        if (!$conn->query($sql)) {
            die("Error creating table: " . $conn->error);
        }
    }
} else {
    die("Error creating database: " . $conn->error);
}

// Handle form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_volunteer'])) {
    // Get form data
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $affiliation = $_POST['affiliation'];
    $department = trim($_POST['department']);
    $availability = trim($_POST['availability']);
    $skills = trim($_POST['skills']);
    $interests = isset($_POST['interests']) ? implode(", ", $_POST['interests']) : "";
    $registration_date = date("Y-m-d H:i:s");

    // Validate required fields
    if (!empty($id) && !empty($name) && !empty($email) && !empty($affiliation) && !empty($availability)) {
        // Check if ID already exists
        $check_id = $conn->prepare("SELECT id FROM volunteers WHERE id = ?");
        $check_id->bind_param("i", $id);
        $check_id->execute();
        $check_id->store_result();
        
        if ($check_id->num_rows > 0) {
            $error_message = "This ID is already registered.";
        } else {
            // Insert data into database
            $stmt = $conn->prepare("INSERT INTO volunteers (id, name, email, phone, affiliation, department, availability, skills, interests, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssss", $id, $name, $email, $phone, $affiliation, $department, $availability, $skills, $interests, $registration_date);
            
            if ($stmt->execute()) {
                $success_message = "Thank you for registering as a volunteer! We'll contact you soon.";
                // Clear form fields
                $_POST = array();
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_id->close();
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch existing volunteers
$volunteers = array();
$result = $conn->query("SELECT * FROM volunteers ORDER BY registration_date DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $volunteers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management System | SKST University</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --success: #27ae60;
            --warning: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --text: #2c3e50;
            --text-light: #7f8c8d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 0 0 10px 10px;
            margin-bottom: 30px;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .logo i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        
        nav a:hover, nav a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80') no-repeat center center/cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            border-radius: 15px;
            margin-bottom: 40px;
            position: relative;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            padding: 0 2rem;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            background-color: var(--accent);
            color: white;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid white;
            margin-left: 1rem;
        }
        
        .btn-outline:hover {
            background-color: white;
            color: var(--primary);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            padding-top: 20px;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            color: var(--primary);
            display: inline-block;
            padding-bottom: 0.5rem;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background-color: var(--accent);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .opportunities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .opportunity-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .opportunity-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .card-img {
            height: 180px;
            background-color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .card-content {
            padding: 1.8rem;
        }
        
        .card-content h3 {
            font-size: 1.4rem;
            margin-bottom: 0.8rem;
            color: var(--primary);
        }
        
        .card-meta {
            display: flex;
            align-items: center;
            margin-bottom: 1.2rem;
            color: var(--text-light);
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.9rem;
        }
        
        .card-meta span {
            display: flex;
            align-items: center;
        }
        
        .card-meta i {
            margin-right: 0.5rem;
            color: var(--accent);
        }
        
        .card-content p {
            margin-bottom: 1.5rem;
            color: var(--text-light);
            line-height: 1.7;
        }
        
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.2rem;
            border-top: 1px solid #e9ecef;
        }
        
        .tag {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            padding: 0.4rem 0.9rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .volunteer-form-container {
            background-color: white;
            border-radius: 12px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 4rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 1.8rem;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 1rem;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            font-size: 1rem;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: white;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .checkbox-group {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.2rem;
        }
        
        .checkbox-item {
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
        }
        
        .checkbox-item input {
            width: auto;
            margin-right: 0.8rem;
        }
        
        .checkbox-item label {
            margin-bottom: 0;
            font-weight: normal;
        }
        
        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            font-size: 1.1rem;
            margin-top: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .volunteers-table {
            background-color: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 4rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        
        th, td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--primary);
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 2.5rem 0;
            text-align: center;
            margin-top: 4rem;
            border-radius: 10px 10px 0 0;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            nav {
                margin-top: 1rem;
            }
            
            nav a {
                margin: 0 0.5rem;
            }
            
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .opportunities-grid {
                grid-template-columns: 1fr;
            }
            
            .checkbox-group {
                grid-template-columns: 1fr;
            }
            
            .volunteer-form-container {
                padding: 2rem;
            }
            
            .card-content {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-hands-helping"></i>
                <span>SKST University Volunteer System</span>
            </div>
            <nav>
                <a href="#" class="active"><i class="fas fa-home"></i> Home</a>
                <a href="#"><i class="fas fa-hand-holding-heart"></i> Volunteer</a>
                <a href="#"><i class="fas fa-info-circle"></i> About</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <section class="hero">
            <div class="hero-content">
                <h1>Make a Difference at SKST University</h1>
                <p>Join our community of volunteers and contribute to making our university a better place for everyone</p>
                <a href="#register" class="btn">Register Now</a>
                <a href="#opportunities" class="btn btn-outline">Browse Opportunities</a>
            </div>
        </section>

        <?php if($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <section id="opportunities">
            <div class="section-title">
                <h2>Current Volunteer Opportunities</h2>
                <p>Join one of our upcoming volunteer events</p>
            </div>
            
            <div class="opportunities-grid">
                <div class="opportunity-card">
                    <div class="card-img">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="card-content">
                        <h3>Campus Cleanup Day</h3>
                        <div class="card-meta">
                            <span><i class="far fa-calendar-alt"></i> Oct 15, 2023</span>
                            <span><i class="far fa-clock"></i> 9:00 AM - 12:00 PM</span>
                            <span><i class="fas fa-map-marker-alt"></i> Main Quad</span>
                        </div>
                        <p>Help keep our campus beautiful by participating in our biannual cleanup event. Gloves and supplies will be provided.</p>
                        <div class="card-footer">
                            <span class="tag">Environment</span>
                            <button class="btn">Sign Up</button>
                        </div>
                    </div>
                </div>
                
                <div class="opportunity-card">
                    <div class="card-img">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="card-content">
                        <h3>New Student Orientation</h3>
                        <div class="card-meta">
                            <span><i class="far fa-calendar-alt"></i> Aug 25-27, 2023</span>
                            <span><i class="far fa-clock"></i> Various Times</span>
                            <span><i class="fas fa-map-marker-alt"></i> Student Center</span>
                        </div>
                        <p>Welcome new students to campus by serving as an orientation leader. Help them navigate their first days at university.</p>
                        <div class="card-footer">
                            <span class="tag">Student Life</span>
                            <button class="btn">Sign Up</button>
                        </div>
                    </div>
                </div>
                
                <div class="opportunity-card">
                    <div class="card-img">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="card-content">
                        <h3>Library Assistance Program</h3>
                        <div class="card-meta">
                            <span><i class="far fa-calendar-alt"></i> Every Wednesday</span>
                            <span><i class="far fa-clock"></i> 2:00 PM - 5:00 PM</span>
                            <span><i class="fas fa-map-marker-alt"></i> University Library</span>
                        </div>
                        <p>Assist librarians with organizing materials and helping students find resources. Perfect for those who love books and quiet environments.</p>
                        <div class="card-footer">
                            <span class="tag">Education</span>
                            <button class="btn">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="register">
            <div class="section-title">
                <h2>Register as a Volunteer</h2>
                <p>Join our volunteer community by filling out the form below</p>
            </div>
            
            <div class="volunteer-form-container">
                <form id="volunteerForm" method="POST" action="">
                    <input type="hidden" name="add_volunteer" value="1">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="id">Student ID *</label>
                            <input type="number" id="id" name="id" value="<?php echo isset($_POST['id']) ? htmlspecialchars($_POST['id']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="affiliation">University Affiliation *</label>
                            <select id="affiliation" name="affiliation" required>
                                <option value="">Select...</option>
                                <option value="undergrad" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'undergrad') ? 'selected' : ''; ?>>Undergraduate Student</option>
                                <option value="grad" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'grad') ? 'selected' : ''; ?>>Graduate Student</option>
                                <option value="faculty" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'faculty') ? 'selected' : ''; ?>>Faculty</option>
                                <option value="staff" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                                <option value="alumni" <?php echo (isset($_POST['affiliation']) && $_POST['affiliation'] == 'alumni') ? 'selected' : ''; ?>>Alumni</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Department (if applicable)</label>
                            <input type="text" id="department" name="department" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Areas of Interest (Select all that apply)</label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-events" name="interests[]" value="events" <?php echo (isset($_POST['interests']) && in_array('events', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-events">Campus Events</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-community" name="interests[]" value="community" <?php echo (isset($_POST['interests']) && in_array('community', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-community">Community Service</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-orientation" name="interests[]" value="orientation" <?php echo (isset($_POST['interests']) && in_array('orientation', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-orientation">Student Orientation</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-sustainability" name="interests[]" value="sustainability" <?php echo (isset($_POST['interests']) && in_array('sustainability', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-sustainability">Sustainability Initiatives</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-fundraising" name="interests[]" value="fundraising" <?php echo (isset($_POST['interests']) && in_array('fundraising', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-fundraising">Fundraising</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-tutoring" name="interests[]" value="tutoring" <?php echo (isset($_POST['interests']) && in_array('tutoring', $_POST['interests'])) ? 'checked' : ''; ?>>
                                    <label for="interest-tutoring">Tutoring</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="availability">Availability *</label>
                            <textarea id="availability" name="availability" placeholder="Please describe when you're typically available to volunteer (e.g., weekends, weekday evenings, etc.)" required><?php echo isset($_POST['availability']) ? htmlspecialchars($_POST['availability']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="skills">Skills or Special Qualifications</label>
                            <textarea id="skills" name="skills" placeholder="Any special skills or certifications you have that might be relevant (first aid, languages, technical skills, etc.)"><?php echo isset($_POST['skills']) ? htmlspecialchars($_POST['skills']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn submit-btn">Submit Volunteer Application</button>
                </form>
            </div>
        </section>

        <section id="volunteers">
            <div class="section-title">
                <h2>Registered Volunteers</h2>
                <p>Our amazing volunteers who are making a difference</p>
            </div>
            
            <div class="volunteers-table">
                <?php if (count($volunteers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Affiliation</th>
                                <th>Department</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($volunteers as $volunteer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($volunteer['id']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['name']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                <td>
                                    <?php 
                                    $affiliation = htmlspecialchars($volunteer['affiliation']);
                                    echo ucfirst($affiliation);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($volunteer['department']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($volunteer['registration_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No volunteers registered yet. Be the first to sign up!</p>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 SKST University Volunteer System. All rights reserved.</p>
            <p>Designed with <i class="fas fa-heart" style="color: #e74c3c;"></i> for our university community</p>
        </div>
    </footer>

    <script>
        // Form validation
        document.getElementById('volunteerForm').addEventListener('submit', function(e) {
            let valid = true;
            const id = document.getElementById('id');
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const affiliation = document.getElementById('affiliation');
            const availability = document.getElementById('availability');
            
            // Reset previous error highlights
            [id, name, email, affiliation, availability].forEach(field => {
                field.style.borderColor = '#e1e8ed';
            });
            
            // Validate required fields
            if (!id.value.trim()) {
                id.style.borderColor = '#e74c3c';
                valid = false;
            }
            
            if (!name.value.trim()) {
                name.style.borderColor = '#e74c3c';
                valid = false;
            }
            
            if (!email.value.trim()) {
                email.style.borderColor = '#e74c3c';
                valid = false;
            }
            
            if (!affiliation.value) {
                affiliation.style.borderColor = '#e74c3c';
                valid = false;
            }
            
            if (!availability.value.trim()) {
                availability.style.borderColor = '#e74c3c';
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>