<?php

require_once __DIR__ . '/../includes/auth.php';

echo "Before login:<br>";
var_dump(is_logged_in());

$_SESSION['user_id'] = 1;
$_SESSION['full_name'] = 'Test User';
$_SESSION['role'] = 'user';

echo "<br><br>After login:<br>";
var_dump(is_logged_in());

echo "<br><br>Current user:<br>";
print_r(current_user());

unset($_SESSION['user_id']);
unset($_SESSION['full_name']);
unset($_SESSION['role']);

echo "<br><br>After logout:<br>";
var_dump(is_logged_in());
