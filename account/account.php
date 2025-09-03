<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

$message = '';
$messageClass = '';

try {
    // Create PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create accounts table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS accounts (
        account_id VARCHAR(20) NOT NULL PRIMARY KEY,
        account_name VARCHAR(100) NOT NULL,
        account_type VARCHAR(50) NOT NULL,
        department VARCHAR(50) NOT NULL,
        current_balance DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        budget_allocation DECIMAL(15,2) NOT NULL,
        fiscal_year VARCHAR(9) NOT NULL,
        account_status VARCHAR(20) NOT NULL DEFAULT 'Active' CHECK (account_status IN ('Active','Inactive','Suspended')),
        created_date DATE NOT NULL DEFAULT CURDATE(),
        last_transaction DATE DEFAULT NULL,
        account_manager VARCHAR(100) NOT NULL,
        contact_email VARCHAR(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $account_id = $_POST['account_id'];
        $account_name = $_POST['account_name'];
        $account_type = $_POST['account_type'];
        $department = $_POST['department'];
        $current_balance = $_POST['current_balance'] ?? 0;
        $budget_allocation = $_POST['budget_allocation'];
        $fiscal_year = $_POST['fiscal_year'];
        $account_status = $_POST['account_status'] ?? 'Active';
        $account_manager = $_POST['account_manager'];
        $contact_email = $_POST['contact_email'] ?? null;

        // Insert data into accounts table
        $stmt = $conn->prepare("INSERT INTO accounts 
            (account_id, account_name, account_type, department, current_balance, budget_allocation, fiscal_year, account_status, account_manager, contact_email)
            VALUES (:account_id, :account_name, :account_type, :department, :current_balance, :budget_allocation, :fiscal_year, :account_status, :account_manager, :contact_email)");
        
        $stmt->bindParam(':account_id', $account_id);
        $stmt->bindParam(':account_name', $account_name);
        $stmt->bindParam(':account_type', $account_type);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':current_balance', $current_balance);
        $stmt->bindParam(':budget_allocation', $budget_allocation);
        $stmt->bindParam(':fiscal_year', $fiscal_year);
        $stmt->bindParam(':account_status', $account_status);
        $stmt->bindParam(':account_manager', $account_manager);
        $stmt->bindParam(':contact_email', $contact_email);

        if ($stmt->execute()) {
            $message = "Account created successfully!";
            $messageClass = "success";
        } else {
            $message = "Failed to create account. Please try again.";
            $messageClass = "error";
        }
    }

} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $message = "This account ID already exists!";
        $messageClass = "error";
    } else {
        $message = "Database error: " . $e->getMessage();
        $messageClass = "error";
    }
}
?>

<!-- Display message -->
<?php if (!empty($message)): ?>
<div class="message <?php echo $messageClass; ?>">
    <?php echo $message; ?>
</div>
<?php endif; ?>

<!-- Example HTML Form for account creation -->
<form method="POST" action="">
    <input type="text" name="account_id" placeholder="Account ID" required>
    <input type="text" name="account_name" placeholder="Account Name" required>
    <input type="text" name="account_type" placeholder="Account Type" required>
    <input type="text" name="department" placeholder="Department" required>
    <input type="number" step="0.01" name="current_balance" placeholder="Current Balance">
    <input type="number" step="0.01" name="budget_allocation" placeholder="Budget Allocation" required>
    <input type="text" name="fiscal_year" placeholder="Fiscal Year" required>
    <select name="account_status">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
        <option value="Suspended">Suspended</option>
    </select>
    <input type="text" name="account_manager" placeholder="Account Manager" required>
    <input type="email" name="contact_email" placeholder="Contact Email">
    <button type="submit">Create Account</button>
</form>
