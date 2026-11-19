<?php
/**
 * ReFind — home page.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="site-main">
    <section class="hero">
        <h1>Welcome to ReFind</h1>
        <p>ReFind is Dong-Eui University's campus lost &amp; found platform. Report a lost or found item, browse recent listings, and help return belongings to their owners.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="listings.php">Browse Listings</a>
            <?php if (!is_logged_in()): ?>
                <a class="btn btn-secondary" href="register.php">Join ReFind</a>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
require_once __DIR__ . '/../includes/footer.php';
