<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

$benefitId = filter_var($_POST['benefit_id'] ?? $_GET['benefit_id'] ?? null, FILTER_VALIDATE_INT);
$slug = trim((string) ($_GET['slug'] ?? $_POST['slug'] ?? ''));
if (!$benefitId && $slug !== '') {
    $lookup = $pdo->prepare('SELECT id FROM benefits WHERE slug = :slug LIMIT 1');
    $lookup->execute([':slug' => $slug]);
    $benefitId = (int) $lookup->fetchColumn();
}

if (!$benefitId) {
    http_response_code(400);
    exit('A valid benefit is required.');
}

$benefitQuery = $pdo->prepare('SELECT id, title, slug FROM benefits WHERE id = :id LIMIT 1');
$benefitQuery->execute([':id' => $benefitId]);
$benefit = $benefitQuery->fetch();
if (!$benefit) {
    http_response_code(404);
    exit('Benefit not found.');
}

$pageTitle = 'Report a benefit — StudentPerks KG';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } else {
        $reason = trim((string) ($_POST['reason'] ?? ''));
        if (mb_strlen($reason) < 10 || mb_strlen($reason) > 255) {
            $error = 'Please describe the issue in 10 to 255 characters.';
        } else {
            $insert = $pdo->prepare('INSERT INTO benefit_reports (benefit_id, user_id, reason) VALUES (:benefit_id, :user_id, :reason)');
            $insert->execute([
                ':benefit_id' => $benefitId,
                ':user_id' => current_user()['id'] ?? null,
                ':reason' => $reason,
            ]);
            header('Location: benefit.php?slug=' . urlencode($benefit['slug']) . '&reported=1');
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="auth-section">
        <div class="auth-card">
            <span class="section-kicker">HELP KEEP IT ACCURATE</span>
            <h1>Report an issue</h1>
            <p class="auth-intro">Reporting: <strong><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></strong></p>
            <?php if ($error !== ''): ?><div class="form-errors" role="alert"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div><?php endif; ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="benefit_id" value="<?= (int) $benefit['id'] ?>">
                <input type="hidden" name="slug" value="<?= htmlspecialchars($benefit['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <label>What needs checking?<textarea name="reason" rows="5" maxlength="255" required><?= htmlspecialchars((string) ($_POST['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <button class="button button-dark" type="submit">Submit report</button>
            </form>
            <p class="auth-switch"><a href="benefit.php?slug=<?= urlencode($benefit['slug']) ?>">Cancel</a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
