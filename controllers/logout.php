<?php
require_once '../functions.php';

$user->logout();
setFlashMessage('success', 'You have been logged out successfully.');
header('Location: ../controllers/login.php');
exit();

?>