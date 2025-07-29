<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

// Initialize variables
$full_name = '';
$gender = '';
$ssc_gpa = '';
$current_gpa = '';
$scholarship_percentage = 0;
$criteria = '';
$errors = [];
$result_display = false;
$highlight_row = '';
$db_success = false;
$db_error = '';

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $db_error = "Database connection failed: " . $conn->connect_error;
} else {
    // Create table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS scholarship_records (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        ssc_gpa DECIMAL(3,2) NOT NULL,
        current_gpa DECIMAL(3,2) NOT NULL,
        scholarship_percentage INT(3) NOT NULL,
        criteria VARCHAR(255) NOT NULL,
        calculation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        $db_error = "Error creating table: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $full_name = htmlspecialchars(trim($_POST['name']));
    $gender = htmlspecialchars($_POST['gender']);
    $ssc_gpa = floatval($_POST['ssc_gpa']);
    $current_gpa = floatval($_POST['current_gpa']);
    
    // Validate inputs
    if (empty($full_name)) {
        $errors[] = "Please enter your name";
    }
    if (empty($gender)) {
        $errors[] = "Please select your gender";
    }
    if ($ssc_gpa < 0 || $ssc_gpa > 5) {
        $errors[] = "SSC GPA must be between 0.00 and 5.00";
    }
    if ($current_gpa < 0 || $current_gpa > 5) {
        $errors[] = "Current GPA must be between 0.00 and 5.00";
    }
    
    // Calculate scholarship if no errors
    if (empty($errors)) {
        $result_display = true;
        
        // Special case for 100% scholarship
        if ($ssc_gpa == 5.00 && $current_gpa == 5.00) {
            $scholarship_percentage = 100;
            $criteria = "SSC GPA 5.00 and Current GPA 5.00";
            $highlight_row = "row-100";
        } 
        // Other cases based on current GPA
        else {
            if ($current_gpa == 5.00) {
                $scholarship_percentage = ($gender == 'male') ? 60 : 75;
                $criteria = "Current GPA 5.00";
                $highlight_row = "row-75-60";
            } 
            else if ($current_gpa >= 4.80 && $current_gpa <= 4.99) {
                $scholarship_percentage = ($gender == 'male') ? 50 : 65;
                $criteria = "Current GPA between 4.80 – 4.99";
                $highlight_row = "row-65-50";
            } 
            else if ($current_gpa >= 4.50 && $current_gpa < 4.80) {
                $scholarship_percentage = ($gender == 'male') ? 25 : 40;
                $criteria = "Current GPA between 4.50 – 4.79";
                $highlight_row = "row-40-25";
            } 
            else if ($current_gpa >= 4.00 && $current_gpa < 4.50) {
                $scholarship_percentage = ($gender == 'male') ? 15 : 30;
                $criteria = "Current GPA between 4.00 – 4.49";
                $highlight_row = "row-30-15";
            } 
            else if ($current_gpa >= 3.50 && $current_gpa < 4.00) {
                $scholarship_percentage = ($gender == 'male') ? 10 : 25;
                $criteria = "Current GPA between 3.50 – 3.99";
                $highlight_row = "row-25-10";
            } 
            else {
                $scholarship_percentage = 0;
                $criteria = "Below minimum GPA requirement (3.50)";
            }
        }
        
        // Save to database if connection exists
        if (empty($db_error)) {
            $stmt = $conn->prepare("INSERT INTO scholarship_records (name, gender, ssc_gpa, current_gpa, scholarship_percentage, criteria) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddds", $full_name, $gender, $ssc_gpa, $current_gpa, $scholarship_percentage, $criteria);
            
            if ($stmt->execute()) {
                $db_success = true;
            } else {
                $db_error = "Error saving record: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Eligibility Calculator</title>
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
            margin-top: 20px;
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
        
        button {
            padding: 16px;
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
            width: 100%;
            margin-top: 10px;
        }
        
        button:hover {
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
        
        .scholarship-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            font-size: 1rem;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            background: white;
        }
        
        .scholarship-table th, 
        .scholarship-table td {
            padding: 16px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .scholarship-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
        }
        
        .scholarship-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .highlight-row {
            background-color: #e0f2fe !important;
            font-weight: 600;
            animation: highlight 1.5s ease;
        }
        
        @keyframes highlight {
            0% { background-color: #fde68a; }
            100% { background-color: #e0f2fe; }
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
        
        .badge-10 {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .badge-15 {
            background: #fee2e2;
            color: #b91c1c;
        }
        
        .badge-25 {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-30 {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-40 {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-50 {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-60 {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-65 {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-75 {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-100 {
            background: #dcfce7;
            color: #166534;
        }
        
        .db-status {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            text-align: left;
            font-size: 1rem;
        }
        
        .db-success {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .db-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
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
            
            .scholarship-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><span class="emoji">🎓</span>Scholarship Eligibility Calculator</h1>
            <p>Determine your scholarship eligibility based on academic performance</p>
        </header>
        
        <?php if (!empty($db_error)): ?>
            <div class="db-status db-error">
                <strong>Database Error:</strong> <?php echo $db_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="calculator-container">
            <div class="form-section">
                <h2 class="section-title">Student Information</h2>
                
                <form method="POST">
                    <div class="form-group">
                      <label for="name">Name</label>
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
                        <label for="ssc_gpa">S.S.C GPA (Scale: 5.00)</label>
                        <input type="number" name="ssc_gpa" id="ssc_gpa" min="0" max="5" step="0.01" value="<?php echo $ssc_gpa; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_gpa">H.S.C GPA (Scale: 5.00)</label>
                        <input type="number" name="current_gpa" id="current_gpa" min="0" max="5" step="0.01" value="<?php echo $current_gpa; ?>" required>
                    </div>
                    
                    <button type="submit">Calculate Scholarship</button>
                </form>
                
                <div class="instructions">
                    <h3>How It Works</h3>
                    <ul>
                        <li>Scholarship is determined by your S.S.C GPA and H.S.C GPA</li>
                        <li>Students with S.S.C GPA 5.00 and H.S.C GPA 5.00 get 100% scholarship</li>
                        <li>For other students, scholarship is based on H.S.C GPA range</li>
                        <li>Female students receive higher scholarship rates in each category</li>
                    </ul>
                    
                    <h3>Database Information</h3>
                    <p><strong>Database Name:</strong> skst_university</p>
                    <p><strong>Table Name:</strong> scholarship_records</p>
                    <p>Records are saved after each calculation</p>
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
                            <div class="scholarship-result">
                                <span class="scholarship-badge badge-<?php echo $scholarship_percentage; ?>">
                                    <?php echo $scholarship_percentage; ?>% Scholarship
                                </span>
                            </div>
                            <p>Based on: <?php echo $criteria; ?></p>
                            
                            <?php if ($db_success): ?>
                                <div class="db-status db-success">
                                    Record saved to database successfully
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="student-info">
                            <h3>Student Details:</h3>
                            <p><strong>Name:</strong> <?php echo $full_name; ?></p>
                            <p><strong>Gender:</strong> <?php echo ucfirst($gender); ?></p>
                            <p><strong>SSC GPA:</strong> <?php echo number_format($ssc_gpa, 2); ?></p>
                            <p><strong>Current GPA:</strong> <?php echo number_format($current_gpa, 2); ?></p>
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
                    <p><strong>100% Scholarship:</strong> S.S.C GPA 5.00 + H.S.C GPA 5.00</p>
                    <p><strong>Other Scholarships:</strong> Based on H.S.C GPA range (see table below)</p>
                </div>
            </div>
        </div>

        <?php /* <div class="scholarship-table-container">
            <h2 class="section-title">Scholarship Rate Table</h2>
            <table class="scholarship-table">
                <thead>
                    <tr>
                        <th>Level of Score</th>
                        <th>Marks (%)</th>
                        <th>Rate of Scholarship (Male)</th>
                        <th>Rate of Scholarship (Female)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="row-100" <?php echo $highlight_row == 'row-100' ? 'class="highlight-row"' : ''; ?>>
                        <td>5.00</td>
                        <td>90% or above</td>
                        <td>100%*</td>
                        <td>100%*</td>
                    </tr>
                    <tr id="row-75-60" <?php echo $highlight_row == 'row-75-60' ? 'class="highlight-row"' : ''; ?>>
                        <td>5.00</td>
                        <td>90% or above</td>
                        <td>60%</td>
                        <td>75%</td>
                    </tr>
                    <tr id="row-65-50" <?php echo $highlight_row == 'row-65-50' ? 'class="highlight-row"' : ''; ?>>
                        <td>4.80 – 4.99</td>
                        <td>80% – below 90%</td>
                        <td>50% of Tuition Fees</td>
                        <td>65% of Tuition Fees</td>
                    </tr>
                    <tr id="row-40-25" <?php echo $highlight_row == 'row-40-25' ? 'class="highlight-row"' : ''; ?>>
                        <td>4.50 – 4.79</td>
                        <td>75% – below 80%</td>
                        <td>25% of Tuition Fees</td>
                        <td>40% of Tuition Fees</td>
                    </tr>
                    <tr id="row-30-15" <?php echo $highlight_row == 'row-30-15' ? 'class="highlight-row"' : ''; ?>>
                        <td>4.00 – 4.49</td>
                        <td>70% – below 75%</td>
                        <td>15% of Tuition Fees</td>
                        <td>30% of Tuition Fees</td>
                    </tr>
                    <tr id="row-25-10" <?php echo $highlight_row == 'row-25-10' ? 'class="highlight-row"' : ''; ?>>
                        <td>3.50 – 3.99</td>
                        <td>60% – below 70%</td>
                        <td>10% of Tuition Fees</td>
                        <td>25% of Tuition Fees</td>
                    </tr>
                </tbody>
            </table>
            <p style="margin-top: 15px; text-align: center; color: var(--text-muted);">*With GPA 5.00 at SSC</p>
        </div>*/ ?>

        <div class="footer">
            <p>Scholarship Calculator System © 2025 | For Educational Purposes</p>
        </div>
    </div>
</body>
</html>