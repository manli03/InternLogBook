<?php
require_once 'db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Authentication required.']);
    exit();
}

try {
    // Generate a new, cryptographically secure secret key
    $new_key = bin2hex(random_bytes(16));

    // Update the user's key in the database
    $stmt = $pdo->prepare("UPDATE users SET secret_key = ? WHERE id = ?");
    $stmt->execute([$new_key, $_SESSION['user_id']]);

    // Construct the new shareable URL
    $new_share_url = "http://" . $_SERVER['HTTP_HOST'] . preg_replace('/regenerate_key\.php$/', '', $_SERVER['PHP_SELF']) . "guest.php?user=" . urlencode($_SESSION['username']) . "&key=" . $new_key;

    // Send a success response back to the JavaScript fetch call
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'newUrl' => $new_share_url]);

} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Failed to regenerate key.']);
}

exit();