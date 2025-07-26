<!DOCTYPE html>
<html>
<head>
    <title>SKST Faculty Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
        }
        .sidebar {
            width: 280px;
            background-color: #1b2635;
            color: white;
            padding: 30px;
            min-height: 100vh;
        }
        .sidebar h2 {
            text-align: center;
            color: #00bfff;
        }
        .sidebar a {
            text-decoration: none;
        }
        .sidebar button {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            background-color: #00bfff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.1s;
        }
        .sidebar button:hover {
            transform: scale(1.03);
            background-color: #0099cc;
        }
        .main-content {
            flex: 1;
            padding: 40px;
            background-color: #f4f4f4;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SKST FACULTY PORTAL</h2>
        <a href="?view_biodata=true"><button>👤 View Biodata</button></a>
        <a href="?add_faculty=true"><button>➕ Add Faculty</button></a>
        <a href="?edit_faculty_biodata=true"><button>✏️ Edit Biodata</button></a>
        <a href="?remove_faculty=true"><button>🗑️ Remove Faculty</button></a>
        <a href="faculty.php"><button>🔙 Back</button></a>
    </div>

    <div class="main-content">
        <?php
        // DB connection
        $conn = new mysqli("localhost", "root", "", "skst_university");
        if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

        // Handle Add Faculty (inline here or include)
        if (isset($_GET['add_faculty'])) {
            include 'add_faculty_section.php'; // or write inline code here
        }

        elseif (isset($_GET['edit_faculty_biodata'])) {
            include 'edit_faculty_section.php';
        }

        elseif (isset($_GET['remove_faculty'])) {
            include 'remove_faculty_section.php';
        }

        elseif (isset($_GET['view_biodata'])) {
            include 'view_biodata_section.php';
        }

        else {
            echo "<h2>Welcome to the Faculty Dashboard</h2><p>Select an action from the left menu.</p>";
        }
        ?>
    </div>
</body>
</html>
