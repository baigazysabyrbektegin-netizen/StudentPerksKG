<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Saved benefits — StudentPerks KG';
$activePage = 'saved';
$user = current_user();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Your form session expired. Please try again.';
    } else {
        $benefitId = filter_var($_POST['benefit_id'] ?? null, FILTER_VALIDATE_INT);
        if ($benefitId && ($_POST['action'] ?? '') === 'remove') {
            $remove = $pdo->prepare('DELETE FROM saved_benefits WHERE user_id = :user_id AND benefit_id = :benefit_id');
            $remove->execute([':user_id' => $user['id'], ':benefit_id' => $benefitId]);
            $message = 'Benefit removed from your saved list.';
        }
    }
}

$statement = $pdo->prepare(
    'SELECT b.*, c.name AS category_name
     FROM saved_benefits s
     JOIN benefits b ON b.id = s.benefit_id
     JOIN categories c ON c.id = b.category_id
     WHERE s.user_id = :user_id
     ORDER BY s.saved_at DESC'
);
$statement->execute([':user_id' => $user['id']]);
$benefits = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-intro">
        <div class="container">
            <a class="back-link page-back-link" href="index.php">← Home</a>
            <span class="section-kicker">YOUR COLLECTION</span>
            <h1>Saved <em>benefits.</em></h1>
            <p>Keep useful opportunities close and revisit them when you are ready to apply.</p>
        </div>
    </section>
    <section class="directory-section">
        <div class="container">
            <?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($benefits === []): ?>
                <div class="empty-state"><h2>Your saved list is empty</h2><p>Browse the directory and save benefits you want to explore later.</p><a class="button button-dark" href="benefits.php">Discover benefits</a></div>
            <?php else: ?>
                <div class="benefit-grid directory-grid">
                    <?php foreach ($benefits as $benefit): ?>
                        <article class="benefit-card">
                            <div class="card-topline"><span class="tool-icon tool-blue"><?= htmlspecialchars(strtoupper(substr($benefit['provider'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="benefit_id" value="<?= (int) $benefit['id'] ?>"><input type="hidden" name="action" value="remove"><button class="save-button is-saved" type="submit" aria-label="Remove <?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?>">♥</button></form></div>
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
