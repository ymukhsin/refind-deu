<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$page_title = 'Browse Listings - ReFind';


$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? '';

$category_id = filter_var(
    $_GET['category_id'] ?? '',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$type = in_array($type, ['lost', 'found'], true)
    ? $type
    : '';

$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();

$sql = "SELECT
            l.*,
            c.name AS category_name,
            loc.name AS location_name,
            loc.building,
            u.full_name AS reporter_name

        FROM listings l

        JOIN categories c
            ON l.category_id = c.category_id

        JOIN locations loc
            ON l.location_id = loc.location_id

        JOIN users u
            ON l.reporter_id = u.user_id

        WHERE l.status = 'open'";

$params = [];

if ($search !== '') {

    $sql .= " AND (l.title LIKE ? OR l.description LIKE ?)";

    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($type !== '') {

    $sql .= " AND l.type = ?";

    $params[] = $type;
}

if ($category_id !== false) {

    $sql .= " AND l.category_id = ?";

    $params[] = $category_id;
}

$sql .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$listings = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="listings-container">

        <div class="listings-heading">

            <h1>Browse Listings</h1>

            <?= is_logged_in()
                ? '<a href="create-listing.php" class="btn btn-primary">
                       + Report Item
                   </a>'
                : '' ?>

        </div>

        <form method="GET" action="listings.php" class="filter-form">

            <input type="text"
                   name="search"
                   placeholder="Search for an item..."
                   value="<?= sanitize($search) ?>">

            <select name="type">

                <option value="">All Types</option>

                <option value="lost"
                    <?= $type === 'lost' ? 'selected' : '' ?>>
                    Lost
                </option>

                <option value="found"
                    <?= $type === 'found' ? 'selected' : '' ?>>
                    Found
                </option>

            </select>

            <select name="category_id">

                <option value="">All Categories</option>

                <?php foreach ($categories as $category): ?>

                    <option value="<?= (int) $category['category_id'] ?>"
                        <?= $category_id === (int) $category['category_id']
                            ? 'selected' : '' ?>>

                        <?= sanitize($category['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <a href="listings.php" class="btn btn-secondary">
                Reset
            </a>

        </form>

        <p class="results-count">
            <?= count($listings) ?> listing(s) found
        </p>

        <?= empty($listings)
            ? '<p class="empty-message">No listings found.</p>'
            : '' ?>

        <!-- Lost/Found Items -->

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

                        <span class="listing-type <?= sanitize($listing['type']) ?>">
                            <?= ucfirst($listing['type']) ?>
                        </span>

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

                        <p>
                            <strong>Status:</strong>
                            <?= ucfirst(sanitize($listing['status'])) ?>
                        </p>

                        <a class="btn btn-secondary"
                           href="view-listing.php?id=<?= (int) $listing['listing_id'] ?>">
                            View Details
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>