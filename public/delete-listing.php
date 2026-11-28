<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my-listings.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: my-listings.php?error=invalid');
    exit;
}

try {

    // Check the listing before checking for claims.
    $stmt = $pdo->prepare(
        "SELECT image_path FROM listings
         WHERE listing_id = ? AND reporter_id = ?
         AND status = 'open'"
    );
    $stmt->execute([$id, $_SESSION['user_id']]);
    $current_listing = $stmt->fetch();

    $stmt = $pdo->prepare(
        "SELECT claim_id FROM claims WHERE listing_id = ? LIMIT 1"
    );
    $stmt->execute([$id]);
    $has_claim = $stmt->fetch();

    if (!$current_listing || $has_claim) {
        header('Location: my-listings.php?error=unavailable');
        exit;
    }

    $stmt = $pdo->prepare(
        "DELETE FROM listings
         WHERE listing_id = ? AND reporter_id = ?"
    );

    $stmt->execute([$id, $_SESSION['user_id']]);

    $deleted = $stmt->rowCount() > 0;
    if ($deleted) {
        header('Location: my-listings.php?success=deleted');
    } else {
        header('Location: my-listings.php?error=not_found');
    }

    exit;

} catch (PDOException $e) {

    error_log($e->getMessage());

    header('Location: my-listings.php?error=delete_failed');
    exit;
}
