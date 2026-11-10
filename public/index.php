<?php
/**
 * ReFind — system health check.
 */

require_once __DIR__ . '/../includes/db.php';

$connected = true;
$errorMessage = '';

try {
    $pdo = Database::getConnection();
    $pdo->query('SELECT 1');
} catch (Throwable $e) {
    $connected = false;
    $errorMessage = $e->getMessage();
    http_response_code(500);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReFind System Status</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f9;
            color: #1f2937;
            padding: 24px;
        }

        .status-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 40px 48px;
            max-width: 560px;
            width: 100%;
            text-align: center;
        }

        .status-card h1 {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 1.4;
            color: #065f46;
        }

        .status-card .error {
            margin-top: 16px;
            font-size: 0.9rem;
            color: #b91c1c;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 16px;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <main class="status-card">
        <?php if ($connected): ?>
            <h1>ReFind System Status: Database Connected Successfully</h1>
        <?php else: ?>
            <h1>ReFind System Status: Database Connection Failed</h1>
            <p class="error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
    </main>
</body>
</html>
