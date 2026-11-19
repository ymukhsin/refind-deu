<?php
/**
 * ReFind — session bootstrap and authentication helpers.
 */

// Start the session securely if it has not already been started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check whether a visitor is currently logged in.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Return the current user's session data, or null when not logged in.
 *
 * @return array|null
 */
function current_user(): ?array
{
    return is_logged_in() ? $_SESSION : null;
}

/**
 * Escape a string for safe HTML output.
 *
 * @param string $data
 * @return string
 */
function sanitize(string $data): string
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to the login page when the visitor is not authenticated.
 *
 * @return void
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
