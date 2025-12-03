<?php
require_once '../functions.php';

// Redirect if already logged in
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle demo login
$demo_type = $_GET['demo'] ?? '';
if (!empty($demo_type)) {
    switch ($demo_type) {
        case 'admin':
            $_POST['username'] = 'admin';
            $_POST['password'] = 'admin123';
            break;
        case 'teacher':
            $_POST['username'] = 'teacher';
            $_POST['password'] = 'teacher123';
            break;
        case 'student':
            $_POST['username'] = 'student';
            $_POST['password'] = 'student123';
            break;
    }
    
    if (!empty($_POST['username'])) {
        $login_result = $user->login($_POST['username'], $_POST['password']);
        if ($login_result) {
            header('Location: dashboard.php');
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $login_result = $user->login($username, $password);
        if ($login_result) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}

require '../views/login.view.php';

?>