<?php
$pageTitle = 'StudentPerks KG — Student benefits, all in one place';
$activePage = 'home';
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$featuredStatement = $pdo->query(
    "SELECT b.*, c.name AS category_name
     FROM benefits b JOIN categories c ON c.id = b.category_id
     WHERE b.featured = 1 ORDER BY b.updated_at DESC LIMIT 3"
);
$featuredBenefits = $featuredStatement->fetchAll();

$categoryStatement = $pdo->query(
    'SELECT c.name, c.slug, COUNT(b.id) AS benefit_count
     FROM categories c LEFT JOIN benefits b ON b.category_id = c.id
     GROUP BY c.id ORDER BY c.name'
);
$categories = $categoryStatement->fetchAll();

$recentStatement = $pdo->query(
    'SELECT b.*, c.name AS category_name
     FROM benefits b JOIN categories c ON c.id = b.category_id
     ORDER BY b.created_at DESC, b.title ASC LIMIT 4'
);
$recentBenefits = $recentStatement->fetchAll();
$totalBenefits = (int) $pdo->query('SELECT COUNT(*) FROM benefits')->fetchColumn();

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-content">
                <div class="eyebrow"><span class="eyebrow-dot"></span> Built for students in Kyrgyzstan</div>
                <h1>More benefits.<br><em>Less searching.</em></h1>
                <p class="hero-lead">Discover free software, student discounts, courses, and premium subscriptions you can access with your student status.</p>
                <form class="hero-search" action="benefits.php" method="get" role="search">
                    <label class="sr-only" for="hero-search-input">Search student benefits</label>
                    <span class="search-icon" aria-hidden="true"></span>
                    <input id="hero-search-input" type="search" name="q" placeholder="Search benefits, tools, courses..." autocomplete="off">
                    <button class="button button-dark" type="submit">Search</button>
                </form>
                <div class="hero-note"><span class="check-icon" aria-hidden="true">✓</span> Curated and reviewed for students</div>
            </div>
            <div class="hero-visual" aria-label="A preview of benefits available on StudentPerks KG">
                <div class="visual-orbit orbit-one"></div>
                <div class="visual-orbit orbit-two"></div>
                <div class="visual-card visual-card-main">
                    <div class="visual-card-top"><span class="mini-label">TRENDING NOW</span><span class="spark">✦</span></div>
                    <div class="visual-tool-icon tool-github">GH</div>
                    <h2>GitHub Student<br>Developer Pack</h2>
                    <p>Professional tools, free for students.</p>
                    <div class="visual-card-footer"><span class="status-pill">Free</span><span class="arrow-circle">↗</span></div>
                </div>
                <div class="floating-card floating-card-top"><span class="floating-icon">✦</span><div><strong><?= $totalBenefits ?></strong><small>curated benefits</small></div></div>
                <div class="floating-card floating-card-bottom"><span class="floating-check">✓</span><div><strong>Verified</strong><small>official sources</small></div></div>
            </div>
        </div>
    </section>

    <section class="section featured-section">
        <div class="container">
            <div class="section-heading">
                <div><span class="section-kicker">START HERE</span><h2>Popular right now</h2></div>
                <a class="text-link" href="benefits.php">View all benefits <span>→</span></a>
            </div>
            <div class="benefit-grid">
                <?php foreach ($featuredBenefits as $benefit): ?>
                    <article class="benefit-card">
                        <div class="card-topline"><span class="tool-icon tool-blue"><?= htmlspecialchars(strtoupper(substr($benefit['provider'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><a class="save-button" href="benefit.php?slug=<?= urlencode($benefit['slug']) ?>" aria-label="View <?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?>">↗</a></div>
                        <p class="card-provider"><?= htmlspecialchars($benefit['provider'], ENT_QUOTES, 'UTF-8') ?></p>
                        <h3><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <p class="card-description"><?= htmlspecialchars($benefit['description'], ENT_QUOTES, 'UTF-8') ?></p>
                        <div class="card-meta"><span class="tag tag-green"><?= htmlspecialchars(ucfirst($benefit['benefit_type']), ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars($benefit['category_name'], ENT_QUOTES, 'UTF-8') ?></span><?= $benefit['kyrgyzstan_status'] === 'confirmed' ? '<span class="verified" title="Confirmed in Kyrgyzstan">✓</span>' : '<span class="unverified">Needs review</span>' ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section categories-section">
        <div class="container">
            <div class="section-heading compact-heading"><div><span class="section-kicker">FIND YOUR THING</span><h2>Browse by category</h2></div></div>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="category-card" href="benefits.php?category=<?= urlencode($category['slug']) ?>">
                        <span class="category-icon">✦</span>
                        <span class="category-name"><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="category-count"><?= (int) $category['benefit_count'] ?> <?= (int) $category['benefit_count'] === 1 ? 'benefit' : 'benefits' ?></span>
                        <span class="category-arrow">↗</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section recent-section">
        <div class="container">
            <div class="section-heading"><div><span class="section-kicker">JUST ADDED</span><h2>Fresh opportunities</h2></div><a class="text-link" href="benefits.php?sort=newest">See what's new <span>→</span></a></div>
            <div class="recent-list">
                <?php foreach ($recentBenefits as $benefit): ?>
                    <a class="recent-item" href="benefit.php?slug=<?= urlencode($benefit['slug']) ?>">
                        <span class="tool-icon tool-blue"><?= htmlspecialchars(strtoupper(substr($benefit['provider'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="recent-main"><strong><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($benefit['provider'], ENT_QUOTES, 'UTF-8') ?></small></span>
                        <span class="recent-category"><?= htmlspecialchars($benefit['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="tag <?= $benefit['benefit_type'] === 'discounted' ? 'tag-yellow' : 'tag-green' ?>"><?= htmlspecialchars(ucfirst($benefit['benefit_type']), ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="recent-arrow">→</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container cta-inner">
            <div><span class="section-kicker">STAY IN THE LOOP</span><h2>Don’t miss out on<br><em>your next perk.</em></h2></div>
            <div class="cta-action"><p>Join students who are getting more from their education.</p><a class="button button-light" href="register.php">Create a free account <span>→</span></a></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
