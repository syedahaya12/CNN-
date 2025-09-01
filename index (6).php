<?php
require_once 'db.php';

// Fetch featured articles
$featured_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                   FROM articles a 
                   LEFT JOIN categories c ON a.category_id = c.id 
                   WHERE a.is_featured = 1 
                   ORDER BY a.created_at DESC LIMIT 3";
$featured_articles = $pdo->query($featured_query)->fetchAll();

// Fetch breaking news
$breaking_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                   FROM articles a 
                   LEFT JOIN categories c ON a.category_id = c.id 
                   WHERE a.is_breaking = 1 
                   ORDER BY a.created_at DESC LIMIT 2";
$breaking_articles = $pdo->query($breaking_query)->fetchAll();

// Fetch latest articles by category
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();

// Fetch recent articles
$recent_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                 FROM articles a 
                 LEFT JOIN categories c ON a.category_id = c.id 
                 ORDER BY a.created_at DESC LIMIT 8";
$recent_articles = $pdo->query($recent_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNN Clone - Breaking News, Latest News and Videos</title>
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

        .nav-menu a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .breaking-banner {
            background: linear-gradient(90deg, #ff0000, #cc0000);
            color: white;
            padding: 10px 0;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        .breaking-text {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-content {
            padding: 30px 0;
        }

        .featured-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #cc0000;
            border-bottom: 3px solid #cc0000;
            padding-bottom: 10px;
        }

        .featured-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .featured-main {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .featured-main:hover {
            transform: translateY(-5px);
        }

        .featured-main img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 30px;
        }

        .featured-category {
            background: #cc0000;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 10px;
        }

        .featured-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .featured-summary {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.5;
        }

        .featured-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-article {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .sidebar-article:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .sidebar-article img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .sidebar-content {
            padding: 15px;
        }

        .sidebar-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            line-height: 1.3;
            color: #333;
        }

        .sidebar-summary {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }

        .categories-section {
            margin-bottom: 40px;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .category-card {
            background: linear-gradient(135deg, #cc0000, #990000);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #990000, #cc0000);
        }

        .category-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .latest-news {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }

        .news-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #cc0000;
        }

        .news-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .news-content {
            padding: 20px;
        }

        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .news-category {
            background: #f8f9fa;
            color: #cc0000;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .news-date {
            font-size: 12px;
            color: #666;
        }

        .news-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.4;
            color: #333;
        }

        .news-summary {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
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

            .featured-grid {
                grid-template-columns: 1fr;
            }

            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }

            .news-grid {
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
                    <div class="logo">CNN</div>
                    <nav>
                        <ul class="nav-menu">
                            <li><a href="index.php">Home</a></li>
                            <?php foreach($categories as $category): ?>
                                <li><a href="javascript:void(0)" onclick="goToCategory('<?php echo $category['slug']; ?>')"><?php echo $category['name']; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <?php if(!empty($breaking_articles)): ?>
    <div class="breaking-banner">
        <div class="container">
            <div class="breaking-text">
                🔴 BREAKING: <?php echo $breaking_articles[0]['title']; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
            <section class="featured-section">
                <h2 class="section-title">Featured Stories</h2>
                <?php if(!empty($featured_articles)): ?>
                <div class="featured-grid">
                    <article class="featured-main" onclick="goToArticle('<?php echo $featured_articles[0]['slug']; ?>')">
                        <img src="<?php echo $featured_articles[0]['image_url']; ?>" alt="<?php echo htmlspecialchars($featured_articles[0]['title']); ?>">
                        <div class="featured-overlay">
                            <span class="featured-category"><?php echo $featured_articles[0]['category_name']; ?></span>
                            <h3 class="featured-title"><?php echo htmlspecialchars($featured_articles[0]['title']); ?></h3>
                            <p class="featured-summary"><?php echo htmlspecialchars(substr($featured_articles[0]['summary'], 0, 150)) . '...'; ?></p>
                        </div>
                    </article>
                    <div class="featured-sidebar">
                        <?php for($i = 1; $i < count($featured_articles) && $i < 3; $i++): ?>
                        <article class="sidebar-article" onclick="goToArticle('<?php echo $featured_articles[$i]['slug']; ?>')">
                            <img src="<?php echo $featured_articles[$i]['image_url']; ?>" alt="<?php echo htmlspecialchars($featured_articles[$i]['title']); ?>">
                            <div class="sidebar-content">
                                <h4 class="sidebar-title"><?php echo htmlspecialchars($featured_articles[$i]['title']); ?></h4>
                                <p class="sidebar-summary"><?php echo htmlspecialchars(substr($featured_articles[$i]['summary'], 0, 100)) . '...'; ?></p>
                            </div>
                        </article>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            </section>

            <section class="categories-section">
                <h2 class="section-title">News Categories</h2>
                <div class="categories-grid">
                    <?php foreach($categories as $category): ?>
                    <a href="javascript:void(0)" onclick="goToCategory('<?php echo $category['slug']; ?>')" class="category-card">
                        <div class="category-name"><?php echo $category['name']; ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="latest-news">
                <h2 class="section-title">Latest News</h2>
                <div class="news-grid">
                    <?php foreach($recent_articles as $article): ?>
                    <article class="news-card" onclick="goToArticle('<?php echo $article['slug']; ?>')">
                        <img src="<?php echo $article['image_url']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <div class="news-content">
                            <div class="news-meta">
                                <span class="news-category"><?php echo $article['category_name']; ?></span>
                                <span class="news-date"><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                            </div>
                            <h3 class="news-title"><?php echo htmlspecialchars($article['title']); ?></h3>
                            <p class="news-summary"><?php echo htmlspecialchars(substr($article['summary'], 0, 120)) . '...'; ?></p>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>News Categories</h3>
                    <ul>
                        <?php foreach($categories as $category): ?>
                        <li><a href="javascript:void(0)" onclick="goToCategory('<?php echo $category['slug']; ?>')"><?php echo $category['name']; ?></a></li>
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
        function goToArticle(slug) {
            window.location.href = 'article.php?slug=' + slug;
        }

        function goToCategory(slug) {
            window.location.href = 'category.php?category=' + slug;
        }

        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
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
