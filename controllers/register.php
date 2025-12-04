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

    if ($user->exists('email', $email)) { 
        $errors[] = "E-pasts jau ir reģistrēts.";
    }

    if (empty($errors)) {
        $username = strtolower($first_name . "." . $last_name);
        $base_username = $username;
        $i = 1;
        while ($user->exists('username', $username)) { 
            $username = $base_username . $i;
            $i++;
        }

        $user_id = $user->register($username, $email, $password, $first_name, $last_name, 'student');

        if ($user_id !== false) {
            setFlashMessage('success', 'Reģistrācija izdevās');
            header('Location: login.php');
            exit();
        } else {
            $errors[] = "Reģistrācija neizdevās datubāzes kļūdas dēļ.";
        }
    }
}

require '../views/register.view.php';

?>