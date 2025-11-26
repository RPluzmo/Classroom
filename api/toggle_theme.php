<?php
require_once '../includes/functions.php';

if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$new_theme = $settings->toggleDarkTheme($user_id);

echo json_encode([
    'success' => true,
    'dark_theme' => $new_theme,
    'message' => 'Theme updated successfully'
]);
?>