<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration - SKST University</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        header {
            background: linear-gradient(135deg, maroon, #1a2530);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 25px;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 12px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 calc(50% - 20px);
            margin: 0 10px 20px;
        }
        
        .form-group-full {
            flex: 1 0 calc(100% - 20px);
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 14px 28px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-reset {
            background: #95a5a6;
        }
        
        .btn-reset:hover {
            background: #7f8c8d;
        }
        
        .btn-submit {
            background: #2ecc71;
        }
        
        .btn-submit:hover {
            background: #27ae60;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .icon {
            margin-right: 8px;
            color: #3498db;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(100% - 20px);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
        .success-message {
            display: none;
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
        }
        
        .success-message i {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SKST University Volunteer Program</h1>
            <p class="subtitle">Join us in making a difference through community service</p>
        </header>
        
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-hand-holding-heart icon"></i>Volunteer Registration Form</h2>
            
            
            <form id="volunteerForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="student_id" class="required">Student ID</label>
                        <input type="text" id="student_id" name="student_id" required placeholder="Enter your student ID">
                    </div>
                    
                    <div class="form-group">
                        <label for="student_name" class="required">Full Name</label>
                        <input type="text" id="student_name" name="student_name" required placeholder="Enter your full name">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="department" class="required">Department</label>
                        <select id="department" name="department" required>
                            <option value="">Select a program</option>
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
                    
                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" id="email" name="email" required placeholder="Enter your email address">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="required">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Enter your phone number">
                    </div>
                    
                    <div class="form-group">
                        <label for="activity_name" class="required">Activity Name</label>
                        <select id="activity_name" name="activity_name" required>
                            <option value="">Select Activity</option>
                            <option value="Blood Donation Camp">Blood Donation Camp</option>
                            <option value="Tree Plantation Drive">Tree Plantation Drive</option>
                            <option value="Campus Clean-up">Campus Clean-up</option>
                            <option value="Fundraising Event">Fundraising Event</option>
                            <option value="Cultural Festival">Cultural Festival</option>
                            <option value="Student Mentorship">Student Mentorship</option>
                            <option value="Community Outreach">Community Outreach</option>
                            <option value="Health Awareness Campaign">Health Awareness Campaign</option>

                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="activity_date" class="required">Activity Date</label>
                        <input type="date" id="activity_date" name="activity_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="required">Preferred Role</label>
                        <select id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="Volunteer">Volunteer</option>
                            <option value="Organizer">Organizer</option>
                            <option value="Leader">Leader</option>
                            <option value="Coordinator">Coordinator</option>
                            <option value="Support Staff">Support Staff</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="hours">Expected Hours</label>
                        <input type="number" id="hours" name="hours" min="1" max="50" placeholder="How many hours can you contribute?">
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Previous Experience</label>
                        <select id="experience" name="experience">
                            <option value="">Select Experience Level</option>
                            <option value="None">None</option>
                            <option value="Beginner">Beginner (1-4 events)</option>
                            <option value="Intermediate">Intermediate (5-10 events)</option>
                            <option value="Experienced">Experienced (10+ events)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="remarks">Remarks / Special Skills</label>
                        <textarea id="remarks" name="remarks" placeholder="Please share any special skills, comments, or preferences..."></textarea>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane"></i> Submit Application</button>
                    <button type="reset" class="btn btn-reset"><i class="fas fa-redo"></i> Reset Form</button>
                    <button type="button" class="btn btn-reset" onclick="history.back();"><i class="fas fa-arrow-left"></i> Back</button>
                    <button type="button" class="btn btn-reset" onclick="window.location.href='index.php';"><i class="fas fa-home"></i> Home</button>

                </div>
            </form>
            
            <div class="success-message" id="successMessage">
                <i class="fas fa-check-circle"></i>
                <h3>Thank You for Registering!</h3>
                <p>Your volunteer application has been submitted successfully. We will contact you shortly with more details.</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('volunteerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple form validation
            const requiredFields = document.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    valid = false;
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            
            if (valid) {
                // In a real application, you would submit to a server here
                // For demonstration, we'll show a success message
                document.getElementById('successMessage').style.display = 'block';
                document.getElementById('volunteerForm').reset();
                
                // Scroll to success message
                document.getElementById('successMessage').scrollIntoView({ behavior: 'smooth' });
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    document.getElementById('successMessage').style.display = 'none';
                }, 5000);
            }
        });
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('activity_date').setAttribute('min', today);
        
        // Add input event listeners to remove error styles when typing
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#ddd';
            });
        });
    </script>
</body>
</html>