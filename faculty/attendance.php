<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University - Attendance Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #1a5fb4, #1c71d8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo i {
            font-size: 32px;
            margin-right: 15px;
        }
        
        .logo h1 {
            font-size: 24px;
        }
        
        .auth-buttons button {
            background-color: white;
            color: #1a5fb4;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
            transition: all 0.3s;
        }
        
        .auth-buttons button:hover {
            background-color: #e6f0ff;
        }
        
        .login-form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: none;
        }
        
        .login-form h2 {
            margin-bottom: 20px;
            color: #1a5fb4;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn {
            background-color: #1a5fb4;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn:hover {
            background-color: #1c71d8;
        }
        
        .dashboard {
            display: none;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .welcome-message h2 {
            color: #1a5fb4;
        }
        
        .attendance-controls {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .control-group {
            flex: 1;
        }
        
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .attendance-table th, 
        .attendance-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .attendance-table th {
            background-color: #f0f5ff;
            color: #1a5fb4;
            font-weight: 600;
        }
        
        .attendance-table tr:hover {
            background-color: #f9fafb;
        }
        
        .attendance-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .attendance-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e53e3e;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #38a169;
        }
        
        input:checked + .slider:before {
            transform: translateX(30px);
        }
        
        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            flex: 1;
            background: linear-gradient(135deg, #1a5fb4, #1c71d8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .card h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .card .value {
            font-size: 28px;
            font-weight: 700;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn-secondary {
            background-color: #e53e3e;
        }
        
        .btn-secondary:hover {
            background-color: #c53030;
        }
        
        .btn-success {
            background-color: #38a169;
        }
        
        .btn-success:hover {
            background-color: #2f855a;
        }
        
        .logout-btn {
            background-color: #4a5568;
        }
        
        .logout-btn:hover {
            background-color: #2d3748;
        }
        
        .attendance-history {
            display: none;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .attendance-history h2 {
            color: #1a5fb4;
            margin-bottom: 20px;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            color: #718096;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .summary-cards, .attendance-controls, .action-buttons {
                flex-direction: column;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-graduation-cap"></i>
                <h1>SKST University - Attendance System</h1>
            </div>
            <div class="auth-buttons">
                <button id="showLoginBtn">Faculty Login</button>
            </div>
        </header>
        
        <div id="loginForm" class="login-form">
            <h2>Faculty Login</h2>
            <div class="form-group">
                <label for="facultyId">Faculty ID</label>
                <input type="text" id="facultyId" placeholder="Enter your faculty ID">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Enter your password">
            </div>
            <button class="btn" id="loginBtn">Login</button>
        </div>
        
        <div id="dashboard" class="dashboard">
            <div class="dashboard-header">
                <div class="welcome-message">
                    <h2>Welcome, <span id="facultyName">Dr. Smith Johnson</span></h2>
                    <p>Department of <span id="department">Computer Science</span></p>
                </div>
                <div class="action-buttons">
                    <button class="btn logout-btn" id="logoutBtn">Logout</button>
                </div>
            </div>
            
            <div class="summary-cards">
                <div class="card">
                    <h3>Today's Attendance</h3>
                    <div class="value"><span id="presentCount">0</span>/<span id="totalStudents">0</span></div>
                </div>
                <div class="card">
                    <h3>Overall Percentage</h3>
                    <div class="value"><span id="attendancePercentage">0</span>%</div>
                </div>
            </div>
            
            <div class="attendance-controls">
                <div class="control-group">
                    <label for="courseSelect">Select Course</label>
                    <select id="courseSelect" class="form-control">
                        <option value="CS101">CS101 - Introduction to Programming</option>
                        <option value="CS201">CS201 - Data Structures</option>
                        <option value="CS301">CS301 - Database Systems</option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="dateSelect">Select Date</label>
                    <input type="date" id="dateSelect" class="form-control" value="">
                </div>
            </div>
            
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="attendanceList">
                    <!-- Attendance data will be populated here -->
                </tbody>
            </table>
            
            <div class="action-buttons">
                <button class="btn btn-success" id="saveAttendanceBtn">Save Attendance</button>
                <button class="btn" id="viewHistoryBtn">View Attendance History</button>
            </div>
        </div>
        
        <div id="attendanceHistory" class="attendance-history">
            <h2>Attendance History</h2>
            <div class="attendance-controls">
                <div class="control-group">
                    <label for="historyCourseSelect">Select Course</label>
                    <select id="historyCourseSelect" class="form-control">
                        <option value="CS101">CS101 - Introduction to Programming</option>
                        <option value="CS201">CS201 - Data Structures</option>
                        <option value="CS301">CS301 - Database Systems</option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="historyDateSelect">Select Date</label>
                    <input type="date" id="historyDateSelect" class="form-control">
                </div>
            </div>
            
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Course</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Percentage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="historyList">
                    <!-- History data will be populated here -->
                </tbody>
            </table>
            
            <div class="action-buttons">
                <button class="btn" id="backToDashboardBtn">Back to Dashboard</button>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2025 SKST University. All rights reserved.</p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date as default
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dateSelect').value = today;
            document.getElementById('historyDateSelect').value = today;
            
            // Sample data - in a real application, this would come from a database
            const sampleStudents = [
                { id: 1001, name: 'Alice Johnson' },
                { id: 1002, name: 'Bob Smith' },
                { id: 1003, name: 'Charlie Brown' },
                { id: 1004, name: 'Diana Prince' },
                { id: 1005, name: 'Edward Davis' },
                { id: 1006, name: 'Fiona Miller' },
                { id: 1007, name: 'George Wilson' },
                { id: 1008, name: 'Hannah Clark' }
            ];
            
            const sampleAttendance = [
                { date: '2025-08-28', course: 'CS101', present: 6, absent: 2, percentage: 75 },
                { date: '2025-08-25', course: 'CS101', present: 7, absent: 1, percentage: 87.5 },
                { date: '2025-08-21', course: 'CS201', present: 5, absent: 3, percentage: 62.5 }
            ];
            
            // DOM Elements
            const showLoginBtn = document.getElementById('showLoginBtn');
            const loginForm = document.getElementById('loginForm');
            const dashboard = document.getElementById('dashboard');
            const loginBtn = document.getElementById('loginBtn');
            const logoutBtn = document.getElementById('logoutBtn');
            const saveAttendanceBtn = document.getElementById('saveAttendanceBtn');
            const viewHistoryBtn = document.getElementById('viewHistoryBtn');
            const attendanceHistory = document.getElementById('attendanceHistory');
            const backToDashboardBtn = document.getElementById('backToDashboardBtn');
            const courseSelect = document.getElementById('courseSelect');
            const dateSelect = document.getElementById('dateSelect');
            const attendanceList = document.getElementById('attendanceList');
            const presentCountElem = document.getElementById('presentCount');
            const totalStudentsElem = document.getElementById('totalStudents');
            const attendancePercentageElem = document.getElementById('attendancePercentage');
            
            // Show login form
            showLoginBtn.addEventListener('click', function() {
                loginForm.style.display = 'block';
                dashboard.style.display = 'none';
                attendanceHistory.style.display = 'none';
            });
            
            // Login functionality
            loginBtn.addEventListener('click', function() {
                const facultyId = document.getElementById('facultyId').value;
                const password = document.getElementById('password').value;
                
                // Simple validation - in a real application, this would verify against a database
                if (facultyId && password) {
                    loginForm.style.display = 'none';
                    dashboard.style.display = 'block';
                    loadAttendanceData();
                } else {
                    alert('Please enter both Faculty ID and Password');
                }
            });
            
            // Logout functionality
            logoutBtn.addEventListener('click', function() {
                dashboard.style.display = 'none';
                attendanceHistory.style.display = 'none';
                loginForm.style.display = 'block';
            });
            
            // View attendance history
            viewHistoryBtn.addEventListener('click', function() {
                dashboard.style.display = 'none';
                attendanceHistory.style.display = 'block';
                loadAttendanceHistory();
            });
            
            // Back to dashboard from history
            backToDashboardBtn.addEventListener('click', function() {
                attendanceHistory.style.display = 'none';
                dashboard.style.display = 'block';
            });
            
            // Load attendance data based on selected course and date
            function loadAttendanceData() {
                const course = courseSelect.value;
                const date = dateSelect.value;
                
                // Clear previous data
                attendanceList.innerHTML = '';
                
                // Add students to the table
                let presentCount = 0;
                sampleStudents.forEach(student => {
                    const row = document.createElement('tr');
                    
                    // Student ID
                    const idCell = document.createElement('td');
                    idCell.textContent = student.id;
                    row.appendChild(idCell);
                    
                    // Student Name
                    const nameCell = document.createElement('td');
                    nameCell.textContent = student.name;
                    row.appendChild(nameCell);
                    
                    // Status (default to present)
                    const statusCell = document.createElement('td');
                    statusCell.textContent = 'Present';
                    statusCell.classList.add('present');
                    row.appendChild(statusCell);
                    
                    // Toggle switch
                    const actionsCell = document.createElement('td');
                    const toggleLabel = document.createElement('label');
                    toggleLabel.className = 'attendance-toggle';
                    
                    const toggleInput = document.createElement('input');
                    toggleInput.type = 'checkbox';
                    toggleInput.checked = true; // Default to present
                    
                    const toggleSlider = document.createElement('span');
                    toggleSlider.className = 'slider';
                    
                    toggleLabel.appendChild(toggleInput);
                    toggleLabel.appendChild(toggleSlider);
                    
                    toggleInput.addEventListener('change', function() {
                        if (this.checked) {
                            statusCell.textContent = 'Present';
                            statusCell.classList.remove('absent');
                            statusCell.classList.add('present');
                            presentCount++;
                        } else {
                            statusCell.textContent = 'Absent';
                            statusCell.classList.remove('present');
                            statusCell.classList.add('absent');
                            presentCount--;
                        }
                        
                        updateSummary(presentCount, sampleStudents.length);
                    });
                    
                    actionsCell.appendChild(toggleLabel);
                    row.appendChild(actionsCell);
                    
                    attendanceList.appendChild(row);
                    presentCount++;
                });
                
                updateSummary(presentCount, sampleStudents.length);
            }
            
            // Update the summary cards
            function updateSummary(present, total) {
                presentCountElem.textContent = present;
                totalStudentsElem.textContent = total;
                
                const percentage = total > 0 ? ((present / total) * 100).toFixed(2) : 0;
                attendancePercentageElem.textContent = percentage;
            }
            
            // Load attendance history
            function loadAttendanceHistory() {
                const historyList = document.getElementById('historyList');
                historyList.innerHTML = '';
                
                sampleAttendance.forEach(record => {
                    const row = document.createElement('tr');
                    
                    // Date
                    const dateCell = document.createElement('td');
                    dateCell.textContent = record.date;
                    row.appendChild(dateCell);
                    
                    // Course
                    const courseCell = document.createElement('td');
                    courseCell.textContent = record.course;
                    row.appendChild(courseCell);
                    
                    // Present
                    const presentCell = document.createElement('td');
                    presentCell.textContent = record.present;
                    row.appendChild(presentCell);
                    
                    // Absent
                    const absentCell = document.createElement('td');
                    absentCell.textContent = record.absent;
                    row.appendChild(absentCell);
                    
                    // Percentage
                    const percentageCell = document.createElement('td');
                    percentageCell.textContent = `${record.percentage}%`;
                    row.appendChild(percentageCell);
                    
                    // Actions
                    const actionsCell = document.createElement('td');
                    const editButton = document.createElement('button');
                    editButton.textContent = 'Edit';
                    editButton.className = 'btn';
                    editButton.style.padding = '5px 10px';
                    editButton.style.fontSize = '14px';
                    
                    editButton.addEventListener('click', function() {
                        alert(`Edit functionality would open the record for ${record.date} - ${record.course}`);
                    });
                    
                    actionsCell.appendChild(editButton);
                    row.appendChild(actionsCell);
                    
                    historyList.appendChild(row);
                });
            }
            
            // Save attendance
            saveAttendanceBtn.addEventListener('click', function() {
                const course = courseSelect.value;
                const date = dateSelect.value;
                
                // In a real application, this would save to a database
                alert(`Attendance for ${course} on ${date} has been saved successfully!`);
            });
            
            // Initialize the page
            loadAttendanceData();
            
            // Event listeners for filters
            courseSelect.addEventListener('change', loadAttendanceData);
            dateSelect.addEventListener('change', loadAttendanceData);
        });
    </script>
</body>
</html>