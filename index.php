<?php
require_once 'includes/functions.php';

// Ja lietotājs jau ir ielogojies → uz dashboard
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

// Pretējā gadījumā → uz login lapu
header('Location: login.php');
exit();