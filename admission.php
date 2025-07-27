<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create admissions table if not exists
$sql = "CREATE TABLE IF NOT EXISTS admissions (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(50) NOT NULL,
    mobile_number VARCHAR(15) NOT NULL,
    program VARCHAR(50) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    level_study VARCHAR(20) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    guardian_name VARCHAR(100) NOT NULL,
    guardian_number VARCHAR(15) NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    district VARCHAR(50) NOT NULL,
    upzilla VARCHAR(50) NOT NULL,
    post_office VARCHAR(50) NOT NULL,
    post_code VARCHAR(20) NOT NULL,
    village VARCHAR(50) NOT NULL,
    birth_date DATE NOT NULL,
    hsc_institute VARCHAR(100) NOT NULL,
    hsc_group VARCHAR(50) NOT NULL,
    hsc_passing_year VARCHAR(4) NOT NULL,
    hsc_result VARCHAR(10) NOT NULL,
    ssc_institute VARCHAR(100) NOT NULL,
    ssc_group VARCHAR(50) NOT NULL,
    ssc_passing_year VARCHAR(4) NOT NULL,
    ssc_result VARCHAR(10) NOT NULL,
    parent_income VARCHAR(50) NOT NULL,
    source_info VARCHAR(50) NOT NULL,
    payment_type VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === FALSE) {
    die("Error creating table: " . $conn->error);
}

