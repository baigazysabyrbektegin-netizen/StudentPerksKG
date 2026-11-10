<?php
$pageTitle = $pageTitle ?? 'Admin — StudentPerks KG';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="../index.php"><span class="brand-mark">SP</span><span class="brand-name">StudentPerks <b>KG</b></span></a>
        <nav class="main-nav admin-nav" aria-label="Admin navigation">
            <a href="index.php">Benefits</a>
            <a href="categories.php">Categories</a>
            <a href="reports.php">Reports</a>
            <a class="button button-small button-dark" href="../logout.php">Log out</a>
        </nav>
    </div>
</header>
