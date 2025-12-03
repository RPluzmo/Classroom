<?php


require_once '../functions.php';

// Pārbaude, vai lietotājam ir Admin loma
requireRole('admin'); 

global $user; // Piekļuve globālajam User objektam
$current_user = $user->getCurrentUser(); // Iegūstam pašreizējo admina lietotāju

$message = getFlashMessage('success');
$error = getFlashMessage('error');

// --- Lietotāja lomas atjaunināšanas loģika ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', "Nederīgs CSRF marķieris.");
        header('Location: users.php');
        exit;
    }
    
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);
    
    // Nevar mainīt savu lomu
    if ($user_id != $current_user['id'] && in_array($new_role, ['admin', 'teacher', 'student'])) {
        // Izmanto User klases metodi, kas veic iekšēju logAction
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

// --- Lietotāja datu (vārds, parole) atjaunināšanas loģika ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_update_user') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', "Nederīgs CSRF marķieris.");
        header('Location: users.php');
        exit;
    }
    
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $password = $_POST['password']; 
    
    // Izmanto jaunpievienoto User klases metodi
    if ($user->adminUpdateUser($current_user['id'], $user_id, $first_name, $last_name, $password)) { 
        setFlashMessage('success', "Lietotāja dati veiksmīgi atjaunināti.");
    } else {
        setFlashMessage('error', "Kļūda lietotāja datu atjaunināšanā.");
    }
    header('Location: users.php');
    exit;
}


$users = $user->getAllUsers(); // Iegūst visus lietotājus no User klases
$page_title = "Lietotāju pārvaldība";

require '../views/admin/users.view.php';

?>