// Process form submission
$success = false;
$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $fullname = htmlspecialchars($_POST['fullname']);
    $email = htmlspecialchars($_POST['email']);
    $mobileNo = htmlspecialchars($_POST['mobileNo']);
    $program = htmlspecialchars($_POST['program']);
    $semester = htmlspecialchars($_POST['semester']);
    $levelStudy = htmlspecialchars($_POST['levelStudy']);
    $gender = htmlspecialchars($_POST['gender']);
    $guardian_name = htmlspecialchars($_POST['guardian_name']);
    $guardian_number = htmlspecialchars($_POST['guardian_number']);
    $nationality = htmlspecialchars($_POST['nationality']);
    $division = htmlspecialchars($_POST['division']);
    $district = htmlspecialchars($_POST['district']);
    $upzilla = htmlspecialchars($_POST['upzilla']);
    $postOffice = htmlspecialchars($_POST['postOffice']);
    $postCode = htmlspecialchars($_POST['postCode']);
    $village = htmlspecialchars($_POST['village']);
    $birthDate = htmlspecialchars($_POST['birthDate']);
    $hscInstitute = htmlspecialchars($_POST['hscInstitute']);
    $hscGroup = htmlspecialchars($_POST['hscGroup']);
    $hscPassingYear = htmlspecialchars($_POST['hscPassingYear']);
    $hscResult = htmlspecialchars($_POST['hscResult']);
    $sscInstitute = htmlspecialchars($_POST['sscInstitute']);
    $sscGroup = htmlspecialchars($_POST['sscGroup']);
    $sscPassingYear = htmlspecialchars($_POST['sscPassingYear']);
    $sscResult = htmlspecialchars($_POST['sscResult']);
    $parent_income = htmlspecialchars($_POST['parent_income']);
    $source_info = htmlspecialchars($_POST['source_info']);
    $payment_type = htmlspecialchars($_POST['payment_type']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO admissions (
        fullname, email, mobile_number, program, semester, level_study, gender, 
        guardian_name, guardian_number, nationality, division, district, upzilla, 
        post_office, post_code, village, birth_date, 
        hsc_institute, hsc_group, hsc_passing_year, hsc_result, 
        ssc_institute, ssc_group, ssc_passing_year, ssc_result, 
        parent_income, source_info, payment_type, password
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        $error_message = "Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param("sssssssssssssssssssssssssssss", 
            $fullname, $email, $mobileNo, $program, $semester, $levelStudy, $gender,
            $guardian_name, $guardian_number, $nationality, $division, $district, $upzilla,
            $postOffice, $postCode, $village, $birthDate,
            $hscInstitute, $hscGroup, $hscPassingYear, $hscResult,
            $sscInstitute, $sscGroup, $sscPassingYear, $sscResult,
            $parent_income, $source_info, $payment_type, $password
        );
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University Admission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #003f8bb0;
            --secondary-color: #FFD700;
            --light-bg: #f8f9fa;
            --form-bg: #ffffff;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #333;
        }
        
        .form-container {
            background-color: var(--form-bg);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin: 0 auto 30px;
            max-width: 1200px;
        }
        
        .form-title {
            color: maroon;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 30px;
            font-weight: 700;
            text-align: center;
        }
        
        .section-title {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }
        
        .required::after {
            content: " *";
            color: red;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), #5a0c0cff);
            border: none;
            padding: 12px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 5px;
            margin-top: 20px;
            transition: all 0.3s ease;
            display: inline-flex;
            color: white;
        }
        
        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ddd;
            transition: border-color 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 49, 139, 0.25);
        }
        
        .thank-you {
            text-align: center;
            padding: 40px 20px;
        }
        
        .thank-you i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .thank-you h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .thank-you p {
            font-size: 1.2rem;
            color: #555;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 5px;
        }
        
        .password-strength {
            height: 5px;
            width: 100%;
            background-color: #e9ecef;
            margin-top: 5px;
            border-radius: 3px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <?php if ($success): ?>
                <div class="thank-you" id="thankYou">
                    <i class="fas fa-check-circle"></i>
                    <h3>Thank You for Applying!</h3>
                    <p>Your application has been submitted successfully. Our admission team will review your application and contact you shortly.</p>
                    <a href="?" class="btn submit-btn">
                        <i class="fas fa-edit me-2"></i>Submit Another Application
                    </a>
                </div>
            <?php else: ?>
                <div id="admissionForm">
                    <h3 class="form-title">
                        <i class="fas fa-user-graduate me-2"></i>Admission Application
                    </h3>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <strong>Error!</strong> <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
                        <!-- Personal Information -->
                        <div class="section-title">
                            <i class="fas fa-user-circle me-2"></i>Personal Information
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Semester</label>
                                <select class="form-select" name="semester" id="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="Fall-2025">Fall 2025</option>
                                    <option value="Spring-2026">Spring 2026</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Level of Study</label>
                                <select class="form-select" name="levelStudy" id="levelStudy" required>
                                    <option value="">Select Level</option>
                                    <option value="Graduate">Graduate</option>
                                    <option value="Undergraduate">Undergraduate</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Program</label>
                                <select class="form-select" name="program" id="program" required>
                                    <option value="" disabled selected>Select a program</option>
                                    <option value="BBA in Accounting">BBA in Accounting</option>
                                    <option value="BBA in Finance">BBA in Finance</option>
                                    <option value="BBA in Human Resource Management">BBA in Human Resource Management</option>
                                    <option value="BBA in Management">BBA in Management</option>
                                    <option value="BBA in Marketing">BBA in Marketing</option>
                                    <option value="BSc in Computer Science and Engineering">BSc in Computer Science and Engineering</option>
                                    <option value="BSc in Civil Engineering">BSc in Civil Engineering</option>
                                    <option value="BSc in Electrical and Electronic Engineering">BSc in Electrical and Electronic Engineering</option>
                                    <option value="BSc in Mechanical Engineering">BSc in Mechanical Engineering</option>
                                    <option value="BSEEE in Electrical and Electronic Engineering">BSEEE in Electrical and Electronic Engineering</option>
                                    <option value="BSAg in Agriculture">BSAg in Agriculture</option>
                                    <option value="BSN in Nursing (Basic)">BSN in Nursing (Basic)</option>
                                    <option value="BSN in Nursing (Post Basic)">BSN in Nursing (Post Basic)</option>
                                    <option value="BATHM in Tourism and Hospitality Management">BATHM in Tourism and Hospitality Management</option>
                                    <option value="BSECO in Economics">BSECO in Economics</option>
                                    <option value="BA in English">BA in English</option>
                                    <option value="LLB (Honours)">LLB (Honours)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Full Name</label>
                                <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Gender</label>
                                <select class="form-select" name="gender" id="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Date of Birth</label>
                                <input type="date" class="form-control" name="birthDate" id="birthDate" required>
                                <div class="error-message" id="birthDateError"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Email Address</label>
                                <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text">+88</span>
                                    <input type="tel" class="form-control" name="mobileNo" id="mobileNo" placeholder="Enter mobile number" required>
                                </div>
                                <div class="error-message" id="mobileNoError"></div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Nationality</label>
                                <input type="text" class="form-control" name="nationality" id="nationality" placeholder="Enter your nationality" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Guardian Name</label>
                                <input type="text" class="form-control" name="guardian_name" id="guardian_name" placeholder="Enter guardian's name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Guardian Mobile</label>
                                <div class="input-group">
                                    <span class="input-group-text">+88</span>
                                    <input type="tel" class="form-control" name="guardian_number" id="guardian_number" placeholder="Enter guardian's mobile number" required>
                                </div>
                                <div class="error-message" id="guardianNumberError"></div>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="section-title">
                            <i class="fas fa-home me-2"></i>Address Information
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Division</label>
                                <input type="text" class="form-control" name="division" id="division" placeholder="Enter division" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">District</label>
                                <input type="text" class="form-control" name="district" id="district" placeholder="Enter district" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Upazila/Thana</label>
                                <input type="text" class="form-control" name="upzilla" id="upzilla" placeholder="Enter upazila/thana" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Post Office</label>
                                <input type="text" class="form-control" name="postOffice" id="postOffice" placeholder="Enter post office" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Postal Code</label>
                                <input type="text" class="form-control" name="postCode" id="postCode" placeholder="Enter postal code" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Village/Area</label>
                                <input type="text" class="form-control" name="village" id="village" placeholder="Enter village/area" required>
                            </div>
                        </div>
                        
                        <!-- Educational Information -->
                        <div class="section-title">
                            <i class="fas fa-book me-2"></i>Educational Information
                        </div>
                        
                        <h5 class="mt-4 mb-3" style="color: var(--primary-color);">HSC/A'Level/Equivalent</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Institute Name</label>
                                <input type="text" class="form-control" name="hscInstitute" id="hscInstitute" placeholder="Enter institute name" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Group</label>
                                <select class="form-select" name="hscGroup" id="hscGroup" required>
                                    <option value="">Select Group</option>
                                    <option value="Science">Science</option>
                                    <option value="Commerce">Commerce</option>
                                    <option value="Arts">Arts</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Passing Year</label>
                                <input type="number" class="form-control" name="hscPassingYear" id="hscPassingYear" min="1990" max="2025" placeholder="Year" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Result (GPA)</label>
                                <input type="text" class="form-control" name="hscResult" id="hscResult" placeholder="Enter GPA/CGPA" required>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3" style="color: var(--primary-color);">SSC/O'Level/Equivalent</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Institute Name</label>
                                <input type="text" class="form-control" name="sscInstitute" id="sscInstitute" placeholder="Enter institute name" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Group</label>
                                <select class="form-select" name="sscGroup" id="sscGroup" required>
                                    <option value="">Select Group</option>
                                    <option value="Science">Science</option>
                                    <option value="Commerce">Commerce</option>
                                    <option value="Arts">Arts</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label required">Passing Year</label>
                                <input type="number" class="form-control" name="sscPassingYear" id="sscPassingYear" min="1990" max="2025" placeholder="Year" required>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label required">Result (GPA)</label>
                                <input type="text" class="form-control" name="sscResult" id="sscResult" placeholder="Enter GPA/CGPA" required>
                            </div>
                        </div>
                        
                        <!-- Additional Information -->
                        <div class="section-title">
                            <i class="fas fa-info-circle me-2"></i>Additional Information
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Parent's Yearly Income</label>
                                <select class="form-select" name="parent_income" id="parent_income" required>
                                    <option value="">Select Income Range</option>
                                    <option value="Below 200,000">Below 200,000</option>
                                    <option value="200,000 - 500,000">200,000 - 500,000</option>
                                    <option value="500,000 - 1,000,000">500,000 - 1,000,000</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Source of Information</label>
                                <select class="form-select" name="source_info" id="source_info" required>
                                    <option value="">Select Source</option>
                                    <option value="Website">University Website</option>
                                    <option value="Social Media">Social Media</option>
                                    <option value="Friends/Family">Friends/Family</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Payment Method</label>
                                <select class="form-select" name="payment_type" id="payment_type" required>
                                    <option value="">Select Method</option>
                                    <option value="Online Payment">Online Payment</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cash">Cash at Campus</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Create Password</label>
                                <div class="password-container">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Create your password" required>
                                    <span class="password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-meter" id="strength-meter"></div>
                                </div>
                                <div class="error-message" id="passwordError"></div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I hereby declare that all information provided in this application is true and accurate to the best of my knowledge.
                            </label>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn submit-btn">
                                <i class="fas fa-paper-plane me-2"></i>Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Form validation function
        function validateForm() {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            
            // Validate mobile number
            const mobileNo = document.getElementById('mobileNo').value;
            if (!/^01[3-9]\d{8}$/.test(mobileNo)) {
                document.getElementById('mobileNoError').textContent = 'Please enter a valid Bangladeshi mobile number (11 digits starting with 01)';
                isValid = false;
            }
            
            // Validate guardian mobile number
            const guardianNumber = document.getElementById('guardian_number').value;
            if (!/^01[3-9]\d{8}$/.test(guardianNumber)) {
                document.getElementById('guardianNumberError').textContent = 'Please enter a valid Bangladeshi mobile number (11 digits starting with 01)';
                isValid = false;
            }
            
            // Validate birth date
            const birthDate = new Date(document.getElementById('birthDate').value);
            const today = new Date();
            if (birthDate >= today) {
                document.getElementById('birthDateError').textContent = 'Birth date must be in the past';
                isValid = false;
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                document.getElementById('passwordError').textContent = 'Password must be at least 8 characters long';
                isValid = false;
            }
            
            return isValid;
        }
        
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter');
            let strength = 0;
            
            // Check length
            if (password.length >= 4) strength += 25;
            if (password.length >= 8) strength += 25;
            
            // Check for uppercase
            if (/[A-Z]/.test(password)) strength += 25;
            
            // Check for numbers
            if (/[0-9]/.test(password)) strength += 25;
            
            // Set width and color
            strengthMeter.style.width = strength + '%';
            
            if (strength < 50) {
                strengthMeter.style.backgroundColor = '#dc3545';
            } else if (strength < 75) {
                strengthMeter.style.backgroundColor = '#ffc107';
            } else {
                strengthMeter.style.backgroundColor = '#28a745';
            }
        });
        
        // Set min/max dates for birth date
        const today = new Date();
        const minDate = new Date(today.getFullYear() - 50, today.getMonth(), today.getDate());
        const maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
        
        document.getElementById('birthDate').min = minDate.toISOString().split('T')[0];
        document.getElementById('birthDate').max = maxDate.toISOString().split('T')[0];
        
        // Set current year as max for passing year
        const currentYear = new Date().getFullYear();
        document.getElementById('hscPassingYear').max = currentYear;
        document.getElementById('sscPassingYear').max = currentYear;
    </script>
</body>
</html>