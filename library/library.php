@ -0,0 +1,280 @@
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
  <div class="navbar">
    <div class="navbar-top">
      <div class="logo">
        <img src="../picture/logo.gif" alt="SKST Logo">
        <h1>SKST University || Library</h1>
      </div>
      <div class="home-button">
    <a href="../index.html">🏠 Home</a>

    </div>

    <div class="menu-section">
         <a href="student.html"><button class="btn">Student</button></a>
      <a href="faculty.html"><button class="btn">Faculty</button></a>
      <a href="administration.html"><button class="btn">Administration</button></a>
      <a href="alumni.html"><button class="btn">Alumni</button></a>
      <a href="campus.html"><button class="btn">Campus Life</button></a>
      <a href="iqac.html"><button class="btn">IQAC</button></a>
      <a href="notice.html"><button class="btn">Notice</button></a>
      <a href="news.html"><button class="btn">News</button></a>
      <a href="ranking.html"><button class="btn">Ranking</button></a>
      <a href="academic.html"><button class="btn">Academics</button></a>
      <a href="scholarship.html"><button class="btn">Scholarships</button></a>
      <a href="admission.html"><button class="btn">Admission</button></a>
      <a href="library.php"><button class="btn">Library</button></a>
      <a href="volunteer.html"><button class="btn">Volunteer</button></a>
      <a href="about.html"><button class="btn">About US</button></a>
    </div>
  </div>
    <!-- Main Content Area with Right Navbar -->
    <div class="main-container">
        <!-- Content Section -->
       <div class="content">
      <?php
      // Database connection
      $con = mysqli_connect("localhost", "root", "", "skst_university");
      if (!$con) {
          die("Connection failed: " . mysqli_connect_error());
      }

      // Set default timezone
      date_default_timezone_set('Asia/Dhaka');

      // Create table if not exists (initial setup)
      $create_table = "CREATE TABLE IF NOT EXISTS notice (
          id INT AUTO_INCREMENT PRIMARY KEY,
          title VARCHAR(255) NOT NULL,
          section VARCHAR(100),
          content TEXT NOT NULL,
          author VARCHAR(100) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )";
      mysqli_query($con, $create_table);

      // Handle form submissions
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          if (isset($_POST['add_notice'])) {
              $title = mysqli_real_escape_string($con, $_POST['title']);
              $section = mysqli_real_escape_string($con, $_POST['section']);
              $content = mysqli_real_escape_string($con, $_POST['content']);
              $author = mysqli_real_escape_string($con, $_POST['author']);

              $stmt = $con->prepare("INSERT INTO notice (title, section, content, author) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $title, $section, $content, $author);
            
            if ($stmt->execute()) {
                echo "<div class='success-message'>Notice added successfully</div>";
            } else {
                echo "<div class='error-message'>Error: " . $stmt->error . "</div>";
            }
        }
    }?>
