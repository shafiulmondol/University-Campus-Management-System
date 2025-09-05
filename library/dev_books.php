<?php
// Database configuration
$host = 'localhost';
$dbname = 'skst_university';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form actions
$action = $_POST['action'] ?? '';
$book_id = $_POST['book_id'] ?? '';

// Add or Update book
if ($action === 'save') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $publication_year = $_POST['publication_year'] ?? '';
    $category = $_POST['category'] ?? '';
    $total_copies = $_POST['total_copies'] ?? 1;
    $shelf_location = $_POST['shelf_location'] ?? '';
    
    // Check for duplicate ISBN
    $check_sql = "SELECT COUNT(*) FROM books WHERE isbn = ? AND book_id != ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$isbn, $book_id ?: 0]);
    $isbn_exists = $check_stmt->fetchColumn();
    
    if ($isbn_exists) {
        $error = "A book with this ISBN already exists in the system.";
    } else {
        if ($book_id) {
            // Update existing book
            $sql = "UPDATE books SET title=?, author=?, isbn=?, publication_year=?, category=?, total_copies=?, shelf_location=?, available_copies=available_copies + (? - total_copies) WHERE book_id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $author, $isbn, $publication_year, $category, $total_copies, $shelf_location, $total_copies, $book_id]);
        } else {
            // Insert new book
            $sql = "INSERT INTO books (title, author, isbn, publication_year, category, total_copies, available_copies, shelf_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $author, $isbn, $publication_year, $category, $total_copies, $total_copies, $shelf_location]);
        }
    }
}

// Delete book
if ($action === 'delete' && $book_id) {
    $sql = "DELETE FROM books WHERE book_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$book_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'book_id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM books WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search OR category LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get book data for editing
$edit_book = null;
if ($action === 'edit' && $book_id) {
    $sql = "SELECT * FROM books WHERE book_id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$book_id]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Calculate statistics
$stats_sql = "SELECT 
    COUNT(*) as total_books, 
    SUM(total_copies) as total_copies, 
    SUM(available_copies) as available_copies 
    FROM books";
$stats_stmt = $pdo->query($stats_sql);
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../picture/SKST.png" type="image/png" />
    <title>Books Management System - Developer View</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
            max-width: 1400px;
        }

        header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-group {
            flex: 1 0 calc(33.333% - 20px);
            margin: 0 10px 20px;
        }
        
        .form-group-full {
            flex: 1 0 calc(100% - 20px);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-primary {
            background: #3498db;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #2ecc71;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .btn-secondary {
            background: #95a5a6;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .search-sort {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        label {
            color: #2c3e50;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            cursor: pointer;
        }
        
        th:hover {
            background-color: #e9ecef;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .actions-cell {
            white-space: nowrap;
            text-align: center;
        }
        
        .actions-cell form {
            display: inline-block;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
        }
        
        .availability-high {
            color: #27ae60;
            font-weight: bold;
        }
        
        .availability-medium {
            color: #f39c12;
            font-weight: bold;
        }
        
        .availability-low {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .error-message {
            background-color: #ffe6e6;
            color: #e74c3c;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        @media (max-width: 768px) {
            .form-group {
                flex: 1 0 calc(50% - 20px);
            }
            
            .search-sort {
                flex-direction: column;
                gap: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .form-group {
                flex: 1 0 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
        
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Books Management System</h1>
            <p class="subtitle">Developer View - SKST University Library</p>
        </header>

        
        
        <!-- Statistics Section -->
        <div class="stats-container">
          <section id="Table">
            <div class="stat-card">
                <div class="stat-label">Total Books</div>
                <div class="stat-value"><?php echo $stats['total_books']; ?></div>
                <div class="stat-label">Unique Titles</div>
            </div>
            </section>
            
            <div class="stat-card">
                <div class="stat-label">Total Copies</div>
                <div class="stat-value"><?php echo $stats['total_copies']; ?></div>
                <div class="stat-label">All Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Available Copies</div>
                <div class="stat-value"><?php echo $stats['available_copies']; ?></div>
                <div class="stat-label">Ready for Checkout</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Availability Rate</div>
                <div class="stat-value">
                    <?php 
                    $rate = $stats['total_copies'] > 0 ? round(($stats['available_copies'] / $stats['total_copies']) * 100) : 0;
                    echo $rate . '%';
                    ?>
                </div>
                <div class="stat-label">of All Copies</div>
            </div>
        </div>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-success" style="margin-bottom: 10px;">+ Add Book</button>
        <button class="btn btn-secondary" onclick="window.location.href='http://localhost:8080/University-Campus-Management-System/library/librarylogin.php'">⬅ Back</button>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Hidden Book Form -->
        <div id="bookForm" class="card" style="display: none;">
            <h2 class="card-title">
                <?php echo $edit_book ? 'Edit Book Record' : 'Add New Book'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="book_id" value="<?php echo $edit_book ? $edit_book['book_id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo $edit_book ? htmlspecialchars($edit_book['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" value="<?php echo $edit_book ? htmlspecialchars($edit_book['author']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="isbn">ISBN *</label>
                        <input type="text" id="isbn" name="isbn" value="<?php echo $edit_book ? htmlspecialchars($edit_book['isbn']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="publication_year">Publication Year *</label>
                        <input type="number" id="publication_year" name="publication_year" min="1000" max="<?php echo date('Y'); ?>" value="<?php echo $edit_book ? $edit_book['publication_year'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" value="<?php echo $edit_book ? htmlspecialchars($edit_book['category']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="total_copies">Total Copies *</label>
                        <input type="number" id="total_copies" name="total_copies" min="1" value="<?php echo $edit_book ? $edit_book['total_copies'] : 1; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="shelf_location">Shelf Location</label>
                        <input type="text" id="shelf_location" name="shelf_location" value="<?php echo $edit_book ? htmlspecialchars($edit_book['shelf_location']) : ''; ?>">
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_book ? 'Update Record' : 'Add Book'; ?></button>
                    <?php if ($edit_book): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Book Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Books</label>
                        <input type="text" id="search" name="search" placeholder="Search by title, author, ISBN, or category..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                        <option value="author" <?php echo $sort === 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="publication_year" <?php echo $sort === 'publication_year' ? 'selected' : ''; ?>>Publication Year</option>
                        <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
                    </select>

                    <button class="btn btn-primary" onclick="window.location.href='?search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
                    </button>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Year</th>
                        <th>Category</th>
                        <th>Total</th>
                        <th>Available</th>
                        <th>Location</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $book): 
                            $availability_class = 'availability-high';
                            if ($book['available_copies'] == 0) {
                                $availability_class = 'availability-low';
                            } elseif ($book['available_copies'] < $book['total_copies'] / 2) {
                                $availability_class = 'availability-medium';
                            }
                        ?>
                            <tr>
                                <td><?php echo $book['book_id']; ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo $book['publication_year']; ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td><?php echo $book['total_copies']; ?></td>
                                <td class="<?php echo $availability_class; ?>"><?php echo $book['available_copies']; ?></td>
                                <td><?php echo htmlspecialchars($book['shelf_location']); ?></td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">No book records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Toggle form functionality
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("bookForm");
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        });

        // Live search functionality
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this.delay);
            this.delay = setTimeout(function() {
                this.form.submit();
            }.bind(this), 800);
        });

        // Auto-show form if editing
        <?php if ($edit_book): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('bookForm').style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>