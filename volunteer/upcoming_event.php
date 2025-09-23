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
        // Check if event has available slots
        $slot_sql = "SELECT e.volunteers_needed, COUNT(er.registration_id) as registered_count 
                     FROM events e 
                     LEFT JOIN event_registrations er ON e.event_id = er.event_id 
                     WHERE e.event_id = ? 
                     GROUP BY e.event_id";
        $slot_stmt = $mysqli->prepare($slot_sql);
        $slot_stmt->bind_param("i", $event_id);
        $slot_stmt->execute();
        $slot_result = $slot_stmt->get_result();
        
        if ($slot_result->num_rows > 0) {
            $slot_data = $slot_result->fetch_assoc();
            $available_slots = $slot_data['volunteers_needed'] - $slot_data['registered_count'];
            
            if ($available_slots > 0) {
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
                $error_msg = "Sorry, this event is already full. No more volunteers needed.";
            }
        } else {
            $error_msg = "Event not found.";
        }
        $slot_stmt->close();
    } else {
        $error_msg = "You are already registered for this event.";
    }
    $check_stmt->close();
}

// Handle event cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_registration'])) {
    $event_id = $_POST['event_id'];
    $volunteer_id = $_SESSION['volunteer_id'];
    
    $cancel_sql = "DELETE FROM event_registrations WHERE event_id = ? AND volunteer_id = ?";
    $cancel_stmt = $mysqli->prepare($cancel_sql);
    $cancel_stmt->bind_param("ii", $event_id, $volunteer_id);
    
    if ($cancel_stmt->execute()) {
        $success_msg = "Registration cancelled successfully.";
    } else {
        $error_msg = "Error cancelling registration: " . $mysqli->error;
    }
    $cancel_stmt->close();
}

// Get filter parameters
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query for upcoming events with filters
$events_sql = "SELECT e.*, 
               COUNT(er.registration_id) as registered_count,
               (e.volunteers_needed - COUNT(er.registration_id)) as available_slots
               FROM events e 
               LEFT JOIN event_registrations er ON e.event_id = er.event_id 
               WHERE e.event_date >= CURDATE()";

// Add month filter
if (!empty($filter_month) && $filter_month != 'all') {
    $events_sql .= " AND MONTH(e.event_date) = ?";
}

// Add category filter (assuming we have event categories)
if (!empty($filter_category) && $filter_category != 'all') {
    // This would require adding a category column to events table
    // $events_sql .= " AND e.category = ?";
}

$events_sql .= " GROUP BY e.event_id ORDER BY e.event_date ASC";

$events_stmt = $mysqli->prepare($events_sql);

// Bind parameters if filters are applied
if (!empty($filter_month) && $filter_month != 'all') {
    $events_stmt->bind_param("i", $filter_month);
}

$events_stmt->execute();
$events_result = $events_stmt->get_result();
$events = [];
if ($events_result) {
    while ($row = $events_result->fetch_assoc()) {
        $events[] = $row;
    }
}
$events_stmt->close();

// Get events the volunteer is registered for with details
$registered_events = [];
if (isset($_SESSION['volunteer_id'])) {
    $reg_sql = "SELECT e.*, er.registration_date 
                FROM events e 
                JOIN event_registrations er ON e.event_id = er.event_id 
                WHERE er.volunteer_id = ? AND e.event_date >= CURDATE() 
                ORDER BY e.event_date ASC";
    $reg_stmt = $mysqli->prepare($reg_sql);
    $reg_stmt->bind_param("i", $_SESSION['volunteer_id']);
    $reg_stmt->execute();
    $reg_result = $reg_stmt->get_result();
    
    while ($row = $reg_result->fetch_assoc()) {
        $registered_events[] = $row;
    }
    $reg_stmt->close();
}

// Get past events for statistics
$past_events_sql = "SELECT e.*, er.registration_date, er.attendance_status 
                    FROM events e 
                    JOIN event_registrations er ON e.event_id = er.event_id 
                    WHERE er.volunteer_id = ? AND e.event_date < CURDATE() 
                    ORDER BY e.event_date DESC 
                    LIMIT 5";
$past_events_stmt = $mysqli->prepare($past_events_sql);
$past_events_stmt->bind_param("i", $_SESSION['volunteer_id']);
$past_events_stmt->execute();
$past_events_result = $past_events_stmt->get_result();
$past_events = [];
while ($row = $past_events_result->fetch_assoc()) {
    $past_events[] = $row;
}
$past_events_stmt->close();

$mysqli->close();

