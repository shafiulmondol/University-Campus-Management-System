 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="library.css">
    <link rel="stylesheet" href="search_results.php">
    
    <style>
    .book-details-container {
        width: 100%;
        margin: 20px 0;
        padding: 20px;
        background-color: #f5f5f5;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .error-message {
        color: #721c24;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px 15px;
        border-radius: 4px;
        margin: 20px 0;
    }
    .back-button {
        display: inline-block;
        margin-top: 20px;
        padding: 8px 15px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s;
    }
    
    .back-button:hover {
        background-color: #2980b9;
    }
    
    .back-button i {
        margin-right: 5px;
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
          $query ="SELECT * FROM notice 
WHERE section NOT IN ('Faculty', 'Student', 'Library', 'Staff','Alumni')
ORDER BY created_at DESC";
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
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}

function see_student_notice(){
    global $con;
          $query ="SELECT * FROM notice 
WHERE section='Student' ORDER BY created_at DESC";
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
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
     echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}
function see_faculty_notice(){
    global $con;
           $query ="SELECT * FROM notice 
WHERE section IS 'Faculty' ORDER BY created_at DESC";
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
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}
function see_alumni_notice(){
    global $con;
           $query ="SELECT * FROM notice 
WHERE section ='Alumni' ORDER BY created_at DESC";
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
     echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
     echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}
function see_library_notice(){
    global $con;
          $query ="SELECT * FROM notice 
WHERE section='Library' ORDER BY created_at DESC";
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
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}
function see_staff_notice(){
    global $con;
          $query ="SELECT * FROM notice 
WHERE section='Staff' ORDER BY created_at DESC";
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
     echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
    
    echo "</div>"; // Close notices-container
} else {
    echo "<div class='no-notices'>";
    echo "<i class='far fa-folder-open'></i>";
    echo "<p>No notices found at this time</p>";
    echo "<a href='javascript:history.back()' class='back-button'><i class='fas fa-arrow-left'></i> Back</a>";
    echo "</div>";
}
}

function login_condition($email,$password) {
    global $con;

        $query = "SELECT * FROM stuf";
        $result = mysqli_query($con, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
              
            if ($password == $row['password'] && $email == $row['email']  ) {
               
                return true; // Stop further execution
            }
        }
        // If we get here, login failed
        echo "<div class='error-message'>Wrong email or password</div>";
    }

 // This should run only once to create the table, not inside the add_book function
function create_books_table() {
    global $con;
    $sqlbook = '
    CREATE TABLE IF NOT EXISTS books (
        book_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        isbn VARCHAR(20) UNIQUE NOT NULL,
        publication_year INT,
        category VARCHAR(100),
        total_copies INT NOT NULL DEFAULT 1,
        available_copies INT NOT NULL DEFAULT 1,
        shelf_location VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )';
    return mysqli_query($con, $sqlbook);
}

function add_book($title, $author, $isbn, $publication_year, $category, $total_copies, $shelf_location) {
    global $con;
    
    // Escape all string values to prevent SQL injection
    $title = mysqli_real_escape_string($con, $title);
    $author = mysqli_real_escape_string($con, $author);
    $isbn = mysqli_real_escape_string($con, $isbn);
    $category = mysqli_real_escape_string($con, $category);
    $shelf_location = mysqli_real_escape_string($con, $shelf_location);
    
    $insertb = "INSERT INTO books (title, author, isbn, publication_year, category, total_copies, available_copies, shelf_location)
                VALUES ('$title', '$author', '$isbn', $publication_year, '$category', $total_copies, $total_copies, '$shelf_location')";
    
    $insertq = mysqli_query($con, $insertb);
    
    if (!$insertq) {
        // Handle error - you might want to log this or return false
        return false;
    }
    echo "Book added successfull";
    return true;
}

