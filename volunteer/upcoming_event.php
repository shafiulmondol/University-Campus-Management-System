<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['volunteer_sl'])) {
    header("Location: volunteer.php");
    exit();
}

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'skst_university');

// Create connection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle event registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_event'])) {
    $event_id = $_POST['event_id'];
    $volunteer_id = $_SESSION['volunteer_id'];
    $volunteer_name = $_SESSION['volunteer_name'];
    $volunteer_email = $_SESSION['volunteer_email'];
    
    // Check if already registered
    $check_sql = "SELECT * FROM event_registrations WHERE event_id = ? AND volunteer_id = ?";
    $check_stmt = $mysqli->prepare($check_sql);
    $check_stmt->bind_param("ii", $event_id, $volunteer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Register for event
        $register_sql = "INSERT INTO event_registrations (event_id, volunteer_id, volunteer_name, volunteer_email, registration_date) VALUES (?, ?, ?, ?, NOW())";
        $register_stmt = $mysqli->prepare($register_sql);
        $register_stmt->bind_param("iiss", $event_id, $volunteer_id, $volunteer_name, $volunteer_email);
        
        if ($register_stmt->execute()) {
            $success_msg = "Successfully registered for the event!";
        } else {
            $error_msg = "Error registering for event: " . $mysqli->error;
        }
        $register_stmt->close();
    } else {
        $error_msg = "You are already registered for this event.";
    }
    $check_stmt->close();
}

// Get upcoming events
$events_sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
$events_result = $mysqli->query($events_sql);
$events = [];
if ($events_result) {
    while ($row = $events_result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Get events the volunteer is registered for
$registered_events = [];
if (isset($_SESSION['volunteer_id'])) {
    $reg_sql = "SELECT event_id FROM event_registrations WHERE volunteer_id = ?";
    $reg_stmt = $mysqli->prepare($reg_sql);
    $reg_stmt->bind_param("i", $_SESSION['volunteer_id']);
    $reg_stmt->execute();
    $reg_result = $reg_stmt->get_result();
    
    while ($row = $reg_result->fetch_assoc()) {
        $registered_events[] = $row['event_id'];
    }
    $reg_stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - SKST University Volunteer Portal</title>
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reuse styles from volunteer.php */
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
        
        /* ================ Navbar Styles ============ */
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
        
        .logo img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background: white;
            padding: 5px;
        }
        
        .logo h1 {
            font-size: 22px;
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
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .nav-buttons button:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .welcome {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        /* ================ Main Layout ============ */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        /* ================ Sidebar Styles ============ */
        .sidebar {
            width: 250px;
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 80px;
            height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 90;
            padding: 25px 0;
        }
        
        .sidebar-menu {
            list-style: none;
            width: 100%;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .sidebar-menu a, .sidebar-menu button {
            display: flex;
            align-items: center;
            color: #4e4376;
            text-decoration: none;
            padding: 12px 25px;
            transition: all 0.3s ease;
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .sidebar-menu a:hover, 
        .sidebar-menu a.active, 
        .sidebar-menu button:hover {
            background-color: #f0f5ff;
            color: #2b5876;
            border-right: 4px solid #2b5876;
        }
        
        .sidebar-menu i {
            margin-right: 12px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        /* ================ Content Area ============ */
        .content-area {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
            height: calc(100vh - 80px);
        }
        
        .page-header {
            background: linear-gradient(to right, #f0f5ff, #f8faff);
            padding: 20px 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title {
            color: #2b5876;
            font-size: 30px;
            font-weight: 600;
            margin: 0;
        }
        
        /* ================ Events Section ============ */
        .events-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .event-header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px;
            position: relative;
        }
        
        .event-date {
            font-size: 14px;
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .event-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .event-location {
            font-size: 14px;
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .event-body {
            padding: 20px;
        }
        
        .event-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .event-details {
            margin-bottom: 20px;
        }
        
        .event-detail {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #555;
        }
        
        .event-detail i {
            margin-right: 10px;
            color: #4e4376;
            width: 20px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        
        .btn-register:hover {
            opacity: 0.9;
        }
        
        .btn-registered {
            background: #28a745;
            cursor: not-allowed;
        }
        
        .no-events {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
        }
        
        .no-events i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* ================ Responsive Design ============ */
        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                top: 0;
            }
            
            .content-area {
                height: auto;
            }
            
            .events-container {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
                padding: 15px;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Dashboard -->
    <div class="navbar">
        <div class="logo">
            <h1>SKST University Volunteers</h1>
        </div>
        
        <div class="nav-buttons">
            <button onclick="location.href='../index.html'"><i class="fas fa-home"></i> Home</button>
        </div>
    </div>
    
    <div class="main-layout">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="volunteer.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="upcoming_event.php" class="active">
                        <i class="fas fa-calendar"></i> Upcoming Events
                    </a>
                </li>
                
                <li>
                    <button onclick="location.href='volunteer.php?logout=1'">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="content-area">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Upcoming Events</h1>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <?php if (empty($events)): ?>
                <div class="no-events">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Upcoming Events</h3>
                    <p>There are currently no upcoming events. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="events-container">
                    <?php foreach ($events as $event): 
                        $isRegistered = in_array($event['event_id'], $registered_events);
                        $eventDate = new DateTime($event['event_date']);
                        $formattedDate = $eventDate->format('F j, Y');
                    ?>
                        <div class="event-card">
                            <div class="event-header">
                                <div class="event-date">
                                    <i class="far fa-calendar"></i> <?php echo $formattedDate; ?>
                                    <?php if ($event['event_time']): ?>
                                        <i class="far fa-clock" style="margin-left: 15px;"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                                    <?php endif; ?>
                                </div>
                                <h2 class="event-name"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                                <div class="event-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                </div>
                            </div>
                            
                            <div class="event-body">
                                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                
                                <div class="event-details">
                                    <?php if ($event['organizer']): ?>
                                        <div class="event-detail">
                                            <i class="fas fa-user-friends"></i> Organized by: <?php echo htmlspecialchars($event['organizer']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['contact_info']): ?>
                                        <div class="event-detail">
                                            <i class="fas fa-envelope"></i> Contact: <?php echo htmlspecialchars($event['contact_info']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($event['volunteers_needed'] > 0): ?>
                                        <div class="event-detail">
                                            <i class="fas fa-hands-helping"></i> Volunteers needed: <?php echo $event['volunteers_needed']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <form method="post">
                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                    <?php if ($isRegistered): ?>
                                        <button type="button" class="btn-register btn-registered" disabled>
                                            <i class="fas fa-check-circle"></i> Registered
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="register_event" class="btn-register">
                                            <i class="fas fa-user-plus"></i> Register for Event
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Simple JavaScript for interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add active class to clicked sidebar items
            const sidebarItems = document.querySelectorAll('.sidebar-menu a, .sidebar-menu button');
            sidebarItems.forEach(item => {
                item.addEventListener('click', function() {
                    sidebarItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>