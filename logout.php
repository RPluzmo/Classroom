<?php
require_once 'includes/functions.php';

$user->logout();
setFlashMessage('success', 'You have been logged out successfully.');
header('Location: login.php');
exit();
?>