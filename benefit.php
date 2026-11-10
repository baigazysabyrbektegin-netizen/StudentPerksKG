<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$statement = $pdo->prepare(
    'SELECT b.*, c.name AS category_name
     FROM benefits b
     JOIN categories c ON c.id = b.category_id
     WHERE b.slug = :slug
     LIMIT 1'
);
$statement->execute([':slug' => $slug]);
$benefit = $statement->fetch();

if (!$benefit) {
    http_response_code(404);
    $pageTitle = 'Benefit not found — StudentPerks KG';
    require __DIR__ . '/includes/header.php';
    ?>
    <main id="main-content">
        <section class="empty-state page-empty">
            <div class="container">
                <h1>Benefit not found</h1>
                <p>This opportunity may have been removed or the link may be incorrect.</p>
                <a class="button button-dark" href="benefits.php">Back to benefits</a>
            </div>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $benefit['title'] . ' — StudentPerks KG';
$activePage = 'discover';
$saveMessage = '';
if (isset($_GET['reported'])) {
    $saveMessage = 'Thanks. Your report has been sent to the StudentPerks team.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $saveMessage = 'Your form session expired. Please try again.';
    } else {
        $saveAction = $_POST['save_action'] ?? '';
        if ($saveAction === 'save') {
            $save = $pdo->prepare('INSERT IGNORE INTO saved_benefits (user_id, benefit_id) VALUES (:user_id, :benefit_id)');
            $save->execute([':user_id' => current_user()['id'], ':benefit_id' => $benefit['id']]);
            $saveMessage = 'Benefit saved to your collection.';
        } elseif ($saveAction === 'remove') {
            $remove = $pdo->prepare('DELETE FROM saved_benefits WHERE user_id = :user_id AND benefit_id = :benefit_id');
            $remove->execute([':user_id' => current_user()['id'], ':benefit_id' => $benefit['id']]);
            $saveMessage = 'Benefit removed from your collection.';
        }
    }
}
$statusLabels = [
    'confirmed' => ['Confirmed in Kyrgyzstan', 'availability-confirmed'],
    'needs_verification' => ['Needs verification', 'availability-review'],
    'not_available' => ['Not currently available', 'availability-unavailable'],
];
$availability = $statusLabels[$benefit['kyrgyzstan_status']] ?? $statusLabels['needs_verification'];
$isSaved = false;
if (is_logged_in()) {
    $savedCheck = $pdo->prepare('SELECT 1 FROM saved_benefits WHERE user_id = :user_id AND benefit_id = :benefit_id');
    $savedCheck->execute([':user_id' => current_user()['id'], ':benefit_id' => $benefit['id']]);
    $isSaved = (bool) $savedCheck->fetchColumn();
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="detail-section">
        <div class="container">
            <a class="back-link" href="benefits.php">← Back to all benefits</a>
            <div class="detail-layout">
                <article class="detail-card">
                    <div class="detail-card-top"><span class="tool-icon tool-blue"><?= htmlspecialchars(strtoupper(substr($benefit['provider'], 0, 2)), ENT_QUOTES, 'UTF-8') ?></span><?php if (is_logged_in()): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="save_action" value="<?= $isSaved ? 'remove' : 'save' ?>"><button class="save-button <?= $isSaved ? 'is-saved' : '' ?>" type="submit" aria-label="<?= $isSaved ? 'Remove this benefit from saved benefits' : 'Save this benefit' ?>"><?= $isSaved ? '♥' : '♡' ?></button></form><?php else: ?><a class="save-button" href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI'] ?? 'benefit.php') ?>" aria-label="Sign in to save this benefit">♡</a><?php endif; ?></div>
                    <p class="card-provider"><?= htmlspecialchars($benefit['provider'], ENT_QUOTES, 'UTF-8') ?></p>
                    <h1><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <div class="detail-tags"><span class="tag tag-green"><?= htmlspecialchars(ucfirst($benefit['benefit_type']), ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars($benefit['category_name'], ENT_QUOTES, 'UTF-8') ?></span></div>
                    <p class="detail-description"><?= htmlspecialchars($benefit['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="button button-dark detail-claim" href="<?= htmlspecialchars($benefit['official_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Visit official website <span>↗</span></a>
                </article>
                <aside class="detail-aside">
                    <div class="verification-panel"><?php if ($saveMessage !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($saveMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><span class="section-kicker">AVAILABILITY IN KG</span><div class="availability <?= htmlspecialchars($availability[1], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($availability[0], ENT_QUOTES, 'UTF-8') ?></div><p>We do not assume an offer is available in Kyrgyzstan until its regional terms have been checked.</p></div>
                    <div class="detail-info"><h2>Eligibility</h2><p><?= nl2br(htmlspecialchars($benefit['eligibility_requirements'], ENT_QUOTES, 'UTF-8')) ?></p><h2>Student verification</h2><p><?= nl2br(htmlspecialchars($benefit['verification_requirements'], ENT_QUOTES, 'UTF-8')) ?></p><h2>How to claim</h2><p><?= nl2br(htmlspecialchars($benefit['claiming_instructions'], ENT_QUOTES, 'UTF-8')) ?></p></div>
                    <div class="report-panel"><strong>Something looks outdated?</strong><p>Help us keep this directory accurate by reporting an issue.</p><a href="report.php?slug=<?= urlencode($benefit['slug']) ?>">Report an issue</a></div>
                </aside>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
