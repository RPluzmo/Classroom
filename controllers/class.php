<?php
require_once '../functions.php';
requireAuth();
require_once '../classes/Classroom.php';
require_once '../classes/Assignment.php';
$class_id = $_GET['class_id'] ?? $_GET['id'] ?? null;
if (!$class_id) {
    header('Location: dashboard.php');
    exit();
}



$classroom = new Classroom();
$assignment = new Assignment();
$current_user = $user->getCurrentUser();

$class = $classroom->getClassById($class_id);
if (!$class) {
    setFlashMessage('error', 'Kurss netika atrasts.');
    header('Location: dashboard.php');
    exit();
}

// Permissions
if ($current_user['role'] === 'teacher' && $class['teacher_id'] != $current_user['id']) {
    setFlashMessage('error', 'Jums nav piekļuves šim kursam.');
    header('Location: dashboard.php');
    exit();
} elseif ($current_user['role'] === 'student') {
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->execute([$class_id, $current_user['id']]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'Jums nav piekļuves šim kursam.');
        header('Location: dashboard.php');
        exit();
    }
}

if (isset($_POST['delete_class']) && $current_user['role'] === 'teacher') {
    $deleted = $classroom->deleteClass($current_user['id'], $class_id);
    if ($deleted) {
        setFlashMessage('success', 'Kurss dzēsts.');
        header('Location: classes.php');
        exit();
    } else {
        setFlashMessage('error', 'Neizdevās dzēst kursu.');
        header('Location: class.php?id=' . $class_id);
        exit();
    }
}

// Skolnieka noņemšana
if ($current_user['role'] === 'teacher' && isset($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];
    $classroom->removeStudent($current_user['id'], $class_id, $student_id);
    header('Location: class.php?id=' . $class_id);
    exit();
}

$assignments = $assignment->getAssignmentsByClass($class_id);
$class_members = $classroom->getClassMembers($class_id);

require '../views/class.view.php';
