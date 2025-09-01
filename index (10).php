<?php
require_once 'db.php';

// Handle form submission
if ($_POST) {
    $title = $_POST['title'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $author = $_POST['author'] ?? 'CNN Staff';
    $image_url = $_POST['image_url'] ?? '';
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_breaking = isset($_POST['is_breaking']) ? 1 : 0;
    
    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    if (!empty($title) && !empty($summary) && !empty($content) && !empty($category_id)) {
        try {
            $insert_query = "INSERT INTO articles (title, slug, summary, content, image_url, category_id, author, is_featured, is_breaking) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->execute([$title, $slug, $summary, $content, $image_url, $category_id, $author, $is_featured, $is_breaking]);
            
            $success_message = "Article added successfully!";
        } catch (PDOException $e) {
            $error_message = "Error adding article: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Fetch categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();

// Fetch recent articles
$recent_query = "SELECT a.*, c.name as category_name FROM articles a 
                 LEFT JOIN categories c ON a.category_id = c.id 
                 ORDER BY a.created_at DESC LIMIT 10";
$recent_articles = $pdo->query($recent_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CNN Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .header {
            background: linear-gradient(135deg, #cc0000 0%, #990000 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }

        .main-content {
            padding: 40px 0;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .form-section {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #cc0000;
            border-bottom: 3px solid #cc0000;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #cc0000;
            box-shadow: 0 0 0 3px rgba(204, 0, 0, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .content-textarea {
            min-height: 200px;
        }

        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .submit-btn {
            background: linear-gradient(135deg, #cc0000, #990000);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(204, 0, 0, 0.3);
        }

        .recent-articles {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .article-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .article-item:hover {
            background: #f8f9fa;
        }

        .article-item:last-child {
            border-bottom: none;
        }

        .article-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .article-meta {
            font-size: 12px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .required {
            color: #cc0000;
        }

        @media (max-width: 768px) {
            .admin-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .form-section,
            .recent-articles {
                padding: 25px;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">CNN Admin Panel</div>
                <a href="javascript:void(0)" onclick="goHome()" class="back-btn">← Back to Website</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="admin-grid">
                <div class="form-section">
                    <h2 class="section-title">Add New Article</h2>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="title">Article Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title" required placeholder="Enter article title">
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category <span class="required">*</span></label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" id="author" name="author" value="CNN Staff" placeholder="Author name">
                        </div>

                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                        </div>

                        <div class="form-group">
                            <label for="summary">Article Summary <span class="required">*</span></label>
                            <textarea id="summary" name="summary" required placeholder="Enter a brief summary of the article"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="content">Article Content <span class="required">*</span></label>
                            <textarea id="content" name="content" class="content-textarea" required placeholder="Enter the full article content"></textarea>
                        </div>

                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="is_featured" name="is_featured">
                                <label for="is_featured">Featured Article</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="is_breaking" name="is_breaking">
                                <label for="is_breaking">Breaking News</label>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Publish Article</button>
                    </form>
                </div>

                <div class="recent-articles">
                    <h2 class="section-title">Recent Articles</h2>
                    <?php if (!empty($recent_articles)): ?>
                        <?php foreach($recent_articles as $article): ?>
                            <div class="article-item">
                                <div class="article-title"><?php echo htmlspecialchars($article['title']); ?></div>
                                <div class="article-meta">
                                    <span><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No articles found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                             .replace(/[^a-z0-9 -]/g, '')
                             .replace(/\s+/g, '-')
                             .replace(/-+/g, '-');
            console.log('Generated slug:', slug);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const summary = document.getElementById('summary').value.trim();
            const content = document.getElementById('content').value.trim();
            const category = document.getElementById('category_id').value;

            if (!title || !summary || !content || !category) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (title.length < 10) {
                e.preventDefault();
                alert('Title must be at least 10 characters long.');
                return false;
            }

            if (summary.length < 50) {
                e.preventDefault();
                alert('Summary must be at least 50 characters long.');
                return false;
            }

            if (content.length < 100) {
                e.preventDefault();
                alert('Content must be at least 100 characters long.');
                return false;
            }
        });

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>
