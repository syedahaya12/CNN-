<?php
require_once 'db.php';

$article_slug = $_GET['slug'] ?? '';

if (empty($article_slug)) {
    header('Location: index.php');
    exit;
}

// Fetch article details
$article_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                  FROM articles a 
                  LEFT JOIN categories c ON a.category_id = c.id 
                  WHERE a.slug = ?";
$stmt = $pdo->prepare($article_query);
$stmt->execute([$article_slug]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit;
}

// Update view count
$update_views = "UPDATE articles SET views = views + 1 WHERE id = ?";
$stmt = $pdo->prepare($update_views);
$stmt->execute([$article['id']]);

// Fetch related articles
$related_query = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                  FROM articles a 
                  LEFT JOIN categories c ON a.category_id = c.id 
                  WHERE a.category_id = ? AND a.id != ? 
                  ORDER BY a.created_at DESC LIMIT 4";
$stmt = $pdo->prepare($related_query);
$stmt->execute([$article['category_id'], $article['id']]);
$related_articles = $stmt->fetchAll();

// Fetch all categories for navigation
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories = $pdo->query($categories_query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - CNN Clone</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($article['summary'], 0, 160)); ?>">
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

        .article-container {
            max-width: 800px;
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

        .nav-menu a:hover {
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
            padding: 40px 0;
        }

        .article-header {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .article-category {
            display: inline-block;
            background: #cc0000;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .article-category:hover {
            background: #990000;
            transform: translateY(-2px);
        }

        .article-title {
            font-size: 42px;
            font-weight: bold;
            line-height: 1.2;
            margin-bottom: 20px;
            color: #333;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin-bottom: 30px;
        }

        .article-author-date {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .article-author {
            font-weight: 600;
            color: #333;
        }

        .article-date {
            color: #666;
            font-size: 14px;
        }

        .article-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #666;
            font-size: 14px;
        }

        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .article-summary {
            font-size: 20px;
            font-weight: 500;
            color: #555;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-left: 4px solid #cc0000;
            border-radius: 0 10px 10px 0;
        }

        .article-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .article-text {
            font-size: 18px;
            line-height: 1.8;
            color: #444;
        }

        .article-text p {
            margin-bottom: 20px;
        }

        .article-text h2 {
            font-size: 28px;
            margin: 30px 0 20px;
            color: #333;
        }

        .article-text h3 {
            font-size: 24px;
            margin: 25px 0 15px;
            color: #333;
        }

        .article-text ul {
            margin: 20px 0;
            padding-left: 30px;
        }

        .article-text li {
            margin-bottom: 10px;
        }

        .article-text blockquote {
            background: #f8f9fa;
            border-left: 4px solid #cc0000;
            padding: 20px;
            margin: 25px 0;
            font-style: italic;
            border-radius: 0 10px 10px 0;
        }

        .social-share {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            text-align: center;
        }

        .social-share h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .share-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .share-facebook { background: #3b5998; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-whatsapp { background: #25d366; }

        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .related-articles {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .related-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .related-card {
            border: 1px solid #eee;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #cc0000;
        }

        .related-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .related-content {
            padding: 20px;
        }

        .related-card-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            line-height: 1.4;
            color: #333;
        }

        .related-card-summary {
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

            .article-title {
                font-size: 28px;
            }

            .article-header, .article-content {
                padding: 25px;
            }

            .article-meta {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .share-buttons {
                justify-content: center;
            }

            .related-grid {
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
                            <?php foreach($categories as $category): ?>
                                <li><a href="javascript:void(0)" onclick="goToCategory('<?php echo $category['slug']; ?>')"><?php echo $category['name']; ?></a></li>
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
                <a href="javascript:void(0)" onclick="goToCategory('<?php echo $article['category_slug']; ?>')"><?php echo htmlspecialchars($article['category_name']); ?></a> > 
                <span><?php echo htmlspecialchars($article['title']); ?></span>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="article-container">
            <article class="article-header">
                <a href="javascript:void(0)" onclick="goToCategory('<?php echo $article['category_slug']; ?>')" class="article-category">
                    <?php echo htmlspecialchars($article['category_name']); ?>
                </a>
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-meta">
                    <div class="article-author-date">
                        <span class="article-author">By <?php echo htmlspecialchars($article['author']); ?></span>
                        <span class="article-date"><?php echo date('F j, Y \a\t g:i A', strtotime($article['created_at'])); ?></span>
                    </div>
                    <div class="article-stats">
                        <span>👁️ <?php echo number_format($article['views']); ?> views</span>
                    </div>
                </div>
            </article>

            <img src="<?php echo $article['image_url']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-image">

            <div class="article-summary">
                <?php echo htmlspecialchars($article['summary']); ?>
            </div>

            <div class="article-content">
                <div class="article-text">
                    <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                </div>
            </div>

            <div class="social-share">
                <h3>Share This Article</h3>
                <div class="share-buttons">
                    <a href="#" class="share-btn share-facebook" onclick="shareOnFacebook()">Facebook</a>
                    <a href="#" class="share-btn share-twitter" onclick="shareOnTwitter()">Twitter</a>
                    <a href="#" class="share-btn share-linkedin" onclick="shareOnLinkedIn()">LinkedIn</a>
                    <a href="#" class="share-btn share-whatsapp" onclick="shareOnWhatsApp()">WhatsApp</a>
                </div>
            </div>

            <?php if (!empty($related_articles)): ?>
            <div class="related-articles">
                <h2 class="related-title">Related Articles</h2>
                <div class="related-grid">
                    <?php foreach($related_articles as $related): ?>
                    <div class="related-card" onclick="goToArticle('<?php echo $related['slug']; ?>')">
                        <img src="<?php echo $related['image_url']; ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                        <div class="related-content">
                            <h3 class="related-card-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p class="related-card-summary"><?php echo htmlspecialchars(substr($related['summary'], 0, 100)) . '...'; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
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
        function goHome() {
            window.location.href = 'index.php';
        }

        function goToArticle(slug) {
            window.location.href = 'article.php?slug=' + slug;
        }

        function goToCategory(slug) {
            window.location.href = 'category.php?category=' + slug;
        }

        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${title}`, '_blank', 'width=600,height=400');
        }

        function shareOnLinkedIn() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank', 'width=600,height=400');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            window.open(`https://wa.me/?text=${title} ${url}`, '_blank');
        }

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Add scroll progress indicator
        window.addEventListener('scroll', function() {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            
            if (!document.getElementById('progress-bar')) {
                const progressBar = document.createElement('div');
                progressBar.id = 'progress-bar';
                progressBar.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: ${scrolled}%;
                    height: 4px;
                    background: linear-gradient(90deg, #cc0000, #990000);
                    z-index: 9999;
                    transition: width 0.3s ease;
                `;
                document.body.appendChild(progressBar);
            } else {
                document.getElementById('progress-bar').style.width = scrolled + '%';
            }
        });
    </script>
</body>
</html>
