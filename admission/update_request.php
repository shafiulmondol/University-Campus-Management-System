<?php
// password_refresh.php (or any name) — drop in your admin area
$con=mysqli_connect('localhost','root','','skst_university');

// Prevent cached old lists after POST
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$self = htmlspecialchars(basename($_SERVER['PHP_SELF']));
$message = "";
$warning = "";

/* -------------------- Handle Update Apply -------------------- */
if (isset($_POST['process_update'])) {
    $update_id    = intval($_POST['update_id']);
    $update_type  = $_POST['update_type'];      // 'password' | 'email'
    $category     = $_POST['category'];         // 'Student' | 'Staff' | 'Faculty'
    $applicant_id = intval($_POST['applicant_id']);
    $new_value    = $_POST['new_value'];

    $table = ""; $id_column = ""; $update_column = ""; $name_column = "";

    if ($category === "Student") {         $table="student_registration"; $id_column="id";         $name_column="first_name"; }
    elseif ($category === "Staff") {       $table="stuf";                 $id_column="id";         $name_column="first_name"; }
    elseif ($category === "Faculty") {     $table="faculty";              $id_column="faculty_id"; $name_column="name"; }

    if ($update_type === "password") { $update_column = "password"; }
    elseif ($update_type === "email") { $update_column = "email"; }

    if ($table && $update_column) {
        // Check existence & fetch old value + name
        $check_sql = "SELECT $update_column AS oldv, $name_column AS pname FROM $table WHERE $id_column = ?";
        $stmt = $con->prepare($check_sql);
        $stmt->bind_param("i", $applicant_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            // Applicant not found anywhere -> warn, don't process
            $warning = "⚠️ Wrong Applicant ID: $applicant_id not found in $table!";
        } else {
            $row = $res->fetch_assoc();
            $old_value = $row['oldv'];
            $applicant_name = $row['pname'];

            // Transaction: update target table + mark this request done + archive older duplicates
            $con->begin_transaction();
            try {
                // 1) Update target table
                $sqlU = "UPDATE $table SET $update_column = ? WHERE $id_column = ?";
                $stmtU = $con->prepare($sqlU);
                $stmtU->bind_param("si", $new_value, $applicant_id);
                $stmtU->execute();

                // 2) This request -> completed (action = 4), store after/old values
                // Removed 'processed_at' column as it doesn't exist in the table
                $sqlDone = "UPDATE update_requests
                           SET action = 4,
                               current_value = ?,               -- after update
                               comments = CONCAT(IFNULL(comments,''), ' | Old Value: ', ?)
                           WHERE id = ?";
                $stmtD = $con->prepare($sqlDone);
                $stmtD->bind_param("ssi", $new_value, $old_value, $update_id);
                $stmtD->execute();

                // 3) Any older *pending* duplicates of same applicant+type -> rejected (superseded)
                $sqlOld = "UPDATE update_requests
                           SET action = 2,  -- rejected/archived
                               comments = CONCAT(IFNULL(comments,''), ' | Superseded by ID ', ?)
                           WHERE action = 1
                             AND applicant_id = ?
                             AND update_type = ?
                             AND id <> ?";
                $stmtOld = $con->prepare($sqlOld);
                $stmtOld->bind_param("iisi", $update_id, $applicant_id, $update_type, $update_id);
                $stmtOld->execute();

                $con->commit();
                // PRG redirect so the updated list reloads cleanly
                header("Location: {$self}?success=1");
                exit;
            } catch (Throwable $e) {
                $con->rollback();
                $warning = "❌ Update failed. ".$e->getMessage();
            }
        }
    }
}

/* -------------------- Success / Warning messages via GET -------------------- */
if (isset($_GET['success'])) {
    $message = "✅ Update applied successfully!";
}

/* -------------------- Pending: only latest per applicant+type -------------------- */
$query = "
    SELECT ur.*
    FROM update_requests ur
    INNER JOIN (
        SELECT applicant_id, update_type, MAX(request_time) AS latest_time
        FROM update_requests
        WHERE action = 1
        GROUP BY applicant_id, update_type
    ) t ON ur.applicant_id = t.applicant_id
        AND ur.update_type  = t.update_type
        AND ur.request_time = t.latest_time
    WHERE ur.action = 1
    ORDER BY ur.request_time DESC
";
$pending = mysqli_query($con, $query);

/* -------------------- Counters -------------------- */
$count_pending   = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) cnt FROM update_requests WHERE action = 1"))['cnt'];
$count_completed = (int) mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(*) cnt FROM update_requests WHERE action = 4"))['cnt'];