<div class="bg-glass">
     
      <?php if (isset($_POST['notice'])) {
          $query = "SELECT * FROM notice ORDER BY created_at DESC";
          $result = mysqli_query($con, $query);
          
          if (mysqli_num_rows($result) > 0) {
    echo "<div class='notices-container'>";
    echo "<h2 class='notices-heading'><i class='fas fa-bullhorn'></i> Latest Notices</h2>";
    
      if($row = mysqli_fetch_assoc($result)){
        echo "<div class='notice-card'>";
        echo "<div class='notice-header'>";
        echo "<h3 class='notice-title'><i class='fas fa-chevron-circle-right'></i> " . htmlspecialchars($row['title']) . "</h3>";
        echo "<span class='notice-section'>" . htmlspecialchars($row['section']) . "</span>";
        echo "</div>";
        
        echo "<div class='notice-content'>" . nl2br(htmlspecialchars($row['content'])) . "</div>";
        
        echo "<div class='notice-footer'>";
        echo "<span class='notice-author'><i class='fas fa-user'></i> " . htmlspecialchars($row['author']) . "</span>";
        echo "<span class='notice-date'><i class='far fa-calendar-alt'></i> " . date('F j, Y h:i A', strtotime($row['created_at'])) . "</span>";
        echo "</div>";
        echo "</div>"; // Close notice-card
    }
    
    echo "<div class='back-button-container'>";
    echo "<a href='library.php' class='back-button'><i class='fas fa-arrow-left'></i> Back to Library</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
    echo "<a href='library.php' class='back-button'><i class='fas fa-arrow-left'></i> Back to Library</a>";
    echo "</div>";
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
      } elseif (isset($_POST['staff'])) {
          echo "<h2>Staff Portal</h2>";
          echo "<p>Login area for library staff members.</p>";
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
      }
       else{ ?>
            
                <!-- Introduction -->
            <h2>📚 Welcome to SKST University Library</h2>
        <p>
          Our library is a knowledge hub supporting the academic and research needs of students, faculty, and staff.
          With access to physical and digital resources, we aim to promote learning, discovery, and innovation.
        </p>
        
        <!-- Library Statistics -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-number">125,000+</div>
            <div class="stat-label">Books Available</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">35,000+</div>
            <div class="stat-label">E-Journals</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">1,200+</div>
            <div class="stat-label">Study Spaces</div>
          </div>
          <div class="stat-card">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Online Access</div>
          </div>
        </div>

        <!-- Featured Books -->
        <h3>🌟 Featured Books</h3>
        <div class="featured-books">
          <div class="book-card">
            <div class="book-cover">The Great Gatsby</div>
            <div class="book-info">
              <div class="book-title">The Great Gatsby</div>
              <div class="book-author">F. Scott Fitzgerald</div>
            </div>
          </div>
          <div class="book-card">
            <div class="book-cover">To Kill a Mockingbird</div>
            <div class="book-info">
              <div class="book-title">To Kill a Mockingbird</div>
              <div class="book-author">Harper Lee</div>
            </div>
          </div>
          <div class="book-card">
            <div class="book-cover">1984</div>
            <div class="book-info">
              <div class="book-title">1984</div>
              <div class="book-author">George Orwell</div>
            </div>
          </div>
          <div class="book-card">
            <div class="book-cover">The Alchemist</div>
            <div class="book-info">
              <div class="book-title">The Alchemist</div>
              <div class="book-author">Paulo Coelho</div>
            </div>
          </div>
          <div class="book-card">
            <div class="book-cover">Sapiens</div>
            <div class="book-info">
              <div class="book-title">Sapiens</div>
              <div class="book-author">Yuval Noah Harari</div>
            </div>
          </div>
        </div>

        <!-- Mission -->
        <h3>🎯 Our Mission</h3>
        <p>
          To provide accessible, high-quality learning materials and support services that empower students and educators
          in achieving academic excellence and research development.
        </p>

        <!-- Benefits -->
        <h3>✅ Why Use Our Library?</h3>
        <ul>
          <li>Open access to thousands of books and journals.</li>
          <li>Quiet and clean reading zones for focus and productivity.</li>
          <li>Digital library with e-books and research papers.</li>
          <li>Friendly staff support and guidance.</li>
          <li>Online book reservation and tracking system.</li>
          <li>Special collections for faculty and departmental publications.</li>
        </ul>

        <!-- Rules -->
        <h3>📌 Library Rules & Policies</h3>
        <ul>
          <li>Maintain silence and discipline at all times.</li>
          <li>Carry your university ID while visiting.</li>
          <li>Return borrowed books by the due date.</li>
          <li>Mobile use inside the library is discouraged.</li>
          <li>Respect library property and staff members.</li>
        </ul>

        <!-- Guide -->
        <h3>🔍 How to Use the Library Portal</h3>
        <ol>
          <li>Use the search tool to find books by title, author, or keyword.</li>
          <li>Browse by categories for subject-specific resources.</li>
          <li>Log in to reserve books or access digital materials.</li>
          <li>Check your borrowing history and due dates.</li>
        </ol>

        <!-- Suggestions -->
        <h3>🧠 Suggest New Books</h3>
        <p>
          If you'd like to see a specific book or resource in our library, feel free to use the request form or talk to
          our librarian. We welcome your suggestions!
        </p>

        <!-- Hours -->
        <h3>🕐 Library Hours</h3>
        <p>
          <strong>Sunday – Thursday:</strong> 9:00 AM – 5:00 PM<br>
          <strong>Friday – Saturday:</strong> Closed (except during exam sessions)
        </p>
                <?php } ?>
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
design remain same remove script and provide me full code
fixt the problem on my way and provide me full code