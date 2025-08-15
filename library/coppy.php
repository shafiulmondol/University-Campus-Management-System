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
    </body>
    </html>
    <?php
    exit;}

    
if(isset($_POST['submit'])){
    // Sanitize and validate input
    $user_type = mysqli_real_escape_string($con, $_POST['user_type']);
    $library_card_number = mysqli_real_escape_string($con, $_POST['library_card_number']);
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $max_books_allowed = intval($_POST['max_books_allowed']);
    $membership_start_date = mysqli_real_escape_string($con, $_POST['membership_start_date']);
    $membership_end_date = !empty($_POST['membership_end_date']) ? mysqli_real_escape_string($con, $_POST['membership_end_date']) : NULL;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate required fields
    $errors = [];
    if(empty($user_type)) $errors[] = "User type is required";
    if(empty($library_card_number)) $errors[] = "Library card number is required";
    if(empty($id)) $errors[] = "ID is required";
    if(empty($membership_start_date)) $errors[] = "Membership start date is required";
    
    if(!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: library.php?action=add_member&id=".$id);
        exit();
    }

    // Prepare the INSERT query
    $addq = "INSERT INTO users (
                user_type, 
                library_card_number, 
                id, 
                max_books_allowed, 
                membership_start_date, 
                membership_end_date, 
                is_active,
                created_at,
                updated_at
            ) VALUES (
                '$user_type',
                '$library_card_number',
                '$id',
                $max_books_allowed,
                '$membership_start_date',
                " . ($membership_end_date ? "'$membership_end_date'" : "NULL") . ",
                $is_active,
                NOW(),
                NOW()
            )";
    
    $result = mysqli_query($con, $addq);
    
    if($result && mysqli_affected_rows($con) > 0) {
        // Success - redirect with success message
        $_SESSION['success'] = "Member added successfully!";
        header("Location: library.php");
        exit();
    } else {
        // Error handling
        $_SESSION['errors'] = ["Error adding member: " . mysqli_error($con)];
        header("Location: library.php?action=add_member&id=".$id);
        exit();
    }
}

// Display success/error messages if they exist
if(isset($_SESSION['success'])) {
    echo '<div class="success-message">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}

if(isset($_SESSION['errors'])) {
    foreach($_SESSION['errors'] as $error) {
        echo '<div class="error-message">'.$error.'</div>';
    }
    unset($_SESSION['errors']);
}

