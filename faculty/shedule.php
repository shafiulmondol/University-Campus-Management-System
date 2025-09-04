//php file
<?php
include 'config.php';
checkFacultyLogin();

$faculty_id = $_SESSION['faculty_id'];

// Get faculty schedule
$schedule_sql = "SELECT c.course_code, c.course_name, ci.class_day, ci.class_time, ci.room_number
                 FROM course_instructor ci
                 JOIN course c ON ci.course_id = c.course_id
                 WHERE ci.faculty_id = ?
                 ORDER BY FIELD(ci.class_day, 'Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                 ci.class_time";
$stmt = $mysqli->prepare($schedule_sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$schedule_result = $stmt->get_result();
$schedule = $schedule_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Organize schedule by day
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
    $schedule_by_day[$class['class_day']][] = $class;
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
        /* Reuse the CSS from faculty1.php with some additions */
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
        
        /* ... (other styles from faculty1.php) ... */
        
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
                <li><button onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> Logout</button></li>
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
                
                <button class="print-btn" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Schedule
                </button>
            </div>
        </div>
    </div>
</body>
</html>