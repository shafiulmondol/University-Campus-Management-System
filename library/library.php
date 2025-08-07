<?php
session_start();
require_once 'notice.php';

// Handle logout first
if (isset($_POST['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: library.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
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
  <style>
     .submit-btn {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 14px 28px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
        width: 100%;
        max-width: 250px;
        align-self: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .submit-btn:hover {
        /* background-color: #cad4daff; */
        transform: translateY(-4px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
     .submit-btn:active {
        transform: translateY(0);
    }
  </style>
</head>
<body>
<div class="navbar">
    <div class="navbar-top">
      <div class="logo">
        <img src="../picture/logo.gif" alt="SKST Logo">
        <h1>SKST University || Library</h1>
      </div>
      <div class="home-button">
        <a href="../index.php">🏠 Home</a>
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
      <?php if (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] == true) { ?>
        <div class="nav-links">
          <a href="#" class="library-logo">
            <nav class="library-navbars">
              <a href="#" class="library-logo">
                <i class="fas fa-book-open"></i><br>
                <span>Dashboard </span> 
                <div class="notification-bell">
          <button type="submit" name="nsubmit" class="bell-btn">
           🔔
             <span class="badge">2</span>
            </button>
      </div>

              </a>
              <input type="checkbox" id="nav-toggle" class="nav-toggle">
              <label for="nav-toggle" class="hamburger">&#9776;</label>
            </nav>
          </a>
          <form action="library.php" method="post">
            <button class="nav-btn" type="submit" name="search"><i class="fas fa-search"></i>  📚 See Books</button>
            <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> 📄 Borrow Details</button>
            <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i>   ➕ Add Books</button>
            <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> 📝 Register student</button>
            <button class="nav-btn" type="submit" name="logout">
              <i class="fas fa-sign-out-alt"></i> 📤 Logout
            </button>
          </form>
        </div> 
        <div class="content">
          <div class="bg-glass">
            <?php
           if (isset($_POST['borrow'])) {
              echo "<h2>Borrow Technology</h2>";
              echo "<p>This section would contain information about borrowing technology equipment from the library.</p>";
            }
           elseif (isset($_POST['search'])) { ?>
           <div class="book-search-container">
    <form action="search_results.php" method="get" class="book-search-form">
        <div class="search-box">
            <input type="text" name="search_query" placeholder="Search books by title, author, or Book name" 
                   class="search-input" required>
            <button type="submit" class="search-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <span class="sr-only">Search books</span>
            </button>
        </div>
        <div >
            <input type="submit" name="see_all" placeholder=" See All books">
        </div>
        <!-- <div class="search-options">
            <div class="filter-group">
                <label for="search_by" class="filter-label">Search in:</label>
                <select name="search_by" id="search_by" class="filter-select">
                    <option value="all">All Fields</option>
                    <option value="all">Book ID</option>
                    <option value="title">Title</option>
                    <option value="author">Author</option>
                    <option value="isbn">ISBN</option>
                </select>
            </div>
        </div> -->
    </form>
</div>


<?php

            }

             elseif (isset($_POST['suggest'])) { ?>
              <div class="suggestion-form-container">
    <h2>Suggest a Book for Our Library</h2>
    
    <?php if (isset($suggestion_message)): ?>
        <div class="message <?php echo $suggestion_message_type; ?>">
            <?php echo $suggestion_message; ?>
        </div>
    <?php endif; ?>
    
    <form action="library.php" method="post" class="suggestion-form">
        <div class="form-row">
            <div class="form-group">
                <label for="title">Book Title*</label>
                <input type="text" id="title" name="title" placeholder="Enter book title" required>
            </div>
            
            <div class="form-group">
                <label for="author">Author*</label>
                <input type="text" id="author" name="author" placeholder="Enter author name" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" placeholder="Enter ISBN (optional)">
            </div>
            
            <div class="form-group">
                <label for="publication_year">Publication Year</label>
                <input type="number" id="publication_year" name="publication_year" 
                       placeholder="YYYY" min="1000" max="<?php echo date('Y'); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" placeholder="Fiction, Science, etc.">
            </div>
            
            <div class="form-group">
                <label for="total_copies">Number of Copies Suggested</label>
                <input type="number" id="total_copies" name="total_copies" value="1" min="1">
            </div>
        </div>
        
        <div class="form-group">
            <label for="shelf_location">Suggested Shelf Location</label>
            <input type="text" id="shelf_location" name="shelf_location" placeholder="e.g., A12, Fiction Section">
        </div>
        
        <div class="form-group">
            <label for="suggested_by">Your Name</label>
            <input type="text" id="suggested_by" name="suggested_by" placeholder="Who is suggesting this book?">
        </div>
        
        <div class="form-group">
            <label for="suggestion_notes">Why should we add this book?</label>
            <textarea id="suggestion_notes" name="suggestion_notes" placeholder="Tell us why this would be a good addition to our library"></textarea>
        </div>
        
        <input type="submit" name="suggest_submit" value="Submit Suggestion" class="submit-btn" >
    </form>
</div>
 <?php
            }
            elseif (isset($_POST['suggest_submit'])){
              create_books_table();
             echo add_book($_POST['title'],$_POST['author'],$_POST['isbn'],$_POST['publication_year'],$_POST['category'],$_POST['total_copies'],$_POST['shelf_location']); 
            }
             elseif (isset($_POST['renew'])) {
              echo "<h2>Renew Books</h2>";
              echo "<p>Information about book renewal policies and procedures.</p>";
            } else {
              echo "<h2>Library Staff Dashboard</h2>";
              echo "<p>Welcome to the library staff portal. Please select an option from the menu.</p>";
            }
            ?>
          </div>
        </div>
      <?php } else { ?>
        <!-- Content Section -->
        <div class="content">
          <div class="bg-glass">
            <?php  
            if (isset($_POST['staff'])) {
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
              if (isset($_POST['email']) && isset($_POST['password'])) {
                $check = login_condition($_POST['email'], $_POST['password']);
                if ($check == true) {
                  $_SESSION['staff_logged_in'] = true;
                  header("Refresh:0");
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
            } elseif (isset($_POST['borrow'])) {
              echo "<h2>Borrow Technology</h2>";
              echo "<p>This section would contain information about borrowing technology equipment from the library.</p>";
            } elseif (isset($_POST['suggest'])) {
              echo "<h2>Suggest a Book</h2>";
              echo "<p>Form for suggesting new books for the library collection.</p>";
            } elseif (isset($_POST['renew'])) {
              echo "<h2>Renew Books</h2>";
              echo "<p>Information about book renewal policies and procedures.</p>";
            } elseif (isset($_POST['notice'])) { 
              require_once 'notice.php';
              echo see_library_notice();
            } elseif (isset($_POST['about'])) {
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
            } else { 
              include "library.html";
            } 
            ?>
          </div>
        </div>
        
        <!-- Right Side Navigation Bar -->
        <div class="nav-links">
          <form action="library.php" method="post">
            <button class="nav-btn" type="submit" name="notice"><i class="fas fa-bullhorn"></i> Library Notice</button>
            <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> Borrow book</button>
            <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i> Suggest a Book</button>
            <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> Renew Books</button>
            <button class="nav-btn" type="submit" name="staff"><i class="fas fa-user-tie"></i> Staff Portal</button>
            <button class="nav-btn" type="submit" name="about"><i class="fas fa-info-circle"></i> About Us</button>
            <button class="nav-btn" type="submit" name="search"><i class="fas fa-search"></i> Book Search</button>
            <button class="nav-btn" type="submit" name="resources"><i class="fas fa-graduation-cap"></i> Student Resources</button>
            <button class="nav-btn" type="submit" name="help"><i class="fas fa-question-circle"></i> Help Desk</button>
          </form>
        </div>
      <?php } ?>
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