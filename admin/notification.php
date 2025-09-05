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

// Get the logged-in admin's email
$admin_email = $_SESSION['admin_email'] ?? '';

// Function to get unread counts for each section
function getUnreadCounts($con, $admin_email) {
    $unreadCounts = [];
    
    // Get counts for regular notice sections
    $sections = ['Student', 'Staff', 'Department', 'Faculty', 'Library', 'Account', 'Admin', 'Bank', 'Event'];
    foreach ($sections as $section) {
        $query = "SELECT COUNT(*) as count FROM notice WHERE section = ? AND viewed = 0";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("s", $section);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $unreadCounts[$section] = $row['count'];
            $stmt->close();
        }
    }
    
    // Get department-specific counts
    $departments = ['BCSE', 'EEE', 'BBA', 'ME', 'ENG', 'MATH', 'CIVIL', 'MBA', 'MHP', 'BSAg', 'BSME', 'BATHM', 'BSN'];
    foreach ($departments as $dept) {
        $query = "SELECT COUNT(*) as count FROM notice WHERE section = 'Department' AND sub_section = ? AND viewed = 0";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("s", $dept);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $unreadCounts[$dept] = $row['count'];
            $stmt->close();
        }
    }
    
    // Get count for update requests
    $query = "SELECT COUNT(*) as count FROM update_requests WHERE admin_email = ? AND action = 0";
    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $unreadCounts['update_request'] = $row['count'];
        $stmt->close();
    }
    
    return $unreadCounts;
}

// Get unread counts
$unreadCounts = getUnreadCounts($con, $admin_email);

