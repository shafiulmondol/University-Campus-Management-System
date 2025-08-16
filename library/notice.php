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
   .button-container {
    display: flex;
    gap: 15px;
    margin-top: 20px;
    justify-content: center;
}

.yes-button, .back-button {
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.yes-button {
    background-color: #4CAF50;
    color: white;
    border: 2px solid #a04b45ff;
}

.yes-button:hover {
    background-color: #c5dd0bff;
    color: #f44336;

}

.back-button {
    background-color: #f44336;
    color: white;
    border: 2px solid #d32f2f;
}

.back-button:hover {
    background-color: #d32f2f;
}

.error-message {
    color: #d32f2f;
    margin: 10px 0;
    font-weight: bold;
}

.member-details-container {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #07f5f5ff;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        .required:after {
            content: " *";
            color: #e74c3c;
        }
         body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background-color: #f5f5f5;
                margin: 0;
              
                color: #333;
            }
            
            .container {
                max-width: 800px;
                margin: 0 auto;
                background-color: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            }
</style>
 </head>
 <body>
    
 
 
 <?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    viewed TINYINT(1) DEFAULT 0 COMMENT '0=unread, 1=read',
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
}function get_unread_notification_count() {
    global $con;
    $query = "SELECT COUNT(*) as count FROM notice WHERE viewed = 0 AND section='Staff'";
    $result = mysqli_query($con, $query);
    if($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'] ?? 0;
    }
    return 0;
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
             $output .= member_check($ch_id,$category);
        }
    } else {
        $output = '<div class="error-message">No student found with this ID</div>';
    }
    break;
            
       case 'faculty':
    $que = "SELECT * FROM faculty WHERE faculty_id = '$ch_id'";
    $qres = mysqli_query($con, $que);
    
    if(mysqli_num_rows($qres) > 0) {
        while ($book = mysqli_fetch_assoc($qres)) {
            $output .= '<div class="book-details-container">';
            $output .= '<div><strong>ID:</strong> '.htmlspecialchars($book['faculty_id']).'</div>';
            $output .= '<div><strong>Name:</strong> '.htmlspecialchars($book['name']).'</div>';
            $output .= '<div><strong>Email:</strong> '.htmlspecialchars($book['email']).'</div>';
            $output .= '<div><strong>Category:</strong> '.htmlspecialchars($category).'</div>';
            $output .= '</div>';
             $output .= member_check($ch_id,$category);
        }
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
                     $output .= member_check($ch_id ,$category);
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
                   $output .= member_check($ch_id, $category);
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
                     $output .= member_check($ch_id, $category);
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



// Your existing member_check function
function member_check($ch_id, $category) {
    global $con;
    $output = '';
    $que = "SELECT * FROM users WHERE id = '$ch_id'";
    $qres = mysqli_query($con, $que);
    
    if (!$qres) {
        $output .= '<div class="error-message">Database error: ' . htmlspecialchars(mysqli_error($con)) . '</div>';
        return $output;
    }

    if (mysqli_num_rows($qres) > 0) {
        $output .= '<div class="member-details-container">';
        $output .= '<h1><u>Library Member Requirement</u></h1>';
        
        while ($book = mysqli_fetch_assoc($qres)) {
            $output .= '<div class="member-detail">';
            $output .= '<div><strong>User ID:</strong> ' . htmlspecialchars($book['user_id'] ?? 'N/A') . '</div>';
            $output .= '<div><strong>Library card number:</strong> ' . htmlspecialchars($book['library_card_number'] ?? 'N/A') . '</div>';
            $output .= '<div><strong>User Type:</strong> ' . htmlspecialchars($book['user_type'] ?? 'N/A') . '</div>';
            $output .= '<div><strong>Join Date:</strong> ' . htmlspecialchars($book['created_at'] ?? 'N/A') . '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '<a href="library.php?action=renew" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>';
    } else {
        $output .= '<div class="error-message">This '. htmlspecialchars($category) .' is not our member</div>';
        $output .= '<div class="error-message">Do you want to add this person as a library member?</div>';
        
        $output .= '<div class="button-container">';
        $output .= '<a href="library.php?action=add_member&id=' . htmlspecialchars($ch_id) . '" class="yes-button"><i class="fas fa-user-plus"></i> Add</a>';
        $output .= '<a href="library.php?action=renew" class="back-button"><i class="fas fa-arrow-left"></i> Back</a>';
        $output .= '</div>';
    }
    
    return $output;
}

// Form display logic
if (isset($_GET['action']) && $_GET['action'] == 'add_member') {
    $member_id = isset($_GET['id']) ? $_GET['id'] : '';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Library User Registration</title>
        <style>
            /* Your CSS styles here */
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Library Member Registration</h1>
            <form id="userRegistrationForm" action="" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($member_id); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="library_card_number" class="required">Library Card Number</label>
                        <input type="text" id="library_card_number" name="library_card_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type" class="required">User Type</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Select User Type</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Researcher">Researcher</option>
                            <option value="Guest">Guest</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="id_display">ID Number</label>
                        <input type="text" id="id_display" value="<?php echo htmlspecialchars($member_id); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_books_allowed">Maximum Books Allowed</label>
                        <input type="number" id="max_books_allowed" name="max_books_allowed" min="1" max="20" value="5">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="membership_start_date" class="required">Membership Start Date</label>
                        <input type="date" id="membership_start_date" name="membership_start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="membership_end_date">Membership End Date</label>
                        <input type="date" id="membership_end_date" name="membership_end_date">
                    </div>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" checked>
                    <label for="is_active">Active Membership</label>
                </div>
                
                <button type="submit" name="add">Register Member</button>
                <a href="library.php?action=renew" class="back-button"><i class="fas fa-arrow-left"></i> Not Now?!</a>
            </form>
        </div>
<?php
        
if(isset($_POST['add'])) {
    // Ensure users table exists
    add_members();
    
    // Sanitize and validate input
    $user_type = mysqli_real_escape_string($con, $_POST['user_type']);
    $library_card_number = mysqli_real_escape_string($con, $_POST['library_card_number']);
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $max_books_allowed = intval($_POST['max_books_allowed']);
    $membership_start_date = mysqli_real_escape_string($con, $_POST['membership_start_date']);
    $membership_end_date = !empty($_POST['membership_end_date']) ? mysqli_real_escape_string($con, $_POST['membership_end_date']) : NULL;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Prepare the INSERT query
    $addq = "INSERT INTO users (
                user_type, 
                library_card_number, 
                id, 
                max_books_allowed, 
                membership_start_date, 
                membership_end_date, 
                is_active
            ) VALUES (
                '$user_type',
                '$library_card_number',
                '$id',
                $max_books_allowed,
                '$membership_start_date',
                " . ($membership_end_date ? "'$membership_end_date'" : "NULL") . ",
                $is_active
            )";
    
    $result = mysqli_query($con, $addq);
    
    if($result) {
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Success</title>
        <meta http-equiv="refresh" content="5;url=library.php">
        <style>
            .success-container {
                text-align: center;
                margin: 100px auto;
                max-width: 500px;
                padding: 20px;
                background-color: #dff0d8;
                border: 1px solid #d6e9c6;
                border-radius: 4px;
                color: #3c763d;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <h2>Member Added Successfully!</h2>
            <p>You will be redirected back to the homepage in 5 seconds.</p>
            <p>If you are not redirected automatically, <a href="library.php">click here</a>.</p>
        </div>
    </body>
    </html>
    ';
    exit();
} else {
        $_SESSION['errors'] = ["Error adding member: " . mysqli_error($con)];
        header("Location: library.php?action=add_member&id=".$id);
        exit();
    }

}

?>

    </body>
    </html>
    <?php
    exit();}




?>




    </body>
 </html>