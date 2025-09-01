<?php
require_once 'db.php';

$category_slug = $_GET['category'] ?? '';

if (empty($category_slug)) {
    header('Location: index.php');
    exit;
}

// Fetch category details
$category_query = "SELECT * FROM categories WHERE slug = ?";
$stmt = $pdo->prepare($category_query);
$stmt->execute([$category_slug]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: index.php');
    exit;
}

// Fetch articles in this category
$articles_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                   FROM articles a 
                   LEFT JOIN categories c ON a.category_id = c.id 
                   WHERE c.slug = ? 
                   ORDER BY a.created_at DESC";
$stmt = $pdo->prepare($articles_query);
$stmt->execute([$category_slug]);
$articles = $stmt->fetchAll();

// Fetch all categories for navigation
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> News - CNN Clone</title>
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
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-top {
            background: #990000;
            padding: 8px 0;
            font-size: 12px;
            text-align: center;
        }

        .header-main {
            padding: 15px 0;
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
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 14px;
        }

        .nav-menu a:hover, .nav-menu a.active {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .breadcrumb {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .breadcrumb-content {
            font-size: 14px;
            color: #666;
        }

        .breadcrumb a {
            color: #cc0000;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .main-content {
            padding: 30px 0;
        }

        .category-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 0;
            background: linear-gradient(135deg, #cc0000, #990000);
            color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .category-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .category-description {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .articles-count {
            margin-top: 15px;
            font-size: 16px;
            opacity: 0.8;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .article-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .article-image {
            position: relative;
            overflow: hidden;
        }

        .article-image img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .article-card:hover .article-image img {
            transform: scale(1.05);
        }

        .article-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #cc0000;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .article-content {
            padding: 25px;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 13px;
            color: #666;
        }

        .article-author {
            font-weight: 500;
        }

        .article-date {
            color: #999;
        }

        .article-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            line-height: 1.4;
            color: #333;
        }

        .article-summary {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            color: #cc0000;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .read-more:hover {
            color: #990000;
            transform: translateX(5px);
        }

        .no-articles {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .no-articles h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 15px;
        }

        .no-articles p {
            color: #999;
            font-size: 16px;
        }

        .back-home {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background: #cc0000;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: #990000;
            transform: translateY(-2px);
        }

        .footer {
            background: #333;
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            margin-bottom: 15px;
            color: #cc0000;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: #cc0000;
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 20px;
            text-align: center;
            color: #999;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .category-title {
                font-size: 32px;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }

            .logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-top">
            <div class="container">
                <span>Breaking News • Live Updates • Latest Stories</span>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <div class="logo" onclick="goHome()">CNN</div>
                    <nav>
                        <ul class="nav-menu">
                            <li><a href="javascript:void(0)" onclick="goHome()">Home</a></li>
                            <?php foreach($categories as $cat): ?>
                                <li><a href="javascript:void(0)" onclick="goToCategory('<?php echo $cat['slug']; ?>')" 
                                   <?php echo ($cat['slug'] == $category_slug) ? 'class="active"' : ''; ?>>
                                   <?php echo $cat['name']; ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <a href="javascript:void(0)" onclick="goHome()">Home</a> > 
                <span><?php echo htmlspecialchars($category['name']); ?></span>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <div class="category-header">
                <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
                <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                <div class="articles-count"><?php echo count($articles); ?> Articles Available</div>
            </div>

            <?php if (!empty($articles)): ?>
                <div class="articles-grid">
                    <?php foreach($articles as $article): ?>
                        <article class="article-card" onclick="goToArticle('<?php echo $article['slug']; ?>')">
                            <div class="article-image">
                                <img src="<?php echo $article['image_url']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                <div class="article-badge"><?php echo $article['category_name']; ?></div>
                            </div>
                            <div class="article-content">
                                <div class="article-meta">
                                    <span class="article-author">By <?php echo htmlspecialchars($article['author']); ?></span>
                                    <span class="article-date"><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                                </div>
                                <h2 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h2>
                                <p class="article-summary"><?php echo htmlspecialchars(substr($article['summary'], 0, 150)) . '...'; ?></p>
                                <a href="javascript:void(0)" class="read-more">Read Full Article →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-articles">
                    <h3>No Articles Found</h3>
                    <p>There are currently no articles in the <?php echo htmlspecialchars($category['name']); ?> category.</p>
                    <a href="javascript:void(0)" onclick="goHome()" class="back-home">Back to Homepage</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>News Categories</h3>
                    <ul>
                        <?php foreach($categories as $cat): ?>
                        <li><a href="javascript:void(0)" onclick="goToCategory('<?php echo $cat['slug']; ?>')"><?php echo $cat['name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>About CNN</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">YouTube</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 CNN Clone. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function goHome() {
            window.location.href = 'index.php';
        }

        function goToArticle(slug) {
            window.location.href = 'article.php?slug=' + slug;
        }

        function goToCategory(slug) {
            window.location.href = 'category.php?category=' + slug;
        }

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Add scroll animations
        window.addEventListener('scroll', function() {
            const cards = document.querySelectorAll('.article-card');
            cards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                const cardVisible = 150;
                
                if (cardTop < window.innerHeight - cardVisible) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        });

        // Initialize cards with animation
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.article-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s ease';
                card.style.transitionDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>
