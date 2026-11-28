<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$page_title = 'My Listings - ReFind';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT
        l.*,
        c.name AS category_name,
        loc.name AS location_name,
        (SELECT COUNT(*) FROM claims WHERE listing_id = l.listing_id) AS claim_count

    FROM listings l

    JOIN categories c
        ON l.category_id = c.category_id

    JOIN locations loc
        ON l.location_id = loc.location_id

    WHERE l.reporter_id = ?

    ORDER BY l.created_at DESC"
);

$stmt->execute([$user_id]);

$listings = $stmt->fetchAll();

// statistics

$total = count($listings);

$lost = 0;
$found = 0;
$open = 0;

foreach ($listings as $listing) {

    $lost += $listing['type'] === 'lost' ? 1 : 0;

    $found += $listing['type'] === 'found' ? 1 : 0;

    $open += $listing['status'] === 'open' ? 1 : 0;
}

$success = match ($_GET['success'] ?? '') {
    'updated' => 'Listing updated successfully!',
    'deleted' => 'Listing deleted successfully!',
    'closed' => 'Listing closed successfully!',
    default => ''
};

$error = match ($_GET['error'] ?? '') {
    'invalid' => 'Invalid listing ID.',
    'not_found' => 'Listing not found or access denied.',
    'unavailable' => 'This listing cannot be modified.',
    'delete_failed' => 'Could not delete the listing.',
    'close_failed' => 'Could not close the listing.',
    default => ''
};

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="listings-container">

        <div class="listings-heading">

            <h1>My Listings</h1>

            <a href="create-listing.php"
               class="btn btn-primary">
                + Report Item
            </a>

        </div>

        <?= $success !== ''
            ? '<p class="success-message">' . sanitize($success) . '</p>'
            : '' ?>

        <?= $error !== ''
            ? '<p class="error-message">' . sanitize($error) . '</p>'
            : '' ?>

        <!-- Statistics -->

        <div class="stats-grid">

            <div class="stat-card">
                <h2><?= $total ?></h2>
                <p>Total Listings</p>
            </div>

            <div class="stat-card stat-lost">
                <h2><?= $lost ?></h2>
                <p>Lost Items</p>
            </div>

            <div class="stat-card stat-found">
                <h2><?= $found ?></h2>
                <p>Found Items</p>
            </div>

            <div class="stat-card stat-open">
                <h2><?= $open ?></h2>
                <p>Open Listings</p>
            </div>

        </div>

        <?= empty($listings)
            ? '<p class="empty-message">You have not reported any items yet.</p>'
            : '' ?>

        <!-- Personal listings -->

        <div class="listings-grid">

            <?php foreach ($listings as $listing): ?>

                <article class="listing-card">

                    <?php if (!empty($listing['image_path'])): ?>

                        <img class="listing-image"
                             src="<?= sanitize($listing['image_path']) ?>"
                             alt="<?= sanitize($listing['title']) ?>">

                    <?php else: ?>

                        <div class="no-image">
                            No Image
                        </div>

                    <?php endif; ?>

                    <div class="listing-content">

                        <div class="details-badges">

                            <span class="listing-type <?= sanitize($listing['type']) ?>">
                                <?= ucfirst($listing['type']) ?>
                            </span>

                            <span class="listing-status <?= sanitize($listing['status']) ?>">
                                <?= ucfirst($listing['status']) ?>
                            </span>

                        </div>

                        <h2><?= sanitize($listing['title']) ?></h2>

                        <p>
                            <strong>Category:</strong>
                            <?= sanitize($listing['category_name']) ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?= sanitize($listing['location_name']) ?>
                        </p>

                        <p>
                            <strong>Date:</strong>
                            <?= sanitize($listing['item_date']) ?>
                        </p>

                        <a href="view-listing.php?id=<?= (int) $listing['listing_id'] ?>"
                           class="btn btn-secondary">
                            View Details
                        </a>

                        <?php if ($listing['status'] === 'open'): ?>
                            <div class="manage-actions">
                                <?php if ((int) $listing['claim_count'] === 0): ?>

                                    <a href="edit-listing.php?id=<?= (int) $listing['listing_id'] ?>"
                                       class="btn btn-primary">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="delete-listing.php"
                                          onsubmit="return confirm('Delete this listing?')">

                                        <input type="hidden" name="id"
                                               value="<?= (int) $listing['listing_id'] ?>">

                                        <button type="submit" class="btn btn-danger">
                                            Delete
                                        </button>

                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="close-listing.php"
                                      onsubmit="return confirm('Close this listing? Pending claims will be rejected.')">
                                    <input type="hidden" name="id" value="<?= (int) $listing['listing_id'] ?>">
                                    <button type="submit" class="btn btn-close">Close</button>
                                </form>
                            </div>
                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>