<?php
// ---------------------------
// Database Connection
// ---------------------------
$host = "localhost";
$dbname = "skst_university";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------------------------
// Fetch Employee Data
// ---------------------------
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$sql = "SELECT * FROM salary_dashboard";

if ($type !== 'all') {
    $typeMap = [
        'faculty' => ['FAC'],
        'admin' => ['ADM'],
        'support' => ['SUP'],
        'librarian' => ['LIB'],
        'research' => ['RES']
    ];
    if (isset($typeMap[$type])) {
        $prefixes = implode("','", $typeMap[$type]);
        $sql .= " WHERE emp_id LIKE '{$prefixes}%'";
    }
}

$result = $conn->query($sql);
$employees = [];
$totalBase = 0;
$totalDeductions = 0;
$totalNet = 0;

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $employees[] = $row;
        $totalBase += $row['base_salary'];
        $totalDeductions += $row['deductions'];
        $totalNet += $row['net_salary'];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #333; color: #fff; }
        .summary { margin-top: 20px; background: #fff; padding: 10px; border: 1px solid #ccc; }
        .summary div { margin-bottom: 5px; }
        select { padding: 5px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Salary Dashboard</h1>

    <form method="GET">
        <label>Filter by Type:</label>
        <select name="type" onchange="this.form.submit()">
            <option value="all" <?= $type=='all'?'selected':'' ?>>All</option>
            <option value="faculty" <?= $type=='faculty'?'selected':'' ?>>Faculty</option>
            <option value="admin" <?= $type=='admin'?'selected':'' ?>>Admin</option>
            <option value="support" <?= $type=='support'?'selected':'' ?>>Support Staff</option>
            <option value="librarian" <?= $type=='librarian'?'selected':'' ?>>Librarian</option>
            <option value="research" <?= $type=='research'?'selected':'' ?>>Research</option>
        </select>
    </form>

    <div class="summary">
        <div><strong>Total Base Salary:</strong> ৳<?= number_format($totalBase) ?></div>
        <div><strong>Total Deductions:</strong> ৳<?= number_format($totalDeductions) ?></div>
        <div><strong>Total Net Salary:</strong> ৳<?= number_format($totalNet) ?></div>
        <div><strong>Average Net Salary:</strong> ৳<?= count($employees)>0 ? number_format($totalNet/count($employees)) : 0 ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>Base Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($employees as $emp): ?>
            <tr>
                <td><?= htmlspecialchars($emp['emp_id']) ?></td>
                <td><?= htmlspecialchars($emp['first_name'].' '.$emp['last_name']) ?></td>
                <td><?= htmlspecialchars($emp['role']) ?></td>
                <td><?= htmlspecialchars($emp['department']) ?></td>
                <td>৳<?= number_format($emp['base_salary']) ?></td>
                <td>৳<?= number_format($emp['deductions']) ?></td>
                <td>৳<?= number_format($emp['net_salary']) ?></td>
                <td><?= htmlspecialchars($emp['status']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
