<?php

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$page_title = 'Login - ReFind';

$errors = [];
$email = '';

$success = isset($_GET['registered'])
    ? 'Registration successful! Please log in.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [

        'email' => filter_var($email, FILTER_VALIDATE_EMAIL)
                   && mb_strlen($email) <= 150
            ? ''
            : 'Enter a valid email address.',

        'password' => $password !== ''
            ? ''
            : 'Password is required.'
    ];

    $errors = array_filter($errors);

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare(
                "SELECT user_id, full_name, email, password_hash, role
                 FROM users WHERE email = ?"
            );

            $stmt->execute([$email]);

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: index.php');
                exit;
            }

            $errors['general'] = 'Invalid email or password.';

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errors['general'] = 'Login failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="site-main">

    <section class="auth-container">

        <h1>Login</h1>

        <?= $success !== ''
            ? '<p class="success">' . sanitize($success) . '</p>'
            : '' ?>

        <?= isset($errors['general'])
            ? '<p class="error">' . sanitize($errors['general']) . '</p>'
            : '' ?>

        <form method="POST" action="login.php">

            <label for="email">Email</label>

            <input type="email" id="email"
                   name="email" maxlength="150"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                   value="<?= sanitize($email) ?>"
                   autocomplete="email" required>

            <small class="field-error"><?= sanitize($errors['email'] ?? '') ?></small>

            <label for="password">Password</label>

            <input type="password" id="password"
                   name="password"
                   class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
                   autocomplete="current-password" required>

            <small class="field-error"><?= sanitize($errors['password'] ?? '') ?></small>


            <button type="submit" class="btn btn-primary">
                Login
            </button>

        </form>

        <p>
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>