<?php
$pageTitle = "Darbību vēsture";

require_once '../functions.php';


requireRole('admin'); 

global $user; // Piekļuve globālajam User objektam

$history_data = $user->getActionHistory(100); 

require '../views/history.view.php';

?>