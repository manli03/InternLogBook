<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Regenerate and destroy session
session_regenerate_id(true);
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();