/* -------------------- History (action = 4 only) -------------------- */
$show_history = isset($_GET['history']);
if ($show_history) {
    $history_query = "
        SELECT ur.id, ur.applicant_id, ur.category, ur.update_type,
               ur.current_value AS after_update,
               TRIM(SUBSTRING_INDEX(ur.comments, 'Old Value: ', -1)) AS old_value,
               ur.action, ur.request_time,
               CASE
                 WHEN ur.category='Student' THEN (SELECT CONCAT(first_name,' ',last_name) FROM student_registration WHERE id=ur.applicant_id)
                 WHEN ur.category='Staff'   THEN (SELECT CONCAT(first_name,' ',last_name) FROM stuf WHERE id=ur.applicant_id)
                 WHEN ur.category='Faculty' THEN (SELECT name FROM faculty WHERE faculty_id=ur.applicant_id)
               END AS applicant_name
        FROM update_requests ur
        WHERE ur.action = 4
        ORDER BY ur.request_time DESC
    ";
    $history = mysqli_query($con, $history_query);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Password/Email Refreshing System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body{font-family:Arial, sans-serif;background:#f4f6f9;margin:0;padding:20px;}
    .container{max-width:1200px;margin:auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 0 20px rgba(0,0,0,0.08);}
    h2{text-align:center;color:#333;margin-top:0}
    .topbar{display:flex;gap:10px;justify-content:flex-end;margin-bottom:10px}
    .stats{display:flex;gap:12px;justify-content:center;margin:16px 0}
    .stat{background:#007bff;color:#fff;padding:10px 18px;border-radius:10px}
    .stat.complete{background:#28a745}
    .msg{padding:10px 14px;border-radius:8px;margin:10px 0;text-align:center}
    .success{background:#d4edda;color:#155724}
    .warning{background:#fff3cd;color:#856404}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:center}
    th{background:#007bff;color:#fff}
    tr:hover{background:#f9fafb}
    .btn{padding:8px 14px;border:none;border-radius:8px;cursor:pointer;text-decoration:none;display:inline-block}
    .btn-apply{background:#28a745;color:#fff}
    .btn-apply:hover{background:#218838}
    .btn-history{background:#6c757d;color:#fff}
    .btn-history:hover{background:#5a6268}
  </style>
</head>
<body>
<div class="container">
    
 <div class="topbar">
    <?php if ($show_history): ?>
      <a class="btn btn-history" href="<?php echo $self; ?>"><i class="fa-solid fa-arrow-left"></i> Back to Requests</a>
    <?php else: ?>
      <a class="btn btn-history" href="<?php echo $self; ?>?history=1"><i class="fa-solid fa-history"></i> View History</a>
    <?php endif; ?>

    <!-- New Back to Dashboard Button -->
    <a class="btn btn-history" href="admission_officer.php"><i class="fa-solid fa-home"></i> Back to Dashboard</a>
</div>


  <h2><i class="fas fa-sync-alt"></i> Password/Email Refreshing System</h2>

  <div class="stats">
    <div class="stat">⏳ Pending: <?php echo $count_pending; ?></div>
    <div class="stat complete">✅ Completed: <?php echo $count_completed; ?></div>
  </div>

  <?php if ($message): ?><div class="msg success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
  <?php if ($warning): ?><div class="msg warning"><?php echo htmlspecialchars($warning); ?></div><?php endif; ?>

  <?php if ($show_history): ?>
    <h3>📜 Completed Requests</h3>
    <table>
      <tr>
        <th>Applicant Name</th>
        <th>Applicant ID</th>
        <th>Category</th>
        <th>Update Type</th>
        <th>Old Value</th>
        <th>Current Value</th>
        <th>Status</th>
      </tr>
      <?php while($r = mysqli_fetch_assoc($history)): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['applicant_name']); ?></td>
        <td><?php echo (int)$r['applicant_id']; ?></td>
        <td><?php echo htmlspecialchars($r['category']); ?></td>
        <td><?php echo htmlspecialchars(ucfirst($r['update_type'])); ?></td>
        <td><?php echo htmlspecialchars($r['old_value']); ?></td>
        <td><?php echo htmlspecialchars($r['after_update']); ?></td>
        <td>✅ Completed</td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <table>
      <tr>
        <th>ID</th>
        <th>Applicant ID</th>
        <th>Category</th>
        <th>Update Type</th>
        <th>Current Value</th>
        <th>New Value</th>
        <th>Request Time</th>
        <th>Action</th>
      </tr>
      <?php while($row = mysqli_fetch_assoc($pending)): ?>
      <tr>
        <td><?php echo (int)$row['id']; ?></td>
        <td><?php echo (int)$row['applicant_id']; ?></td>
        <td><?php echo htmlspecialchars($row['category']); ?></td>
        <td><?php echo htmlspecialchars(ucfirst($row['update_type'])); ?></td>
        <td><?php echo htmlspecialchars($row['current_value']); ?></td>
        <td><?php echo htmlspecialchars($row['new_value']); ?></td>
        <td><?php echo htmlspecialchars($row['request_time']); ?></td>
        <td>
          <form method="post" style="margin:0">
            <input type="hidden" name="update_id" value="<?php echo (int)$row['id']; ?>">
            <input type="hidden" name="update_type" value="<?php echo htmlspecialchars($row['update_type']); ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($row['category']); ?>">
            <input type="hidden" name="applicant_id" value="<?php echo (int)$row['applicant_id']; ?>">
            <input type="hidden" name="new_value" value="<?php echo htmlspecialchars($row['new_value']); ?>">
            <button type="submit" name="process_update" class="btn btn-apply">
              <i class="fas fa-check"></i> Apply Update
            </button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
</div>
</body>
</html>