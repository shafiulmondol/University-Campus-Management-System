<?php
// Initialize variables
$full_name = '';
$gender = '';
$ssc_gpa = '';
$hsc_gpa = '';
$scholarship_percentage = 0;
$criteria = '';
$errors = [];
$result_display = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $full_name = htmlspecialchars(trim($_POST['name']));
    $gender = htmlspecialchars($_POST['gender']);
    $ssc_gpa = floatval($_POST['ssc_gpa']);
    $hsc_gpa = floatval($_POST['hsc_gpa']);
    
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
    if ($hsc_gpa < 0 || $hsc_gpa > 5) {
        $errors[] = "HSC GPA must be between 0.00 and 5.00";
    }
    
    // Calculate scholarship if no errors
    if (empty($errors)) {
        $result_display = true;
        
        // Special case for 100% scholarship (both SSC and HSC GPA 5.00)
        if ($ssc_gpa == 5.00 && $hsc_gpa == 5.00) {
            $scholarship_percentage = 100;
            $criteria = "SSC GPA 5.00 and HSC GPA 5.00";
        } 
        // HSC GPA 5.00 cases
        else if ($hsc_gpa == 5.00) {
            if ($gender == 'male') {
                $scholarship_percentage = 60;
                $criteria = "HSC GPA 5.00 (Male)";
            } else {
                $scholarship_percentage = 75;
                $criteria = "HSC GPA 5.00 (Female - Extra 15%)";
            }
        }
        // Other GPA ranges
        else if ($hsc_gpa >= 4.80 && $hsc_gpa <= 4.99) {
            $scholarship_percentage = ($gender == 'male') ? 50 : 65;
            $criteria = "HSC GPA between 4.80 – 4.99";
        } 
        else if ($hsc_gpa >= 4.50 && $hsc_gpa < 4.80) {
            $scholarship_percentage = ($gender == 'male') ? 25 : 40;
            $criteria = "HSC GPA between 4.50 – 4.79";
        } 
        else if ($hsc_gpa >= 4.00 && $hsc_gpa < 4.50) {
            $scholarship_percentage = ($gender == 'male') ? 15 : 30;
            $criteria = "HSC GPA between 4.00 – 4.49";
        } 
        else if ($hsc_gpa >= 3.50 && $hsc_gpa < 4.00) {
            $scholarship_percentage = ($gender == 'male') ? 10 : 25;
            $criteria = "HSC GPA between 3.50 – 3.99";
        } 
        else {
            $scholarship_percentage = 0;
            $criteria = "Below minimum GPA requirement (3.50)";
        }
    }
}
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
            margin-bottom: 20px;
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
            height: auto;
            justify-items: center;
            text-align: center;
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

        .refresh-button {
            background: green;
            color: white;
            border-radius: 12px;
            padding: 14px 25px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .refresh-button:hover {
            background: darkgreen;
        }

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Scholarship Eligibility Calculator</h1>
            <p>Determine your scholarship eligibility based on academic performance</p>
        </header>
        
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
                        <label for="hsc_gpa">H.S.C GPA (Scale: 5.00)</label>
                        <input type="number" name="hsc_gpa" id="hsc_gpa" min="0" max="5" step="0.01" value="<?php echo $hsc_gpa; ?>" required>
                    </div>
                    
                    <button type="submit">Calculate Scholarship</button>
                </form>
                
                <div class="instructions">
                    <h3>How It Works</h3>
                    <ul>
                        <li>Scholarship is determined by your S.S.C GPA and H.S.C GPA</li>
                        <li>Students with S.S.C GPA 5.00 and H.S.C GPA 5.00 get 100% scholarship</li>
                        <li>Male students with H.S.C GPA 5.00 get 60% scholarship</li>
                        <li>Female students with H.S.C GPA 5.00 get 75% scholarship (Extra 15%)</li>
                        <li>For other students, scholarship is based on H.S.C GPA range</li>
                    </ul>
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
                        </div>
                        <div class="student-info">
                            <h3>Student Details:</h3>
                            <p><strong>Name:</strong> <?php echo $full_name; ?></p>
                            <p><strong>Gender:</strong> <?php echo ucfirst($gender); ?></p>
                            <p><strong>SSC GPA:</strong> <?php echo number_format($ssc_gpa, 2); ?></p>
                            <p><strong>HSC GPA:</strong> <?php echo number_format($hsc_gpa, 2); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="result-placeholder">
                            <div class="icon">📋</div>
                            <p>Fill out the form to calculate your scholarship eligibility</p>
                            <p>Your scholarship percentage will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="scholarship2.php"><button class="refresh-button">Refresh</button></a>
                </div>
                
                <div class="criteria-info">
                    <h3>Scholarship Criteria</h3>
                    <p><strong>100% Scholarship:</strong> S.S.C GPA 5.00 + H.S.C GPA 5.00</p>
                    <p><strong>HSC 5.00 Scholarship:</strong> Male: 60%, Female: 75% (Extra 15%)</p>
                    <p><strong>Other Scholarships:</strong> Based on H.S.C GPA range (see table below)</p>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Scholarship Calculator System © 2025 | For Educational Purposes</p>
        </div>
    </div>
</body>
</html>