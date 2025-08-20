<?php
session_start();
require_once 'notice.php';

      // Database connection
      $con = mysqli_connect("localhost", "root", "", "skst_university");
      if (!$con) {
          die("Connection failed: " . mysqli_connect_error());
      }

      // Set default timezone
      date_default_timezone_set('Asia/Dhaka');

// Handle logout
// Alternative logout handler with JavaScript fallback
if (isset($_POST['logout'])) {
    $_SESSION = array();
    session_destroy();
    
    // JavaScript redirect fallback
    echo '<script>window.location.href = "library.php";</script>';
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
        .dashboard-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.welcome-header {
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.welcome-image {
    flex: 0 0 200px;
}

.cartoon-img {
    width: 100%;
    height: auto;
    filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.2));
}

.welcome-message {
    flex: 1;
    padding-left: 30px;
}

.welcome-message h2 {
    font-size: 28px;
    margin-bottom: 10px;
}

.welcome-message p {
    font-size: 16px;
    opacity: 0.9;
}

.staff-bio {
    margin-bottom: 30px;
}

.bio-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.bio-card h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 22px;
    display: flex;
    align-items: center;
}

.bio-card h3 i {
    margin-right: 10px;
    color: #2575fc;
}
.bot-logo-container {
    text-align: center;
    margin: 25px 0;
    position: relative;
}

.bot-logo {
    display: inline-block;
    position: relative;
}

.bot-img {
    width: 90px;
    height: auto;
    filter: drop-shadow(0 0 8px rgba(74, 144, 226, 0.4));
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: 50%;
    padding: 5px;
    background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(240,240,240,0.9) 100%);
}

.bot-img:hover {
    transform: scale(1.1) rotate(-5deg);
    filter: drop-shadow(0 0 12px rgba(74, 144, 226, 0.6));
}

.bot-title {
    margin-top: 10px;
    font-weight: 600;
    color: #4a90e2;
    font-size: 1.1em;
    letter-spacing: 0.5px;
}

