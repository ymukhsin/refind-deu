<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$page_title = 'Register - ReFind';

$errors = [];

$full_name = '';
$email = '';
$student_id = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [

        'full_name' => mb_strlen($full_name) >= 5
                       && mb_strlen($full_name) <= 100
            ? ''
            : 'Full name must contain 5-100 characters.',

        'email' => filter_var($email, FILTER_VALIDATE_EMAIL)
                   && mb_strlen($email) <= 150
            ? ''
            : 'Enter a valid email address.',

        'student_id' => filter_var(
            $student_id,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[0-9]{8}$/D']]
        ) !== false
            ? ''
            : 'Student ID must contain exactly 8 digits.',

        'phone' => filter_var(
            $phone,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[0-9]{1,20}$/D']]
        ) !== false
            ? ''
            : 'Phone number must contain only digits (max 20).',

        'password' => mb_strlen($password) >= 8
                      && strlen($password) <= 72
                      && !str_contains($password, "\0")
            ? ''
            : 'Password must be valid and at least 8 characters.',

        'confirm_password' => $confirm_password !== ''
                              && $password === $confirm_password
            ? ''
            : 'Passwords do not match.'
    ];

    $errors = array_filter($errors);

    try {

        // duplicate email

        if (!isset($errors['email'])) {

            $stmt = $pdo->prepare(
                "SELECT user_id FROM users WHERE email = ?"
            );

            $stmt->execute([$email]);

            $errors['email'] = $stmt->fetch()
                ? 'This email is already registered.'
                : '';
        }

        // duplicate student ID

        if (!isset($errors['student_id'])) {

            $stmt = $pdo->prepare(
                "SELECT user_id FROM users WHERE student_id = ?"
            );

            $stmt->execute([$student_id]);

            $errors['student_id'] = $stmt->fetch()
                ? 'This student ID is already registered.'
                : '';
        }

        $errors = array_filter($errors);

        // no errors

        if (empty($errors)) {

            $password_hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (full_name, email, password_hash, phone, student_id)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $full_name,
                $email,
                $password_hash,
                $phone,
                $student_id
            ]);

            header('Location: login.php?registered=1');
            exit;
        }

    } catch (PDOException $e) {

        error_log($e->getMessage());

        $errors['general'] = $e->getCode() === '23000'
            ? 'Email or student ID already exists.'
            : 'Registration failed. Please try again.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="auth-container">

        <h1>Create an Account</h1>

        <?= isset($errors['general'])
            ? '<p class="error">' . sanitize($errors['general']) . '</p>'
            : '' ?>

        <form method="POST" action="register.php">

            <label for="full_name">Full Name</label>

            <input type="text" id="full_name"
                   name="full_name"
                   minlength="5" maxlength="100"
                   class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($full_name) ?>" required>

            <small class="field-error"><?= sanitize($errors['full_name'] ?? '') ?></small>


            <label for="email">Email</label>

            <input type="email" id="email"
                   name="email" maxlength="150"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($email) ?>" required>

            <small class="field-error"><?= sanitize($errors['email'] ?? '') ?></small>


            <label for="student_id">Student ID</label>

            <input type="text" id="student_id"
                   name="student_id"
                   minlength="8" maxlength="8"
                   placeholder="20251234"
                   class="<?= isset($errors['student_id']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($student_id) ?>" required>

            <small class="field-error"><?= sanitize($errors['student_id'] ?? '') ?></small>


            <label for="phone">Phone Number</label>

            <input type="tel" id="phone"
                   name="phone" maxlength="20"
                   inputmode="numeric"
                   placeholder="01012345678"
                   class="<?= isset($errors['phone']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($phone) ?>" required>

            <small class="field-error"><?= sanitize($errors['phone'] ?? '') ?></small>


            <label for="password">Password</label>

            <input type="password" id="password"
                   name="password" minlength="8"
                   class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
                   autocomplete="new-password" required>

            <small class="field-error"><?= sanitize($errors['password'] ?? '') ?></small>


            <label for="confirm_password">Confirm Password</label>

            <input type="password" id="confirm_password"
                   name="confirm_password"
                   class="<?= isset($errors['confirm_password']) ? 'input-error' : '' ?>"
                   autocomplete="new-password" required>

            <small class="field-error"><?= sanitize($errors['confirm_password'] ?? '') ?></small>


            <button type="submit" class="btn btn-primary">
                Register
            </button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>