<?php
require_once '../functions.php';
requireAuth();

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) {
    header('Location: dashboard.php');
    exit();
}

require_once '../classes/Assignment.php';
require_once '../classes/Classroom.php';

$assignment = new Assignment();
$classroom = new Classroom();
$current_user = $user->getCurrentUser();

$assignment_data = $assignment->getAssignmentById($assignment_id);
if (!$assignment_data) {
    setFlashMessage('error', 'Uzdevums nav atrasts.');
    header('Location: dashboard.php');
    exit();
}

$class = $classroom->getClassById($assignment_data['class_id']);
if (!$class) {
    setFlashMessage('error', 'Kurss nav atrasts.');
    header('Location: dashboard.php');
    exit();
}

// Piekļuves tiesību pārbaude
if ($current_user['role'] === 'teacher' && $class['teacher_id'] != $current_user['id']) {
    setFlashMessage('error', 'Jums nav piekļuves šim uzdevumam.');
    header('Location: dashboard.php');
    exit();
} elseif ($current_user['role'] === 'student') {
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->execute([$assignment_data['class_id'], $current_user['id']]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'Jums nav piekļuves šim uzdevumam.');
        header('Location: dashboard.php');
        exit();
    }
}

// Dzēšana
if (isset($_POST['delete_assignment']) && $current_user['role'] === 'teacher') {
    $deleted = $assignment->deleteAssignment($current_user['id'], $assignment_id);
    if ($deleted) {
        setFlashMessage('success', 'Uzdevums dzēsts.');
        header("Location: class.php?id=" . $assignment_data['class_id']);
        exit();
    } else {
        setFlashMessage('error', 'Neizdevās dzēst uzdevumu.');
        header("Location: assignment.php?id=" . $assignment_id);
        exit();
    }
}

// Uzdevuma dati un iesniegumi
$submission = $current_user['role'] === 'student' 
    ? $assignment->getSubmission($assignment_id, $current_user['id'])
    : null;

$submissions = $current_user['role'] === 'teacher' 
    ? $assignment->getSubmissions($assignment_id)
    : [];

$assignment_files = $assignment->getAssignmentFiles($assignment_id);
$submission_files = $submission ? $assignment->getSubmissionFiles($submission['id']) : [];

// Komentāri
require_once '../classes/Comment.php';
$comment = new Comment();
$comments = $comment->getAssignmentComments($assignment_id);

// Formu apstrāde (iesniegšana, vērtēšana, komentāri)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($current_user['role'] === 'student' && isset($_POST['submit_assignment'])) {
        $files = [];
        if (!empty($_FILES['submission_files']['name'])) {
            foreach ($_FILES['submission_files']['name'] as $key => $name) {
                if ($_FILES['submission_files']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $name,
                        'type' => $_FILES['submission_files']['type'][$key],
                        'tmp_name' => $_FILES['submission_files']['tmp_name'][$key],
                        'error' => $_FILES['submission_files']['error'][$key],
                        'size' => $_FILES['submission_files']['size'][$key]
                    ];
                    $uploaded_file = handleFileUpload($file, 'uploads/submissions/');
                    if ($uploaded_file) $files[] = $uploaded_file;
                }
            }
        }
        $assignment->submitAssignment($assignment_id, $current_user['id'], $files);
        setFlashMessage('success', 'Uzdevums iesniegts.');
        header('Location: assignment.php?id=' . $assignment_id);
        exit();
    } elseif ($current_user['role'] === 'teacher' && isset($_POST['grade_submission'])) {
        $submission_id = intval($_POST['submission_id']);
        $grade = intval($_POST['grade']);
        $feedback = sanitize($_POST['feedback'] ?? '');
        $assignment->gradeSubmission($current_user['id'], $submission_id, $grade, $feedback);
        setFlashMessage('success', 'Uzdevums novērtēts.');
        header('Location: assignment.php?id=' . $assignment_id);
        exit();
    } elseif (isset($_POST['add_comment'])) {
        $comment_text = sanitize($_POST['comment_text'] ?? '');
        if ($comment_text) {
            $sub_id = $submission['id'] ?? null;
            $comment->addComment($current_user['id'], $comment_text, $assignment_id, $sub_id);
            setFlashMessage('success', 'Komentārs pievienots.');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        }
    }
}

require '../views/assignnment.view.php';
?>
