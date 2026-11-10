<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: saved.php');
    exit;
}

$pageTitle = 'Create account — StudentPerks KG';
$errors = [];
$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Please try again.';
    }
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Enter a name between 2 and 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    $password = (string) ($_POST['password'] ?? '');
    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters.';
    }
    if (!isset($_POST['terms'])) {
        $errors[] = 'Please confirm that you understand this is a student directory.';
    }

    if ($errors === []) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
            $insert->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $pdo->lastInsertId(), 'name' => $name, 'email' => $email, 'role' => 'student'];
            header('Location: saved.php');
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="auth-section">
        <div class="auth-card">
            <span class="section-kicker">JOIN STUDENTPERKS</span>
            <h1>Create your account</h1>
            <p class="auth-intro">Save useful opportunities and come back to them when you are ready.</p>
            <?php if ($errors !== []): ?><div class="form-errors" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <label>Name<input type="text" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" required></label>
                <label>Email<input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required></label>
                <label>Password<input type="password" name="password" autocomplete="new-password" minlength="8" required><small>Use at least 8 characters.</small></label>
                <label class="checkbox-label"><input type="checkbox" name="terms" required> I understand that offers must be checked against official sources.</label>
                <button class="button button-dark" type="submit">Create account</button>
            </form>
            <p class="auth-switch">Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
