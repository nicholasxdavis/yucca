<?php
/**
 * Dynamic Post Viewer
 * Displays user posts, stories, guides, and community posts based on slug
 */

require_once 'config.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';
$type = $_GET['type'] ?? 'community'; // community, story, guide

if (empty($slug)) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

$post = null;
$error = null;

try {
    $conn = db_connect();
    
    // Based on type, query different tables
    if ($type === 'story') {
        $stmt = $conn->prepare("SELECT * FROM stories WHERE slug = ? AND status = 'published'");
    } elseif ($type === 'guide') {
        $stmt = $conn->prepare("SELECT * FROM guides WHERE slug = ? AND status = 'published'");
    } else {
        // Community post
        $stmt = $conn->prepare("SELECT up.*, u.email as user_email FROM user_posts up JOIN users u ON up.user_id = u.id WHERE up.slug = ? AND up.status = 'published'");
    }
    
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $post = $result->fetch_assoc();
    } else {
        $error = "Post not found.";
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Post view error: " . $e->getMessage());
    $error = "Unable to load post.";
}

if ($error || !$post) {
    header('HTTP/1.0 404 Not Found');
    echo "<html><head><title>404 - Post Not Found</title></head><body style='font-family: Arial; text-align: center; padding: 50px;'><h1>404</h1><p>Post not found.</p><a href='index.php'>Go Home</a></body></html>";
    exit;
}

