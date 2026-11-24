<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}
require dirname(__DIR__) . '/config/database.php';
$query = $pdo->query("SELECT r.id, r.ends_on, b.title, b.provider, u.email, u.name FROM user_registrations r JOIN benefits b ON b.id = r.benefit_id JOIN users u ON u.id = r.user_id WHERE u.expiry_email_notifications = 1 AND r.reminder_sent_at IS NULL AND r.ends_on = DATE_ADD(CURDATE(), INTERVAL 1 DAY)");
$mark = $pdo->prepare('UPDATE user_registrations SET reminder_sent_at = NOW() WHERE id = :id AND reminder_sent_at IS NULL');
$sent = 0;
foreach ($query->fetchAll() as $row) {
    $subject = 'StudentPerks reminder: ' . $row['title'] . ' expires tomorrow';
    $message = "Hi {$row['name']},\n\nYour {$row['provider']} benefit, {$row['title']}, is scheduled to end tomorrow ({$row['ends_on']}).\n\nReview your schedule: " . (getenv('APP_URL') ?: 'http://studentperks-kg.test') . "/schedule.php\n\nStudentPerks KG";
    $headers = "From: " . (getenv('MAIL_FROM') ?: 'no-reply@studentperks-kg.local');
    if (mail($row['email'], $subject, $message, $headers)) {
        $mark->execute([':id' => $row['id']]);
        $sent++;
    }
}
echo "Sent {$sent} expiry reminder(s).\n";
