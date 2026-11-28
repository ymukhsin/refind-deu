<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$user_id = $_SESSION['user_id'];

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
    $stmt = $pdo->prepare(
        "UPDATE listings SET status = 'closed'
         WHERE listing_id = ? AND reporter_id = ? AND status = 'open'"
    );
    $stmt->execute([$id, $user_id]);

    if ($stmt->rowCount() === 0) {
        header('Location: my-listings.php?error=unavailable');
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE claims SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW()
         WHERE listing_id = ? AND status = 'pending'"
    );
    $stmt->execute([$user_id, $id]);

    header('Location: my-listings.php?success=closed');
    exit;

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Location: my-listings.php?error=close_failed');
    exit;
}
