<?php 
require_once 'notice.php'; ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SKST University</title>
  <link rel="icon" href="../picture/SKST.png" type="image/png" />
  <link rel="stylesheet" href="../Design/buttom_bar.css">
  <link rel="stylesheet" href="library.css">
  <link rel="stylesheet" href="stuf.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
         
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<div class="navbar">
    <div class="navbar-top">
      <div class="logo">
        
        <img src="../picture/logo.gif" alt="SKST Logo">
        <h1>SKST University || Library</h1>
      </div>
      <div class="home-button">
    <a href="../index.html">🏠 Home</a>

   
    </div>


    <div class="menu-section" style="margin-bottom: 20px;">
         <a href="../student/studentf.php"><button class="btn">Student</button></a>
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
      <a href="library.php"><button class="btn">Library</button></a>
      <a href="../volunteer.html"><button class="btn">Volunteer</button></a>
      <a href="../about.html"><button class="btn">About US</button></a>
    </div>
  </div>
    <!-- Main Content Area with Right Navbar -->
    <div class="main-container">
        <!-- Content Section -->
       <div class="content">
     
         <div class="bg-glass">
     <?php  if (isset($_POST['staff'])) {
          // Display staff login form
          ?>
          <div class="staff-login-container">
            <div class="staff-login-box">
              <div class="login-header">
                <img src="../picture/logo.gif" alt="SKST Logo" class="login-logo">
                <h1>Library Staff Login</h1>
              </div>
              
              <div class="login-body">
                <form method="post" class="login-form">
                  <div class="form-group">
                    <label for="staffmail">E-mail</label>
                    <input type="email" id="staffmail" name="email" placeholder="Enter email" required>
                  </div>
                  
                  <div class="form-group">
                    <label for="staffpass">Password</label>
                    <input type="password" id="staffpass" name="password" placeholder="Enter password" required>
                  </div>
                  
                  <button type="submit" name="submit" class="login-button">Login</button>
                </form>
              </div>
            </div>
          </div>
          <?php
        } elseif (isset($_POST['submit'])) {
          // Handle staff login submission
          if (isset($_POST['email']) && isset($_POST['password'])) {
            $check = login_condition($_POST['email'], $_POST['password']);
            if ($check == true) {
              $_SESSION['staff_logged_in'] = true;
              ?>
              <nav class="library-navbars">
                <a href="#" class="library-logo">
                  <i class="fas fa-book-open"></i>
                  <span>Library Dashboard</span>
                </a>
                
                <input type="checkbox" id="nav-toggle" class="nav-toggle">
                <label for="nav-toggle" class="hamburger">&#9776;</label>
                
                <div class="library-nav-link">
                  <a href="#" class="active">
                    <i class="fas fa-book"></i>
                    <span>See Books</span>
                  </a>
                 <a href="#" class="active">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Borrow Details</span>
                  </a>
                 <a href="#" class="active">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Books</span>
                  </a>
                 <a href="#" class="active">
                    <i class="fas fa-user-plus"></i>
                    <span>Register Student</span>
                  </a>
                <a href="#" class="active">
                    <i class="fas fa-bell"></i>
                    <span class="badge"></span>
                  </a>
                </div>
              </nav>
              
              <div style="padding: 2rem; max-width: 1200px; margin: 0 auto;">
                <h2>Welcome to Library Management</h2>
                <p>You are now logged in as library staff. Use the navigation above to manage library resources.</p>
              </div>
              <?php
            } else {
              echo "<div class='error-message'><p>Invalid email or password. Please try again.</p></div>";
              ?>
              <div class="staff-login-container">
                <div class="staff-login-box">
                  <div class="login-header">
                    <img src="../picture/logo.gif" alt="SKST Logo" class="login-logo">
                    <h1>Library Staff Login</h1>
                  </div>
                  
                  <div class="login-body">
                    <form method="post" class="login-form">
                      <div class="form-group">
                        <label for="staffmail">E-mail</label>
                        <input type="email" id="staffmail" name="email" placeholder="Enter email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                      </div>
                      
                      <div class="form-group">
                        <label for="staffpass">Password</label>
                        <input type="password" id="staffpass" name="password" placeholder="Enter password" required>
                      </div>
                      
                      <button type="submit" name="submit" class="login-button">Login</button>
                    </form>
                  </div>
                </div>
              </div>
              <?php
            }
          }
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
        include "library.html";
       } ?>

            </div>
        </div>
        
        <!-- Right Side Navigation Bar -->
        <div class="nav-links">
      <form action="library.php" method="post">
        <button class="nav-btn" type="submit" name="notice"><i class="fas fa-bullhorn"></i> Library Notice</button>
        <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> Borrow Tech</button>
        <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i> Suggest a Book</button>
        <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> Renew Books</button>
        <button class="nav-btn" type="submit" name="staff"><i class="fas fa-user-tie"></i> Staff Portal</button>
        <button class="nav-btn" type="submit" name="about"><i class="fas fa-info-circle"></i> About Us</button>
        <button class="nav-btn" type="submit" name="search"><i class="fas fa-search"></i> Book Search</button>
        <button class="nav-btn" type="submit" name="resources"><i class="fas fa-graduation-cap"></i> Student Resources</button>
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