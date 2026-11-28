<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$page_title = 'Report an Item - ReFind';

$errors = [];

$type = '';
$title = '';
$category_id = '';
$location_id = '';
$description = '';
$item_date = date('Y-m-d');
$image_path = null;

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

    // Save image

    if (empty($errors) && $extension !== null) {

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $upload_path = __DIR__ . '/assets/uploads/' . $filename;

        if (move_uploaded_file($image['tmp_name'], $upload_path)) {

            $image_path = 'assets/uploads/' . $filename;

        } else {

            $errors['image'] = 'Could not save the image.';
        }
    }

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare(
                "INSERT INTO listings
                (reporter_id, type, category_id, location_id,
                 title, description, item_date, image_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $_SESSION['user_id'],
                $type,
                $category_id,
                $location_id,
                $title,
                $description,
                $item_date,
                $image_path
            ]);

            header('Location: listings.php?created=1');
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            if ($image_path !== null) {
                unlink(__DIR__ . '/' . $image_path);
            }

            $errors['general'] = 'Could not create the listing. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="auth-container report-container">

        <h1>Report an Item</h1>

        <?= isset($errors['general'])
            ? '<p class="error">' . sanitize($errors['general']) . '</p>'
            : '' ?>

        <form method="POST" action="create-listing.php"
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
                   placeholder="e.g. Lost Apple AirPods"
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
                      placeholder="Describe the lost or found item..."
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


            <!-- Image -->

            <label for="image">Image (Optional)</label>

            <input type="file" name="image" id="image"
                   accept="image/jpeg,image/png,image/webp"
                   class="<?= isset($errors['image']) ? 'input-error' : '' ?>">

            <span class="field-error"><?= sanitize($errors['image'] ?? '') ?></span>


            <button type="submit" class="btn btn-primary">
                Submit Report
            </button>

        </form>

        <p>
            <a href="listings.php">Back to Listings</a>
        </p>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>