// Get months for filter
$months = [
    'all' => 'All Months',
    '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
    '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
    '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

// Get current month for default filter
$current_month = date('n');
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
        /* Enhanced CSS with new features */
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
        
        /* Navbar Styles */
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
        
        /* Main Layout */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        /* Sidebar Styles */
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
            margin-bottom: 8px;
            width: 100%;
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
        
        /* Content Area */
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
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            color: #2b5876;
            font-size: 30px;
            font-weight: 600;
            margin: 0;
        }
        
        /* Filter Section */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #2b5876;
            font-size: 14px;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            color: #333;
            min-width: 150px;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            align-self: flex-end;
        }
        
        /* Tab Navigation */
        .tab-nav {
            display: flex;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tab-btn {
            padding: 12px 25px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #2b5876;
            border-bottom-color: #2b5876;
        }
        
        .tab-btn:hover {
            color: #2b5876;
            background: #f8f9fa;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Events Section */
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
            position: relative;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .event-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }
        
        .badge-registered {
            background: #28a745;
            color: white;
        }
        
        .badge-full {
            background: #dc3545;
            color: white;
        }
        
        .badge-available {
            background: #17a2b8;
            color: white;
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
        
        .progress-container {
            margin: 15px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #2b5876, #4e4376);
            border-radius: 4px;
            transition: width 0.3s ease;
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
        
        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: opacity 0.3s;
        }
        
        .btn-cancel:hover {
            opacity: 0.9;
        }
        
        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .no-events {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
            grid-column: 1 / -1;
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
        
        /* Statistics Section */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #2b5876;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        /* Past Events Section */
        .past-events {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .past-events h3 {
            color: #2b5876;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f5ff;
        }
        
        .past-event-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f5ff;
        }
        
        .past-event-item:last-child {
            border-bottom: none;
        }
        
        .past-event-info h4 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .past-event-date {
            color: #666;
            font-size: 14px;
        }
        
        .attendance-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-attended {
            background: #d4edda;
            color: #155724;
        }
        
        .status-registered {
            background: #fff3cd;
            color: #856404;
        }
        
        /* Responsive Design */
        @media (max-width: 900px) {
            .main-layout {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
            }
            
            .content-area {
                height: auto;
            }
            
            .events-container {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
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
                align-items: flex-start;
            }
            
            .tab-nav {
                flex-direction: column;
            }
            
            .tab-btn {
                text-align: left;
                border-bottom: 1px solid #e0e0e0;
                border-left: 3px solid transparent;
            }
            
            .tab-btn.active {
                border-left-color: #2b5876;
                border-bottom-color: #e0e0e0;
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
            <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['volunteer_name']); ?></span>
            <button onclick="location.href='volunteer.php'"><i class="fas fa-user"></i> Profile</button>
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
                <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Event Management</h1>
            </div>

            <?php if (isset($success_msg)): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_msg)): ?>
                <div class="alert alert-error"><?php echo $error_msg; ?></div>
            <?php endif; ?>
            
            <!-- Statistics Overview -->
            <div class="stats-overview">
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($events); ?></div>
                    <div class="stat-label">Upcoming Events</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($registered_events); ?></div>
                    <div class="stat-label">My Registrations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo count($past_events); ?></div>
                    <div class="stat-label">Recent Participations</div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filters">
                <div class="filter-group">
                    <label for="month-filter"><i class="fas fa-filter"></i> Filter by Month</label>
                    <select id="month-filter" class="filter-select" onchange="applyFilters()">
                        <?php foreach ($months as $value => $name): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($filter_month == $value || (empty($filter_month) && $value == $current_month)) ? 'selected' : ''; ?>>
                                <?php echo $name; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button class="btn-apply" onclick="applyFilters()"><i class="fas fa-sync-alt"></i> Apply Filters</button>
            </div>
            
            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="openTab('tab-upcoming')">
                    <i class="fas fa-calendar-plus"></i> Upcoming Events
                </button>
                <button class="tab-btn" onclick="openTab('tab-registered')">
                    <i class="fas fa-calendar-check"></i> My Registrations (<?php echo count($registered_events); ?>)
                </button>
                <button class="tab-btn" onclick="openTab('tab-past')">
                    <i class="fas fa-history"></i> Past Events
                </button>
            </div>
            
            <!-- Upcoming Events Tab -->
            <div id="tab-upcoming" class="tab-content active">
                <?php if (empty($events)): ?>
                    <div class="no-events">
                        <i class="far fa-calendar-times"></i>
                        <h3>No Upcoming Events</h3>
                        <p>There are currently no upcoming events matching your filters. Please check back later.</p>
                    </div>
                <?php else: ?>
                    <div class="events-container">
                        <?php foreach ($events as $event): 
                            $isRegistered = in_array($event['event_id'], array_column($registered_events, 'event_id'));
                            $eventDate = new DateTime($event['event_date']);
                            $formattedDate = $eventDate->format('F j, Y');
                            $available_slots = $event['available_slots'] ?? $event['volunteers_needed'];
                            $registration_percentage = $event['volunteers_needed'] > 0 ? 
                                (($event['volunteers_needed'] - $available_slots) / $event['volunteers_needed']) * 100 : 0;
                        ?>
                            <div class="event-card">
                                <!-- Event Badge -->
                                <?php if ($isRegistered): ?>
                                    <div class="event-badge badge-registered">Registered</div>
                                <?php elseif ($available_slots <= 0): ?>
                                    <div class="event-badge badge-full">Full</div>
                                <?php elseif ($available_slots < 5): ?>
                                    <div class="event-badge badge-available">Few Spots Left</div>
                                <?php endif; ?>
                                
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
                                                <i class="fas fa-hands-helping"></i> 
                                                Volunteers: <?php echo $event['volunteers_needed'] - $available_slots; ?>/<?php echo $event['volunteers_needed']; ?>
                                            </div>
                                            
                                            <div class="progress-container">
                                                <div class="progress-label">
                                                    <span>Registration Progress</span>
                                                    <span><?php echo number_format($registration_percentage, 0); ?>%</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $registration_percentage; ?>%"></div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <form method="post">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <?php if ($isRegistered): ?>
                                            <button type="button" class="btn-register btn-registered" disabled>
                                                <i class="fas fa-check-circle"></i> Already Registered
                                            </button>
                                        <?php elseif ($available_slots <= 0): ?>
                                            <button type="button" class="btn-register btn-disabled" disabled>
                                                <i class="fas fa-times-circle"></i> Event Full
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="register_event" class="btn-register">
                                                <i class="fas fa-user-plus"></i> Register Now (<?php echo $available_slots; ?> spots left)
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- My Registrations Tab -->
            <div id="tab-registered" class="tab-content">
                <?php if (empty($registered_events)): ?>
                    <div class="no-events">
                        <i class="far fa-calendar-minus"></i>
                        <h3>No Event Registrations</h3>
                        <p>You haven't registered for any upcoming events yet. Browse the upcoming events tab to get involved!</p>
                    </div>
                <?php else: ?>
                    <div class="events-container">
                        <?php foreach ($registered_events as $event): 
                            $eventDate = new DateTime($event['event_date']);
                            $formattedDate = $eventDate->format('F j, Y');
                            $regDate = new DateTime($event['registration_date']);
                            $formattedRegDate = $regDate->format('M j, Y g:i A');
                        ?>
                            <div class="event-card">
                                <div class="event-badge badge-registered">Registered</div>
                                
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
                                        <div class="event-detail">
                                            <i class="fas fa-calendar-check"></i> Registered on: <?php echo $formattedRegDate; ?>
                                        </div>
                                        
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
                                    </div>
                                    
                                    <form method="post">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <button type="submit" name="cancel_registration" class="btn-cancel" 
                                                onclick="return confirm('Are you sure you want to cancel your registration for <?php echo htmlspecialchars($event['event_name']); ?>?')">
                                            <i class="fas fa-times-circle"></i> Cancel Registration
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Past Events Tab -->
            <div id="tab-past" class="tab-content">
                <?php if (empty($past_events)): ?>
                    <div class="no-events">
                        <i class="far fa-calendar-times"></i>
                        <h3>No Past Events</h3>
                        <p>You haven't participated in any events yet. Get involved by registering for upcoming events!</p>
                    </div>
                <?php else: ?>
                    <div class="past-events">
                        <h3><i class="fas fa-history"></i> Recently Attended Events</h3>
                        <?php foreach ($past_events as $event): 
                            $eventDate = new DateTime($event['event_date']);
                            $formattedDate = $eventDate->format('F j, Y');
                        ?>
                            <div class="past-event-item">
                                <div class="past-event-info">
                                    <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>
                                    <div class="past-event-date"><?php echo $formattedDate; ?> • <?php echo htmlspecialchars($event['location']); ?></div>
                                </div>
                                <div class="attendance-status <?php echo $event['attendance_status'] == 'attended' ? 'status-attended' : 'status-registered'; ?>">
                                    <?php echo ucfirst($event['attendance_status']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab content
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Remove active class from all tab buttons
            const tabButtons = document.getElementsByClassName('tab-btn');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            // Show the specific tab content and activate the button
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Filter functionality
        function applyFilters() {
            const monthFilter = document.getElementById('month-filter').value;
            let url = 'upcoming_event.php?';
            
            if (monthFilter && monthFilter !== 'all') {
                url += 'month=' + monthFilter;
            }
            
            window.location.href = url;
        }
        
        // Auto-close alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
            
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