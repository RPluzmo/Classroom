<?php
require_once '../functions.php';

header('Content-Type: application/json');

if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

global $settings; 
$user_id = $_SESSION['user_id'];
$new_theme_state = $settings->toggleDarkTheme($user_id); 

echo json_encode([
    'success' => true,
    'dark_theme' => $new_theme_state, 
    'new_theme_name' => $new_theme_state ? 'dark' : 'light',
    'message' => 'Team balts vai Team menls aah'
]);
?>