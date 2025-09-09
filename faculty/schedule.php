<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];

// Debug: Check if faculty_id is set
if (empty($faculty_id)) {
    die("Faculty ID not found in session. Please log in again.");
}

// Get faculty schedule with error handling
$schedule_sql = "SELECT c.course_code, c.course_name, ci.class_day, ci.class_time, ci.room_number
                 FROM course_instructor ci
                 JOIN course c ON ci.course_id = c.course_id
                 WHERE ci.faculty_id = ?
                 ORDER BY 
                   CASE 
                     WHEN LOWER(ci.class_day) = 'saturday' THEN 1
                     WHEN LOWER(ci.class_day) = 'sunday' THEN 2
                     WHEN LOWER(ci.class_day) = 'monday' THEN 3
                     WHEN LOWER(ci.class_day) = 'tuesday' THEN 4
                     WHEN LOWER(ci.class_day) = 'wednesday' THEN 5
                     WHEN LOWER(ci.class_day) = 'thursday' THEN 6
                     WHEN LOWER(ci.class_day) = 'friday' THEN 7
                     ELSE 8
                   END,
                 ci.class_time";
                 
// Debug: Check connection
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$stmt = $mysqli->prepare($schedule_sql);
if (!$stmt) {
    die("Error in prepared statement: " . $mysqli->error);
}

$stmt->bind_param("i", $faculty_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$schedule_result = $stmt->get_result();
$schedule = [];

if ($schedule_result) {
    $schedule = $schedule_result->fetch_all(MYSQLI_ASSOC);
} else {
    // No error, just no schedule data
    $schedule = [];
}

$stmt->close();

// Organize schedule by day (case-insensitive)
$schedule_by_day = [
    'Saturday' => [],
    'Sunday' => [],
    'Monday' => [],
    'Tuesday' => [],
    'Wednesday' => [],
    'Thursday' => [],
    'Friday' => []
];

foreach ($schedule as $class) {
    if (isset($class['class_day'])) {
        // Normalize day name to match our keys
        $normalized_day = ucfirst(strtolower($class['class_day']));
        
        if (array_key_exists($normalized_day, $schedule_by_day)) {
            // Update the class day to normalized version
            $class['class_day'] = $normalized_day;
            $schedule_by_day[$normalized_day][] = $class;
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Schedule - SKST University</title>
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
        
        .navbar {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .nav-buttons button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .nav-buttons button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px 0;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            text-decoration: none;
            color: #333;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu button:hover {
            background: #f0f5ff;
        }
        
        .sidebar-menu a.active {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
        }
        
        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 28px;
            color: #2b5876;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .schedule-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .schedule-day {
            background: #f8faff;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #4e4376;
        }
        
        .day-header {
            color: #2b5876;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 18px;
            font-weight: 600;
        }
        
        .class-item {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .class-time {
            font-weight: 600;
            color: #4e4376;
            margin-bottom: 5px;
        }
        
        .class-course {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .class-details {
            display: flex;
            justify-content: space-between;
            color: #666;
            font-size: 14px;
        }
        
        .no-classes {
            color: #888;
            font-style: italic;
            padding: 10px;
        }
        
        .print-btn {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .debug-info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .content-area {
                order: 1;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .schedule-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media print {
            .navbar, .sidebar, .page-header button, .print-btn {
                display: none;
            }
            
            .content-area {
                margin: 0;
                padding: 0;
            }
            
            .schedule-container {
                box-shadow: none;
                padding: 0;
            }
            
            .schedule-grid {
                display: block;
            }
            
            .schedule-day {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <div style="width: 50px; height: 50px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center;">
                <span style="font-weight: bold; color: #2b5876;">SKST</span>
            </div>
            <h1>SKST University Faculty</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='faculty1.php'"><i class="fas fa-user"></i> Profile</button>
            <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
            <button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </div>
    </div>
    
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="faculty1.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="attendance.php"><i class="fas fa-user-check"></i> Attendance</a></li>
                <li><a href="materials.php"><i class="fas fa-file-alt"></i> Materials</a></li>
              
            </ul>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Teaching Schedule</h1>
                <button class="print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Schedule
                </button>
            </div>
            
            <div class="schedule-container">
                <h2>Weekly Class Schedule</h2>
                <p>This is your teaching schedule for the current semester.</p>
                
                <?php if (empty($schedule)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i> 
                        No schedule found for Faculty ID: <?php echo $faculty_id; ?>.
                        Please contact administration if this is incorrect.
                    </div>
                <?php else: ?>
                    <div class="schedule-grid">
                        <?php foreach ($schedule_by_day as $day => $classes): ?>
                            <div class="schedule-day">
                                <div class="day-header">
                                    <i class="fas fa-calendar-day"></i> <?php echo $day; ?>
                                </div>
                                
                                <?php if (count($classes) > 0): ?>
                                    <?php foreach ($classes as $class): ?>
                                        <div class="class-item">
                                            <div class="class-time">
                                                <i class="fas fa-clock"></i> <?php echo $class['class_time']; ?>
                                            </div>
                                            <div class="class-course">
                                                <?php echo $class['course_code'] . ' - ' . $class['course_name']; ?>
                                            </div>
                                            <div class="class-details">
                                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $class['room_number']; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-classes">No classes scheduled</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <button class="print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Schedule
                </button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Schedule page loaded successfully');
        });
    </script>
</body>
</html>