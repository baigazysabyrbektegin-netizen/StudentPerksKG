<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Discover benefits — StudentPerks KG';
$activePage = 'discover';
$query = trim((string) ($_GET['q'] ?? ''));
$categorySlug = trim((string) ($_GET['category'] ?? ''));
$type = trim((string) ($_GET['type'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$sort = ($_GET['sort'] ?? 'newest') === 'alphabetical' ? 'alphabetical' : 'newest';

$categoryStatement = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name');
$categories = $categoryStatement->fetchAll();

$where = [];
$parameters = [];
if ($query !== '') {
    $where[] = '(b.title LIKE :title_search OR b.provider LIKE :provider_search OR b.description LIKE :description_search)';
    $searchTerm = '%' . $query . '%';
    $parameters[':title_search'] = $searchTerm;
    $parameters[':provider_search'] = $searchTerm;
    $parameters[':description_search'] = $searchTerm;
}
if ($categorySlug !== '') {
    $where[] = 'c.slug = :category';
    $parameters[':category'] = $categorySlug;
}
if (in_array($type, ['free', 'discounted', 'premium', 'trial'], true)) {
    $where[] = 'b.benefit_type = :type';
    $parameters[':type'] = $type;
}
if (in_array($status, ['confirmed', 'needs_verification', 'not_available'], true)) {
    $where[] = 'b.kyrgyzstan_status = :status';
    $parameters[':status'] = $status;
}

$orderBy = $sort === 'alphabetical' ? 'b.title ASC' : 'b.created_at DESC, b.title ASC';
$sql = 'SELECT b.*, c.name AS category_name FROM benefits b JOIN categories c ON c.id = b.category_id';
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY ' . $orderBy;
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$benefits = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-intro">
        <div class="container">
            <a class="back-link page-back-link" href="index.php">← Home</a>
            <span class="section-kicker">THE DIRECTORY</span>
            <h1>Discover your next <em>student perk.</em></h1>
            <p>Explore opportunities curated for students. Availability is shown clearly so you know what still needs checking.</p>
        </div>
    </section>
    <section class="directory-section">
        <div class="container">
            <form class="filters" method="get" action="benefits.php">
                <label class="filter-search"><span class="search-icon" aria-hidden="true"></span><span class="sr-only">Search benefits</span><input type="search" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search benefits or providers"></label>
                <label><span class="sr-only">Category</span><select name="category"><option value="">All categories</option><?php foreach ($categories as $category): ?><option value="<?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= $categorySlug === $category['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                <label><span class="sr-only">Benefit type</span><select name="type"><option value="">All types</option><option value="free" <?= $type === 'free' ? 'selected' : '' ?>>Free</option><option value="discounted" <?= $type === 'discounted' ? 'selected' : '' ?>>Discounted</option><option value="premium" <?= $type === 'premium' ? 'selected' : '' ?>>Premium</option><option value="trial" <?= $type === 'trial' ? 'selected' : '' ?>>Free trial</option></select></label>
                <label><span class="sr-only">Availability</span><select name="status"><option value="">Any availability</option><option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed in KG</option><option value="needs_verification" <?= $status === 'needs_verification' ? 'selected' : '' ?>>Needs verification</option><option value="not_available" <?= $status === 'not_available' ? 'selected' : '' ?>>Not available</option></select></label>
                <label><span class="sr-only">Sort</span><select name="sort"><option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest first</option><option value="alphabetical" <?= $sort === 'alphabetical' ? 'selected' : '' ?>>Alphabetical</option></select></label>
                <button class="button button-dark" type="submit">Apply filters</button>
            </form>
            <div class="directory-heading"><div><span class="section-kicker">CURATED OPPORTUNITIES</span><h2><?= count($benefits) ?> benefits found</h2></div></div>
            <?php if ($benefits === []): ?>
                <div class="empty-state"><h2>No benefits match those filters</h2><p>Try a broader search or clear one of the filters.</p><a class="button button-dark" href="benefits.php">Clear filters</a></div>
            <?php else: ?>
                <div class="benefit-grid directory-grid">
                    <?php foreach ($benefits as $benefit): ?>
                        <article class="benefit-card">
                            <div class="card-topline"><span class="tool-icon tool-blue"><?= htmlspecialchars(strtoupper(substr($benefit['provider'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><span class="availability <?= $benefit['kyrgyzstan_status'] === 'confirmed' ? 'availability-confirmed' : 'availability-review' ?>"><?= $benefit['kyrgyzstan_status'] === 'confirmed' ? 'Confirmed' : 'Needs review' ?></span></div>
                            <p class="card-provider"><?= htmlspecialchars($benefit['provider'], ENT_QUOTES, 'UTF-8') ?></p>
                            <h3><a href="benefit.php?slug=<?= urlencode($benefit['slug']) ?>"><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></a></h3>
                            <p class="card-description"><?= htmlspecialchars($benefit['description'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="card-meta"><span class="tag tag-green"><?= htmlspecialchars(ucfirst($benefit['benefit_type']), ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars($benefit['category_name'], ENT_QUOTES, 'UTF-8') ?></span></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
