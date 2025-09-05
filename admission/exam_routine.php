<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Routine - SKST University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --skst-blue: #1a4f8b;
            --skst-light-blue: #4b86b4;
            --skst-green: #2a9d8f;
            --skst-light: #e8f1f5;
            --skst-dark: #1c2b3a;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding-top: 20px;
        }
        
        .skst-header {
            background: linear-gradient(135deg, var(--skst-blue), var(--skst-light-blue));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .skst-card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
            transition: transform 0.3s ease;
            background-color: white;
        }
        
        .skst-card:hover {
            transform: translateY(-5px);
        }
        
        .skst-card-header {
            background: linear-gradient(135deg, var(--skst-blue), var(--skst-light-blue));
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .exam-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .exam-item:last-child {
            border-bottom: none;
        }
        
        .exam-time {
            font-weight: 600;
            color: var(--skst-blue);
        }
        
        .exam-course {
            font-weight: 600;
            color: var(--skst-dark);
        }
        
        .exam-room {
            background-color: var(--skst-light);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            color: var(--skst-blue);
        }
        
        .no-exams {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .filter-section {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .btn-skst {
            background-color: var(--skst-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-skst:hover {
            background-color: var(--skst-green);
            transform: translateY(-2px);
        }
        
        .skst-footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9rem;
            background-color: var(--skst-light);
            border-radius: 10px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }
        
        .skst-badge {
            background-color: var(--skst-green);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .result-card {
            border-left: 4px solid var(--skst-green);
        }
        
        @media (max-width: 768px) {
            .skst-header h1 {
                font-size: 24px;
            }
            
            .skst-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="skst-header text-center">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div></div>
                <h1><i class="fas fa-calendar-alt me-2"></i>Exam Routine</h1>
                <div class="logo">SKST UNIVERSITY</div>
            </div>
            <p class="mt-3">Department of Computer Science and Engineering</p>
            <p class="mb-0">View your examination schedule based on enrollment</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-select" id="semester">
                        <option value="">Select Semester</option>
                        <option value="Fall-2025">Fall 2025</option>
                        <option value="Spring-2025">Spring 2025</option>
                        <option value="Summer-2025">Summer 2025</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="course" class="form-label">Course</label>
                    <select class="form-select" id="course">
                        <option value="">Select Course</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-skst" id="filterBtn"><i class="fas fa-filter me-2"></i>Filter Exams</button>
                <button class="btn btn-outline-secondary ms-2" id="resetBtn"><i class="fas fa-sync me-2"></i>Reset Filters</button>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="skst-card">
            <div class="skst-card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-circle me-2"></i>Important Notice</span>
                <span class="skst-badge">Updated Today</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i> All exams will be held at the main campus. Students must bring their ID cards and admit cards. No electronic devices are allowed in the examination hall.
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="resultsContainer">
            <!-- Results will be displayed here -->
        </div>

        <!-- Contact Information -->
        <div class="skst-card mt-4">
            <div class="skst-card-header">
                <i class="fas fa-headset me-2"></i>Contact Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Exam Controller Office</h5>
                        <p><i class="fas fa-phone me-2"></i>+880 1712 345678</p>
                        <p><i class="fas fa-envelope me-2"></i>exam@skst.edu.bd</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i>Room 101, Admin Building</p>
                    </div>
                    <div class="col-md-6">
                        <h5>CSE Department</h5>
                        <p><i class="fas fa-phone me-2"></i>+880 1713 456789</p>
                        <p><i class="fas fa-envelope me-2"></i>cse@skst.edu.bd</p>
                        <p><i class="fas fa-map-marker-alt me-2"></i>3rd Floor, Tech Building</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="skst-footer">
            <p>© 2025 SKST University. All rights reserved.</p>
            <p>Contact: examoffice@skst.edu.bd | Phone: +880 1712 345678</p>
            <p class="mb-0">Uttara, Dhaka-1230, Bangladesh</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sample data - in a real application, this would come from your database
        const courses = [
            { id: 'CSC112', name: 'CSC 112 - Database Management Systems', semester: 'Fall-2025' },
            { id: 'CSC222', name: 'CSC 222 - Computer Architecture', semester: 'Fall-2025' },
            { id: 'EEE100', name: 'EEE 100 - Electrical Fundamentals', semester: 'Fall-2025' },
            { id: 'MAT101', name: 'MAT 101 - Linear Algebra', semester: 'Spring-2025' },
            { id: 'ENG101', name: 'ENG 101 - English Composition', semester: 'Spring-2025' }
        ];

        const examData = [
            { 
                id: 1, 
                course: 'CSC112', 
                courseName: 'CSC 112 - Database Management Systems',
                date: '2025-09-15', 
                time: '8:30 AM - 11:30 AM', 
                room: 'Room 402 (Main Building)', 
                professor: 'Prof. Dr. Rahman',
                semester: 'Fall-2025'
            },
            { 
                id: 2, 
                course: 'CSC222', 
                courseName: 'CSC 222 - Computer Architecture',
                date: '2025-09-16', 
                time: '2:00 PM - 5:00 PM', 
                room: 'Room 305 (Tech Block)', 
                professor: 'Dr. Fatima Ahmed',
                semester: 'Fall-2025'
            },
            { 
                id: 3, 
                course: 'EEE100', 
                courseName: 'EEE 100 - Electrical Fundamentals',
                date: '2025-09-18', 
                time: '9:00 AM - 12:00 PM', 
                room: 'Room 201 (Engineering Wing)', 
                professor: 'Prof. Abdul Malik',
                semester: 'Fall-2025'
            },
            { 
                id: 4, 
                course: 'MAT101', 
                courseName: 'MAT 101 - Linear Algebra',
                date: '2025-03-10', 
                time: '10:00 AM - 1:00 PM', 
                room: 'Room 501', 
                professor: 'Dr. Nusrat Jahan',
                semester: 'Spring-2025'
            },
            { 
                id: 5, 
                course: 'ENG101', 
                courseName: 'ENG 101 - English Composition',
                date: '2025-03-12', 
                time: '2:00 PM - 5:00 PM', 
                room: 'Room 302', 
                professor: 'Prof. Robert Smith',
                semester: 'Spring-2025'
            }
        ];

        // Populate course dropdown based on selected semester
        document.getElementById('semester').addEventListener('change', function() {
            const semester = this.value;
            const courseSelect = document.getElementById('course');
            
            // Clear previous options except the first one
            while (courseSelect.options.length > 1) {
                courseSelect.remove(1);
            }
            
            // Add courses for the selected semester
            if (semester) {
                const semesterCourses = courses.filter(course => course.semester === semester);
                semesterCourses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.name;
                    courseSelect.appendChild(option);
                });
            }
        });

        // Filter exams based on selections
        document.getElementById('filterBtn').addEventListener('click', function() {
            const semester = document.getElementById('semester').value;
            const course = document.getElementById('course').value;
            const date = document.getElementById('date').value;
            
            // Filter exam data based on selections
            let filteredExams = examData;
            
            if (semester) {
                filteredExams = filteredExams.filter(exam => exam.semester === semester);
            }
            
            if (course) {
                filteredExams = filteredExams.filter(exam => exam.course === course);
            }
            
            if (date) {
                filteredExams = filteredExams.filter(exam => exam.date === date);
            }
            
            // Display results
            displayResults(filteredExams);
        });

        // Reset filters
        document.getElementById('resetBtn').addEventListener('click', function() {
            document.getElementById('semester').value = '';
            document.getElementById('course').value = '';
            document.getElementById('date').value = '';
            
            // Clear course options except the first one
            const courseSelect = document.getElementById('course');
            while (courseSelect.options.length > 1) {
                courseSelect.remove(1);
            }
            
            // Display all exams
            displayResults(examData);
        });

        // Display results in the container
        function displayResults(exams) {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';
            
            if (exams.length === 0) {
                container.innerHTML = `
                    <div class="skst-card">
                        <div class="no-exams">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <h4>No exams found</h4>
                            <p>Try adjusting your filters to find exam schedules</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Group exams by date
            const examsByDate = {};
            exams.forEach(exam => {
                if (!examsByDate[exam.date]) {
                    examsByDate[exam.date] = [];
                }
                examsByDate[exam.date].push(exam);
            });
            
            // Create HTML for each date group
            for (const date in examsByDate) {
                const dateExams = examsByDate[date];
                const formattedDate = formatDate(date);
                
                const dateCard = document.createElement('div');
                dateCard.className = 'skst-card';
                dateCard.innerHTML = `
                    <div class="skst-card-header">
                        <i class="fas fa-calendar-day me-2"></i>${formattedDate}
                    </div>
                    <div class="card-body p-0">
                `;
                
                dateExams.forEach(exam => {
                    const examElement = document.createElement('div');
                    examElement.className = 'exam-item result-card';
                    examElement.innerHTML = `
                        <div class="d-flex justify-content-between">
                            <span class="exam-time">${exam.time}</span>
                            <span class="text-muted">${formatDay(date)}</span>
                        </div>
                        <h5 class="exam-course mt-2">${exam.courseName}</h5>
                        <div class="d-flex justify-content-between mt-3">
                            <span class="exam-room"><i class="fas fa-door-open me-1"></i>${exam.room}</span>
                            <span class="text-muted">${exam.professor}</span>
                        </div>
                    `;
                    dateCard.querySelector('.card-body').appendChild(examElement);
                });
                
                container.appendChild(dateCard);
            }
        }

        // Helper function to format date as "Monday, September 15, 2025"
        function formatDate(dateString) {
            const date = new Date(dateString);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Helper function to format date as "Mon, Sep 15, 2025"
        function formatDay(dateString) {
            const date = new Date(dateString);
            const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Initialize by showing all exams
        displayResults(examData);
    </script>
</body>
</html>