function add_members() {
    global $con;
    
    $usquery = "
    CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        library_card_number VARCHAR(20) UNIQUE NOT NULL,
        user_type VARCHAR(20) NOT NULL,
        id INT UNIQUE NOT NULL,
        max_books_allowed INT DEFAULT 5,
        membership_start_date DATE NOT NULL,
        membership_end_date DATE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (library_card_number),
        INDEX (user_type)
    )";
    
    if (!mysqli_query($con, $usquery)) {
        die("Error creating users table: " . mysqli_error($con));
    }
    return true;
}


function id_check($ch_id, $category) {
    global $con;
    
    // Initialize output variable
    $output = '';
    
    // Sanitize inputs
    $ch_id = mysqli_real_escape_string($con, $ch_id);
    $category = mysqli_real_escape_string($con, $category);
    
    switch($category) {
        case 'student':
    $que = "SELECT * FROM student_registration WHERE id = '$ch_id'";
    $qres = mysqli_query($con, $que);
    
    if(mysqli_num_rows($qres) > 0) {
        while ($book = mysqli_fetch_assoc($qres)) {
            $output .= '<div class="book-details-container">';
            $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['id']).'</div>';
            $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['first_name']).' '.htmlspecialchars($book['last_name']).'</div>';
            $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
            $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
            $output .= '</div>';
        }
        // Use a proper link back to your form
        $output .= '<a href="library.php?action=renew" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>';
    } else {
        $output = '<div class="error-message">No student found with this ID</div>';
    }
    break;
            
       case 'faculty':
    $que = "SELECT * FROM faculty WHERE id = '$ch_id'";
    $qres = mysqli_query($con, $que);
    
    if(mysqli_num_rows($qres) > 0) {
        while ($book = mysqli_fetch_assoc($qres)) {
            $output .= '<div class="book-details-container">';
            $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['id']).'</div>';
            $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['first_name']).' '.htmlspecialchars($book['last_name']).'</div>';
            $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
            $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
            $output .= '</div>';
        }
        // Use a proper link back to your form
        $output .= '<a href="library.php?action=renew" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>';
    } else {
        $output = '<div class="error-message">No student found with this ID</div>';
    }
    break;
            
        case 'staff':
            $que = "SELECT * FROM stuf WHERE id = '$ch_id'";
            $qres = mysqli_query($con, $que);
            
            if(mysqli_num_rows($qres) > 0) {
                while ($book = mysqli_fetch_assoc($qres)) {
                    $output .= '<div class="book-details-container">';
                    $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['id']).'</div>';
                    $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['name']).'</div>';
                    $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
                    $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
                    $output .= '</div>';
                }
            } else {
                $output = '<div class="error-message">No staff member found with this ID</div>';
            }
            break;
            
        case 'alumni':
            $que = "SELECT * FROM alumni WHERE id = '$ch_id'";
            $qres = mysqli_query($con, $que);
            
            if(mysqli_num_rows($qres) > 0) {
                while ($book = mysqli_fetch_assoc($qres)) {
                    $output .= '<div class="book-details-container">';
                    $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['id']).'</div>';
                    $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['name']).'</div>';
                    $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
                    $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
                    $output .= '</div>';
                }
            } else {
                $output = '<div class="error-message">No alumni found with this ID</div>';
            }
            break;
            
        case 'admin':
            $que = "SELECT * FROM admin_users WHERE id = '$ch_id'";
            $qres = mysqli_query($con, $que);
            
            if(mysqli_num_rows($qres) > 0) {
                while ($book = mysqli_fetch_assoc($qres)) {
                    $output .= '<div class="book-details-container">';
                    $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['id']).'</div>';
                    $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['name']).'</div>';
                    $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
                    $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
                    $output .= '</div>';
                }
            } else {
                $output = '<div class="error-message">No admin found with this ID</div>';
            }
            break;
            
        default:
            $output = '<div class="error-message">Invalid category selected</div>';
            break;
    }
    
    return $output;
}
?>




    </body>
 </html>