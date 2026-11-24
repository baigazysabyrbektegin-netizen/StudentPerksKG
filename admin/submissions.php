<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Review submissions — StudentPerks KG';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } else {
        $submissionId = filter_var($_POST['submission_id'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        $adminNote = trim((string) ($_POST['admin_note'] ?? ''));

        $load = $pdo->prepare('SELECT * FROM benefit_submissions WHERE id = :id');
        $load->execute([':id' => $submissionId]);
        $submission = $load->fetch();

        if (!$submission) {
            $error = 'Submission not found.';
        } elseif ($action === 'review') {
            $update = $pdo->prepare("UPDATE benefit_submissions SET status = 'reviewing', admin_note = :admin_note WHERE id = :id");
            $update->execute([':admin_note' => $adminNote ?: null, ':id' => $submissionId]);
            $message = 'Submission marked for review.';
        } elseif ($action === 'reject') {
            $update = $pdo->prepare("UPDATE benefit_submissions SET status = 'rejected', admin_note = :admin_note WHERE id = :id");
            $update->execute([':admin_note' => $adminNote ?: null, ':id' => $submissionId]);
            $message = 'Submission rejected.';
        } elseif ($action === 'approve' && $submission['submission_type'] === 'suggestion') {
            if (!$submission['title'] || !$submission['provider'] || !$submission['category_id'] || !$submission['official_url']) {
                $error = 'This suggestion is missing required approval fields.';
            } else {
                $baseSlug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $submission['title']), '-'));
                $slug = $baseSlug ?: 'student-benefit';
                $existing = $pdo->prepare('SELECT COUNT(*) FROM benefits WHERE slug = :slug');
                $existing->execute([':slug' => $slug]);
                if ((int) $existing->fetchColumn() > 0) {
                    $slug .= '-' . substr(sha1((string) $submission['id']), 0, 6);
                }

                $insert = $pdo->prepare(
                    'INSERT INTO benefits
                     (title, slug, provider, description, category_id, benefit_type,
                      eligibility_requirements, kyrgyzstan_status, verification_requirements,
                      claiming_instructions, official_url, featured, verification_date)
                     VALUES
                     (:title, :slug, :provider, :description, :category_id, :benefit_type,
                      :eligibility_requirements, :kyrgyzstan_status, :verification_requirements,
                      :claiming_instructions, :official_url, 0, NULL)'
                );
                $insert->execute([
                    ':title' => $submission['title'],
                    ':slug' => $slug,
                    ':provider' => $submission['provider'],
                    ':description' => $submission['description'],
                    ':category_id' => $submission['category_id'],
                    ':benefit_type' => $submission['benefit_type'],
                    ':eligibility_requirements' => 'Eligibility requirements require administrator verification.',
                    ':kyrgyzstan_status' => 'needs_verification',
                    ':verification_requirements' => 'Student verification requirements require administrator verification.',
                    ':claiming_instructions' => 'Visit the official provider website and follow the current claim instructions.',
                    ':official_url' => $submission['official_url'],
                ]);
                $update = $pdo->prepare("UPDATE benefit_submissions SET status = 'approved', admin_note = :admin_note WHERE id = :id");
                $update->execute([':admin_note' => $adminNote ?: null, ':id' => $submissionId]);
                $message = 'Suggestion approved and added to the benefits database.';
            }
        } elseif ($action === 'approve' && $submission['submission_type'] === 'complaint') {
            $update = $pdo->prepare("UPDATE benefit_submissions SET status = 'approved', admin_note = :admin_note WHERE id = :id");
            $update->execute([':admin_note' => $adminNote ?: null, ':id' => $submissionId]);
            $message = 'Complaint marked as reviewed and approved.';
        }
    }
}

$submissions = $pdo->query(
    'SELECT s.*, u.name AS student_name, u.email AS student_email, u.role AS student_role,
            c.name AS category_name, b.title AS related_benefit
     FROM benefit_submissions s
     JOIN users u ON u.id = s.user_id
     LEFT JOIN categories c ON c.id = s.category_id
     LEFT JOIN benefits b ON b.id = s.benefit_id
     ORDER BY s.created_at DESC'
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>
<main id="main-content">
    <section class="admin-section">
        <div class="container">
            <div class="admin-heading"><div><span class="section-kicker">ADMINISTRATION</span><h1>User submissions</h1></div></div>
            <?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error !== ''): ?><div class="form-errors" role="alert"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div><?php endif; ?>
            <div class="submission-review-list">
                <?php if ($submissions === []): ?>
                    <div class="empty-state"><h2>No submissions yet</h2><p>Student suggestions and complaints will appear here.</p></div>
                <?php else: ?>
                    <?php foreach ($submissions as $submission): ?>
                        <article class="submission-review-card">
                            <div class="submission-review-top">
                                <div><span class="submission-kind"><?= htmlspecialchars(ucfirst($submission['submission_type']), ENT_QUOTES, 'UTF-8') ?></span><h2><?= htmlspecialchars($submission['submission_type'] === 'suggestion' ? ($submission['title'] ?? 'Benefit suggestion') : ($submission['related_benefit'] ?? 'Benefit complaint'), ENT_QUOTES, 'UTF-8') ?></h2></div>
                                <span class="submission-status status-<?= htmlspecialchars($submission['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($submission['status']), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="submission-meta"><strong><?= htmlspecialchars($submission['student_name'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars($submission['student_role'] === 'admin' ? 'Admin' : 'Student', ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars($submission['student_email'], ENT_QUOTES, 'UTF-8') ?></span></div>
                            <?php if ($submission['submission_type'] === 'suggestion'): ?>
                                <dl class="submission-details"><div><dt>Provider</dt><dd><?= htmlspecialchars($submission['provider'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd></div><div><dt>Category</dt><dd><?= htmlspecialchars($submission['category_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></dd></div><div><dt>Type</dt><dd><?= htmlspecialchars(ucfirst($submission['benefit_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></dd></div></dl>
                                <?php if ($submission['official_url']): ?><a class="submission-link" href="<?= htmlspecialchars($submission['official_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Open official link ↗</a><?php endif; ?>
                            <?php endif; ?>
                            <p class="submission-description"><?= nl2br(htmlspecialchars($submission['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <form method="post" class="submission-actions">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="submission_id" value="<?= (int) $submission['id'] ?>">
                                <label>Admin note<textarea name="admin_note" rows="2" placeholder="Optional note"><?= htmlspecialchars((string) $submission['admin_note'], ENT_QUOTES, 'UTF-8') ?></textarea></label>
                                <div>
                                    <button class="button button-small button-dark" type="submit" name="action" value="review">Review</button>
                                    <?php if ($submission['submission_type'] === 'suggestion'): ?><button class="button button-small button-light" type="submit" name="action" value="approve">Approve as benefit</button><?php else: ?><button class="button button-small button-light" type="submit" name="action" value="approve">Approve complaint</button><?php endif; ?>
                                    <button class="button button-small danger-button" type="submit" name="action" value="reject" onclick="return confirm('Reject this submission?')">Reject</button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
