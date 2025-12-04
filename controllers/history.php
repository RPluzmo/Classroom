<?php
require_once '../functions.php';
$pageTitle = "Darbību vēsture";
$current_user = $user->getCurrentUser();
requireRole('admin');

global $user;
$current_user = $user->getCurrentUser();
$history = $user->getActionHistory(200);



require '../views/history.view.php';
?>
