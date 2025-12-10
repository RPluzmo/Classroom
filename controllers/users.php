<?php

require_once '../functions.php';

// Tikai adminiem
requireRole('admin');

$pageTitle = "Lietotāji";

global $user;
$current_user = $user->getCurrentUser();

// Flash ziņas
$message = getFlashMessage('success');
$error   = getFlashMessage('error');

// =======================================================
// 1) Lietotāja lomas maiņa
// =======================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'update_role') {

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', "Nederīgs CSRF marķieris. itkā es zinātu kas tās ir :D");
        header('Location: users.php');
        exit;
    }

    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);

    // Aizliegts mainīt sev lomu + tikai derīgas lomas
    if ($user_id != $current_user['id'] &&
        in_array($new_role, ['admin', 'teacher', 'student'])) {

        if ($user->updateUserRole($current_user['id'], $user_id, $new_role)) {
            setFlashMessage('success', "Lietotāja loma veiksmīgi atjaunināta.");
        } else {
            setFlashMessage('error', "Kļūda lomas atjaunināšanā.");
        }
    } else {
        setFlashMessage('error', "Nederīga loma vai nevar mainīt savu lomu.");
    }

    header('Location: users.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'admin_update_user') {


    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', "Nederīgs CSRF marķieris.itkā es zinātu kas tās ir :D");
        header('Location: users.php');
        exit;
    }

    $user_id    = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $username   = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email      = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name  = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $password   = $_POST['password'] ?: null;


    
    if ($user->adminUpdateUser(
        $current_user['id'],
        $user_id,
        $username,
        $email,
        $first_name,
        $last_name,
        $password
    )) {
        setFlashMessage('success', "Lietotāja dati veiksmīgi atjaunināti.");
    } else {
        setFlashMessage('error', "Kļūda lietotāja datu atjaunināšanā.");
    }

    header('Location: users.php');
    exit;
}

$users = $user->getAllUsers();
$pageTitle = "Lietotāju pārvaldība";

require '../views/users.view.php';

?>
