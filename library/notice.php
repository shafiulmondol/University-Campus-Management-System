 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../library.css">
    <link rel="stylesheet" href="../student/student.html">
    <style>
        :root {
            --primary: #c1ecee;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        } 
        </style>
 </head>
 <body>
    
 
 
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
          id INT,
          title VARCHAR(255) NOT NULL,
          section VARCHAR(100),
          content TEXT NOT NULL,
          author VARCHAR(100) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )";
      mysqli_query($con, $create_table);

      // Handle form submissions
    //   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //       if (isset($_POST['add_notice'])) {
    //           $title = $_POST['title'];
    //           $section = $_POST['section'];
    //           $content =  $_POST['content'];
    //           $author = $_POST['author'];

    //           $stmt = mysqli_query($con,"INSERT INTO notice (title, section, content, author) VALUES (?, ?, ?, ?)");
            
    //         if ($stmt) {
    //             echo "<div class='success-message'>Notice added successfully</div>";
    //         } else {
    //             die ("Table not created".mysqli_error($con));
    //         }
    //     }
    // }
    function see_notice(){
    global $con;
          $query = "SELECT * FROM notice ORDER BY created_at DESC";
          $result = mysqli_query($con, $query);
          
          if (mysqli_num_rows($result) > 0) {
    echo "<div class='notices-container'>";
    echo "<h2 class='notices-heading'><i class='fas fa-bullhorn'></i> Latest Notices</h2>";
    
      while($row = mysqli_fetch_assoc($result)){
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
}
    
     function login_form() {
    
    
    // Display the login form if not submitted or if login failed
    ?>
    <div class="staff-login-container">
        <!-- Your existing login form HTML -->
        <div class="staff-login-box">
            <div class="login-header">
                <img src="../picture/logo.gif" alt="SKST Logo" class="login-logo">
                <h1>Library Staff Login</h1>
            </div>
            
            <div class="login-body">
                <form  method="post" class="login-form">  <!-- Changed action to empty -->
                    <div class="form-group">
                        <label for="staffmail">E-mail</label>
                        <input type="email" id="staffmail" name="staffmail" placeholder="Enter email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="staffpass">Password</label>
                        <input type="password" id="staffpass" name="staffpass" placeholder="Enter password" required>
                    </div>
                    
                    <button type="submit" name="submit" class="login-button">Login</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['submit'])) {
        $email =  $_POST['staffmail'];
        $password = $_POST['staffpass'];
       login_condition($email,$password);
    }
}

function login_condition($email,$password) {
    global $con;

        $query = "SELECT * FROM stuf";
        $result = mysqli_query($con, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($password == $row['password'] && $email == $row['email']  ) {
                // Display welcome message and dashboard
                ?>
                <div class='welcome-message'>Welcome, <?php echo $row['first_name']." ".$row['last_name']; ?>!</div>
                <div class="staff-dashboard">
                    <h2>Library Staff Dashboard</h2>
                    <div class="staff-actions">
                        <form method="post">
                            <button type="submit" name="addn"><i class="fas fa-bullhorn"></i> Add Library Notice</button>
                        </form>
                    </div>
                </div>
                <?php
                return; // Stop further execution
            }
        }
        // If we get here, login failed
        echo "<div class='error-message'>Wrong email or password</div>";
    }

?>



    </body>
 </html>