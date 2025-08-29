<?php
// Database configuration
session_start();
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'skst_university';

// Initialize variables
$success_message = '';
$error_message = '';
$conn = null;

try {
    // Create connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize and validate input
        $name = $conn->real_escape_string($_POST['name']);
        $id = $conn->real_escape_string($_POST['id']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $affiliation = $conn->real_escape_string($_POST['affiliation']);
        $department = $conn->real_escape_string($_POST['department']);
        $availability = $conn->real_escape_string($_POST['availability']);
        $skills = $conn->real_escape_string($_POST['skills']);
        
        // Process interests (checkboxes)
        $interests = isset($_POST['interests']) ? $_POST['interests'] : [];
        $interests_str = implode(', ', $interests);

        // Insert into database
        $sql = "INSERT INTO volunteers (name, student_staff_id, email, phone, affiliation, department, availability, skills, interests, registration_date)
                VALUES ('$name', '$id', '$email', '$phone', '$affiliation', '$department', '$availability', '$skills', '$interests_str', NOW())";

        if ($conn->query($sql)) {
            $success_message = "Thank you for registering as a volunteer!";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    // Get volunteer opportunities from database
    $opportunities_sql = "SELECT * FROM volunteer_opportunities ORDER BY date DESC";
    $opportunities_result = $conn->query($opportunities_sql);

    // Get volunteer hours (sample data - in real app you'd filter by user)
    $hours_sql = "SELECT * FROM volunteer_hours ORDER BY event_date DESC LIMIT 5";
    $hours_result = $conn->query($hours_sql);

    // Calculate total hours
    $total_hours = 0;
    if ($hours_result && $hours_result->num_rows > 0) {
        while($row = $hours_result->fetch_assoc()) {
            $total_hours += $row['hours'];
        }
        // Reset pointer for displaying again
        $hours_result->data_seek(0);
    }
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer System | University Campus Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS styles here */
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f72585;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* ... rest of your CSS ... */
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-hands-helping"></i>
                <span>Campus Volunteers</span>
            </div>
            <nav>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="#" class="active"><i class="fas fa-hand-holding-heart"></i> Volunteer</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Make a Difference on Campus</h1>
            <p>Join our vibrant community of volunteers and help shape the university experience for everyone</p>
            <a href="#opportunities" class="btn">Browse Opportunities</a>
            <a href="#register" class="btn btn-outline">Register Now</a>
        </div>
    </section>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <section id="opportunities">
            <div class="section-title">
                <h2>Current Volunteer Opportunities</h2>
            </div>
            
            <div class="opportunities-grid">
                <?php if (isset($opportunities_result) && $opportunities_result && $opportunities_result->num_rows > 0): ?>
                    <?php while($opportunity = $opportunities_result->fetch_assoc()): ?>
                        <div class="opportunity-card">
                            <?php if (!empty($opportunity['image_url'])): ?>
                                <div class="card-image">
                                    <img src="<?php echo htmlspecialchars($opportunity['image_url']); ?>" alt="<?php echo htmlspecialchars($opportunity['title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($opportunity['title']); ?></h3>
                                <div class="card-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('M j, Y', strtotime($opportunity['date'])); ?></span>
                                    <span><i class="far fa-clock"></i> <?php echo htmlspecialchars($opportunity['time']); ?></span>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($opportunity['location']); ?></span>
                                </div>
                                <p><?php echo htmlspecialchars($opportunity['description']); ?></p>
                                <div class="card-footer">
                                    <span class="tag <?php echo $opportunity['is_urgent'] ? 'urgent' : ''; ?>">
                                        <?php echo htmlspecialchars($opportunity['category']); ?>
                                        <?php echo $opportunity['is_urgent'] ? ' • Urgent' : ''; ?>
                                    </span>
                                    <button class="btn">Sign Up</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No volunteer opportunities available at this time.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="register">
            <div class="section-title">
                <h2>Register as a Volunteer</h2>
            </div>
            
            <div class="volunteer-form-container">
                <form action="volunteer.php" method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="id">Student/Staff ID</label>
                            <input type="text" id="id" name="id" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="affiliation">University Affiliation</label>
                            <select id="affiliation" name="affiliation" required>
                                <option value="">Select...</option>
                                <option value="undergrad">Undergraduate Student</option>
                                <option value="grad">Graduate Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="staff">Staff</option>
                                <option value="alumni">Alumni</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="department">Department (if applicable)</label>
                            <input type="text" id="department" name="department">
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Areas of Interest (Select all that apply)</label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-events" name="interests[]" value="events">
                                    <label for="interest-events">Campus Events</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-community" name="interests[]" value="community">
                                    <label for="interest-community">Community Service</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-orientation" name="interests[]" value="orientation">
                                    <label for="interest-orientation">Student Orientation</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-sustainability" name="interests[]" value="sustainability">
                                    <label for="interest-sustainability">Sustainability Initiatives</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="interest-fundraising" name="interests[]" value="fundraising">
                                    <label for="interest-fundraising">Fundraising</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="availability">Availability</label>
                            <textarea id="availability" name="availability" placeholder="Please describe when you're typically available to volunteer (e.g., weekends, weekday evenings, etc.)"></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="skills">Skills or Special Qualifications</label>
                            <textarea id="skills" name="skills" placeholder="Any special skills or certifications you have that might be relevant (first aid, languages, etc.)"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn submit-btn">Submit Volunteer Application</button>
                </form>
            </div>
        </section>

        <section id="hours">
            <div class="section-title">
                <h2>Your Volunteer Hours</h2>
            </div>
            
            <div class="hours-tracker">
                <div class="total-hours">
                    <h3>Total Hours Contributed</h3>
                    <p><?php echo $total_hours; ?></p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($hours_result) && $hours_result && $hours_result->num_rows > 0): ?>
                            <?php while($hours = $hours_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($hours['event_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($hours['event_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($hours['hours']); ?></td>
                                    <td><span class="tag"><?php echo htmlspecialchars($hours['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No volunteer hours recorded yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <footer>
        <div class="footer-container">
            <div class="logo">
                <i class="fas fa-hands-helping"></i>
                <span>Campus Volunteers</span>
            </div>
            <p>Making our university community stronger, one volunteer at a time</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
            <p>&copy; <?php echo date("Y"); ?> University Campus Management System</p>
        </div>
    </footer>

    <script>
        // Form submission handling
        document.querySelector('form').addEventListener('submit', function(e) {
            // Form is already handled by PHP, this is just for UX
            // You can add form validation here if needed
        });

        // Set current year in footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>

<?php
// Close database connection if it exists
if ($conn) {
    $conn->close();
}
?>