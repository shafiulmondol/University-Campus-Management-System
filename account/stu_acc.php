<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define file to store data
$dataFile = 'student_data.json';

// Initialize variables
$errors = [];
$success = false;
$formData = [
    'department' => '',
    'scholarship' => '',
    'payment_method' => '',
    'account_number' => '',
    'amount' => '',
    'transaction_id' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $formData['department'] = sanitizeInput($_POST['department'] ?? '');
    $formData['scholarship'] = sanitizeInput($_POST['scholarship'] ?? '');
    $formData['payment_method'] = sanitizeInput($_POST['payment_method'] ?? '');
    $formData['account_number'] = sanitizeInput($_POST['account_number'] ?? '');
    $formData['amount'] = sanitizeInput($_POST['amount'] ?? '');
    $formData['transaction_id'] = sanitizeInput($_POST['transaction_id'] ?? '');
    
    // Validate required fields
    if (empty($formData['department'])) {
        $errors[] = "Department is required";
    }
    
    if (empty($formData['scholarship'])) {
        $errors[] = "Scholarship category is required";
    }
    
    // If payment method is selected, validate payment details
    if (!empty($formData['payment_method'])) {
        if (empty($formData['account_number'])) {
            $errors[] = "Account number is required for payment";
        }
        
        if (empty($formData['amount']) || !is_numeric($formData['amount']) || $formData['amount'] <= 0) {
            $errors[] = "Valid payment amount is required";
        }
    }
    
    // If no errors, save data
    if (empty($errors)) {
        // Add timestamp
        $formData['submitted_at'] = date('Y-m-d H:i:s');
        
        // Read existing data
        $existingData = [];
        if (file_exists($dataFile)) {
            $jsonData = file_get_contents($dataFile);
            $existingData = json_decode($jsonData, true) ?? [];
        }
        
        // Add new data
        $existingData[] = $formData;
        
        // Save back to file
        if (file_put_contents($dataFile, json_encode($existingData, JSON_PRETTY_PRINT))) {
            $success = true;
        } else {
            $errors[] = "Failed to save data. Please check file permissions.";
        }
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Read existing data for display
$allData = [];
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $allData = json_decode($jsonData, true) ?? [];
}

// Calculate statistics
$departmentStats = [];
$scholarshipStats = [];
$totalPayments = 0;

foreach ($allData as $record) {
    // Department statistics
    $dept = $record['department'] ?? 'Unknown';
    if (!isset($departmentStats[$dept])) {
        $departmentStats[$dept] = 0;
    }
    $departmentStats[$dept]++;
    
    // Scholarship statistics
    $scholarship = $record['scholarship'] ?? 'Unknown';
    if (!isset($scholarshipStats[$scholarship])) {
        $scholarshipStats[$scholarship] = 0;
    }
    $scholarshipStats[$scholarship]++;
    
    // Total payments
    if (!empty($record['amount']) && is_numeric($record['amount'])) {
        $totalPayments += (float)$record['amount'];
    }
}

// Function to format amounts in Bangladeshi Taka
function formatTaka($amount) {
    return '৳' . number_format((float)$amount, 2);
}
?>

<!-- HTML for displaying statistics -->
<h2>Statistics</h2>

<h3>Department Counts:</h3>
<ul>
<?php foreach ($departmentStats as $dept => $count): ?>
    <li><?php echo htmlspecialchars($dept) . ": $count"; ?></li>
<?php endforeach; ?>
</ul>

<h3>Scholarship Counts:</h3>
<ul>
<?php foreach ($scholarshipStats as $sch => $count): ?>
    <li><?php echo htmlspecialchars($sch) . ": $count"; ?></li>
<?php endforeach; ?>
</ul>

<h3>Total Payments:</h3>
<p><?php echo formatTaka($totalPayments); ?></p>

<h3>All Records:</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Department</th>
        <th>Scholarship</th>
        <th>Payment Method</th>
        <th>Account Number</th>
        <th>Amount</th>
        <th>Transaction ID</th>
        <th>Submitted At</th>
    </tr>
    <?php foreach ($allData as $record): ?>
    <tr>
        <td><?php echo htmlspecialchars($record['department'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($record['scholarship'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($record['payment_method'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($record['account_number'] ?? ''); ?></td>
        <td><?php echo !empty($record['amount']) ? formatTaka($record['amount']) : '৳0.00'; ?></td>
        <td><?php echo htmlspecialchars($record['transaction_id'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($record['submitted_at'] ?? ''); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
