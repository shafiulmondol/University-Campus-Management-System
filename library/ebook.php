<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "skst_university";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sorting and searching
$sort_column = $_GET['sort'] ?? 'id';
$search_term = $_GET['search'] ?? '';
$allowed_sort = ['id', 'book_name', 'title', 'author', 'publish_year'];
if (!in_array($sort_column, $allowed_sort)) $sort_column = 'id';

// Build query with search
$query = "SELECT * FROM ebook";
if (!empty($search_term)) {
    $search_term = $conn->real_escape_string($search_term);
    $query .= " WHERE book_name LIKE '%$search_term%' OR title LIKE '%$search_term%' OR author LIKE '%$search_term%' OR publish_year LIKE '%$search_term%' OR id LIKE '%$search_term%'";
}
$query .= " ORDER BY $sort_column ASC";

$books = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../picture/SKST.png" type="image/png" />
<title>E-Book Library Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        color: #333;
        line-height: 1.6;
        min-height: 100vh;
    }
    
    .container {
        margin: 0 auto;
        padding: 20px;
    }
    
    header {
        background: linear-gradient(135deg, #2b5876, #4e4376);
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .logo i {
        font-size: 2.5rem;
    }
    
    .logo h1 {
        font-size: 1.8rem;
    }
    
    nav a {
        color: white;
        text-decoration: none;
        margin: 0 15px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    nav a:hover {
        color: #ffd700;
    }
    
    .search-container {
        display: flex;
        justify-content: left;
        margin-bottom: 20px;
        gap: 10px;
    }
    
    .search-box {
        display: flex;
        width: 100%;
        
    }
    
    .search-box input {
        flex: 1;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px 0 0 5px;
        font-size: 16px;
    }
    
    .search-box button {
        padding: 12px 20px;
        background: #800000;
        color: white;
        border: none;
        border-radius: 0 5px 5px 0;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .search-box button:hover {
        background: #a52a2a;
    }
    
    .refresh-btn {
        padding: 12px 20px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: background 0.3s ease;
        text-decoration: none;
    }
    
    .refresh-btn:hover {
        background: #218838;
         transform: translateY(-2px);
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-card i {
        font-size: 2.5rem;
        color: #800000;
        margin-bottom: 15px;
    }
    
    .stat-card h3 {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #333;
    }
    
    .stat-card p {
        color: #666;
    }
    
    .book-table {
        width: 100%;
        border-collapse: collapse;
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 30px;
    }
    
    .book-table th, .book-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .book-table th {
        background: maroon;
        color: white;
        cursor: pointer;
        font-weight: 600;
        position: relative;
    }
    
    .book-table th:hover {
        background-color: #a52a2a;
    }
    
    .book-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .book-table tr:hover {
        background-color: #f1f1f1;
    }
    
    .download-btn {
        display: inline-block;
        padding: 8px 15px;
        background: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .download-btn:hover {
        background: #218838;
        transform: translateY(-2px);
    }
    
    .view-btn {
        display: inline-block;
        padding: 8px 15px;
        background: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-right: 10px;
    }
    
    .view-btn:hover {
        background: #138496;
        transform: translateY(-2px);
    }
    
    .action-btns {
        display: flex;
        gap: 10px;
    }
    
    footer {
        text-align: center;
        color: #666;
    }
    
    .no-books {
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .no-books i {
        font-size: 3rem;
        color: #ccc;
        margin-bottom: 15px;
    }
    
    .no-books p {
        font-size: 1.2rem;
        color: #666;
    }
    
    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        
        nav {
            margin-top: 15px;
        }
        
        .search-container {
            flex-direction: column;
        }
        
        .search-box {
            max-width: 100%;
        }
        
        .refresh-btn {
            width: 100%;
            justify-content: center;
        }
        
        .book-table {
            display: block;
            overflow-x: auto;
        }
        
        .action-btns {
            flex-direction: column;
        }
    }
</style>
</head>
<body>
<div class="container">
    <header>
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <h1>SKST University E-Library</h1>
            </div>
            <nav>
                <a href="../index.html"><i class="fas fa-home"></i> Home</a>
            </nav>
        </div>
    </header>

    <div class="stats-container">
        <div class="stat-card">
            <i class="fas fa-book"></i>
            <h3><?php echo $books->num_rows; ?></h3>
            <p>Total Books</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-alt"></i>
            <h3>2025</h3>
            <p>Since Year</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <h3>5,000+</h3>
            <p>Active Users</p>
        </div>
        <div class="stat-card">
            <i class="fas fa-download"></i>
            <h3>12,345</h3>
            <p>Monthly Downloads</p>
        </div>
    </div>

    <div class="search-container">
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search books..." value="<?php echo htmlspecialchars($search_term); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
        </form>
        <a href="?" class="refresh-btn"><i class="fas fa-sync-alt"></i> Refresh</a>
    </div>

    <?php if ($books->num_rows > 0): ?>
    <table class="book-table">
        <thead>
            <tr>
                <th onclick="sortTable('id')">Book ID <i class="fas fa-sort"></i></th>
                <th onclick="sortTable('book_name')">Book Name <i class="fas fa-sort"></i></th>
                <th onclick="sortTable('title')">Title <i class="fas fa-sort"></i></th>
                <th onclick="sortTable('author')">Author <i class="fas fa-sort"></i></th>
                <th onclick="sortTable('publish_year')">Year <i class="fas fa-sort"></i></th>
                <th style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $books->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['book_name']) ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><?= $row['publish_year'] ?></td>
                <td class="action-btns">
                    <a href="<?= $row['link'] ?>" target="_blank" class="view-btn"><i class="fas fa-external-link-alt"></i> View</a>
                    <a href="<?= $row['link'] ?>" download class="download-btn"><i class="fas fa-download"></i> Download</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-books">
        <i class="fas fa-book-open"></i>
        <p>No books found. Please try a different search term.</p>
    </div>
    <?php endif; ?>

    <footer>
        <p style="padding: 0px; margin-top: -10px;">&copy; 2025 SKST University E-Library Management System. All rights reserved.</p>
    </footer>
</div>

<script>
function sortTable(column) {
    const url = new URL(window.location);
    url.searchParams.set('sort', column);
    window.location = url.toString();
}
</script>
</body>
</html>
<?php $conn->close(); ?>