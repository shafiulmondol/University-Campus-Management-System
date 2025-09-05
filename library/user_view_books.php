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

// Search and sort parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'title';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM books WHERE title LIKE :search OR author LIKE :search OR isbn LIKE :search OR category LIKE :search ORDER BY $sort $order";
$params = ['search' => "%$search%"];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Book Catalog - SKST University Library</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #2b5876, #4e4376);
            color: white;
            padding: 30px 0;
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            color: maroon;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
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
            flex-wrap: wrap;
        }
        
        label {
            color: #2c3e50;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
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
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
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
        
        .btn-primary {
            background: #3498db;
        }
        
        .btn-primary:hover {
            background: #2980b9;
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
        
        .no-books {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .no-books i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
            color: #bdc3c7;
        }
        
        @media (max-width: 768px) {
            .search-sort {
                flex-direction: column;
                gap: 15px;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            .stats-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .sort-options {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SKST University Library</h1>
            <p class="subtitle">Book Catalog - Public Access</p>
        </header>
        
        <!-- Statistics Section -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-label">Total Books</div>
                <div class="stat-value"><?php echo $stats['total_books']; ?></div>
                <div class="stat-label">Unique Titles</div>
            </div>
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
        
        <div class="card">
            <h2 class="card-title">Browse Our Collection</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Books</label>
                        <input type="text" id="search" name="search" placeholder="Search by title, author, ISBN, or category..." value="<?php echo htmlspecialchars($search); ?>">
                    </form>
                </div>
                
                <div class="sort-options">
                    <div>
                        <label for="sort">Sort by:</label>
                        <select id="sort" onchange="applySort()">
                            <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                            <option value="author" <?php echo $sort === 'author' ? 'selected' : ''; ?>>Author</option>
                            <option value="publication_year" <?php echo $sort === 'publication_year' ? 'selected' : ''; ?>>Publication Year</option>
                            <option value="category" <?php echo $sort === 'category' ? 'selected' : ''; ?>>Category</option>
                        </select>
                    </div>

                    <button class="btn btn-primary" style="margin-top: 28px;" onclick="toggleSortOrder()">
                        <?php echo $order === 'ASC' ? 'Asc' : 'Desc'; ?>
                    </button>
                </div>
            </div>
            
            <?php if (count($books) > 0): ?>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): 
                        $availability_class = 'availability-high';
                        if ($book['available_copies'] == 0) {
                            $availability_class = 'availability-low';
                        } elseif ($book['available_copies'] < $book['total_copies'] / 2) {
                            $availability_class = 'availability-medium';
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="book-cover">
                                    <?php echo $book['book_id']; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><?php echo $book['publication_year']; ?></td>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                            <td><?php echo $book['total_copies']; ?></td>
                            <td class="<?php echo $availability_class; ?>"><?php echo $book['available_copies']; ?></td>
                            <td><?php echo htmlspecialchars($book['shelf_location']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php else: ?>
                <div class="no-books">
                    <i>📚</i>
                    <h3>No books found</h3>
                    <p>Try adjusting your search criteria</p>
                </div>
            <?php endif; ?>
        </div>
        <footer style="text-align: center; color: #7f8c8d; margin-top: -20px;">
            <p>SKST University Library &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>

    <script>
        // Live search functionality
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this.delay);
            this.delay = setTimeout(function() {
                this.form.submit();
            }.bind(this), 800);
        });
        
        // Apply sort
        function applySort() {
            var search = '<?php echo urlencode($search); ?>';
            var sort = document.getElementById('sort').value;
            var order = '<?php echo $order; ?>';
            
            var url = '?search=' + search + 
                     '&sort=' + sort + 
                     '&order=' + order;
            
            window.location.href = url;
        }
        
        // Toggle sort order
        function toggleSortOrder() {
            var search = '<?php echo urlencode($search); ?>';
            var sort = '<?php echo $sort; ?>';
            var order = '<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>';
            
            var url = '?search=' + search + 
                     '&sort=' + sort + 
                     '&order=' + order;
            
            window.location.href = url;
        }
    </script>
</body>
</html>