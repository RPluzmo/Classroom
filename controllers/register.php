<?php
require_once '../functions.php';

// Redirect if logged in
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name  = sanitize($_POST['first_name'] ?? '');
    $last_name   = sanitize($_POST['last_name'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    // Validation
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        $errors[] = "Visi lauki ir obligāti.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Neatbilstošs e-pasta formāts.";
    }

    // Pārbaudām e-pasta unikalitāti, izmantojot jauno metodi
    if ($user->exists('email', $email)) { 
        $errors[] = "E-pasts jau ir reģistrēts.";
    }

    if (empty($errors)) {
        // Pārvietojam lietotājvārda ģenerēšanu uz klasi, 
        // vai izmantojam kontrolieri, lai to ģenerētu (kā Jums jau bija)
        
        // 1. Ģenerējam lietotājvārdu (kā bija Jūsu sākotnējā loģikā)
        $username = strtolower($first_name . "." . $last_name);
        $base_username = $username;
        $i = 1;
        while ($user->exists('username', $username)) { // Izmantojam exists()
            $username = $base_username . $i;
            $i++;
        }
        
        // 2. Izmantojam User::register() metodi, lai reģistrētu lietotāju
        $user_id = $user->register($username, $email, $password, $first_name, $last_name, 'student'); // LABOJUMS

        if ($user_id !== false) {
            // Success message and redirect
            setFlashMessage('success', 'Reģistrācija izdevās! Tagad vari pieslēgties.');
            header('Location: login.php');
            exit();
        } else {
            $errors[] = "Reģistrācija neizdevās datubāzes kļūdas dēļ.";
        }
    }
}

require '../views/register.view.php';

?>