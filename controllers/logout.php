<?php
require_once '../functions.php';

$user->logout();
setFlashMessage('success', '"Anuka pisās arā nošejienes" viņš teica ar savu tubu. ');
header('Location: ../controllers/login.php');
exit();

?>