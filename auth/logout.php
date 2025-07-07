<?php
session_start();

// Destroy all session data
session_destroy();

// Remove remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Redirect to login page with a logout message
header('Location: login.php?message=logged_out');
exit;
?>
