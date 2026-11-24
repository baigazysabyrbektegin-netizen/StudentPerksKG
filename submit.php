<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Submit feedback — StudentPerks KG';
$activePage = 'submit';
$user = current_user();
$errors = [];
$success = '';
$submissionType = $_POST['submission_type'] ?? 'suggestion';
$title = trim((string) ($_POST['title'] ?? ''));
$provider = trim((string) ($_POST['provider'] ?? ''));
$categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
$benefitType = $_POST['benefit_type'] ?? 'free';
$benefitId = filter_var($_POST['benefit_id'] ?? null, FILTER_VALIDATE_INT);
$description = trim((string) ($_POST['description'] ?? ''));
$officialUrl = trim((string) ($_POST['official_url'] ?? ''));

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$benefits = $pdo->query('SELECT id, title FROM benefits ORDER BY title')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    }
    if (!in_array($submissionType, ['suggestion', 'complaint'], true)) {
        $errors[] = 'Choose whether this is a suggestion or complaint.';
    }
    if (mb_strlen($description) < 10 || mb_strlen($description) > 2000) {
        $errors[] = 'Describe the feedback in 10 to 2000 characters.';
    }

    if ($submissionType === 'suggestion') {
        if (mb_strlen($title) < 3 || mb_strlen($title) > 180) {
            $errors[] = 'Enter an offer title between 3 and 180 characters.';
        }
        if (mb_strlen($provider) < 2 || mb_strlen($provider) > 120) {
            $errors[] = 'Enter the provider name.';
        }
        if (!$categoryId) {
            $errors[] = 'Choose a category.';
        }
        if (!in_array($benefitType, ['free', 'discounted', 'premium', 'trial', 'event'], true)) {
            $errors[] = 'Choose a valid benefit type.';
        }
        if (!filter_var($officialUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Enter a valid official website link.';
        }
    } elseif (!$benefitId) {
        $errors[] = 'Choose the benefit this complaint concerns.';
    }

    if ($errors === []) {
        $insert = $pdo->prepare(
            'INSERT INTO benefit_submissions
             (user_id, submission_type, title, provider, category_id, benefit_type, benefit_id, description, official_url)
             VALUES (:user_id, :submission_type, :title, :provider, :category_id, :benefit_type, :benefit_id, :description, :official_url)'
        );
        $insert->execute([
            ':user_id' => $user['id'],
            ':submission_type' => $submissionType,
            ':title' => $submissionType === 'suggestion' ? $title : null,
            ':provider' => $submissionType === 'suggestion' ? $provider : null,
            ':category_id' => $submissionType === 'suggestion' ? $categoryId : null,
            ':benefit_type' => $submissionType === 'suggestion' ? $benefitType : null,
            ':benefit_id' => $submissionType === 'complaint' ? $benefitId : null,
            ':description' => $description,
            ':official_url' => $submissionType === 'suggestion' ? $officialUrl : null,
        ]);
        $success = 'Your submission was sent to the StudentPerks admin team.';
        $title = $provider = $officialUrl = $description = '';
        $categoryId = $benefitId = null;
    }
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-intro">
        <div class="container">
            <a class="back-link page-back-link" href="index.php">← Home</a>
            <span class="section-kicker">COMMUNITY FEEDBACK</span>
            <h1>Submit a <em>suggestion or complaint.</em></h1>
            <p>Help improve the directory. All submissions are reviewed by an administrator before a benefit is published.</p>
        </div>
    </section>
    <section class="directory-section">
        <div class="container submission-layout">
            <form method="post" class="submission-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($errors !== []): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
                <?php if ($success !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <fieldset class="submission-type">
                    <legend>What would you like to send?</legend>
                    <label><input type="radio" name="submission_type" value="suggestion" <?= $submissionType === 'suggestion' ? 'checked' : '' ?>> <strong>Suggest a benefit</strong><small>Add a new student offer</small></label>
                    <label><input type="radio" name="submission_type" value="complaint" <?= $submissionType === 'complaint' ? 'checked' : '' ?>> <strong>Submit a complaint</strong><small>Report an issue or incorrect listing</small></label>
                </fieldset>
                <div class="suggestion-fields">
                    <div class="admin-form-grid">
                        <label>Benefit title<input name="title" maxlength="180" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>" placeholder="Example: Canva for Education"></label>
                        <label>Provider<input name="provider" maxlength="120" value="<?= htmlspecialchars($provider, ENT_QUOTES, 'UTF-8') ?>" placeholder="Example: Canva"></label>
                    </div>
                    <div class="admin-form-grid">
                        <label>Category<select name="category_id"><option value="">Choose category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) $categoryId === (string) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                        <label>Benefit type<select name="benefit_type"><option value="free" <?= $benefitType === 'free' ? 'selected' : '' ?>>Free</option><option value="discounted" <?= $benefitType === 'discounted' ? 'selected' : '' ?>>Discounted</option><option value="premium" <?= $benefitType === 'premium' ? 'selected' : '' ?>>Premium</option><option value="trial" <?= $benefitType === 'trial' ? 'selected' : '' ?>>Free trial</option><option value="event" <?= $benefitType === 'event' ? 'selected' : '' ?>>Event</option></select></label>
                    </div>
                    <label>Official link<input type="url" name="official_url" maxlength="500" value="<?= htmlspecialchars($officialUrl, ENT_QUOTES, 'UTF-8') ?>" placeholder="https://official-provider.example/student-offer"></label>
                </div>
                <div class="complaint-fields">
                    <label>Which benefit is involved?<select name="benefit_id"><option value="">Choose benefit</option><?php foreach ($benefits as $benefit): ?><option value="<?= (int) $benefit['id'] ?>" <?= (string) $benefitId === (string) $benefit['id'] ? 'selected' : '' ?>><?= htmlspecialchars($benefit['title'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                </div>
                <label>Description<textarea name="description" rows="7" maxlength="2000" required placeholder="Explain why this benefit should be added, or describe the problem in detail."><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea></label>
                <button class="button button-dark" type="submit">Send to administrators</button>
            </form>
            <aside class="submission-guide"><span class="section-kicker">SUGGESTION TEMPLATE</span><h2>Include these details</h2><ul><li>Benefit title and provider</li><li>The correct category</li><li>Whether it is free, discounted, premium, or a trial</li><li>An official provider link</li><li>A short explanation</li></ul><p>StudentPerks administrators will verify availability before publishing a submitted benefit.</p></aside>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
