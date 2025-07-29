<?php
// Database configuration - corrected to your university database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";  // Changed to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists - fixed syntax
$sql = "CREATE TABLE IF NOT EXISTS scholarship_applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(30) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    ssc_gpa FLOAT NOT NULL,
    hsc_gpa FLOAT NOT NULL,
    cgpa FLOAT NOT NULL,
    prev_scholarship INT(3) NOT NULL,
    scholarship_percentage INT(3) NOT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execute table creation
if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Initialize variables
$student_id = $full_name = $gender = $ssc_gpa = $hsc_gpa = $cgpa = $prev_scholarship = '';
$scholarship_percentage = 0;
$criteria = '';
$errors = [];
$result_display = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $student_id = htmlspecialchars($_POST['id']);
    $full_name = htmlspecialchars($_POST['name']);
    $gender = htmlspecialchars($_POST['gender']);
    $ssc_gpa = floatval($_POST['ssc']);
    $hsc_gpa = floatval($_POST['hsc']);
    $cgpa = floatval($_POST['cgpa']);
    $prev_scholarship = intval($_POST['prev_scholarship']);
    
    // Validate inputs
    if (empty($student_id)) $errors[] = "Student ID is required";
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($gender) || !in_array($gender, ['male', 'female'])) $errors[] = "Please select a valid gender";
    if ($ssc_gpa < 0 || $ssc_gpa > 5) $errors[] = "SSC GPA must be between 0.00 and 5.00";
    if ($hsc_gpa < 0 || $hsc_gpa > 5) $errors[] = "HSC GPA must be between 0.00 and 5.00";
    if ($cgpa < -1 || $cgpa > 4) $errors[] = "CGPA must be between 0.00 and 4.00 (or -1 for 1st semester)";
    if ($prev_scholarship < -1 || $prev_scholarship > 100) $errors[] = "Previous scholarship must be between -1 and 100";
    if ($cgpa == -1 && $prev_scholarship != -1) $errors[] = "For 1st semester students, previous scholarship must be -1";
    
    // Calculate scholarship if no errors
    if (empty($errors)) {
        $result_display = true;
        
        if ($cgpa == -1) {
            // First semester student
            if ($gender == 'male') {
                if ($ssc_gpa >= 4.5 && $hsc_gpa >= 4.0) {
                    $scholarship_percentage = 25;
                    $criteria = "Male student with SSC ≥ 4.5 and HSC ≥ 4.0";
                } else {
                    $scholarship_percentage = 0;
                    $criteria = "Does not meet requirements for first semester male students";
                }
            } else {
                if ($ssc_gpa >= 4.0 && $hsc_gpa >= 3.5) {
                    $scholarship_percentage = 25;
                    $criteria = "Female student with SSC ≥ 4.0 and HSC ≥ 3.5";
                } else {
                    $scholarship_percentage = 0;
                    $criteria = "Does not meet requirements for first semester female students";
                }
            }
        } else {
            // Continuing student
            if ($prev_scholarship == 25) {
                if ($cgpa >= 3.5) {
                    $scholarship_percentage = 25;
                    $criteria = "Continued 25% scholarship (CGPA ≥ 3.5)";
                } else {
                    $scholarship_percentage = 0;
                    $criteria = "Discontinued scholarship (CGPA < 3.5)";
                }
            } else if ($prev_scholarship == 50) {
                if ($cgpa >= 3.7) {
                    $scholarship_percentage = 50;
                    $criteria = "Continued 50% scholarship (CGPA ≥ 3.7)";
                } else {
                    $scholarship_percentage = 0;
                    $criteria = "Discontinued scholarship (CGPA < 3.7)";
                }
            } else {
                // No previous scholarship or first time applying
                if ($cgpa >= 3.9) {
                    $scholarship_percentage = 50;
                    $criteria = "Awarded 50% scholarship (CGPA ≥ 3.9)";
                } else if ($cgpa >= 3.7) {
                    $scholarship_percentage = 25;
                    $criteria = "Awarded 25% scholarship (CGPA ≥ 3.7)";
                } else {
                    $scholarship_percentage = 0;
                    $criteria = "CGPA below minimum requirement (3.7) for new scholarship";
                }
            }
        }
        
        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO scholarship_applications (student_id, full_name, gender, ssc_gpa, hsc_gpa, cgpa, prev_scholarship, scholarship_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssddiii", $student_id, $full_name, $gender, $ssc_gpa, $hsc_gpa, $cgpa, $prev_scholarship, $scholarship_percentage);
            if ($stmt->execute()) {
                // Successfully saved
            } else {
                $errors[] = "Error saving application: " . $conn->error;
                $result_display = false;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
            $result_display = false;
        }
    }
}

// Fetch existing applications for display
$applications = [];
$search_id = '';

