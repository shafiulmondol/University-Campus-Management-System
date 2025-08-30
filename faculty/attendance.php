<?php
// ---------------- Database Connection ----------------
$host = "localhost";
$user = "root";
$pass = "";
$db = "skst_university";  // Your DB name
$conn = new mysqli($host, $user, $pass, $db);
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// ---------------- Define variables ----------------
$faculty_id = 1; // Example faculty ID (you can make a login system later)
$date = date('Y-m-d');

// ---------------- Fetch faculty courses ----------------
$courses = $conn->query("SELECT DISTINCT course_code FROM attendance WHERE faculty_id='$faculty_id'");

// ---------------- Mark Attendance ----------------
if(isset($_POST['mark_attendance'])){
    $selected_course = $_POST['course_code'];
    $statuses = $_POST['status']; // student_id => status

    foreach($statuses as $student_id => $status){
        // Check if attendance already exists
        $check = $conn->query("SELECT * FROM attendance WHERE faculty_id='$faculty_id' AND course_code='$selected_course' AND student_id='$student_id' AND date='$date'");
        if($check->num_rows == 0){
            $stmt = $conn->prepare("INSERT INTO attendance (faculty_id, course_code, student_id, status, date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $faculty_id, $selected_course, $student_id, $status, $date);
            $stmt->execute();
        }
    }
    $message = "Attendance marked for $date, course $selected_course";
}

// ---------------- Fetch students ----------------
$selected_course = isset($_POST['course_code']) ? $_POST['course_code'] : '';
$students = [];
if($selected_course){
    $students = $conn->query("SELECT * FROM student_registration");
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Attendance System</title>
    <style>
        body{font-family:Arial;background:#f0f2f5;}
        .container{max-width:1000px;margin:30px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
        h2{text-align:center;color:#007bff;}
        table{width:100%;border-collapse:collapse;margin-top:20px;}
        table, th, td{border:1px solid #ddd;}
        th, td{padding:10px;text-align:center;}
        input, select, button{padding:8px;margin:5px 0;}
        button{background:#007bff;color:#fff;border:none;cursor:pointer;}
        button:hover{background:#0056b3;}
        .section{margin-bottom:40px;}
    </style>
</head>
<body>
<div class="container">
    <h2>Faculty Attendance System</h2>

    <!-- Select Course -->
    <div class="section">
        <h3>Select Course</h3>
        <form method="POST">
            <select name="course_code" required>
                <option value="">--Select Course--</option>
                <?php
                $course_list = $conn->query("SELECT DISTINCT course_code FROM attendance WHERE faculty_id='$faculty_id'");
                while($course = $course_list->fetch_assoc()){
                    $selected = ($selected_course == $course['course_code']) ? 'selected' : '';
                    echo "<option value='".$course['course_code']."' $selected>".$course['course_code']."</option>";
                }
                ?>
            </select>
            <button type="submit">Load Students</button>
        </form>
    </div>

    <!-- Mark Attendance -->
    <?php if($selected_course){ ?>
    <div class="section">
        <h3>Mark Attendance for <?php echo $selected_course; ?> (<?php echo $date; ?>)</h3>
        <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
        <form method="POST">
            <input type="hidden" name="course_code" value="<?php echo $selected_course; ?>">
            <table>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Status</th>
                </tr>
                <?php while($student = $students->fetch_assoc()){ ?>
                <tr>
                    <td><?php echo $student['roll_no']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td>
                        <select name="status[<?php echo $student['id']; ?>]">
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <button type="submit" name="mark_attendance">Submit Attendance</button>
        </form>
    </div>
    <?php } ?>

    <!-- View Attendance Report -->
    <?php if($selected_course){ ?>
    <div class="section">
        <h3>Attendance Report for <?php echo $selected_course; ?></h3>
        <table>
            <tr>
                <th>Roll No</th>
                <th>Name</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Percentage</th>
            </tr>
            <?php
            $students_report = $conn->query("SELECT * FROM student_registration");
            while($student = $students_report->fetch_assoc()){
                $sid = $student['id'];
                $total = $conn->query("SELECT * FROM attendance WHERE student_id='$sid' AND course_code='$selected_course' AND faculty_id='$faculty_id'")->num_rows;
                $present = $conn->query("SELECT * FROM attendance WHERE student_id='$sid' AND course_code='$selected_course' AND faculty_id='$faculty_id' AND status='Present'")->num_rows;
                $absent = $total - $present;
                $percentage = $total>0 ? round(($present/$total)*100,2) : 0;
            ?>
            <tr>
                <td><?php echo $student['roll_no']; ?></td>
                <td><?php echo $student['name']; ?></td>
                <td><?php echo $present; ?></td>
                <td><?php echo $absent; ?></td>
                <td><?php echo $percentage; ?>%</td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <?php } ?>

</div>
</body>
</html>
