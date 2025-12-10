<?php
require_once '../functions.php';
requireAuth();
$pageTitle = "Kursi";
$current_user = $user->getCurrentUser();

// Pārbaude, vai lietotājs ir skolotājs
if ($current_user['role'] !== 'teacher') {
    setFlashMessage('error', 'Šī lapa ir pieejama tikai skolotājiem.');
    header('Location: dashboard.php');
    exit();
}

// Iegūst visus skolotāja kursus
$user_classes = $classroom->getClassesByTeacher($current_user['id']);

$page_title = "Mani kursi";
require '../views/classes.view.php';
