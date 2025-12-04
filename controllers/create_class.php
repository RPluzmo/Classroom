<?php
require_once '../functions.php';
requireRole('teacher');

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name)) {
        $error = 'Nepieciešams nosaukums.';
    } else {
        $class_id = $classroom->createClass($current_user['id'], $name, $description);
        if ($class_id) {
            setFlashMessage('success', 'Kurss veiksmīgi izveidots.');
            header('Location: class.php?id=' . $class_id);
            exit();
        } else {
            $error = 'Neizdevās izveidot kursu.';
        }
    }
}

require '../views/create_class.view.php';

?>