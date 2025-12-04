<?php
require_once '../functions.php';

if (!$user->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$class_id = $_GET['class_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;

if (!$class_id || !$student_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

$current_user = $user->getCurrentUser();

// Verify teacher owns the class
$class = $classroom->getClassById($class_id);
if (!$class || $class['teacher_id'] != $current_user['id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($classroom->removeStudent($current_user['id'], $class_id, $student_id)) {
    setFlashMessage('success', 'Skolnieks veiksmīgi noņemts.');
    header('Location: ../class.php?id=' . $class_id);
    exit();
} else {
    setFlashMessage('error', 'Neizdevās noņemt skolnieku.');
    header('Location: ../class.php?id=' . $class_id);
    exit();
}
?>