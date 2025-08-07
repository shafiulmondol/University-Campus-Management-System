<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "skst_university");
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Initialize variables
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$show_all = isset($_GET['all']);

// Build SQL query
if ($show_all) {
    $sql = "SELECT * FROM books ORDER BY title ASC";
    $stmt = mysqli_prepare($con, $sql);
} else {
    $sql = "SELECT * FROM books WHERE book_id LIKE ? OR title LIKE ? OR author LIKE ? ORDER BY book_id";
    $stmt = mysqli_prepare($con, $sql);
    $search_param = "%$search_query%";
    mysqli_stmt_bind_param($stmt, "sss",$search_param, $search_param, $search_param);
}

// Execute query
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$results_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Search Results</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .search-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .results-count {
            font-size: 1.1em;
            margin-bottom: 20px;
            color: #555;
        }
        .results-count strong {
            color: #5c6bc0;
        }
        .book-results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .book-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .book-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .book-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .book-author {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .book-details {
            margin-top: 10px;
            font-size: 0.9em;
            color: #555;
        }
        .book-details div {
            margin-bottom: 5px;
        }
        .availability {
            font-weight: bold;
        }
        .available {
            color: #27ae60;
        }
        .unavailable {
            color: #e74c3c;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            font-size: 1.2em;
            color: #7f8c8d;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #5c6bc0;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-header">
            <h1><?php echo $show_all ? 'All Books' : 'Search Results'; ?></h1>
            
            <div class="results-count">
                <?php if ($show_all): ?>
                    Showing all books in the library
                <?php else: ?>
                    Found <strong><?php echo $results_count; ?></strong> results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                <?php endif; ?>
            </div>
            
            <a href="library.php" class="back-link">← Back to search</a>
        </div>

        <?php if ($results_count > 0): ?>
            <div class="book-results">
                <?php while ($book = mysqli_fetch_assoc($result)): ?>
                    <div class="book-card">
                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                        <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                        
                        <div class="book-details">
                            <div><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></div>
                            <div><strong>Year:</strong> <?php echo htmlspecialchars($book['publication_year']); ?></div>
                            <div><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></div>
                            <div><strong>Location:</strong> <?php echo htmlspecialchars($book['shelf_location']); ?></div>
                            <div class="availability <?php echo ($book['available_copies'] > 0) ? 'available' : 'unavailable'; ?>">
                                <?php echo ($book['available_copies'] > 0) ? 'Available' : 'Checked Out'; ?>
                                (<?php echo $book['available_copies']; ?> of <?php echo $book['total_copies']; ?> copies)
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>No books found matching your search criteria.</p>
                <a href="library.php" class="back-link">Try another search</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Close database connection
mysqli_stmt_close($stmt);
mysqli_close($con);
?>