if (isset($_GET['search'])) {
    $search_id = htmlspecialchars($_GET['search_id']);
    $search_query = "SELECT * FROM scholarship_applications WHERE student_id LIKE ? ORDER BY application_date DESC";
    $stmt = $conn->prepare($search_query);
    $search_param = "%$search_id%";
    $stmt->bind_param("s", $search_param);
} else {
    $search_query = "SELECT * FROM scholarship_applications ORDER BY application_date DESC LIMIT 10";
    $stmt = $conn->prepare($search_query);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Calculator</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --secondary: #8b5cf6;
            --accent: #f97316;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #1e293b;
            --text-medium: #334155;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 30px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
        }
        
        header h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
        }
        
        header p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .emoji {
            font-size: 3rem;
            margin-right: 15px;
            vertical-align: middle;
        }
        
        .calculator-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .form-section, .result-section {
            flex: 1;
            min-width: 300px;
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }
        
        .result-section {
            display: flex;
            flex-direction: column;
        }
        
        .section-title {
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
            color: var(--primary);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.1rem;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            background: white;
            color: var(--text-dark);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        input:focus,
        select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: var(--shadow-md);
        }
        
        button[type="submit"]:hover {
            background: linear-gradient(to right, var(--primary-light), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }
        
        .instructions {
            background: #eff6ff;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
            border-left: 4px solid var(--primary);
        }
        
        .instructions h3 {
            margin-bottom: 12px;
            color: var(--primary);
        }
        
        .instructions ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        
        .instructions li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .result-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .result-placeholder {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin: 30px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .result-placeholder .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--primary-light);
            opacity: 0.7;
        }
        
        .scholarship-result {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 20px 0;
            background: linear-gradient(to right, var(--accent), #ea580c);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .student-info {
            background: #eff6ff;
            border-radius: 15px;
            padding: 20px;
            width: 100%;
            margin-top: 20px;
            text-align: left;
        }
        
        .student-info p {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .student-info strong {
            color: var(--primary);
            font-weight: 700;
        }
        
        .criteria-info {
            margin-top: 30px;
            padding: 20px;
            background: #ecfdf5;
            border-radius: 15px;
            border-left: 4px solid var(--success);
        }
        
        .criteria-info h3 {
            color: var(--success);
            margin-bottom: 15px;
        }
        
        .error {
            color: var(--error);
            background: #fef2f2;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid var(--error);
            text-align: left;
        }
        
        .success {
            color: var(--success);
            background: #ecfdf5;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid var(--success);
            text-align: left;
        }
        
        .records-section {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            width: 100%;
        }
        
        .search-form {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            font-size: 1.1rem;
        }
        
        .search-button {
            padding: 0 30px;
            background: linear-gradient(to right, var(--secondary), var(--primary));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }
        
        .search-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1rem;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
        }
        
        .records-table th, 
        .records-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .records-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        .records-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .records-table tr:hover {
            background-color: #f0f9ff;
        }
        
        .no-records {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-size: 1.2rem;
            background: white;
            border-radius: 12px;
            margin-top: 20px;
        }
        
        .clear-search {
            display: inline-block;
            margin-top: 15px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            background: #eff6ff;
            transition: all 0.2s;
        }
        
        .clear-search:hover {
            background: #dbeafe;
            text-decoration: none;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: var(--text-muted);
            padding: 20px;
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
            width: 100%;
        }
        
        .scholarship-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .badge-0 {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .badge-25 {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-50 {
            background: #d1fae5;
            color: #065f46;
        }
        
        .date-cell {
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .calculator-container {
                flex-direction: column;
            }
            
            header h1 {
                font-size: 2.3rem;
            }
            
            .emoji {
                font-size: 2.5rem;
            }
            
            .records-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><span class="emoji">🎓</span> Scholarship Calculator</h1>
            <p>Calculate your scholarship eligibility based on academic performance</p>
        </header>
        
        <div class="calculator-container">
            <div class="form-section">
                <h2 class="section-title">Student Information</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="id">Student ID</label>
                        <input type="text" name="id" id="id" value="<?php echo $student_id; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" value="<?php echo $full_name; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select name="gender" id="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo $gender == 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $gender == 'female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ssc">SSC GPA (Scale: 5.00)</label>
                        <input type="number" name="ssc" id="ssc" min="0" max="5" step="0.01" value="<?php echo $ssc_gpa; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hsc">HSC GPA (Scale: 5.00)</label>
                        <input type="number" name="hsc" id="hsc" min="0" max="5" step="0.01" value="<?php echo $hsc_gpa; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cgpa">Current Semester CGPA (Scale: 4.00)</label>
                        <input type="number" name="cgpa" id="cgpa" min="-1" max="4" step="0.01" value="<?php echo $cgpa; ?>" required>
                        <small>Note: Enter -1 for 1st semester students</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="prev_scholarship">Previous Scholarship (%)</label>
                        <input type="number" name="prev_scholarship" id="prev_scholarship" min="-1" max="100" step="1" required value="<?php echo $prev_scholarship; ?>">
                        <small>Note: Enter -1 for first-time applicants</small>
                    </div>
                    
                    <button type="submit">Calculate Scholarship</button>
                </form>
                
                <div class="instructions">
                    <h3>How It Works</h3>
                    <ul>
                        <li>For 1st semester students: Scholarship based on SSC & HSC results</li>
                        <li>For continuing students: Based on current CGPA and previous scholarship</li>
                        <li>Scholarship percentages: 0%, 25%, or 50%</li>
                    </ul>
                    <p>Fill in all fields to see your scholarship eligibility</p>
                </div>
            </div>
            
            <div class="result-section">
                <h2 class="section-title">Scholarship Result</h2>
                
                <div class="result-content">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($errors)): ?>
                        <div class="error">
                            <h3>Validation Errors:</h3>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php elseif ($result_display): ?>
                        <div class="success">
                            <h3>Scholarship Eligibility:</h3>
                            <div class="scholarship-result"><?php echo $scholarship_percentage; ?>% Scholarship</div>
                            <p>Based on: <?php echo $criteria; ?></p>
                        </div>
                        <div class="student-info">
                            <h3>Student Details:</h3>
                            <p><strong>ID:</strong> <?php echo $student_id; ?></p>
                            <p><strong>Name:</strong> <?php echo $full_name; ?></p>
                            <p><strong>Gender:</strong> <?php echo ucfirst($gender); ?></p>
                            <p><strong>SSC GPA:</strong> <?php echo number_format($ssc_gpa, 2); ?></p>
                            <p><strong>HSC GPA:</strong> <?php echo number_format($hsc_gpa, 2); ?></p>
                            <p><strong>Current CGPA:</strong> <?php echo $cgpa == -1 ? 'First Semester' : number_format($cgpa, 2); ?></p>
                            <p><strong>Previous Scholarship:</strong> <?php echo $prev_scholarship == -1 ? 'First-time applicant' : $prev_scholarship . '%'; ?></p>
                        </div>
                    <?php else: ?>
                        <div class="result-placeholder">
                            <div class="icon">📋</div>
                            <p>Fill out the form to calculate your scholarship eligibility</p>
                            <p>Your scholarship percentage will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="criteria-info">
                    <h3>Scholarship Criteria</h3>
                    <p><strong>First Semester:</strong> Male: SSC ≥4.5 & HSC ≥4.0 | Female: SSC ≥4.0 & HSC ≥3.5</p>
                    <p><strong>Continuing Students:</strong> Maintain CGPA ≥3.5 (25%) or ≥3.7 (50%)</p>
                    <p><strong>New Applicants:</strong> CGPA ≥3.7 (25%) or ≥3.9 (50%)</p>
                </div>
            </div>
        </div>
        
        <div class="records-section">
            <h2 class="section-title">Scholarship Records</h2>
            
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search_id" 
                    class="search-input" 
                    placeholder="Search by Student ID..."
                    value="<?php echo $search_id; ?>"
                >
                <button type="submit" name="search" class="search-button">Search</button>
            </form>
            
            <?php if (!empty($search_id)): ?>
                <p>Showing results for: <strong><?php echo $search_id; ?></strong></p>
                <a href="?" class="clear-search">Clear Search</a>
            <?php endif; ?>
            
            <div class="table-container">
                <?php if (count($applications) > 0): ?>
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>SSC GPA</th>
                                <th>HSC GPA</th>
                                <th>CGPA</th>
                                <th>Prev. Sch.</th>
                                <th>Awarded</th>
                                <th class="date-cell">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?php echo $app['id']; ?></td>
                                    <td><?php echo htmlspecialchars($app['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td><?php echo ucfirst($app['gender']); ?></td>
                                    <td><?php echo number_format($app['ssc_gpa'], 2); ?></td>
                                    <td><?php echo number_format($app['hsc_gpa'], 2); ?></td>
                                    <td>
                                        <?php echo $app['cgpa'] == -1 ? 
                                            'First Sem' : 
                                            number_format($app['cgpa'], 2); 
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $app['prev_scholarship'] == -1 ? 
                                            'New' : 
                                            $app['prev_scholarship'] . '%'; 
                                        ?>
                                    </td>
                                    <td>
                                        <span class="scholarship-badge badge-<?php echo $app['scholarship_percentage']; ?>">
                                            <?php echo $app['scholarship_percentage']; ?>%
                                        </span>
                                    </td>
                                    <td class="date-cell"><?php echo date('M d, Y', strtotime($app['application_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-records">
                        <p>No scholarship records found</p>
                        <?php if (!empty($search_id)): ?>
                            <p>Try a different search term</p>
                        <?php else: ?>
                            <p>Submit applications to see records here</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="footer">
            <p>Scholarship Calculator System © 2025 | For Educational Purposes</p>
            <p>Results are calculated based on institutional scholarship policies</p>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>