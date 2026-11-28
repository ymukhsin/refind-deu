<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'Listing Details - ReFind';

$error = '';
$listing = null;

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {

    $error = 'Invalid listing ID.';

} else {

    $stmt = $pdo->prepare(
        "SELECT
            l.*,
            c.name AS category_name,
            loc.name AS location_name,
            loc.building,
            u.full_name AS reporter_name,
            (SELECT COUNT(*) FROM claims WHERE listing_id = l.listing_id) AS claim_count

        FROM listings l

        JOIN categories c
            ON l.category_id = c.category_id

        JOIN locations loc
            ON l.location_id = loc.location_id

        JOIN users u
            ON l.reporter_id = u.user_id

        WHERE l.listing_id = ?"
    );

    $stmt->execute([$id]);

    $listing = $stmt->fetch();

    $error = $listing ? '' : 'Listing not found.';
}

$is_owner = $listing && is_logged_in()
    && (int) $_SESSION['user_id'] === (int) $listing['reporter_id'];

$can_claim = $listing && is_logged_in()
    && !$is_owner
    && $listing['type'] === 'found'
    && $listing['status'] === 'open';

if ($error !== '') {
    http_response_code(404);
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="details-container">

        <a href="listings.php" class="back-link">
            &larr; Back to Listings
        </a>

        <?php if ($error !== ''): ?>

            <p class="error"><?= sanitize($error) ?></p>

        <?php else: ?>

            <div class="details-card">

                <div class="details-layout">

                    <!-- Item image -->

                    <div class="details-image-container">

                        <?php if (!empty($listing['image_path'])): ?>

                            <img class="details-image"
                                 src="<?= sanitize($listing['image_path']) ?>"
                                 alt="<?= sanitize($listing['title']) ?>">

                        <?php else: ?>

                            <div class="no-image">
                                No Image
                            </div>

                        <?php endif; ?>

                    </div>

                    <!-- Item information -->

                    <div class="details-info">

                        <div class="details-badges">

                            <span class="listing-type <?= sanitize($listing['type']) ?>">
                                <?= ucfirst($listing['type']) ?>
                            </span>

                            <span class="listing-status <?= sanitize($listing['status']) ?>">
                                <?= ucfirst($listing['status']) ?>
                            </span>

                        </div>

                        <h1><?= sanitize($listing['title']) ?></h1>

                        <p>
                            <strong>Category:</strong>
                            <?= sanitize($listing['category_name']) ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?= sanitize($listing['location_name']) ?>
                        </p>

                        <p>
                            <strong>Building:</strong>
                            <?= sanitize($listing['building']) ?>
                        </p>

                        <p>
                            <strong>Item Date:</strong>
                            <?= sanitize($listing['item_date']) ?>
                        </p>

                        <p>
                            <strong>Reported By:</strong>
                            <?= sanitize($listing['reporter_name']) ?>
                        </p>

                        <p>
                            <strong>Posted On:</strong>
                            <?= date('Y-m-d', strtotime($listing['created_at'])) ?>
                        </p>

                    </div>

                </div>

                <!-- Description -->

                <div class="details-description">

                    <h2>Description</h2>

                    <p><?= nl2br(sanitize($listing['description'])) ?></p>

                </div>

                <!-- Available actions -->

                <div class="details-actions">

                    <?php if ($is_owner && $listing['status'] === 'open'): ?>

                        <?php if ((int) $listing['claim_count'] === 0): ?>

                            <a href="edit-listing.php?id=<?= (int) $listing['listing_id'] ?>"
                               class="btn btn-primary">
                                Edit Listing
                            </a>

                            <form method="POST" action="delete-listing.php"
                                  onsubmit="return confirm('Delete this listing?')">

                                <input type="hidden" name="id"
                                       value="<?= (int) $listing['listing_id'] ?>">

                                <button type="submit" class="btn btn-danger">
                                    Delete Listing
                                </button>

                            </form>
                        <?php endif; ?>

                        <form method="POST" action="close-listing.php"
                              onsubmit="return confirm('Close this listing? Pending claims will be rejected.')">
                            <input type="hidden" name="id" value="<?= (int) $listing['listing_id'] ?>">
                            <button type="submit" class="btn btn-close">Close Listing</button>
                        </form>

                    <?php elseif ($can_claim): ?>

                        <a href="create-claim.php?id=<?= (int) $listing['listing_id'] ?>"
                           class="btn btn-primary">
                            Claim This Item
                        </a>

                    <?php elseif (!is_logged_in()
                                  && $listing['type'] === 'found'
                                  && $listing['status'] === 'open'): ?>

                        <a href="login.php" class="btn btn-primary">
                            Log in to Claim
                        </a>

                    <?php elseif ($listing['status'] !== 'open'): ?>

                        <p class="unavailable-message">
                            This listing is no longer open.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>