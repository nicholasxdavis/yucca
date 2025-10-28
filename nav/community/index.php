<?php
// Community Page - User Posts
require_once '../../config.php';

// Check if user is logged in
$is_logged_in = is_logged_in();
$user_email = $is_logged_in ? htmlspecialchars($_SESSION['user_email']) : '';
$user_id = $_SESSION['user_id'] ?? null;

// Get user's post usage for current month
$post_usage = [
    'current' => 0,
    'limit' => 5,
    'remaining' => 5
];

if ($is_logged_in) {
    try {
        $conn = db_connect();
        $current_month = date('Y-m');
        
        if (!$conn || $conn->connect_error) { throw new Exception('DB connection error'); }
        $stmt = $conn->prepare("SELECT post_count FROM post_usage WHERE user_id = ? AND month = ?");
        $stmt->bind_param("is", $user_id, $current_month);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $post_usage['current'] = $row['post_count'];
            $post_usage['remaining'] = max(0, $post_usage['limit'] - $row['post_count']);
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Post usage error: " . $e->getMessage());
    }
}

// Get published community posts
$community_posts = [];
try {
    $conn = db_connect();
    if (!$conn || $conn->connect_error) { throw new Exception('DB connection error'); }
    $stmt = $conn->prepare("SELECT up.*, u.email as user_email FROM user_posts up JOIN users u ON up.user_id = u.id WHERE up.status = 'published' ORDER BY up.created_at DESC LIMIT 20");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $community_posts[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Community posts error: " . $e->getMessage());
}

$page_title = "Community - Yucca Club";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="icon" type="image/png" href="../../ui/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Lora:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../ui/css/styles.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a href="../../index.php" class="site-logo">
                <img class="logo-light" src="../../ui/img/logo.png" alt="Yucca Club Logo" style="width:180px;">
                <img class="logo-dark" src="../../ui/img/logo_dark.png" alt="Yucca Club Logo Dark" style="width:180px;">
            </a>
            <nav class="primary-nav">
                <ul>
                    <li><a href="../stories/index.php">Stories</a></li>
                    <li><a href="../guides/index.php">Guides</a></li>
                    <li><a href="../events/index.php">Events</a></li>
                    <li><a href="https://yucca.printify.me/" target="_blank">Shop</a></li>
                    <li><a href="index.php" class="active">Community</a></li>
                    <li><a href="../membership/index.php">Membership</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <?php if ($is_logged_in): ?>
                    <a href="../../create-post.php" id="create-post" aria-label="Create post" title="Create post" class="desktop-only">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
                <div class="mobile-menu">
                    <button id="mobile-menu-trigger" aria-label="Menu"><i class="fas fa-ellipsis-h"></i></button>
                    <div id="mobile-menu-dropdown" class="mobile-dropdown" style="display:none; position:absolute; right:0; top:100%; background: var(--desert-sand); border:1px solid rgba(0,0,0,0.1); border-radius:8px; padding:0.5rem; min-width: 180px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                        <?php if ($is_logged_in): ?>
                            <div style="padding: 0.5rem 0.75rem; font-weight:700; font-size:0.9rem; opacity:0.8;"><?= $user_email ?></div>
                            <a href="../../create-post.php" class="dropdown-item" style="display:block; padding:0.5rem 0.75rem;">New Post</a>
                            <a href="../../index.php?logout=true" class="dropdown-item" style="display:block; padding:0.5rem 0.75rem;">Log Out</a>
                        <?php else: ?>
                            <a href="../../index.php#account-modal" class="dropdown-item" style="display:block; padding:0.5rem 0.75rem;">Log In</a>
                        <?php endif; ?>
                        <button id="theme-toggle" class="dropdown-item" style="display:block; padding:0.5rem 0.75rem; background:none; border:none; text-align:left; width:100%;">
                            <i class="fas fa-moon" aria-hidden="true"></i>
                            <i class="fas fa-sun" aria-hidden="true"></i>
                            <span style="margin-left: 0.5rem;">Theme</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <h1 class="page-title">Community Posts</h1>
            
            <?php if ($is_logged_in): ?>
            <!-- User posting disabled - Staff only -->
            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 2px solid #ffc107;">
                <p style="margin: 0; font-weight: 700;">Community posts are currently available to Yucca Club staff only. Check back soon for user posting!</p>
            </div>
            <?php else: ?>
            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
                <p style="margin-bottom: 1rem;">Join our community! <a href="../../index.php#account-modal" style="color: var(--yucca-yellow); font-weight: 700;">Sign up</a> to create up to 5 posts per month.</p>
            </div>
            <?php endif; ?>
            
            <div class="community-posts">
                <?php if (count($community_posts) === 0): ?>
                <p style="text-align: center; padding: 3rem; opacity: 0.7;">No community posts yet. Be the first to share!</p>
                <?php else: ?>
                    <?php foreach ($community_posts as $post): ?>
                    <article class="post-card" style="background: var(--off-white); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <?php if ($post['featured_image']): ?>
                        <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; border-radius: 6px; margin-bottom: 1rem;">
                        <?php endif; ?>
                        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($post['title']) ?></h3>
                        <p style="color: #666; font-size: 0.875rem; margin-bottom: 1rem;">By <?= htmlspecialchars($post['user_email']) ?> • <?= date('M j, Y', strtotime($post['created_at'])) ?></p>
                        <p><?= htmlspecialchars(substr($post['content'], 0, 300)) ?>...</p>
                        <a href="../../view-post.php?slug=<?= htmlspecialchars($post['slug']) ?>&type=community" class="card-cta">Read More</a>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Yucca Club. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="../../ui/js/if-then.js"></script>
    <script src="../../ui/js/main.js"></script>
</body>
</html>

