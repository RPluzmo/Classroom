<?php
require_once '../functions.php';
requireAuth();

$class_id = $_GET['id'] ?? null;
if (!$class_id) {
    header('Location: dashboard.php');
    exit();
}

$class = $classroom->getClassById($class_id);
if (!$class) {
    setFlashMessage('error', 'Class not found');
    header('Location: dashboard.php');
    exit();
}

$current_user = $user->getCurrentUser();

// Check permissions
if ($current_user['role'] === 'teacher' && $class['teacher_id'] != $current_user['id']) {
    setFlashMessage('error', 'You do not have permission to view this class');
    header('Location: dashboard.php');
    exit();
} elseif ($current_user['role'] === 'student') {
    // Check if student is enrolled
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->execute([$class_id, $current_user['id']]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'You are not enrolled in this class');
        header('Location: dashboard.php');
        exit();
    }
}

$assignments = $assignment->getAssignmentsByClass($class_id);
$class_members = $classroom->getClassMembers($class_id);

require '../views/class.view.php';
?>