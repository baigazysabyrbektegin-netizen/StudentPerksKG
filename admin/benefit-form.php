<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$editing = $id > 0;
$errors = [];
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$benefit = [
    'title' => '', 'provider' => '', 'description' => '', 'category_id' => '',
    'benefit_type' => 'free', 'eligibility_requirements' => '', 'kyrgyzstan_status' => 'needs_verification',
    'verification_requirements' => '', 'claiming_instructions' => '', 'official_url' => '',
    'featured' => 0, 'verification_date' => '',
];

if ($editing) {
    $load = $pdo->prepare('SELECT * FROM benefits WHERE id = :id');
    $load->execute([':id' => $id]);
    $loaded = $load->fetch();
    if (!$loaded) {
        http_response_code(404);
        exit('Benefit not found.');
    }
    $benefit = array_merge($benefit, $loaded);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $benefit = array_merge($benefit, $_POST);
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    }
    $requiredFields = ['title', 'provider', 'description', 'eligibility_requirements', 'verification_requirements', 'claiming_instructions', 'official_url'];
    foreach ($requiredFields as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }
    if (!filter_var($_POST['official_url'] ?? '', FILTER_VALIDATE_URL)) {
        $errors[] = 'Official URL must be a valid URL.';
    }
    $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$categoryId) {
        $errors[] = 'Choose a category.';
    }
    if (!in_array($_POST['benefit_type'] ?? '', ['free', 'discounted', 'premium', 'trial'], true)) {
        $errors[] = 'Choose a valid benefit type.';
    }
    if (!in_array($_POST['kyrgyzstan_status'] ?? '', ['confirmed', 'needs_verification', 'not_available'], true)) {
        $errors[] = 'Choose a valid availability status.';
    }

    if ($errors === []) {
        $title = trim((string) $_POST['title']);
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
        $values = [
            ':title' => $title, ':slug' => $slug, ':provider' => trim((string) $_POST['provider']),
            ':description' => trim((string) $_POST['description']), ':category_id' => $categoryId,
            ':benefit_type' => $_POST['benefit_type'], ':eligibility_requirements' => trim((string) $_POST['eligibility_requirements']),
            ':kyrgyzstan_status' => $_POST['kyrgyzstan_status'], ':verification_requirements' => trim((string) $_POST['verification_requirements']),
            ':claiming_instructions' => trim((string) $_POST['claiming_instructions']), ':official_url' => trim((string) $_POST['official_url']),
            ':featured' => isset($_POST['featured']) ? 1 : 0, ':verification_date' => ($_POST['verification_date'] ?? '') !== '' ? $_POST['verification_date'] : null,
        ];
        if ($editing) {
            $values[':id'] = $id;
            $save = $pdo->prepare('UPDATE benefits SET title=:title, slug=:slug, provider=:provider, description=:description, category_id=:category_id, benefit_type=:benefit_type, eligibility_requirements=:eligibility_requirements, kyrgyzstan_status=:kyrgyzstan_status, verification_requirements=:verification_requirements, claiming_instructions=:claiming_instructions, official_url=:official_url, featured=:featured, verification_date=:verification_date WHERE id=:id');
        } else {
            $save = $pdo->prepare('INSERT INTO benefits (title, slug, provider, description, category_id, benefit_type, eligibility_requirements, kyrgyzstan_status, verification_requirements, claiming_instructions, official_url, featured, verification_date) VALUES (:title, :slug, :provider, :description, :category_id, :benefit_type, :eligibility_requirements, :kyrgyzstan_status, :verification_requirements, :claiming_instructions, :official_url, :featured, :verification_date)');
        }
        try {
            $save->execute($values);
            header('Location: index.php');
            exit;
        } catch (PDOException $exception) {
            if ($exception->errorInfo[1] === 1062) {
                $errors[] = 'A benefit with this title already exists.';
            } else {
                throw $exception;
            }
        }
    }
}

$pageTitle = ($editing ? 'Edit benefit' : 'Add benefit') . ' — StudentPerks KG';
require __DIR__ . '/includes/admin_header.php';
?>
<main id="main-content">
    <section class="admin-section">
        <div class="container admin-form-container">
            <a class="back-link" href="index.php">← Back to benefits</a>
            <h1><?= $editing ? 'Edit benefit' : 'Add a benefit' ?></h1>
            <?php if ($errors !== []): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <div class="admin-form-grid"><label>Title<input name="title" value="<?= htmlspecialchars((string) $benefit['title'], ENT_QUOTES, 'UTF-8') ?>" required></label><label>Provider<input name="provider" value="<?= htmlspecialchars((string) $benefit['provider'], ENT_QUOTES, 'UTF-8') ?>" required></label></div>
                <label>Description<textarea name="description" rows="4" required><?= htmlspecialchars((string) $benefit['description'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <div class="admin-form-grid"><label>Category<select name="category_id" required><option value="">Choose category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) $benefit['category_id'] === (string) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><label>Benefit type<select name="benefit_type"><option value="free" <?= $benefit['benefit_type'] === 'free' ? 'selected' : '' ?>>Free</option><option value="discounted" <?= $benefit['benefit_type'] === 'discounted' ? 'selected' : '' ?>>Discounted</option><option value="premium" <?= $benefit['benefit_type'] === 'premium' ? 'selected' : '' ?>>Premium</option><option value="trial" <?= $benefit['benefit_type'] === 'trial' ? 'selected' : '' ?>>Free trial</option></select></label></div>
                <div class="admin-form-grid"><label>Availability<select name="kyrgyzstan_status"><option value="confirmed" <?= $benefit['kyrgyzstan_status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed in KG</option><option value="needs_verification" <?= $benefit['kyrgyzstan_status'] === 'needs_verification' ? 'selected' : '' ?>>Needs verification</option><option value="not_available" <?= $benefit['kyrgyzstan_status'] === 'not_available' ? 'selected' : '' ?>>Not available</option></select></label><label>Last verified<input type="date" name="verification_date" value="<?= htmlspecialchars((string) $benefit['verification_date'], ENT_QUOTES, 'UTF-8') ?>"></label></div>
                <label>Eligibility requirements<textarea name="eligibility_requirements" rows="3" required><?= htmlspecialchars((string) $benefit['eligibility_requirements'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <label>Student verification requirements<textarea name="verification_requirements" rows="3" required><?= htmlspecialchars((string) $benefit['verification_requirements'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <label>Claiming instructions<textarea name="claiming_instructions" rows="4" required><?= htmlspecialchars((string) $benefit['claiming_instructions'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <label>Official URL<input type="url" name="official_url" value="<?= htmlspecialchars((string) $benefit['official_url'], ENT_QUOTES, 'UTF-8') ?>" required></label>
                <label class="checkbox-label"><input type="checkbox" name="featured" <?= $benefit['featured'] ? 'checked' : '' ?>> Show this benefit as featured</label>
                <button class="button button-dark" type="submit"><?= $editing ? 'Save changes' : 'Create benefit' ?></button>
            </form>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
