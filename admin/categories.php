<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Manage categories — StudentPerks KG';
$error = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        if ($name === '' || $description === '' || $slug === '') {
            $error = 'Name and description are required.';
        } else {
            try {
                $insert = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)');
                $insert->execute([':name' => $name, ':slug' => $slug, ':description' => $description]);
                $message = 'Category added.';
            } catch (PDOException $exception) {
                $error = $exception->errorInfo[1] === 1062 ? 'That category already exists.' : 'The category could not be added.';
            }
        }
    }
}
$categories = $pdo->query('SELECT c.id, c.name, c.slug, c.description, COUNT(b.id) AS benefit_count FROM categories c LEFT JOIN benefits b ON b.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
require __DIR__ . '/includes/admin_header.php';
?>
<main id="main-content"><section class="admin-section"><div class="container"><div class="admin-heading"><div><span class="section-kicker">ADMINISTRATION</span><h1>Manage categories</h1></div></div><?php if ($error !== ''): ?><div class="form-errors" role="alert"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div><?php endif; ?><?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><div class="admin-split"><form method="post" class="admin-form admin-form-card"><h2>Add category</h2><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><label>Name<input name="name" required></label><label>Description<textarea name="description" rows="4" required></textarea></label><button class="button button-dark" type="submit">Add category</button></form><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Category</th><th>Benefits</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><strong><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= (int) $category['benefit_count'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></section></main>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
