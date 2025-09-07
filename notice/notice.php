<?php
ob_start();
session_start();
require_once '../library/notice.php';

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'skst_university');

$con = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if user is logged in


// Get the logged-in admin's email
$admin_email = $_SESSION['admin_email'] ?? '';

// Define departments
$departments = ['BCSE', 'EEE', 'BBA', 'ME', 'ENG', 'MATH', 'CIVIL', 'MBA', 'MHP', 'BSAg', 'BSME', 'BATHM', 'BSN'];

// Get filter if set
$section_filter = $_GET['section'] ?? '';
$department_filter = $_GET['department'] ?? '';

// Build query
$query = "SELECT * FROM notice WHERE section IN ('Department', 'Event', 'Bank')";
$params = [];
$types = '';

if (!empty($section_filter) && in_array($section_filter, ['Department', 'Event', 'Bank'])) {
    $query .= " AND section = ?";
    $params[] = $section_filter;
    $types .= 's';
}

if (!empty($department_filter) && in_array($department_filter, $departments)) {
    $query .= " AND sub_section = ?";
    $params[] = $department_filter;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $con->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$notices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKST University Notice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #2b5876;
            --secondary: #4e4376;
            --success: #198754;
            --danger: #dc3545;
            --muted: #6b7280;
            --card: #ffffff;
            --bg: #f4f6f9;
            --border: #e5e7eb;
            --text: #111827;
            --unread-bg: #eef6ff;
        }
        
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .university-name {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .page-subtitle {
            font-size: 20px;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            background: white;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 15px;
        }
        
        .back-btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: var(--card);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 200px;
        }
        
        .filter-label {
            font-weight: 600;
            font-size: 14px;
            color: var(--muted);
        }
        
        .filter-select {
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            font-size: 14px;
        }
        
        .apply-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: white;
            font-weight: 600;
            cursor: pointer;
            align-self: flex-end;
            transition: all 0.3s;
        }
        
        .apply-btn:hover {
            background: var(--secondary);
        }
        
        .notices-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .notice-card {
            background: var(--card);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .notice-card.unread {
            background: var(--unread-bg);
            border-left: 4px solid var(--success);
        }
        
        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .notice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .notice-title {
            font-weight: 700;
            font-size: 18px;
            margin: 0;
            color: var(--text);
            flex: 1;
        }
        
        .notice-section {
            background: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .notice-department {
            background: var(--secondary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
            display: inline-block;
        }
        
        .notice-content {
            margin-bottom: 15px;
            color: var(--muted);
            line-height: 1.6;
            flex: 1;
        }
        
        .notice-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: var(--muted);
            margin-top: auto;
        }
        
        .notice-author {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .notice-date {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .no-notices {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: var(--muted);
            font-size: 18px;
            background: var(--card);
            border-radius: 10px;
        }
        
        .section-department {
            border-left-color: #4e4376;
        }
        
        .section-event {
            border-left-color: #fd7e14;
        }
        
        .section-bank {
            border-left-color: #20c997;
        }
        
        @media (max-width: 768px) {
            .notices-container {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .university-name {
                font-size: 24px;
            }
            
            .page-subtitle {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="university-name">
                <i class="fas fa-university"></i>
                SKST University
            </h1>
            <p class="page-subtitle">Official Notice Board</p>
            <a href="notice.html" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        
        <form method="GET" class="filters">
            <div class="filter-group">
                <label class="filter-label">Notice Type</label>
                <select name="section" class="filter-select">
                    <option value="">All Notice Types</option>
                    <option value="Department" <?= $section_filter === 'Department' ? 'selected' : '' ?>>Department</option>
                    <option value="Event" <?= $section_filter === 'Event' ? 'selected' : '' ?>>Event</option>
                    <option value="Bank" <?= $section_filter === 'Bank' ? 'selected' : '' ?>>Bank</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Department</label>
                <select name="department" class="filter-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept ?>" <?= $department_filter === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="apply-btn">
                <i class="fas fa-filter"></i> Apply Filters
            </button>
        </form>
        
        <div class="notices-container">
            <?php if (count($notices) > 0): ?>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice-card <?= $notice['viewed'] == 0 ? 'unread' : '' ?> section-<?= strtolower($notice['section']) ?>">
                        <div class="notice-header">
                            <h3 class="notice-title"><?= htmlspecialchars($notice['title']) ?></h3>
                            <span class="notice-section"><?= htmlspecialchars($notice['section']) ?></span>
                        </div>
                        
                        <?php if (!empty($notice['sub_section'])): ?>
                            <span class="notice-department"><?= htmlspecialchars($notice['sub_section']) ?></span>
                        <?php endif; ?>
                        
                        <div class="notice-content">
                            <?= nl2br(htmlspecialchars($notice['content'])) ?>
                        </div>
                        
                        <div class="notice-footer">
                            <span class="notice-author">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($notice['author']) ?>
                            </span>
                            <span class="notice-date">
                                <i class="fas fa-clock"></i> <?= date('M j, Y h:i A', strtotime($notice['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-notices">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>No notices found for the selected filters</p>
                    <p>Try adjusting your filter criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php ob_end_flush(); ?>