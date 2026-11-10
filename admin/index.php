<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Manage benefits — StudentPerks KG';
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $benefitId = filter_var($_POST['benefit_id'] ?? null, FILTER_VALIDATE_INT);
        if ($benefitId) {
            $delete = $pdo->prepare('DELETE FROM benefits WHERE id = :id');
            $delete->execute([':id' => $benefitId]);
            $message = 'Benefit deleted.';
        }
    } elseif (($_POST['action'] ?? '') === 'feature') {
        $benefitId = filter_var($_POST['benefit_id'] ?? null, FILTER_VALIDATE_INT);
        if ($benefitId) {
            $feature = $pdo->prepare('UPDATE benefits SET featured = NOT featured WHERE id = :id');
            $feature->execute([':id' => $benefitId]);
            $message = 'Featured status updated.';
        }
    }
}

$benefits = $pdo->query(
    'SELECT b.id, b.title, b.provider, b.featured, b.kyrgyzstan_status, b.updated_at, c.name AS category_name
     FROM benefits b JOIN categories c ON c.id = b.category_id ORDER BY b.updated_at DESC'
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>
<main id="main-content">
    <section class="admin-section">
        <div class="container">
            <div class="admin-heading"><div><span class="section-kicker">ADMINISTRATION</span><h1>Manage benefits</h1></div><a class="button button-dark" href="benefit-form.php">Add benefit <span>+</span></a></div>
            <?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($errors !== []): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Benefit</th><th>Category</th><th>Availability</th><th>Featured</th><th>Actions</th></tr></thead><tbody><?php foreach ($benefits as $benefit): ?><tr><td><strong><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($benefit['provider'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars($benefit['category_name'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="availability <?= $benefit['kyrgyzstan_status'] === 'confirmed' ? 'availability-confirmed' : 'availability-review' ?>"><?= htmlspecialchars($benefit['kyrgyzstan_status'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= $benefit['featured'] ? 'Yes' : 'No' ?></td><td class="admin-actions"><a href="benefit-form.php?id=<?= (int) $benefit['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this benefit?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="benefit_id" value="<?= (int) $benefit['id'] ?>"><button name="action" value="feature" type="submit">Feature</button><button name="action" value="delete" type="submit" class="danger-action">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