.bot-tooltip {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: #4a90e2;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    opacity: 0;
    transition: all 0.3s ease;
    width: max-content;
    max-width: 180px;
    pointer-events: none;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.bot-img:hover + .bot-tooltip {
    opacity: 1;
    bottom: -35px;
}

@media (max-width: 768px) {
    .bot-img {
        width: 70px;
    }
    .bot-title {
        font-size: 1em;
    }
}
.bio-details {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.detail-label {
    font-weight: 600;
    color: #555;
}

.detail-value {
    color: #333;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card i {
    font-size: 36px;
    color: #2575fc;
    margin-bottom: 15px;
}

.stat-card h4 {
    color: #555;
    margin-bottom: 10px;
    font-size: 16px;
}

.stat-card p {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}
.notification-wrapper {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 100;
}

.notification-bell {
    position: relative;
    display: inline-block;
}

.bell-btn {
    background: none;
    border: none;
    cursor: pointer;
    position: relative;
    padding: 10px;
    font-size: 1.5rem;
    color: #4a90e2;
    transition: all 0.3s ease;
}

.bell-btn:hover {
    transform: scale(1.1);
    color: #2575fc;
}

.badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
}

.notice-card.unread {
    background-color: #f8f9fa;
    border-left: 3px solid #4a90e2;
}
    </style>
</head>
<body>
    


    
        <!-- Main Content Area with Right Navbar -->
        <div class="main-container">
            <?php if (isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] == true) { ?>
                <div class="nav-links">
                    <a href="#" class="library-logo">
                        <nav class="library-navbars">
                            <a href="#" class="library-logo">
                                <i class="fas fa-book-open"></i><br>
                                <span>Dashboard </span> 
                            </a>
                            <input type="checkbox" id="nav-toggle" class="nav-toggle">
                            <label for="nav-toggle" class="hamburger">&#9776;</label>
                        </nav>
                    </a>
                    <form action="library.php" method="post">
                        <button class="nav-btn" type="submit" name="search"><i class="fas fa-search"></i>  📚 See Books</button>
                        <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> 📄 Borrow Details</button>
                        <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i>   ➕ Add Books</button>
                        <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> 📝 Add Members</button>
                        <button type="submit" name="logout" class="nav-btn">
        <i class="fas fa-sign-out-alt"></i> 📤 Logout
    </button>
                    </form>
                </div> 
                <div class="content">
                    <div class="bg-glass">
                        <?php
                        if (isset($_POST['borrow'])) {
                            echo "<h2>Borrow Book</h2>";
                            echo "<p>This section would contain information about borrowing Book equipment from the library.</p>";
                        }
                        elseif (isset($_POST['search'])) { 
                            if (isset($_POST['search']) || isset($_POST['all'])) { ?>
                                <div class="book-search-container">
                                    <form action="search_results.php" method="get" class="book-search-form">
                                        <div class="search-box">
                                            <input type="text" name="search_query" placeholder="Search books by title, author, or Book name" 
                                                class="search-input" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" required>
                                            <button type="submit" class="search-button" name="search_submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                                </svg>
                                                <span class="sr-only">Search books</span>
                                            </button>
                                        </div>
                                    </form>
                                    
                                    <form action="search_results.php" method="get">
                                        <button type="submit" class="search_all">Show All Books</button>
                                    </form>
                                </div>

                                <?php
                                // Show results if search was performed
                                if (isset($_POST['all'])) {
                                    // Code to display all books
                                    echo "<div class='search-results'>";
                                    echo "Showing all books...";
                                    // Your code to fetch and display all books would go here
                                    echo "</div>";
                                }
                                elseif (isset($_POST['search'])) {
                                    // Code to display search results
                                    echo "<div class='search-results'>";
                                    echo "Showing search results...";
                                    // Your code to fetch and display search results would go here
                                    echo "</div>";
                                }
                            }
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
                           add_members(); ?>
                           <form action="library.php" method="post">
                            <p>Enter your ID</p>
                            <input type="text" name="ids" required>
                            
                            <label for="category">Select your category:</label>
                            <select name="category" id="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="staff">Staff</option>
                                <option value="alumni">Alumni</option>
                                <option value="admin">Admin</option>
                            </select>
                            
                            <input type="submit" name="idssubmit" class="submit-btn">
                        </form>
                           <?php
                        }
                        elseif (isset($_POST['idssubmit'])) {
                          $ch_id = $_POST['ids'];
                          $category = $_POST['category'];
                          echo id_check($ch_id, $category);
                      }
                        elseif(isset($_POST['ssubmit'])){
                            echo see_staff_notice();
                            $query = "UPDATE notice SET viewed =0  WHERE section = 'Staff'";
                             $stmt = mysqli_prepare($con, $query);
                        }
                        else {
                            ?>
                         <div class="dashboard-container">
    <!-- Notification Bell -->
    <div class="dashboard-container">
    <!-- Notification Bell - Improved Version -->
    <div class="notification-wrapper">
        <form action="library.php" method="post">
            <div class="notification-bell">
                <button type="submit" name="ssubmit" class="bell-btn">
                    <i class="fas fa-bell"></i>
                    <?php 
                    $unread = get_unread_notification_count(); 
                    if($unread > 0): ?>
                        <span class="badge"><?= htmlspecialchars($unread) ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </form>
    </div>
</div>
    <div class="welcome-header">
        <div class="bot-logo-container">
            <div class="bot-logo">
                <img src="https://cdn-icons-png.flaticon.com/512/3344/3344372.png" alt="Library Bot" class="bot-img">
                <div class="bot-tooltip">Hi! I'm your library assistant</div>
            </div>
            <p class="bot-title">LibraryAI Assistant</p>
        </div>
        <div class="welcome-message">
            <h2>Welcome Back to Your Library Dashboard!</h2>
            <p>We're glad to see you again. Here's what's happening today.</p>
        </div>
    </div>

    <div class="staff-bio">
        <div class="bio-card">
            <h3><i class="fas fa-user"></i> Staff Information</h3>
            <div class="bio-details">
                <div class="detail-item">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars( $_SESSION['name'] ?? 'Not available'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Staff ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($_SESSION['id'] ?? 'Not available'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Position:</span>
                    <span class="detail-value"><?php echo htmlspecialchars( $_SESSION['position'] ?? 'Not available'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Department:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($_SESSION['department'] ?? 'Not available'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($_SESSION['email'] ?? 'Not available'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="quick-stats">
        <div class="stat-card">
            <i class="fas fa-book-open"></i>
            <h4>Books Checked Out Today</h4>
            <p>24</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h4>New Patrons This Week</h4>
            <p>15</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-clock"></i>
            <h4>Overdue Items</h4>
            <p>7</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-alt"></i>
            <h4>Upcoming Events</h4>
            <p>3</p>
        </div>
    </div>
</div>


<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?php
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
                        } 
                        elseif (isset($_POST['submit'])) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $password = $_POST['password']; // Don't escape password (hashes need raw input)
        
        // Fixed query - select only the user with matching email
        $query = "SELECT * FROM stuf WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            die("Database query failed: " . mysqli_error($con));
        }
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Verify password - assuming it's stored as plain text (NOT RECOMMENDED)
            // For better security, use password_hash() and password_verify()
            if ($password == $row['password']) {
                // Login success
                $_SESSION['staff_logged_in'] = true;
                $_SESSION['id'] = $row['id'];
                $_SESSION['name'] = $row['first_name'] . " " . $row['last_name']; // Fixed: was using first_name twice
                $_SESSION['department'] = $row['department'];
                $_SESSION['position'] = $row['position']; // Fixed typo in column name (was 'ipositiond')
                $_SESSION['email'] = $row['email'];
                
                // header("Location: dashboard.php");
                // exit();
            } else {
                // Password doesn't match
                echo "<div class='error-message'>Wrong email or password</div>";
            }
        } else {
            // No user found with that email
            echo "<div class='error-message'>Wrong email or password</div>";
        }
    } 
        
                                    // header("Refresh:0");
                                else {
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
                         elseif (isset($_POST['borrow'])) {
                            echo "<h2>Borrow Book</h2>";
                            echo "<p>This section would contain information about borrowing Book equipment from the library.</p>";
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
                            if (isset($_POST['search'])) { ?>
                                <div class="book-search-container">
                                    <form action="search_results.php" method="get" class="book-search-form">
                                        <div class="search-box">
                                            <input type="text" name="search_query" placeholder="Search books by title, author, or Book name" 
                                                class="search-input" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" required>
                                            <button type="submit" class="search-button" name="search_submit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                                </svg>
                                                <span class="sr-only">Search books</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <?php
                                // Show results if search was performed
                                if (isset($_POST['search'])) {
                                    // Code to display search results
                                    echo "<div class='search-results'>";
                                    echo "Showing search results...";
                                    // Your code to fetch and display search results would go here
                                    echo "</div>";
                                }
                            }
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
                        <button class="nav-btn" type="submit" name="search"><i class="fas fa-search"></i> Book Search</button>
                        <button class="nav-btn" type="submit" name="borrow"><i class="fas fa-laptop"></i> Borrow book</button>
                        <button class="nav-btn" type="submit" name="suggest"><i class="fas fa-book-medical"></i> Suggest a Book</button>
                        <button class="nav-btn" type="submit" name="renew"><i class="fas fa-sync-alt"></i> Renew Books</button>
                        <button class="nav-btn" type="submit" name="staff"><i class="fas fa-user-tie"></i> Staff Portal</button>
                        <button class="nav-btn" type="submit" name="about"><i class="fas fa-info-circle"></i> About Us</button>
                        
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
    </div>
</body>
</html>