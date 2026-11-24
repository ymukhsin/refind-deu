<?php

require_once __DIR__ . '/../includes/db.php';

echo "Database connected successfully!<br>";

$stmt = $pdo->query("SELECT * FROM categories");

$categories = $stmt->fetchAll();

foreach ($categories as $category) {
    echo htmlspecialchars($category['name']) . "<br>";
}
