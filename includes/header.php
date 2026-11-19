<?php
/**
 * ReFind — shared page header.
 *
 * Include after any page setup. An optional `$page_title` variable may
 * be set before including this file to override the default title.
 */

require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'ReFind — Dong-Eui University';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($page_title); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a class="brand" href="index.php">ReFind</a>
            <div class="nav-links">
                <?php if (is_logged_in()): $user = current_user(); ?>
                    <span class="user-name"><?php echo sanitize($user['full_name'] ?? 'User'); ?></span>
                    <a href="my-listings.php">My Listings</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
