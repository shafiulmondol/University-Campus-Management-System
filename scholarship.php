<?php
$conn = new mysqli("localhost", "root", "", "university");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $gender = strtolower($_POST['gender']);
    $ssc = floatval($_POST['ssc']);
    $hsc = floatval($_POST['hsc']);
    $cgpa = floatval($_POST['cgpa']);
    $prev_scholarship = isset($_POST['prev_scholarship']) ? intval($_POST['prev_scholarship']) : -1;

    $current_scholarship = 0;
    $first_semester = ($prev_scholarship == -1);

    if ($first_semester) {
        if ($ssc == 5.0 && $hsc == 5.0) {
            $current_scholarship = 100;
        } elseif (($ssc + $hsc) == 9.0) {
            $current_scholarship = 75;
        } elseif ($gender === "female") {
            $current_scholarship = 15;
        }
    } else {
        if ($cgpa < 3.2) {
            if ($prev_scholarship == 100) {
                $current_scholarship = 75;
            } elseif ($prev_scholarship == 75) {
                $current_scholarship = 60;
            } elseif ($prev_scholarship == 15) {
                $current_scholarship = 0;
            } else {
                $current_scholarship = 0;
            }
        } else {
            $current_scholarship = $prev_scholarship;
        }
    }

    $stmt = $conn->prepare("INSERT INTO students (name, gender, ssc_gpa, hsc_gpa, cgpa, prev_scholarship, current_scholarship) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdddii", $name, $gender, $ssc, $hsc, $cgpa, $prev_scholarship, $current_scholarship);
    $stmt->execute();

    $last_id = $stmt->insert_id;

    echo "<h2>Scholarship Result</h2>";
    echo "<p><strong>ID:</strong> $last_id</p>";
    echo "<p><strong>Name:</strong> $name</p>";
    echo "<p><strong>Gender:</strong> $gender</p>";
    echo "<p><strong>CGPA:</strong> $cgpa</p>";
    echo "<p><strong>Scholarship:</strong> $current_scholarship%</p>";
    echo "<a href='scholarship.php'>Try Again</a><br><br>";
}
?>

<h2>Scholarship Calculator</h2>
<form action="" method="POST">
    <label>Name:</label>
    <input type="text" name="name" required><br><br>

    <label>Gender (male/female):</label>
    <input type="text" name="gender" required><br><br>

    <label>SSC GPA:</label>
    <input type="number" name="ssc" step="0.01" required><br><br>

    <label>HSC GPA:</label>
    <input type="number" name="hsc" step="0.01" required><br><br>

    <label>Semester CGPA (use -1 for 1st semester):</label>
    <input type="number" name="cgpa" step="0.01" required><br><br>

    <label>Previous Scholarship (use -1 if 1st time):</label>
    <input type="number" name="prev_scholarship" value="-1" required><br><br>

    <input type="submit" value="Calculate Scholarship">
</form>
