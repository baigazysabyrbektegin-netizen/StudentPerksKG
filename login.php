<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: saved.php');
    exit;
}

$pageTitle = 'Sign in — StudentPerks KG';
$error = '';
$email = trim((string) ($_POST['email'] ?? ''));
$redirect = redirect_after_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Please try again.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $statement->execute([':email' => $email]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'The email or password is not correct.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']];
            header('Location: ' . $redirect);
            exit;
        }
    }
}

require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="auth-section">
        <div class="auth-card">
            <span class="section-kicker">WELCOME BACK</span>
            <h1>Sign in</h1>
            <p class="auth-intro">Access your saved student benefits.</p>
            <?php if ($error !== ''): ?><div class="form-errors" role="alert"><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div><?php endif; ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
                <label>Email<input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required></label>
                <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
                <button class="button button-dark" type="submit">Sign in</button>
            </form>
            <p class="auth-switch">New to StudentPerks? <a href="register.php">Create an account</a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
