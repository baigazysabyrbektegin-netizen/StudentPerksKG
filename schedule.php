<?php
declare(strict_types=1);
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'My schedule — StudentPerks KG';
$activePage = 'schedule';
$user = current_user();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $message = 'Your form session expired. Please try again.';
    } else {
        $enabled = isset($_POST['expiry_email_notifications']) ? 1 : 0;
        $update = $pdo->prepare('UPDATE users SET expiry_email_notifications = :enabled WHERE id = :id');
        $update->execute([':enabled' => $enabled, ':id' => $user['id']]);
        $message = $enabled ? 'Expiry email reminders are on.' : 'Expiry email reminders are off.';
    }
}

$pref = $pdo->prepare('SELECT expiry_email_notifications FROM users WHERE id = :id');
$pref->execute([':id' => $user['id']]);
$notificationsEnabled = (bool) $pref->fetchColumn();
$monthParam = (string) ($_GET['month'] ?? date('Y-m'));
$month = DateTimeImmutable::createFromFormat('!Y-m-d', $monthParam . '-01');
if (!$month || $month->format('Y-m') !== $monthParam) {
    $month = new DateTimeImmutable('first day of this month');
}
$monthStart = $month->modify('first day of this month');
$gridStart = $monthStart->modify('-' . ((int) $monthStart->format('N') - 1) . ' days');
$gridEnd = $monthStart->modify('last day of this month');
$gridEnd = $gridEnd->modify('+' . (7 - (int) $gridEnd->format('N')) . ' days');
$today = new DateTimeImmutable('today');
$items = $pdo->prepare('SELECT b.title, b.provider, b.slug, b.benefit_type, r.started_on, r.ends_on FROM user_registrations r JOIN benefits b ON b.id = r.benefit_id WHERE r.user_id = :id ORDER BY r.started_on, b.title');
$items->execute([':id' => $user['id']]);
$registrations = $items->fetchAll();
$calendarEvents = [];
foreach ($registrations as $item) {
    $startKey = $item['started_on'];
    $calendarEvents[$startKey][] = ['kind' => 'start', 'item' => $item];
    if ($item['ends_on'] !== null) {
        $calendarEvents[$item['ends_on']][] = ['kind' => 'expiry', 'item' => $item];
    }
}
$previousMonth = $monthStart->modify('-1 month')->format('Y-m');
$nextMonth = $monthStart->modify('+1 month')->format('Y-m');

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-intro"><div class="container"><span class="section-kicker">YOUR STUDENT DASHBOARD</span><h1>My <em>schedule.</em></h1><p>See when your registered courses, events, and subscriptions begin and expire.</p></div></section>
    <section class="directory-section"><div class="container schedule-page">
        <?php if ($message !== ''): ?><div class="notice" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" class="registration-form reminder-settings"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><div><h2>Email expiry reminders</h2><p>Get one email 24 hours before an item with an end date expires.</p></div><label class="checkbox-label"><input type="checkbox" name="expiry_email_notifications" value="1" <?= $notificationsEnabled ? 'checked' : '' ?>> Send reminders to my email</label><button class="button button-dark" type="submit">Save preference</button></form>

        <section class="calendar" aria-label="Monthly schedule calendar">
            <div class="calendar-toolbar"><div><span class="section-kicker">YOUR CALENDAR</span><h2><?= htmlspecialchars($monthStart->format('F Y'), ENT_QUOTES, 'UTF-8') ?></h2></div><nav class="calendar-nav" aria-label="Calendar month navigation"><a class="button button-light" href="?month=<?= htmlspecialchars($previousMonth, ENT_QUOTES, 'UTF-8') ?>" aria-label="Previous month">←</a><a class="button button-light" href="schedule.php">Today</a><a class="button button-light" href="?month=<?= htmlspecialchars($nextMonth, ENT_QUOTES, 'UTF-8') ?>" aria-label="Next month">→</a></nav></div>
            <?php if ($registrations === []): ?><div class="empty-state calendar-empty"><h2>Your schedule is empty</h2><p>Open a benefit, course, or event and add it to your schedule with start and end dates.</p><a class="button button-dark" href="benefits.php">Discover opportunities</a></div><?php else: ?>
            <div class="calendar-grid"><div class="calendar-weekdays"><?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $weekday): ?><div><?= htmlspecialchars($weekday, ENT_QUOTES, 'UTF-8') ?></div><?php endforeach; ?></div><div class="calendar-days">
                <?php for ($day = $gridStart; $day <= $gridEnd; $day = $day->modify('+1 day')): $key = $day->format('Y-m-d'); $inMonth = $day->format('Y-m') === $monthStart->format('Y-m'); $isToday = $day->format('Y-m-d') === $today->format('Y-m-d'); ?>
                    <div class="calendar-day <?= $inMonth ? '' : 'is-outside' ?> <?= $isToday ? 'is-today' : '' ?>"><span class="calendar-day-number"><?= $day->format('j') ?><?= $isToday ? '<span class="today-label">Today</span>' : '' ?></span><div class="calendar-day-events">
                        <?php foreach ($calendarEvents[$key] ?? [] as $event): $item = $event['item']; $isExpiry = $event['kind'] === 'expiry'; ?>
                            <a class="calendar-event <?= $isExpiry ? 'event-expiry' : 'event-start' ?>" href="benefit.php?slug=<?= urlencode($item['slug']) ?>" title="<?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?> — <?= $isExpiry ? 'expires' : 'starts' ?>"><span class="event-kind"><?= $isExpiry ? 'EXPIRES' : 'STARTS' ?></span><span class="event-title"><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></span></a>
                        <?php endforeach; ?>
                    </div></div>
                <?php endfor; ?>
            </div></div>
            <?php endif; ?>
            <div class="calendar-legend"><span><i class="legend-dot legend-start"></i> Registration / start</span><span><i class="legend-dot legend-expiry"></i> Expiry day</span></div>
        </section>
    </div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
