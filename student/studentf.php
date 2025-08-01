
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SKST University</title>
  <link rel="icon" href="../picture/SKST.png" type="image/png" />
  <link rel="stylesheet" href="../Design/buttom_bar.css">
  <link rel="stylesheet" href="library.css">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  
</head>
<body>
  <header>
        <div class="container header-content">
           <div class="logo">
        <img src="../picture/logo.gif" alt="SKST Logo">
                <h1>SKST University Student</h1>
            </div>
            <div class="user-controls">
                <button class="btn btn-outline"><i class="fas fa-bell"></i></button>
               <div class="home-button">
    <a href="../index.html">🏠 Home</a>

    </div>
            </div>
        </div>
         <div class="menu-section">
         <a href="studentf.php"><button class="btn">Student</button></a>
      <a href="../faculty.html"><button class="btn">Faculty</button></a>
      <a href="../administration.html"><button class="btn">Administration</button></a>
      <a href="../alumni.html"><button class="btn">Alumni</button></a>
      <a href="../campus.html"><button class="btn">Campus Life</button></a>
      <a href="../iqac.html"><button class="btn">IQAC</button></a>
      <a href="../notice.html"><button class="btn">Notice</button></a>
      <a href="../news.html"><button class="btn">News</button></a>
      <a href="../ranking.html"><button class="btn">Ranking</button></a>
      <a href="../academic.html"><button class="btn">Academics</button></a>
      <a href="../scholarship.html"><button class="btn">Scholarships</button></a>
      <a href="../admission.html"><button class="btn">Admission</button></a>
      <a href="../library/library.php"><button class="btn">Library</button></a>
      <a href="../volunteer.html"><button class="btn">Volunteer</button></a>
      <a href="../about.html"><button class="btn">About US</button></a>
    </div>
  </div>
    </header>
    
   
    <!-- Main Content Area with Right Navbar -->
    <div class="main-container">
        <!-- Content Section -->
       <div class="content">
     
         <div class="bg-glass">
     <?php  if  (isset($_POST['staff'])) {
         require_once 'notice.php';
         login_form();
      } 
    
      
      elseif (isset($_POST['borrow'])) {
          echo "<h2>Borrow Technology</h2>";
          echo "<p>This section would contain information about borrowing technology equipment from the library.</p>";
      } elseif (isset($_POST['suggest'])) {
          echo "<h2>Suggest a Book</h2>";
          echo "<p>Form for suggesting new books for the library collection.</p>";
      } elseif (isset($_POST['renew'])) {
          echo "<h2>Renew Books</h2>";
          echo "<p>Information about book renewal policies and procedures.</p>";
      } 
      elseif (isset($_POST['notice'])) { 
     
    require_once 'notice.php';
    
    echo see_notice(); 
      
     }
     
    

        elseif (isset($_POST['about'])) {
          echo "<h2>About the Library</h2>";
          echo "<p>Information about library services, hours, and resources.</p>";
      } elseif (isset($_POST['search'])) {
          echo "<h2>Book Search</h2>";
          echo "<p>Search interface for the library catalog.</p>";
      } elseif (isset($_POST['resources'])) {
          echo "<h2>Student Resources</h2>";
          echo "<p>Links to academic resources and research tools.</p>";
      } elseif (isset($_POST['help'])) {
          echo "<h2>Help Desk</h2>";
          echo "<p>Contact information and FAQs for library assistance.</p>";
      }

       else{ 
        include "student.html";
       } ?>

            </div>
        </div>
        
        <!-- Right Side Navigation Bar -->
        <div class="nav-links">
      <form action="library.php" method="post">
        <button class="nav-btn" type="submit" name="notice"><i class="fas fa-bullhorn"></i> Student Notice</button>
        <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> Personal Information</button>
        <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i> View Course</button>
        <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> Course Offering</button>
        <button class="nav-btn" type="submit" name="staff"><i class="fas fa-user-tie"></i> Bank History</button>
        <button class="nav-btn" type="submit" name="about"><i class="fas fa-info-circle"></i> Routine</button>
        <button class="nav-btn" type="submit" name="resources"><i class="fas fa-graduation-cap"></i> Publish Resources</button>
        <button class="nav-btn" type="submit" name="help"><i class="fas fa-question-circle"></i> Help Desk</button>
      </form>
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