$page_title = htmlspecialchars($post['title']) . " - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="ui/css/styles.css">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars(substr($post['content'], 0, 160)) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(substr($post['content'], 0, 160)) ?>">
    <?php if (!empty($post['featured_image'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($post['featured_image']) ?>">
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="index.php" class="site-logo" aria-label="Yucca Club Homepage">
                <img class="logo-light" src="ui/img/logo.png" alt="Yucca Club Logo Light" style="width:180px; height:auto;">
                <img class="logo-dark" src="ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px; height:auto;">
            </a>
            <nav class="primary-nav" aria-label="Main Navigation">
                <ul>
                    <li><a href="nav/stories/index.php">Stories</a></li>
                    <li><a href="nav/guides/index.php">Guides</a></li>
                    <li><a href="nav/events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank" rel="noopener noreferrer">Shop</a></li>
                    <li><a href="nav/community/index.php">Community</a></li>
                    <li><a href="nav/membership/index.php">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if (is_logged_in()): ?>
                    <span class="desktop-only" style="font-size: 14px; font-weight: 700; color: var(--lobo-gray);"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                    <a href="?logout=true" aria-label="Logout" class="desktop-only">
                        <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="#" id="account-trigger" aria-label="Account" class="desktop-only">
                        <i class="fas fa-user" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                <button id="theme-toggle" aria-label="Toggle dark mode" class="desktop-only">
                    <i class="fas fa-moon" aria-hidden="true"></i>
                    <i class="fas fa-sun" aria-hidden="true"></i>
                </button>
                
                <!-- Mobile Menu -->
                <div class="mobile-menu">
                    <button id="mobile-menu-trigger" aria-label="Menu">
                        <i class="fas fa-ellipsis-h" aria-hidden="true"></i>
                    </button>
                    <div id="mobile-menu-dropdown" class="mobile-dropdown">
                        <?php if (is_logged_in()): ?>
                            <div class="mobile-user-info"><?= htmlspecialchars($_SESSION['user_email']) ?></div>
                            <a href="?logout=true">
                                <i class="fas fa-sign-out-alt"></i>Log Out
                            </a>
                        <?php else: ?>
                            <a href="#" id="mobile-account-trigger">
                                <i class="fas fa-user"></i>Log In
                            </a>
                        <?php endif; ?>
                        <button id="mobile-theme-toggle">
                            <i class="fas fa-moon"></i>
                            <span>Theme</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main style="max-width: 900px; margin: 3rem auto; padding: 0 2rem;">
        <article class="post-content">
            <?php if (!empty($post['featured_image'])): ?>
            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; border-radius: 8px; margin-bottom: 2rem;">
            <?php endif; ?>
            
            <p style="color: var(--yucca-yellow); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-size: 0.875rem; margin-bottom: 1rem;"><?= htmlspecialchars($post['category'] ?: 'Post') ?></p>
            
            <?php if (isset($post['user_email'])): ?>
            <p style="color: #666; font-size: 0.875rem; margin-bottom: 1.5rem;">By <?= htmlspecialchars($post['user_email']) ?> • <?= date('F j, Y', strtotime($post['created_at'])) ?></p>
            <?php else: ?>
            <p style="color: #666; font-size: 0.875rem; margin-bottom: 1.5rem;">Published on <?= date('F j, Y', strtotime($post['created_at'])) ?></p>
            <?php endif; ?>
            
            <h1 style="font-size: 3rem; font-weight: 700; margin-bottom: 1.5rem; line-height: 1.2;"><?= htmlspecialchars($post['title']) ?></h1>
            
            <?php if (!empty($post['excerpt'])): ?>
            <p style="font-size: 1.25rem; color: #666; margin-bottom: 2rem; font-style: italic;"><?= htmlspecialchars($post['excerpt']) ?></p>
            <?php endif; ?>
            
            <div style="font-size: 1.125rem; line-height: 1.8; color: #333;">
                <?php
                $rendered = false;
                $content_raw = $post['content'] ?? '';
                $contentData = json_decode($content_raw, true);
                
                // New rich format with intro and sections
                if (is_array($contentData) && (isset($contentData['intro']) || isset($contentData['sections']))) {
                    $rendered = true;
                    
                    // Render intro if exists
                    if (!empty($contentData['intro'])) {
                        echo "<p style=\"font-size:1.25rem; font-weight:500; color:var(--lobo-gray); margin-bottom:2rem;\">" . nl2br(htmlspecialchars($contentData['intro'])) . "</p>";
                    }
                    
                    // Render sections
                    if (isset($contentData['sections']) && is_array($contentData['sections'])) {
                        foreach ($contentData['sections'] as $section) {
                            $type = $section['type'] ?? '';
                            $data = $section['data'] ?? [];
                            
                            switch($type) {
                                case 'paragraph':
                                    if (!empty($data['text'])) {
                                        echo "<p style=\"margin:1rem 0;\">" . nl2br(htmlspecialchars($data['text'])) . "</p>";
                                    }
                                    break;
                                    
                                case 'heading':
                                    if (!empty($data['text'])) {
                                        echo "<h2 style=\"font-size:2rem; margin-top:2rem; margin-bottom:1rem; font-family:var(--font-serif); color:var(--lobo-gray);\">" . htmlspecialchars($data['text']) . "</h2>";
                                    }
                                    break;
                                    
                                case 'image':
                                    if (!empty($data['url'])) {
                                        $url = htmlspecialchars($data['url']);
                                        $alt = htmlspecialchars($data['alt'] ?? '');
                                        echo "<figure style=\"margin:2rem 0;\">";
                                        echo "<img src=\"$url\" alt=\"$alt\" style=\"width:100%; border-radius:12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);\">";
                                        if (!empty($alt)) {
                                            echo "<figcaption style=\"font-size:0.9rem; opacity:0.8; margin-top:0.75rem; text-align:center; color:var(--lobo-gray);\">$alt</figcaption>";
                                        }
                                        echo "</figure>";
                                    }
                                    break;
                                    
                                case 'list':
                                    if (!empty($data['text'])) {
                                        $items = explode("\n", $data['text']);
                                        echo "<ul style=\"margin:1.5rem 0; padding-left: 2rem; list-style: disc;\">";
                                        foreach ($items as $item) {
                                            if (trim($item)) {
                                                echo "<li style=\"margin:0.5rem 0;\">" . htmlspecialchars(trim($item)) . "</li>";
                                            }
                                        }
                                        echo "</ul>";
                                    }
                                    break;
                            }
                        }
                    }
                }
                
                // Fallback to plain text
                if (!$rendered) {
                    echo nl2br(htmlspecialchars($post['content']));
                }
                ?>
            </div>
        </article>
        
        <div style="margin-top: 4rem; padding-top: 2rem; border-top: 2px solid var(--darker-sand);">
            <a href="index.php" class="cta-button">← Back to Home</a>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content site-footer-main">
                <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved.</p>
                <nav class="footer-nav" aria-label="Footer Navigation">
                    <ul>
                        <li><a href="#" id="contact-trigger">Contact</a></li>
                        <li><a href="privacy_policy.php">Privacy Policy</a></li>
                    </ul>
                </nav>
            </div>
            <p class="sustainability-statement">
                Crafted with love in Las Cruces, New Mexico
            </p>
        </div>
    </footer>

    <script src="ui/js/if-then.js"></script>
    <script src="ui/js/main.js"></script>
</body>
</html>

