<?php
require_once '../functions.php';
requireRole('student');

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_code = sanitize($_POST['class_code'] ?? '');
    
    if (empty($class_code)) {
        $error = 'Nepieciešams ievadīt kursa kodu';
    } else {
        if ($classroom->joinClass($current_user['id'], $class_code)) {
            setFlashMessage('success', 'Esat pievienojies');
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid class code or you are already enrolled in this class';
        }
    }
}

// Handle QR code join (GET parameter)
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $class_code = sanitize($_GET['code']);
    if ($classroom->joinClass($current_user['id'], $class_code)) {
        setFlashMessage('success', 'Successfully joined the class!');
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid class code or you are already enrolled in this class';
    }
}

require '../views/join_class.view.php';

?>