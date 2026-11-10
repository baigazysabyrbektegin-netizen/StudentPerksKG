<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';

$pageTitle = 'About StudentPerks KG';
$activePage = 'about';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content">
    <section class="page-intro">
        <div class="container">
            <a class="back-link page-back-link" href="index.php">← Home</a>
            <span class="section-kicker">WHY STUDENTPERKS KG</span>
            <h1>Make the most of your <em>student status.</em></h1>
            <p>StudentPerks KG brings useful student opportunities together so you can spend less time searching and more time learning, building, and creating.</p>
        </div>
    </section>
    <section class="section about-section">
        <div class="container about-grid">
            <div><span class="section-kicker">OUR PURPOSE</span><h2>One clear place for the benefits students actually need.</h2></div>
            <div class="about-copy"><p>Universities provide access to more than classes. Students may be eligible for professional software, online learning, cloud credits, creative tools, discounts, and premium services. These opportunities are often scattered across provider websites and difficult to compare.</p><p>StudentPerks KG organizes them into a practical directory with eligibility details, official links, and a visible Kyrgyzstan availability status.</p></div>
        </div>
    </section>
    <section class="section about-values-section">
        <div class="container"><div class="section-heading compact-heading"><div><span class="section-kicker">OUR PRINCIPLES</span><h2>Useful, honest, student-first.</h2></div></div><div class="value-grid"><article><span class="value-number">01</span><h3>Clear availability</h3><p>We distinguish confirmed offers from opportunities that still need regional verification.</p></article><article><span class="value-number">02</span><h3>Official sources</h3><p>Benefits link back to provider pages so students can review current terms before applying.</p></article><article><span class="value-number">03</span><h3>Community upkeep</h3><p>Students can report outdated information and help keep the directory useful.</p></article></div></div>
    </section>
    <section class="cta-section"><div class="container cta-inner"><div><span class="section-kicker">READY TO EXPLORE?</span><h2>Find something useful<br><em>for your next semester.</em></h2></div><div class="cta-action"><p>Browse the current directory and save opportunities for later.</p><a class="button button-light" href="benefits.php">Explore benefits <span>→</span></a></div></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
