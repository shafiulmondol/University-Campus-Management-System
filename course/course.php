<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Management - SKST University</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
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
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        h1 {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f5ff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 250px;
        }
        
        .btn {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #00b09b, #96c93d);
        }
        
        .btn-export {
            background: linear-gradient(135deg, #ff8c00, #ff5500);
        }
        
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #2b5876;
        }
        
        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 180px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            position: sticky;
            top: 0;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f0f5ff;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit {
            background: #4e4376;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        
        .status-enrolled {
            background: #00a651;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .status-dropped {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .pagination button.active {
            background: #2b5876;
            color: white;
            border: 1px solid #2b5876;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-top: 4px solid #4e4376;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #2b5876;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .search-box {
                width: 100%;
            }
            
            .search-box input {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .filter-group select, .filter-group input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-graduate"></i> Enrollment Management System</h1>
        
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-label">Total Enrollments</div>
                <div class="stat-number">1,248</div>
                <div class="stat-label">Current Semester</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Students</div>
                <div class="stat-number">842</div>
                <div class="stat-label">Enrolled in Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Faculty Members</div>
                <div class="stat-number">64</div>
                <div class="stat-label">Teaching Courses</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Available Courses</div>
                <div class="stat-number">78</div>
                <div class="stat-label">Across Departments</div>
            </div>
        </div>
        
        <div class="controls">
            <div class="search-box">
                <input type="text" placeholder="Search enrollments...">
                <button class="btn"><i class="fas fa-search"></i> Search</button>
            </div>
            <div>
                <button class="btn btn-add"><i class="fas fa-plus"></i> New Enrollment</button>
                <button class="btn btn-export"><i class="fas fa-file-export"></i> Export</button>
            </div>
        </div>
        
        <div class="filters">
            <div class="filter-group">
                <label for="department">Department</label>
                <select id="department">
                    <option value="">All Departments</option>
                    <option value="cs">Computer Science</option>
                    <option value="math">Mathematics</option>
                    <option value="physics">Physics</option>
                    <option value="english">English</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="semester">Semester</label>
                <select id="semester">
                    <option value="">All Semesters</option>
                    <option value="fall2023">Fall 2023</option>
                    <option value="spring2023">Spring 2023</option>
                    <option value="summer2023">Summer 2023</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status">
                    <option value="">All Statuses</option>
                    <option value="enrolled">Enrolled</option>
                    <option value="pending">Pending</option>
                    <option value="dropped">Dropped</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="faculty">Faculty</label>
                <select id="faculty">
                    <option value="">All Faculty</option>
                    <option value="smith">Dr. Smith</option>
                    <option value="johnson">Dr. Johnson</option>
                    <option value="williams">Prof. Williams</option>
                </select>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Enrollment ID</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Faculty</th>
                    <th>Department</th>
                    <th>Enrollment Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ENR-2023-001</td>
                    <td>John Smith</td>
                    <td>CS101 - Introduction to Computer Science</td>
                    <td>Dr. Anderson</td>
                    <td>Computer Science</td>
                    <td>2023-09-01</td>
                    <td><span class="status-enrolled">Enrolled</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>ENR-2023-002</td>
                    <td>Maria Garcia</td>
                    <td>MATH202 - Calculus II</td>
                    <td>Dr. Johnson</td>
                    <td>Mathematics</td>
                    <td>2023-09-02</td>
                    <td><span class="status-enrolled">Enrolled</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>ENR-2023-003</td>
                    <td>David Kim</td>
                    <td>PHYS301 - Advanced Physics</td>
                    <td>Dr. Brown</td>
                    <td>Physics</td>
                    <td>2023-09-03</td>
                    <td><span class="status-pending">Pending</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>ENR-2023-004</td>
                    <td>Sarah Johnson</td>
                    <td>ENG101 - English Composition</td>
                    <td>Prof. Williams</td>
                    <td>English</td>
                    <td>2023-09-01</td>
                    <td><span class="status-enrolled">Enrolled</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>ENR-2023-005</td>
                    <td>Robert Chen</td>
                    <td>CS101 - Introduction to Computer Science</td>
                    <td>Dr. Anderson</td>
                    <td>Computer Science</td>
                    <td>2023-09-05</td>
                    <td><span class="status-dropped">Dropped</span></td>
                    <td class="action-buttons">
                        <button class="btn-edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="pagination">
            <button><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <button>4</button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <script>
        // Simple JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event to all edit buttons
            const editButtons = document.querySelectorAll('.btn-edit');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    alert('Edit enrollment functionality would open here.');
                });
            });
            
            // Add click event to all delete buttons
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this enrollment record?')) {
                        alert('Enrollment would be deleted here.');
                    }
                });
            });
            
            // Add click event to add new enrollment button
            const addButton = document.querySelector('.btn-add');
            addButton.addEventListener('click', function() {
                alert('Add new enrollment form would open here.');
            });
            
            // Add functionality to filter dropdowns
            const filterSelects = document.querySelectorAll('.filter-group select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    alert('Filtering would be applied here based on: ' + this.id + ' = ' + this.value);
                });
            });
        });
    </script>
</body>
</html>