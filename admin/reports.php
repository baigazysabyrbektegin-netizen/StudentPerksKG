<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require_admin();

$pageTitle = 'Review reports — StudentPerks KG';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $reportId = filter_var($_POST['report_id'] ?? null, FILTER_VALIDATE_INT);
    $reportStatus = $_POST['status'] ?? '';
    if ($reportId && in_array($reportStatus, ['reviewing', 'resolved', 'dismissed'], true)) {
        $update = $pdo->prepare('UPDATE benefit_reports SET status = :status WHERE id = :id');
        $update->execute([':status' => $reportStatus, ':id' => $reportId]);
        $message = 'Report status updated.';
    }
}
$reports = $pdo->query('SELECT r.*, b.title, u.email FROM benefit_reports r JOIN benefits b ON b.id = r.benefit_id LEFT JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC')->fetchAll();
require __DIR__ . '/includes/admin_header.php';
?>
<main id="main-content"><section class="admin-section"><div class="container"><div class="admin-heading"><div><span class="section-kicker">ADMINISTRATION</span><h1>Review reports</h1></div></div><?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Benefit</th><th>Reason</th><th>Submitted by</th><th>Status</th><th>Update</th></tr></thead><tbody><?php if ($reports === []): ?><tr><td colspan="5">No reports have been submitted.</td></tr><?php else: ?><?php foreach ($reports as $report): ?><tr><td><?= htmlspecialchars($report['title'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($report['reason'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($report['email'] ?? 'Guest', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8') ?></td><td><form method="post" class="inline-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>"><select name="status"><option value="reviewing">Reviewing</option><option value="resolved">Resolved</option><option value="dismissed">Dismissed</option></select><button class="button button-small button-dark" type="submit">Update</button></form></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div></section></main>
<?php require __DIR__ . '/includes/admin_footer.php'; ?>
