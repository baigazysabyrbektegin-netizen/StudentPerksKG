<?php
$pageTitle = $pageTitle ?? 'StudentPerks KG';
$activePage = $activePage ?? '';
$currentUser = function_exists('current_user') ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudentPerks KG helps university students in Kyrgyzstan discover verified software, discounts, courses, and premium student benefits.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main-content">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="StudentPerks KG home">
            <span class="brand-mark">SP</span>
            <span class="brand-name">StudentPerks <b>KG</b></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="sr-only">Toggle navigation</span>
            <span></span><span></span><span></span>
        </button>
        <nav id="primary-navigation" class="main-nav" aria-label="Primary navigation">
            <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">Home</a>
            <a class="<?= $activePage === 'discover' ? 'active' : '' ?>" href="benefits.php">Discover</a>
            <a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="about.php">About us</a>
            <a class="nav-saved" href="saved.php"><span class="heart-icon" aria-hidden="true">♡</span> Saved</a>
            <?php if ($currentUser): ?>
                <a href="schedule.php">My schedule</a>
                <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                    <a href="admin/index.php" class="nav-admin">Admin panel</a>
                <?php endif; ?>
                <a href="submit.php" class="nav-submit">Submit</a>
                <div class="user-menu" aria-label="Current user">
                    <span class="user-name"><?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="user-role"><?= $currentUser['role'] === 'admin' ? 'Admin' : 'Student' ?></span>
                </div>
                <a class="button button-small button-dark" href="logout.php">Log out</a>
            <?php else: ?>
                <div class="auth-actions">
                    <a class="auth-login <?= $activePage === 'login' ? 'active' : '' ?>" href="login.php">Log in</a>
                    <a class="button button-small button-dark <?= $activePage === 'register' ? 'active' : '' ?>" href="register.php">Sign up</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
