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

// Fetch all accounts
$sql = "SELECT * FROM accounts";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SKST University Accounts</title>
<link rel="icon" href="../picture/SKST.png" type="image/png" />
<link rel="stylesheet" href="../Design/buttom_bar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ---------- Global Styles ---------- */
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f9f9ff; line-height:1.6; }
a { text-decoration:none; }

/* ---------- Navigation Styles ---------- */
.navbar { background-color:#e0e7ff; padding:10px 20px; }
.navbar-top { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
.logo { display:flex; align-items:center; gap:10px; }
.logo img { height:80px; }
.logo h1 { font-size:26px; color:#333; }
.menu-section { display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:15px; }
.btn { background:linear-gradient(135deg,#6a11cb 0%,#2575fc 100%); color:white; border:none; padding:12px 20px; font-size:15px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:all 0.3s ease; cursor:pointer; }
.btn:hover { transform:translateY(-3px); background:linear-gradient(135deg,#512da8,#1e88e5); }
.account-login { background-color:#28a745; color:white; border:none; padding:10px 20px; font-size:16px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:0.3s; cursor:pointer; min-width:120px; }
.account-login:hover { transform:translateY(-3px); background:linear-gradient(135deg,#b31217,#e52d27); }
.home-button { background:gray; color:white; border:none; padding:10px 16px; font-size:15px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); transition:0.3s; cursor:pointer; display:flex; align-items:center; gap:8px; }
.home-button:hover { transform:translateY(-3px); background:linear-gradient(135deg,#18bcae,#f3af02); }

/* ---------- Content Styles ---------- */
.future-students-title { text-align:center; color:white; background:#800000; font-weight:bold; margin:20px 0; padding:15px; }
.future-students-text { text-align:center; max-width:1200px; margin:0 auto 30px auto; color:black; line-height:1.6; font-size:16px; padding:0 20px; }
.content-container { max-width:1200px; margin:0 auto 50px auto; padding:0 20px; }

/* ---------- Table Styles ---------- */
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; margin-top:20px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
table, th, td { border:1px solid #ccc; }
th, td { padding:12px; text-align:left; }
th { background:#6a11cb; color:white; position:sticky; top:0; }
tr:nth-child(even) { background-color:#f2f2f2; }
tr:hover { background-color:#e6e6ff; }

/* ---------- Responsive Design ---------- */
@media screen and (max-width: 768px) {
    .navbar-top { flex-direction:column; }
    .logo { margin-bottom:15px; }
    .logo h1 { font-size:20px; }
    .menu-section { gap:5px; }
    .btn { padding:8px 12px; font-size:14px; }
    
    table { font-size:14px; }
    th, td { padding:8px; }
    
    .future-students-title { font-size:18px; }
}

@media screen and (max-width: 480px) {
    .logo img { height:60px; }
    .logo h1 { font-size:18px; }
    
    th, td { padding:6px; font-size:12px; }
    
    .future-students-title { font-size:16px; padding:10px; }
}
</style>
</head>
<body>
<div class="navbar">
  <div class="navbar-top">
    <div class="logo">
      <img src="../picture/logo.gif" alt="SKST Logo">
      <h1>SKST University || Accounts</h1>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center;">
      <a href="account.php"><button class="account-login"><i class="fas fa-user"></i> Account Login</button></a>
      <a href="../index.html" class="home-button"><i class="fas fa-home"></i> Home</a>
    </div>
  </div>
  <div class="menu-section">
    <a href="../student/student.html"><button class="btn">Student</button></a>
    <a href="../faculty/faculty.html"><button class="btn">Faculty</button></a>
    <a href="../admin/administration.html"><button class="btn">Administration</button></a>
    <a href="../alumni/alumni.html"><button class="btn">Alumni</button></a>
    <a href="../campus/campus.html"><button class="btn">Campus Life</button></a>
    <a href="../iqac/iqac.html"><button class="btn">IQAC</button></a>
    <a href="../notice/notice.html"><button class="btn">Notice</button></a>
    <a href="../news/news.html"><button class="btn">News</button></a>
    <a href="../ranking/ranking.html"><button class="btn">Ranking</button></a>
    <a href="../academic/academic.html"><button class="btn">Academics</button></a>
    <a href="../scholarship/scholarship.html"><button class="btn">Scholarships</button></a>
    <a href="../admission/admission.html"><button class="btn">Admission</button></a>
    <a href="../library/library1.html"><button class="btn">Library</button></a>
    <a href="account.php"><button class="btn">Account</button></a>
    <a href="../volunteer/volunteer.html"><button class="btn">Volunteer</button></a>
    <a href="../about/about.html"><button class="btn">About US</button></a>
  </div>
</div>

<div class="future-students-title">SKST University Banking Accounts</div>
<p class="future-students-text">All university accounts and balances are listed below.</p>

<div class="content-container">
  <div class="table-container">
    <table>
      <tr>
        <th>Account ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Department</th>
        <th>Balance</th>
        <th>Budget</th>
        <th>Fiscal Year</th>
        <th>Status</th>
        <th>Manager</th>
        <th>Email</th>
      </tr>
      <?php
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              echo "<tr>
              <td>{$row['account_id']}</td>
              <td>{$row['account_name']}</td>
              <td>{$row['account_type']}</td>
              <td>{$row['department']}</td>
              <td>{$row['current_balance']}</td>
              <td>{$row['budget_allocation']}</td>
              <td>{$row['fiscal_year']}</td>
              <td>{$row['account_status']}</td>
              <td>{$row['account_manager']}</td>
              <td>{$row['contact_email']}</td>
              </tr>";
          }
      } else {
          echo "<tr><td colspan='10' style='text-align:center;'>No accounts found.</td></tr>";
      }
      ?>
    </table>
  </div>
</div>

<div class="buttom_bar">
  <img src="../picture/SKST.png" alt="Logo" style="height:80px; width:auto;">
  <p>SKST University</p>
  <p>4 Embankment Drive Road,Sector-10, Uttara Model Town, Dhaka-1230.</p>
  <p>Phone: (88 02) 55091801-5, Mobile : +88 01714 014 933, 01810030041-9, 01325080581-9</p>
  <p>Fax: (880-2) 5895 2625, Email : info@skst.edu</p>
</div>
</body>
</html>

<?php
$conn->close();
?>