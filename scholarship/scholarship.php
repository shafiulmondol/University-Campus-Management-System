<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bangladesh University CGPA Calculator</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            color: #333;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .container {
            width: 100%;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            margin: 20px 0;
        }
        
        header {
            background: maroon;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .description {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .tabs {
            display: flex;
            border-radius: 10px;
            border: 2px solid whitesmoke;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .tab {
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 600;
            text-align: center;
            flex: 1;
            transition: background 0.3s;
        }
        
        .tab.active {
            background: darkgreen;
            color: white;
            border-radius: 2px;
        }
        
        .tab:hover:not(.active) {
            background: white;
        }
        
        .calculator {
            padding: 20px;
        }
        
        .calculator-section {
            display: none;
        }
        
        .calculator-section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background: maroon;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        button:hover {
            background: darkred;
            transform: translateY(-3px);
        }
        
        .course-list {
            margin: 20px 0;
        }
        
        .course-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .course-item input, .course-item select {
            flex: 1;
        }
        
        .remove-btn {
            background: #ff4757;
            width: auto;
            padding: 12px 15px;
        }
        
        .remove-btn:hover {
            background: #ff3745;
        }
        
        .result {
            background: #f1f9ff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
            display: none;
        }
        
        .result h3 {
            color: #1a2a6c;
            margin-bottom: 10px;
        }
        
        .gpa-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a2a6c;
        }
        
        .grade-scale {
            margin-top: 30px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }
        
        .grade-scale h3 {
            margin-bottom: 10px;
            color: #1a2a6c;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        th {
            background: #eaf4ff;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .add-btn {
            background: #2ed573;
        }
        
        .add-btn:hover {
            background: #25c965;
        }
        
        .reset-btn {
            background: #ffa502;
        }
        
        .reset-btn:hover {
            background: #f59e0b;
        }
        
        .semester-inputs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .semester-inputs input {
            flex: 1;
        }
        
        footer {
            text-align: center;
            margin-top: 20px;
            color: white;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .course-item {
                flex-direction: column;
            }
            
            .semester-inputs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SKST University CGPA Calculator</h1>
            <p class="description">Calculate your SGPA and CGPA based on the UGC grading system</p>
        </header>
        
        <div class="tabs">
            <div class="tab active" data-tab="sgpa">SGPA Calculator</div>
            <div class="tab" data-tab="cgpa">CGPA Calculator</div>
        </div>
        
        <div class="calculator">
            <!-- SGPA Calculator -->
            <div class="calculator-section active" id="sgpa-section">
                <h2 style="text-align: center;">Semester Grade Point Average (SGPA)</h2>
                <p style="text-align: center;">Calculate your GPA for a single semester</p>
                
                <form id="sgpa-form">
                    <div class="form-group">
                        <label for="num-courses">Number of Courses:</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="num-courses" min="1" max="15" value="5" required>
                        <button class="form-group" style="width: 200px;">Submit</button>
                    </div>

                    </div>
                    
                    <div class="course-list" id="sgpa-courses">
                        <!-- Courses will be generated here -->
                    </div>
                    
                    <button type="button" id="calculate-sgpa">Calculate SGPA</button>
                </form>
                
                <div class="result" id="sgpa-result">
                    <h3>Your SGPA is:</h3>
                    <div class="gpa-value" id="sgpa-value">0.00</div>
                </div>
            </div>
            
            <!-- CGPA Calculator -->
            <div class="calculator-section" id="cgpa-section">
                <h2 style="text-align: center;">Cumulative Grade Point Average (CGPA)</h2>
                <p style="text-align: center;">Calculate your overall CGPA across multiple semesters</p>
                
                <form id="cgpa-form">
                    <div class="form-group">
                        <label for="previous-cgpa">Previous CGPA (if any):</label>
                        <input type="number" id="previous-cgpa" min="0" max="4" step="0.01" placeholder="Enter previous CGPA">
                    </div>
                    
                    <div class="form-group">
                        <label for="completed-credits">Total Completed Credits:</label>
                        <input type="number" id="completed-credits" min="0" step="1" placeholder="Enter completed credits">
                    </div>
                    
                    <div class="form-group">
                        <label for="current-sgpa">Current Semester SGPA:</label>
                        <input type="number" id="current-sgpa" min="0" max="4" step="0.01" placeholder="Enter current SGPA" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="current-credits">Current Semester Credits:</label>
                        <input type="number" id="current-credits" min="0" step="1" placeholder="Enter current credits" required>
                    </div>
                    
                    <button type="button" id="calculate-cgpa">Calculate CGPA</button>
                </form>
                
                <div class="result" id="cgpa-result">
                    <h3>Your CGPA is:</h3>
                    <div class="gpa-value" id="cgpa-value">0.00</div>
                </div>
            </div>    
            
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = document.querySelectorAll('.tab');
            const sections = document.querySelectorAll('.calculator-section');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding section
                    sections.forEach(section => {
                        section.classList.remove('active');
                        if (section.id === `${targetTab}-section`) {
                            section.classList.add('active');
                        }
                    });
                });
            });
            
            // SGPA Calculator functionality
            const numCoursesInput = document.getElementById('num-courses');
            const sgpaCoursesContainer = document.getElementById('sgpa-courses');
            const calculateSgpaBtn = document.getElementById('calculate-sgpa');
            const sgpaResult = document.getElementById('sgpa-result');
            const sgpaValue = document.getElementById('sgpa-value');
            
            // Generate course inputs based on number of courses
            function generateCourseInputs() {
                const numCourses = parseInt(numCoursesInput.value);
                sgpaCoursesContainer.innerHTML = '';
                
                for (let i = 1; i <= numCourses; i++) {
                    const courseDiv = document.createElement('div');
                    courseDiv.className = 'course-item';
                    courseDiv.innerHTML = `
                        <input type="text" placeholder="Course ${i} Name" class="course-name">
                        <input type="number" placeholder="Credit Hours" min="1" max="5" step="0.5" class="credit-hours" required>
                        <select class="grade" required>
                            <option value="">Select Grade</option>
                            <option value="4.0">A+ (4.0)</option>
                            <option value="3.75">A (3.75)</option>
                            <option value="3.5">A- (3.5)</option>
                            <option value="3.25">B+ (3.25)</option>
                            <option value="3.0">B (3.0)</option>
                            <option value="2.75">B- (2.75)</option>
                            <option value="2.5">C+ (2.5)</option>
                            <option value="2.25">C (2.25)</option>
                            <option value="2.0">D (2.0)</option>
                            <option value="0.0">F (0.0)</option>
                        </select>
                    `;
                    sgpaCoursesContainer.appendChild(courseDiv);
                }
            }
            
            // Initial generation of course inputs
            generateCourseInputs();
            
            // Update course inputs when number changes
            numCoursesInput.addEventListener('change', generateCourseInputs);
            
            // Calculate SGPA
            calculateSgpaBtn.addEventListener('click', function() {
                const creditInputs = document.querySelectorAll('.credit-hours');
                const gradeSelects = document.querySelectorAll('.grade');
                
                let totalCreditHours = 0;
                let totalGradePoints = 0;
                let isValid = true;
                
                // Validate inputs
                creditInputs.forEach(input => {
                    if (!input.value) isValid = false;
                });
                
                gradeSelects.forEach(select => {
                    if (!select.value) isValid = false;
                });
                
                if (!isValid) {
                    alert('Please fill in all credit hours and select grades for all courses.');
                    return;
                }
                
                // Calculate SGPA
                for (let i = 0; i < creditInputs.length; i++) {
                    const credit = parseFloat(creditInputs[i].value);
                    const grade = parseFloat(gradeSelects[i].value);
                    
                    totalCreditHours += credit;
                    totalGradePoints += credit * grade;
                }
                
                const sgpa = totalGradePoints / totalCreditHours;
                
                // Display result
                sgpaValue.textContent = sgpa.toFixed(2);
                sgpaResult.style.display = 'block';
                
                // Prefill current SGPA in CGPA calculator
                document.getElementById('current-sgpa').value = sgpa.toFixed(2);
            });
            
            // CGPA Calculator functionality
            const calculateCgpaBtn = document.getElementById('calculate-cgpa');
            const cgpaResult = document.getElementById('cgpa-result');
            const cgpaValue = document.getElementById('cgpa-value');
            
            calculateCgpaBtn.addEventListener('click', function() {
                const previousCgpa = parseFloat(document.getElementById('previous-cgpa').value) || 0;
                const completedCredits = parseFloat(document.getElementById('completed-credits').value) || 0;
                const currentSgpa = parseFloat(document.getElementById('current-sgpa').value);
                const currentCredits = parseFloat(document.getElementById('current-credits').value);
                
                if (!currentSgpa || !currentCredits) {
                    alert('Please enter current SGPA and credits.');
                    return;
                }
                
                // Calculate CGPA
                const totalGradePoints = (previousCgpa * completedCredits) + (currentSgpa * currentCredits);
                const totalCredits = completedCredits + currentCredits;
                const cgpa = totalGradePoints / totalCredits;
                
                // Display result
                cgpaValue.textContent = cgpa.toFixed(2);
                cgpaResult.style.display = 'block';
            });
        });
    </script>
</body>
</html>