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
$ebook_id = $_POST['ebook_id'] ?? '';

// Add or Update ebook
if ($action === 'save') {
    $book_name = $_POST['book_name'] ?? '';
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $publish_year = $_POST['publish_year'] ?? '';
    $link = $_POST['link'] ?? '';
    
    if ($ebook_id) {
        // Update existing ebook
        $sql = "UPDATE ebook SET book_name=?, title=?, author=?, publish_year=?, link=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$book_name, $title, $author, $publish_year, $link, $ebook_id]);
    } else {
        // Insert new ebook
        $sql = "INSERT INTO ebook (book_name, title, author, publish_year, link) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$book_name, $title, $author, $publish_year, $link]);
    }
}

// Delete ebook
if ($action === 'delete' && $ebook_id) {
    $sql = "DELETE FROM ebook WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ebook_id]);
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'id';
$order = $_GET['order'] ?? 'ASC';

// Build query with search and sort
$sql = "SELECT * FROM ebook WHERE book_name LIKE :search OR title LIKE :search OR author LIKE :search ORDER BY $sort $order";
$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$ebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get ebook data for editing
$edit_ebook = null;
if ($action === 'edit' && $ebook_id) {
    $sql = "SELECT * FROM ebook WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ebook_id]);
    $edit_ebook = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle view ebook link
$view_ebook = null;
if (isset($_GET['view_ebook']) && $_GET['view_ebook']) {
    $sql = "SELECT * FROM ebook WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['view_ebook']]);
    $view_ebook = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ebook Management System - Developer View</title>
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
            background: linear-gradient(135deg, #2c3e50, #1a2530);
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
        
        .link-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .link-cell a {
            color: #3498db;
            text-decoration: none;
        }
        
        .link-cell a:hover {
            text-decoration: underline;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 700px;
            border-radius: 8px;
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .ebook-view {
            padding: 20px;
        }
        
        .ebook-view h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .ebook-view p {
            margin-bottom: 10px;
        }
        
        .ebook-view a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .ebook-view a:hover {
            background: #2980b9;
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
            <h1>Ebook Management System</h1>
            <p class="subtitle">Developer View - SKST University</p>
        </header>
        
        <!-- Trigger button -->
        <button id="toggleFormBtn" class="btn btn-success" style="margin-bottom: 10px;">+ Add Ebook</button>
        <button class="btn btn-secondary" onclick="history.back()">⬅ Back</button>

        <!-- Hidden Ebook Form -->
        <div id="ebookForm" class="card" style="display: none;">
            <h2 class="card-title">
                <?php echo $edit_ebook ? 'Edit Ebook Record' : 'Add New Ebook'; ?>
            </h2>
            <form method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="ebook_id" value="<?php echo $edit_ebook ? $edit_ebook['id'] : ''; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="book_name">Book Name *</label>
                        <input type="text" id="book_name" name="book_name" value="<?php echo $edit_ebook ? htmlspecialchars($edit_ebook['book_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo $edit_ebook ? htmlspecialchars($edit_ebook['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" value="<?php echo $edit_ebook ? htmlspecialchars($edit_ebook['author']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="publish_year">Publish Year *</label>
                        <input type="number" id="publish_year" name="publish_year" min="1000" max="<?php echo date('Y'); ?>" value="<?php echo $edit_ebook ? $edit_ebook['publish_year'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="link">Link *</label>
                        <input type="url" id="link" name="link" value="<?php echo $edit_ebook ? htmlspecialchars($edit_ebook['link']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success"><?php echo $edit_ebook ? 'Update Record' : 'Add Ebook'; ?></button>
                    <?php if ($edit_ebook): ?>
                        <a href="?" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Ebook Records</h2>
            
            <div class="search-sort">
                <div class="search-box">
                    <form method="GET">
                        <label for="search">Search Ebooks</label>
                        <input type="text" id="search" name="search" placeholder="Search by book name, title, or author..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" style="display:none;">Search</button>
                    </form>
                </div>
                
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select id="sort" onchange="window.location.href='?search=<?php echo urlencode($search); ?>&sort='+this.value+'&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>'">
                        <option value="book_name" <?php echo $sort === 'book_name' ? 'selected' : ''; ?>>Book Name</option>
                        <option value="author" <?php echo $sort === 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="publish_year" <?php echo $sort === 'publish_year' ? 'selected' : ''; ?>>Publish Year</option>
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
                        <th>Book Name</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Year</th>
                        <th style="text-align:center">Link</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ebooks) > 0): ?>
                        <?php foreach ($ebooks as $ebook): ?>
                            <tr>
                                <td><?php echo $ebook['id']; ?></td>
                                <td><?php echo htmlspecialchars($ebook['book_name']); ?></td>
                                <td><?php echo htmlspecialchars($ebook['title']); ?></td>
                                <td><?php echo htmlspecialchars($ebook['author']); ?></td>
                                <td><?php echo $ebook['publish_year']; ?></td>
                                <td class="link-cell">
                                    <a href="<?php echo htmlspecialchars($ebook['link']); ?>" target="_blank" title="<?php echo htmlspecialchars($ebook['link']); ?>">
                                        View eBook
                                    </a>
                                </td>
                                <td class="actions-cell">
                                    <form method="POST">
                                        <input type="hidden" name="ebook_id" value="<?php echo $ebook['id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ebook?');">
                                        <input type="hidden" name="ebook_id" value="<?php echo $ebook['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                    <button class="btn btn-info" onclick="viewEbook(<?php echo $ebook['id']; ?>)">View</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No ebook records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ebook View Modal -->
    <div id="ebookModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="ebookDetails" class="ebook-view"></div>
        </div>
    </div>

    <script>
        // Toggle form functionality
        document.getElementById("toggleFormBtn").addEventListener("click", function() {
            var form = document.getElementById("ebookForm");
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

        // View ebook details
        function viewEbook(ebookId) {
            fetch('?view_ebook=' + ebookId)
                .then(response => response.text())
                .then(data => {
                    // This would normally be done with AJAX, but for simplicity we'll redirect
                    window.location.href = '?view_ebook=' + ebookId;
                })
                .catch(error => console.error('Error:', error));
        }

        // Show modal if view_ebook parameter is set
        <?php if ($view_ebook): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('ebookModal');
            var detailsDiv = document.getElementById('ebookDetails');
            
            detailsDiv.innerHTML = `
                <h3><?php echo htmlspecialchars($view_ebook['book_name']); ?></h3>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($view_ebook['title']); ?></p>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($view_ebook['author']); ?></p>
                <p><strong>Publish Year:</strong> <?php echo $view_ebook['publish_year']; ?></p>
                <p><strong>Link:</strong> <a href="<?php echo htmlspecialchars($view_ebook['link']); ?>" target="_blank"><?php echo htmlspecialchars($view_ebook['link']); ?></a></p>
                <a href="<?php echo htmlspecialchars($view_ebook['link']); ?>" target="_blank" class="btn btn-primary">Read eBook</a>
            `;
            
            modal.style.display = 'block';
        });
        <?php endif; ?>

        function closeModal() {
            document.getElementById('ebookModal').style.display = 'none';
            // Remove view_ebook parameter from URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Close modal when clicking outside the content
        window.onclick = function(event) {
            var modal = document.getElementById('ebookModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>