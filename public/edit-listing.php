<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$page_title = 'Edit Listing - ReFind';

$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: my-listings.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM listings
     WHERE listing_id = ? AND reporter_id = ?
     AND status = 'open'
     AND NOT EXISTS (SELECT 1 FROM claims WHERE claims.listing_id = listings.listing_id)"
);

$stmt->execute([$id, $_SESSION['user_id']]);

$listing = $stmt->fetch();

if (!$listing) {
    header('Location: my-listings.php?error=unavailable');
    exit;
}

$type = $listing['type'];
$title = $listing['title'];
$category_id = (int) $listing['category_id'];
$location_id = (int) $listing['location_id'];
$description = $listing['description'];
$item_date = $listing['item_date'];

$old_image = $listing['image_path'];
$image_path = $old_image;

$categories = $pdo->query(
    "SELECT * FROM categories ORDER BY name"
)->fetchAll();

$locations = $pdo->query(
    "SELECT * FROM locations ORDER BY name"
)->fetchAll();

$category_ids = array_column($categories, 'category_id');
$location_ids = array_column($locations, 'location_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type = (string) filter_input(INPUT_POST, 'type');
    $title = trim((string) filter_input(INPUT_POST, 'title'));
    $description = trim((string) filter_input(INPUT_POST, 'description'));
    $item_date = (string) filter_input(INPUT_POST, 'item_date');

    $category_id = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );

    $location_id = filter_input(
        INPUT_POST,
        'location_id',
        FILTER_VALIDATE_INT
    );

    $valid_date = str_contains($item_date, "\0")
        ? false
        : DateTime::createFromFormat('Y-m-d', $item_date);

    // Validation

    $errors = [

        'type' => in_array($type, ['lost', 'found'], true)
            ? ''
            : 'Please select Lost or Found.',

        'title' => mb_strlen($title) >= 5
                   && mb_strlen($title) <= 150
            ? ''
            : 'Title must contain 5-150 characters.',

        'category_id' => $category_id > 0
                         && in_array($category_id, $category_ids)
            ? ''
            : 'Please select a valid category.',

        'location_id' => $location_id > 0
                         && in_array($location_id, $location_ids)
            ? ''
            : 'Please select a valid location.',

        'description' => mb_strlen($description) >= 10
                         && mb_strlen($description) <= 2000
            ? ''
            : 'Description must contain 10-2000 characters.',

        'item_date' => $valid_date
                       && $valid_date->format('Y-m-d') === $item_date
                       && $item_date <= date('Y-m-d')
            ? ''
            : 'Please enter a valid date that is not in the future.'
    ];

    $errors = array_filter($errors);

    // Image validation

    $image = $_FILES['image'] ?? null;

    $extension = null;

    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if ($image && $image['error'] !== UPLOAD_ERR_NO_FILE) {

        $errors['image'] = $image['error'] === UPLOAD_ERR_OK
            ? ''
            : 'Image upload failed.';

        if ($image['error'] === UPLOAD_ERR_OK) {

            $mime_type = mime_content_type($image['tmp_name']);

            $extension = $allowed_types[$mime_type] ?? null;

            $errors['image'] = $image['size'] > 5 * 1024 * 1024
                ? 'Image must not exceed 5 MB.'
                : ($extension && @getimagesize($image['tmp_name'])
                    ? ''
                    : 'Only JPG, PNG and WEBP images are allowed.');
        }
    }

    $errors = array_filter($errors);

    // Upload new image

    if (empty($errors) && $extension !== null) {

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $upload_path = __DIR__ . '/assets/uploads/' . $filename;

        if (move_uploaded_file($image['tmp_name'], $upload_path)) {

            $image_path = 'assets/uploads/' . $filename;

        } else {

            $errors['image'] = 'Could not save the image.';
        }
    }

    // Update listing

    if (empty($errors)) {

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
                if ($image_path !== $old_image && $image_path !== null) {
                    unlink(__DIR__ . '/' . $image_path);
                }
                header('Location: my-listings.php?error=unavailable');
                exit;
            }

            // Preserve the latest image if no replacement was uploaded.
            if ($image_path === $old_image) {
                $image_path = $old_image = $current_listing['image_path'];
            }

            $stmt = $pdo->prepare(
                "UPDATE listings
                 SET type = ?,
                     title = ?,
                     category_id = ?,
                     location_id = ?,
                     description = ?,
                     item_date = ?,
                     image_path = ?
                 WHERE listing_id = ? AND reporter_id = ?"
            );

            $stmt->execute([
                $type,
                $title,
                $category_id,
                $location_id,
                $description,
                $item_date,
                $image_path,
                $id,
                $_SESSION['user_id']
            ]);

            header('Location: my-listings.php?success=updated');
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            // Removing newly uploaded image if update fails

            if ($image_path !== $old_image && $image_path !== null) {
                unlink(__DIR__ . '/' . $image_path);
            }

            $image_path = $old_image;

            $errors['general'] = 'Could not update the listing.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="auth-container report-container">

        <h1>Edit Listing</h1>

        <?= isset($errors['general'])
            ? '<p class="error">' . sanitize($errors['general']) . '</p>'
            : '' ?>

        <form method="POST"
              action="edit-listing.php?id=<?= $id ?>"
              enctype="multipart/form-data" novalidate>

            <!-- Type -->

            <label for="type">Listing Type</label>

            <select name="type" id="type"
                    class="<?= isset($errors['type']) ? 'input-error' : '' ?>"
                    required>

                <option value="">Select Type</option>

                <option value="lost"
                    <?= $type === 'lost' ? 'selected' : '' ?>>
                    Lost
                </option>

                <option value="found"
                    <?= $type === 'found' ? 'selected' : '' ?>>
                    Found
                </option>

            </select>

            <span class="field-error"><?= sanitize($errors['type'] ?? '') ?></span>


            <!-- Title -->

            <label for="title">Title</label>

            <input type="text" name="title" id="title"
                   minlength="5" maxlength="150"
                   class="<?= isset($errors['title']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($title) ?>" required>

            <span class="field-error"><?= sanitize($errors['title'] ?? '') ?></span>


            <!-- Category -->

            <label for="category_id">Category</label>

            <select name="category_id" id="category_id"
                    class="<?= isset($errors['category_id']) ? 'input-error' : '' ?>"
                    required>

                <option value="">Select Category</option>

                <?php foreach ($categories as $category): ?>

                    <option value="<?= (int) $category['category_id'] ?>"
                        <?= $category_id === (int) $category['category_id']
                            ? 'selected' : '' ?>>

                        <?= sanitize($category['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <span class="field-error"><?= sanitize($errors['category_id'] ?? '') ?></span>


            <!-- Location -->

            <label for="location_id">Location</label>

            <select name="location_id" id="location_id"
                    class="<?= isset($errors['location_id']) ? 'input-error' : '' ?>"
                    required>

                <option value="">Select Location</option>

                <?php foreach ($locations as $location): ?>

                    <option value="<?= (int) $location['location_id'] ?>"
                        <?= $location_id === (int) $location['location_id']
                            ? 'selected' : '' ?>>

                        <?= sanitize($location['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

            <span class="field-error"><?= sanitize($errors['location_id'] ?? '') ?></span>


            <!-- Description -->

            <label for="description">Description</label>

            <textarea name="description" id="description"
                      rows="5" minlength="10" maxlength="2000"
                      class="<?= isset($errors['description']) ? 'input-error' : '' ?>"
                      required><?= sanitize($description) ?></textarea>

            <span class="field-error"><?= sanitize($errors['description'] ?? '') ?></span>


            <!-- Item Date -->

            <label for="item_date">Item Date</label>

            <input type="date" name="item_date" id="item_date"
                   max="<?= date('Y-m-d') ?>"
                   class="<?= isset($errors['item_date']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($item_date) ?>" required>

            <span class="field-error"><?= sanitize($errors['item_date'] ?? '') ?></span>


            <!-- Current Image -->

            <label>Current Image</label>

            <?php if (!empty($old_image)): ?>

                <img class="current-image"
                     src="<?= sanitize($old_image) ?>"
                     alt="Current listing image">

            <?php else: ?>

                <p class="image-hint">No image uploaded.</p>

            <?php endif; ?>


            <!-- Replace Image -->

            <label for="image">Replace Image (Optional)</label>

            <input type="file" name="image" id="image"
                   accept="image/jpeg,image/png,image/webp"
                   class="<?= isset($errors['image']) ? 'input-error' : '' ?>">

            <span class="field-error"><?= sanitize($errors['image'] ?? '') ?></span>

            <p class="image-hint">
                Leave empty to keep the current image.
            </p>


            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

        </form>

        <p>
            <a href="my-listings.php">Cancel</a>
        </p>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>