<?php
require_once '../functions.php';
requireRole('teacher');
$pageTitle = "Izveidot uzdevumu";

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    header('Location: dashboard.php');
    exit();
}

$class = $classroom->getClassById($class_id);
if (!$class || $class['teacher_id'] != $user->getCurrentUser()['id']) {
    setFlashMessage('error', 'Kurss netika atrasts');
    header('Location: dashboard.php');
    exit();
}

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $due_date = sanitize($_POST['due_date'] ?? '');
    $max_points = intval($_POST['max_points'] ?? 100);
    
    if (empty($title)) {
        $error = 'Assignment title is required';
    } else {
        $assignment_id = $assignment->createAssignment($class_id, $title, $description, $due_date ?: null, $max_points);
        if ($assignment_id) {
            // Handle file uploads
            if (!empty($_FILES['attachments'])) {
                foreach ($_FILES['attachments']['name'] as $key => $name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['attachments']['type'][$key],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$key],
                            'error' => $_FILES['attachments']['error'][$key],
                            'size' => $_FILES['attachments']['size'][$key]
                        ];
                        
                        $uploaded_file = handleFileUpload($file, 'uploads/assignments/');
                        if ($uploaded_file) {
                            $assignment->addAssignmentFile($assignment_id, $uploaded_file['name'], $uploaded_file['path'], $uploaded_file['size']);
                        }
                    }
                }
            }
            
            setFlashMessage('success', 'Uzdevums veiksmīgi izveidots.');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        } else {
            $error = 'Neizdevās pievienot uzdevumu.';
        }
    }
}

require '../views/create_assignment.view.php';

?>