// ---------- Handle Mark as Read ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notice_id = intval($_POST['notice_id']);
    
    if ($stmt = $con->prepare("UPDATE notice SET viewed = 1 WHERE id = ?")) {
        $stmt->bind_param("i", $notice_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh unread counts after update
        $unreadCounts = getUnreadCounts($con, $admin_email);
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ---------- Handle Delete Notice ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {
    $notice_id = intval($_POST['notice_id']);
    
    if ($stmt = $con->prepare("DELETE FROM notice WHERE id = ?")) {
        $stmt->bind_param("i", $notice_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh unread counts after delete
        $unreadCounts = getUnreadCounts($con, $admin_email);
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ---------- Handle Delete All Notices ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_notices'])) {
    $section = $_POST['section'] ?? '';
    $sub_section = $_POST['sub_section'] ?? '';
    $search_query = $_POST['search_query'] ?? '';
    
    if (!empty($search_query)) {
        // Delete based on search query
        $search_term = '%' . $search_query . '%';
        $stmt = $con->prepare("DELETE FROM notice WHERE 
                              id LIKE ? OR title LIKE ? OR content LIKE ? OR author LIKE ? OR section LIKE ? OR sub_section LIKE ?");
        $stmt->bind_param("ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $stmt->close();
        
        // Also delete from update_requests if needed
        $stmt = $con->prepare("DELETE FROM update_requests WHERE 
                              applicant_id LIKE ? OR admin_email LIKE ? OR update_type LIKE ? OR current_value LIKE ? OR new_value LIKE ? OR comments LIKE ?");
        $stmt->bind_param("ssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $stmt->close();
    } else if (!empty($section)) {
        if ($section === 'update_request') {
            // Delete all update requests for this admin
            $stmt = $con->prepare("DELETE FROM update_requests WHERE admin_email = ?");
            $stmt->bind_param("s", $admin_email);
            $stmt->execute();
            $stmt->close();
        } else if (!empty($sub_section)) {
            // Delete by section and sub-section
            $stmt = $con->prepare("DELETE FROM notice WHERE section = ? AND sub_section = ?");
            $stmt->bind_param("ss", $section, $sub_section);
            $stmt->execute();
            $stmt->close();
        } else {
            // Delete by section only
            $stmt = $con->prepare("DELETE FROM notice WHERE section = ?");
            $stmt->bind_param("s", $section);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Refresh unread counts after delete
    $unreadCounts = getUnreadCounts($con, $admin_email);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ---------- Handle Delete Update Request ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_update_request'])) {
    $request_id = intval($_POST['request_id']);
    
    if ($stmt = $con->prepare("DELETE FROM update_requests WHERE id = ?")) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
        
        // Refresh unread counts after delete
        $unreadCounts = getUnreadCounts($con, $admin_email);
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ---------- Handle Accept / Reject update requests ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['accept_update']) || isset($_POST['reject_update']))) {
    $applicant_id = intval($_POST['applicant_id']);
    $action = isset($_POST['accept_update']) ? 1 : 2;

    if ($stmt = $con->prepare("UPDATE update_requests SET action = ? WHERE applicant_id = ? AND admin_email = ? AND action = 0")) {
        $stmt->bind_param("iis", $action, $applicant_id, $admin_email);
        $stmt->execute();
        $stmt->close();
    }
    
    // Refresh unread counts after update
    $unreadCounts = getUnreadCounts($con, $admin_email);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// ---------- Handle Insert Notice ----------
$flash_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_notice'])) {
    $id = !empty($_POST['id']) ? intval($_POST['id']) : NULL;
    $title = trim($_POST['title']);
    $section_notice = trim($_POST['section_notice']);
    $sub_section = trim($_POST['sub_section'] ?? '');
    $content = trim($_POST['content']);
    $author = trim($_POST['author']);

    if ($id) {
        // Insert with specific ID
        $stmt = $con->prepare("INSERT INTO notice (id, title, section, sub_section, content, author, created_at, viewed) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("isssss", $id, $title, $section_notice, $sub_section, $content, $author);
    } else {
        // Insert without ID (auto-increment)
        $stmt = $con->prepare("INSERT INTO notice (title, section, sub_section, content, author, created_at, viewed) VALUES (?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("sssss", $title, $section_notice, $sub_section, $content, $author);
    }
    
    if ($stmt->execute()) {
        $flash_message = "<div class='success-msg'>✅ Notice added successfully!</div>";
        
        // Refresh unread counts after adding notice
        $unreadCounts = getUnreadCounts($con, $admin_email);
    } else {
        $flash_message = "<div class='error-msg'>❌ Failed to add notice. Error: " . htmlspecialchars($con->error) . "</div>";
    }
    $stmt->close();
}

// ---------- Handle Search ----------
$search_query = $_POST['search_query'] ?? '';
$search_results = [];
$show_search_results = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_notices'])) {
    $show_search_results = true;
    $search_term = '%' . $search_query . '%';
    
    // Search in notices
    $stmt = $con->prepare("SELECT * FROM notice WHERE 
                          (id LIKE ? OR title LIKE ? OR content LIKE ? OR author LIKE ? OR section LIKE ? OR sub_section LIKE ? OR DATE(created_at) LIKE ?)
                          ORDER BY created_at DESC");
    $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'notice';
        $search_results[] = $row;
    }
    $stmt->close();
    
    // Search in update requests
    $stmt = $con->prepare("SELECT * FROM update_requests WHERE 
                          (applicant_id LIKE ? OR admin_email LIKE ? OR update_type LIKE ? OR current_value LIKE ? OR new_value LIKE ? OR comments LIKE ? OR DATE(request_time) LIKE ?)
                          ORDER BY request_time DESC");
    $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['type'] = 'update_request';
        $search_results[] = $row;
    }
    $stmt->close();
}

$show_add_notice_form = isset($_POST['show_add_notice_form']);
$selected_section = $_POST['section'] ?? 'Admin';
$selected_sub_section = $_POST['sub_section'] ?? '';
$search_applicant = $_POST['search_applicant'] ?? '';

// Define departments and events
$departments = ['BCSE', 'EEE', 'BBA', 'ME', 'ENG', 'MATH', 'CIVIL', 'MBA', 'MHP', 'BSAg', 'BSME', 'BATHM', 'BSN'];
$events = ['Sports Day', 'Cultural Festival', 'Seminar', 'Workshop', 'Conference', 'Orientation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Notification</title>
    <link rel="stylesheet" href="all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root{
            --primary:#0d6efd;
            --success:#198754;
            --danger:#dc3545;
            --muted:#6b7280;
            --card:#ffffff;
            --bg:#f4f6f9;
            --border:#e5e7eb;
            --text:#111827;
            --unread-bg:#eef6ff;
        }
        body{ margin:0; padding:24px; font-family:Inter, Arial, sans-serif; background:var(--bg); color:var(--text); }

        /* Top bar */
        .topbar{ display:flex; align-items:center; justify-content:space-between; max-width:1100px; margin:0 auto 18px auto; gap:12px; }
        .back-btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:50px; background:var(--primary); color:#fff; text-decoration:none; font-weight:600; box-shadow:0 4px 8px rgba(0,0,0,0.15); transition:all .2s; }
        .back-btn:hover{ background:#0b5ed7; transform:translateY(-1px); }
        .page-title{ font-weight:700; font-size:20px; display:flex; gap:10px; align-items:center; }

        /* Panels */
        .panel{ background:var(--card); padding:18px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.06); border:1px solid var(--border); max-width:1100px; margin:0 auto 16px auto; }
        .notices-heading{ text-align:center; margin:0 0 12px 0; font-size:20px; display: flex; align-items: center; justify-content: center; gap: 10px; }

        /* Filter */
        .filter-form{ display:flex; gap:10px; flex-wrap:wrap; justify-content:center; align-items:center; margin-bottom:12px; }
        .filter-form select, .filter-form input[type="text"], .filter-form input[type="search"]{ padding:10px 12px; border:1px solid var(--border); border-radius:10px; min-width:200px; }

        /* Buttons */
        button{ padding:10px 14px; border-radius:10px; border:none; cursor:pointer; font-weight:700; }
        .btn-primary{ background:var(--primary); color:#fff; }
        .btn-success{ background:var(--success); color:#fff; }
        .btn-danger{ background:var(--danger); color:#fff; }
        .btn-ghost{ background:#f1f5f9; color:var(--text); border:1px solid var(--border); }
        .btn-read { background: #6c757d; color: #fff; }
        .btn-warning { background: #ffc107; color: #212529; }

        /* Add form */
        .add-form-grid{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
        .full{ grid-column:1 / -1; }
        .add-form-grid input, .add-form-grid textarea, .add-form-grid select{ width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:8px; }

        /* Notice cards */
        .notice-card{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:14px; margin-bottom:12px; transition:background 0.2s; position: relative; }
        .notice-card.unread{ border-left:6px solid var(--primary); background:var(--unread-bg); }
        .notice-card.unread .notice-title{ font-weight:800; color:var(--primary); }
        .notice-card.read{ border-left:6px solid transparent; }
        .notice-card.update{ border-left:6px solid #fd7e14; background:#fffbf2; }
        .notice-header{ display:flex; justify-content:space-between; align-items:center; gap:10px; }
        .notice-title{ font-weight:700; margin:0; }
        .notice-section{ background:var(--primary); color:#fff; padding:6px 10px; border-radius:20px; font-size:13px; position: relative; }
        .notice-content{ margin:10px 0; color:#374151; line-height:1.5; }
        .notice-footer{ font-size:13px; color:var(--muted); display:flex; justify-content:space-between; align-items:center; }

        .success-msg, .error-msg{ padding:12px; border-radius:8px; margin-bottom:12px; }
        .success-msg{ background:#e8f7ee; color:#14532d; border:1px solid #b7e4c7; }
        .error-msg{ background:#fdecea; color:#7f1d1d; border:1px solid #f5c2c7; }

        .no-notices{ text-align:center; padding:20px; color:var(--muted); }
        
        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 50%;
            background-color: var(--danger);
            color: white;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
            min-width: 20px;
            text-align: center;
        }
        
        .section-badge {
            background-color: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .mark-read-btn {
            margin-top: 10px;
            text-align: right;
        }
        
        .sub-section-filter {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: flex-end;
        }
        
        .search-panel {
            margin-bottom: 20px;
        }
        
        .search-results {
            margin-top: 20px;
        }
        
        .delete-btn {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .date-delete-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .delete-all-container {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--border);
            text-align: center;
        }

        @media (max-width:720px){
            .add-form-grid{ grid-template-columns:1fr; }
            .filter-form select{ min-width:150px; }
            .filter-form { flex-direction: column; }
            .sub-section-filter { flex-direction: column; align-items: flex-start; }
            .date-delete-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            .delete-btn {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<div class="topbar">
    <a href="admin.php" class="back-btn">
    <i class="fa-solid fa-arrow-left"></i> Back
</a>

    <div class="page-title"><i class="fa-solid fa-bell"></i> Notifications</div>
    <div style="width:70px"></div>
</div>

<!-- Search Panel -->
<div class="panel search-panel">
    <h3><i class="fa-solid fa-search"></i> Search Notifications</h3>
    <form method="post" class="filter-form">
        <input type="search" name="search_query" placeholder="Search by ID, title, content, author, or date..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1;">
        <button type="submit" name="search_notices" class="btn-primary">
            <i class="fa-solid fa-search"></i> Search
        </button>
        <?php if ($show_search_results): ?>
            <button type="button" onclick="window.location.href=window.location.href.split('?')[0]" class="btn-ghost">
                <i class="fa-solid fa-times"></i> Clear Search
            </button>
        <?php endif; ?>
    </form>
</div>

<?php if ($show_add_notice_form): ?>
    <div class="panel">
        <h3 style="margin:0 0 12px 0;"><i class="fas fa-plus-circle"></i> Add Notice</h3>
        <form method="post">
            <div class="add-form-grid">
                <div>
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div>
                    <label>ID (Optional)</label>
                    <input type="text" name="id" placeholder="Leave empty for auto ID">
                </div>
                <div>
                    <label>Section *</label>
                    <select name="section_notice" id="section_notice" required onchange="toggleSubSection()">
                        <?php
                        $sections_notice = ['Student', 'Staff', 'Department', 'Faculty', 'Library', 'Account', 'Admin', 'Bank', 'Event'];
                        foreach ($sections_notice as $sec) {
                            $count = $unreadCounts[$sec] ?? 0;
                            $badge = $count > 0 ? " <span class='badge'>$count</span>" : "";
                            echo "<option value=\"".htmlspecialchars($sec)."\">".htmlspecialchars($sec).$badge."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div id="sub_section_container" style="display: none;">
                    <label id="sub_section_label">Sub Category</label>
                    <select name="sub_section" id="sub_section">
                        <option value="">-- Select --</option>
                    </select>
                </div>
                <div>
                    <label>Author *</label>
                    <input type="text" name="author" required>
                </div>
                <div class="full">
                    <label>Content *</label>
                    <textarea name="content" rows="5" required></textarea>
                </div>
            </div>
            <div style="margin-top:12px; display:flex; gap:10px;">
                <button type="submit" name="submit_notice" class="btn-success"><i class="fa-solid fa-check"></i> Add Notice</button>
                <button type="submit" name="notification" class="btn-danger"><i class="fa-solid fa-xmark"></i> Cancel</button>
            </div>
        </form>
    </div>
    
    <script>
        function toggleSubSection() {
            const section = document.getElementById('section_notice').value;
            const container = document.getElementById('sub_section_container');
            const subSection = document.getElementById('sub_section');
            const label = document.getElementById('sub_section_label');
            
            // Clear previous options
            subSection.innerHTML = '<option value="">-- Select --</option>';
            
            if (section === 'Department') {
                container.style.display = 'block';
                label.textContent = 'Department';
                <?php foreach ($departments as $dept): ?>
                    subSection.innerHTML += '<option value="<?= $dept ?>"><?= $dept ?></option>';
                <?php endforeach; ?>
            } else if (section === 'Event') {
                container.style.display = 'block';
                label.textContent = 'Event Type';
                <?php foreach ($events as $event): ?>
                    subSection.innerHTML += '<option value="<?= $event ?>"><?= $event ?></option>';
                <?php endforeach; ?>
            } else {
                container.style.display = 'none';
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleSubSection();
        });
    </script>
<?php endif; ?>

<?php if ($show_search_results && !empty($search_results)): ?>
    <div class="panel search-results">
        <h3><i class="fa-solid fa-search"></i> Search Results</h3>
        <p>Found <?php echo count($search_results); ?> results for "<?php echo htmlspecialchars($search_query); ?>"</p>
        
        <!-- Delete All Button for Search Results -->
        <div class="delete-all-container">
            <form method="post">
                <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" name="delete_all_notices" class="btn-danger" onclick="return confirm('Are you sure you want to delete ALL search results? This action cannot be undone.');">
                    <i class="fa-solid fa-trash"></i> Delete All Search Results (<?php echo count($search_results); ?>)
                </button>
            </form>
        </div>
        
        <?php foreach ($search_results as $row): ?>
            <?php if ($row['type'] === 'notice'): ?>
                <div class="notice-card <?php echo ($row['viewed'] == 0) ? 'unread' : 'read'; ?>">
                    <div class="notice-header">
                        <h3 class="notice-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <span class="notice-section">
                            <?php 
                            echo htmlspecialchars($row['section']);
                            if (!empty($row['sub_section'])) {
                                echo " (" . htmlspecialchars($row['sub_section']) . ")";
                            }
                            ?>
                        </span>
                    </div>
                    <div class="notice-content"><?php echo nl2br(htmlspecialchars($row['content'])); ?></div>
                    <div class="notice-footer">
                        <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($row['author']); ?></span>
                        <div class="date-delete-container">
                            <span><i class="fa-regular fa-clock"></i> <?php echo date('F j, Y h:i A', strtotime($row['created_at'])); ?></span>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="notice_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_notice" class="delete-btn" onclick="return confirm('Are you sure you want to delete this notice?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($row['viewed'] == 0): ?>
                    <div class="mark-read-btn">
                        <form method="post">
                            <input type="hidden" name="notice_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="mark_as_read" class="btn-read">
                                <i class="fa-solid fa-check-circle"></i> Mark as Read
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="notice-card update">
                    <div class="notice-header">
                        <h3 class="notice-title">Update Request (<?php echo htmlspecialchars($row['update_type']); ?>)</h3>
                        <span class="notice-section">Profile Update</span>
                    </div>
                    <div class="notice-content">
                        <b>Applicant ID:</b> <?php echo htmlspecialchars($row['applicant_id']); ?><br>
                        <b>Category:</b> <?php echo htmlspecialchars($row['category']); ?><br>
                        <b>Admin Email:</b> <?php echo htmlspecialchars($row['admin_email']); ?><br>
                        <b>Old Value:</b> <?php echo htmlspecialchars($row['current_value']); ?><br>
                        <b>New Value:</b> <?php echo htmlspecialchars($row['new_value']); ?><br>
                        <?php if (!empty($row['comments'])): ?>
                            <b>Reason / Comment:</b> <?php echo htmlspecialchars($row['comments']); ?><br>
                        <?php endif; ?>
                        <?php if ((int)$row['action'] === 0): ?>
                            <b>Status:</b> ⏳ Pending<br>
                            <form method="post" style="display:flex; gap:10px; margin-top:8px;">
                                <input type="hidden" name="applicant_id" value="<?php echo (int)$row['applicant_id']; ?>">
                                <button type="submit" name="accept_update" class="btn-success"><i class="fa-solid fa-check"></i> Accept</button>
                                <button type="submit" name="reject_update" class="btn-danger"><i class="fa-solid fa-xmark"></i> Reject</button>
                            </form>
                        <?php elseif ((int)$row['action'] === 1): ?>
                            <b>Status:</b> ✅ Request Accepted<br>
                        <?php else: ?>
                            <b>Status:</b> ❌ Request Rejected<br>
                        <?php endif; ?>
                    </div>
                    <div class="notice-footer">
                        <span></span>
                        <div class="date-delete-container">
                            <span><i class="fa-regular fa-clock"></i> <?php echo date('F j, Y h:i A', strtotime($row['request_time'])); ?></span>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_update_request" class="delete-btn" onclick="return confirm('Are you sure you want to delete this update request?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php elseif ($show_search_results): ?>
    <div class="panel search-results">
        <h3><i class="fa-solid fa-search"></i> Search Results</h3>
        <p>No results found for "<?php echo htmlspecialchars($search_query); ?>"</p>
    </div>
<?php endif; ?>

<?php if (!$show_search_results): ?>
<div class="panel">
    <?php
    $current_unread = 0;
    if ($selected_section === 'update_request') {
        $current_unread = $unreadCounts['update_request'] ?? 0;
    } else if (in_array($selected_section, $departments)) {
        $current_unread = $unreadCounts[$selected_section] ?? 0;
    } else {
        $current_unread = $unreadCounts[$selected_section] ?? 0;
    }
    ?>
    
    <div class="section-header">
        <div class="section-title">
            <i class="fa-solid fa-bullhorn"></i> 
            Latest Notifications
            <?php if ($current_unread > 0): ?>
                <span class="section-badge"><?php echo $current_unread; ?> Unread</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($flash_message)) echo $flash_message; ?>

    <form method="post" class="filter-form">
        <label for="section">Section:</label>
        <select name="section" id="section" onchange="this.form.submit()">
            <?php
            $sections = ['Student', 'Faculty', 'Staff', 'Admin', 'AO', 'Bank', 'Department', 'Event', 'update_request'];
            foreach ($sections as $sec) {
                $sel = ($sec === $selected_section) ? 'selected' : '';
                if ($sec === 'update_request') {
                    $count = $unreadCounts['update_request'] ?? 0;
                } else {
                    $count = $unreadCounts[$sec] ?? 0;
                }
                $badge = $count > 0 ? " <span class='badge'>$count</span>" : "";
                echo "<option value=\"".htmlspecialchars($sec)."\" $sel>".htmlspecialchars($sec).$badge."</option>";
            }
            ?>
        </select>
        
        <?php if ($selected_section === 'Department'): ?>
            <div class="sub-section-filter">
                <label for="sub_section">Department:</label>
                <select name="sub_section" id="sub_section_filter" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <?php
                        $count = $unreadCounts[$dept] ?? 0;
                        $badge = $count > 0 ? " <span class='badge'>$count</span>" : "";
                        $sel = ($dept === $selected_sub_section) ? 'selected' : '';
                        ?>
                        <option value="<?= $dept ?>" <?= $sel ?>><?= $dept . $badge ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif ($selected_section === 'Event'): ?>
            <div class="sub-section-filter">
                <label for="sub_section">Event Type:</label>
                <select name="sub_section" id="sub_section_filter" onchange="this.form.submit()">
                    <option value="">All Events</option>
                    <?php foreach ($events as $event): ?>
                        <?php $sel = ($event === $selected_sub_section) ? 'selected' : ''; ?>
                        <option value="<?= $event ?>" <?= $sel ?>><?= $event ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        
        <?php if ($selected_section === 'update_request'): ?>
            <input type="text" name="search_applicant" placeholder="Search by Applicant ID" value="<?php echo htmlspecialchars($search_applicant); ?>">
        <?php endif; ?>
        <button type="submit" name="notification" class="btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>

    <!-- Delete All Button for Filtered Results -->
    <?php if (!empty($notices)): ?>
    <div class="delete-all-container">
        <form method="post">
            <input type="hidden" name="section" value="<?php echo htmlspecialchars($selected_section); ?>">
            <input type="hidden" name="sub_section" value="<?php echo htmlspecialchars($selected_sub_section); ?>">
            <button type="submit" name="delete_all_notices" class="btn-danger" onclick="return confirm('Are you sure you want to delete ALL notifications in this section? This action cannot be undone.');">
                <i class="fa-solid fa-trash"></i> Delete All in This Section (<?php echo count($notices); ?>)
            </button>
        </form>
    </div>
    <?php endif; ?>

    <?php if ($selected_section !== 'update_request' && $selected_section !== 'Department' && $selected_section !== 'Event'): ?>
        <div style="text-align:center; margin:12px 0;">
            <form method="post" style="display:inline-block;">
                <button type="submit" name="show_add_notice_form" class="btn-ghost"><i class="fa-solid fa-plus"></i> Add Notice</button>
            </form>
        </div>
    <?php endif; ?>

    <?php
    $notices = [];
    if ($selected_section !== 'update_request') {
        if ($selected_section === 'Department' && !empty($selected_sub_section)) {
            // Filter by specific department
            $query1 = "SELECT * FROM notice WHERE section='Department' AND sub_section = ? ORDER BY created_at DESC";
            if ($stmt = $con->prepare($query1)) {
                $stmt->bind_param("s", $selected_sub_section);
                $stmt->execute();
                $result1 = $stmt->get_result();
                while ($row = $result1->fetch_assoc()) {
                    $row['source'] = 'notice';
                    $notices[] = $row;
                }
                $stmt->close();
            }
        } else if ($selected_section === 'Event' && !empty($selected_sub_section)) {
            // Filter by specific event type
            $query1 = "SELECT * FROM notice WHERE section='Event' AND sub_section = ? ORDER BY created_at DESC";
            if ($stmt = $con->prepare($query1)) {
                $stmt->bind_param("s", $selected_sub_section);
                $stmt->execute();
                $result1 = $stmt->get_result();
                while ($row = $result1->fetch_assoc()) {
                    $row['source'] = 'notice';
                    $notices[] = $row;
                }
                $stmt->close();
            }
        } else if ($selected_section === 'Department' || $selected_section === 'Event') {
            // Show all department or event notices
            $query1 = "SELECT * FROM notice WHERE section = ? ORDER BY created_at DESC";
            if ($stmt = $con->prepare($query1)) {
                $stmt->bind_param("s", $selected_section);
                $stmt->execute();
                $result1 = $stmt->get_result();
                while ($row = $result1->fetch_assoc()) {
                    $row['source'] = 'notice';
                    $notices[] = $row;
                }
                $stmt->close();
            }
        } else {
            // Regular section filtering
            $query1 = "SELECT * FROM notice WHERE section = ? ORDER BY created_at DESC";
            if ($stmt = $con->prepare($query1)) {
                $stmt->bind_param("s", $selected_section);
                $stmt->execute();
                $result1 = $stmt->get_result();
                while ($row = $result1->fetch_assoc()) {
                    $row['source'] = 'notice';
                    $notices[] = $row;
                }
                $stmt->close();
            }
        }
    } else {
        // Filter update requests by admin email
        $query2 = "SELECT * FROM update_requests WHERE admin_email = '" . mysqli_real_escape_string($con, $admin_email) . "'";
        
        if (!empty($search_applicant)) {
            $q = mysqli_real_escape_string($con, $search_applicant);
            $query2 .= " AND applicant_id LIKE '%{$q}%'";
        }
        
        $query2 .= " ORDER BY request_time DESC";
        
        if ($result2 = mysqli_query($con, $query2)) {
            while ($row = mysqli_fetch_assoc($result2)) {
                $row['source'] = 'update';
                $notices[] = $row;
            }
        }
    }

    if (count($notices) > 0) {
        foreach ($notices as $row) {
            if ($row['source'] === 'notice') {
                $noticeClass = ($row['viewed'] == 0) ? "notice-card unread" : "notice-card read"; ?>
                <div class="<?php echo $noticeClass; ?>">
                    <div class="notice-header">
                        <h3 class="notice-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <span class="notice-section">
                            <?php 
                            echo htmlspecialchars($row['section']);
                            if (!empty($row['sub_section'])) {
                                echo " (" . htmlspecialchars($row['sub_section']) . ")";
                            }
                            ?>
                        </span>
                    </div>
                    <div class="notice-content"><?php echo nl2br(htmlspecialchars($row['content'])); ?></div>
                    <div class="notice-footer">
                        <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($row['author']); ?></span>
                        <div class="date-delete-container">
                            <span><i class="fa-regular fa-clock"></i> <?php echo date('F j, Y h:i A', strtotime($row['created_at'])); ?></span>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="notice_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_notice" class="delete-btn" onclick="return confirm('Are you sure you want to delete this notice?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($row['viewed'] == 0): ?>
                    <div class="mark-read-btn">
                        <form method="post">
                            <input type="hidden" name="notice_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="mark_as_read" class="btn-read">
                                <i class="fa-solid fa-check-circle"></i> Mark as Read
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            <?php } else { ?>
                <div class="notice-card update">
                    <div class="notice-header">
                        <h3 class="notice-title">Update Request (<?php echo htmlspecialchars($row['update_type']); ?>)</h3>
                        <span class="notice-section">Profile Update</span>
                    </div>
                    <div class="notice-content">
                        <b>Applicant ID:</b> <?php echo htmlspecialchars($row['applicant_id']); ?><br>
                        <b>Category:</b> <?php echo htmlspecialchars($row['category']); ?><br>
                        <b>Admin Email:</b> <?php echo htmlspecialchars($row['admin_email']); ?><br>
                        <b>Old Value:</b> <?php echo htmlspecialchars($row['current_value']); ?><br>
                        <b>New Value:</b> <?php echo htmlspecialchars($row['new_value']); ?><br>
                        <?php if (!empty($row['comments'])): ?>
                            <b>Reason / Comment:</b> <?php echo htmlspecialchars($row['comments']); ?><br>
                        <?php endif; ?>
                        <?php if ((int)$row['action'] === 0): ?>
                            <b>Status:</b> ⏳ Pending<br>
                            <form method="post" style="display:flex; gap:10px; margin-top:8px;">
                                <input type="hidden" name="applicant_id" value="<?php echo (int)$row['applicant_id']; ?>">
                                <button type="submit" name="accept_update" class="btn-success"><i class="fa-solid fa-check"></i> Accept</button>
                                <button type="submit" name="reject_update" class="btn-danger"><i class="fa-solid fa-xmark"></i> Reject</button>
                            </form>
                        <?php elseif ((int)$row['action'] === 1): ?>
                            <b>Status:</b> ✅ Request Accepted<br>
                        <?php else: ?>
                            <b>Status:</b> ❌ Request Rejected<br>
                        <?php endif; ?>
                    </div>
                    <div class="notice-footer">
                        <span></span>
                        <div class="date-delete-container">
                            <span><i class="fa-regular fa-clock"></i> <?php echo date('F j, Y h:i A', strtotime($row['request_time'])); ?></span>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_update_request" class="delete-btn" onclick="return confirm('Are you sure you want to delete this update request?');">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php }
        }
    } else {
        echo "<div class='no-notices'><p>No notifications found.</p></div>";
    }
    ?>
</div>
<?php endif; ?>
<?php ob_end_flush(); ?>