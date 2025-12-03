<?php
require_once '../functions.php';
requireAuth();

$pageTitle = "Lietotājprofils";

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'Aizpildiet visus lauciņus';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Neatbilstoš epasts';
        } else {
            if ($user->updateProfile($current_user['id'], $first_name, $last_name, $email)) {
                $success = 'Profils izmainīts';
                $current_user = $user->getCurrentUser(); // Refresh user data
            } else {
                $error = 'Nesanāca nomainīt profila attēlu';
            }
        }
    } elseif (isset($_POST['upload_avatar'])) {
        if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($file['type'], $allowed_types)) {
                $error = 'Neatbilstošs attēla formāts, tikai: JPG vai PNG';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                $error = 'Attēlam max ir 5MB.';
            } else {
                $uploaded_file = handleFileUpload($file, 'uploads/avatars/');
                if ($uploaded_file) {
                    if ($user->updateProfilePicture($current_user['id'], $uploaded_file['path'])) {
                        $success = 'Profila attēls nomainīts';
                        $current_user = $user->getCurrentUser(); // Refresh user data
                    } else {
                        $error = 'Nesanāca nomainīt profila attēlu';
                    }
                } else {
                    $error = 'Nesanāca augšupielādēt';
                }
            }
        } else {
            $error = 'Izvēlieties 1 failu ko iestatīt par savu profila attēlu';
        }
    }
}

require '../views/profile.view.php';   

?>