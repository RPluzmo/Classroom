<?php
require_once '../functions.php';

$current_user = $user->getCurrentUser();
requireRole('admin');

global $user;
$current_user = $user->getCurrentUser();
$history = $user->getActionHistory(200);

$page_title = "Darbību vēsture";

require '../views/history.view.php';
?>
