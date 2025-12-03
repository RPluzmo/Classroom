<?php
require_once 'includes/functions.php';

$user->logout();
setFlashMessage('success', 'You have been logged out successfully.');
header('Location: index.php');
exit();
?>