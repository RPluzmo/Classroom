<?php
require_once '../functions.php';
requireAuth();

$assignment_id = $_GET['id'] ?? null;
if (!$assignment_id) {
    header('Location: dashboard.php');
    exit();
}

$assignment_data = $assignment->getAssignmentById($assignment_id);
if (!$assignment_data) {
    setFlashMessage('error', 'Assignment not found');
    header('Location: dashboard.php');
    exit();
}

$current_user = $user->getCurrentUser();
$class = $classroom->getClassById($assignment_data['class_id']);

// Check permissions
if ($current_user['role'] === 'teacher' && $class['teacher_id'] != $current_user['id']) {
    setFlashMessage('error', 'You do not have permission to view this assignment');
    header('Location: dashboard.php');
    exit();
} elseif ($current_user['role'] === 'student') {
    // Check if student is enrolled in the class
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->execute([$assignment_data['class_id'], $current_user['id']]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'You are not enrolled in this class');
        header('Location: dashboard.php');
        exit();
    }
}

$submission = null;
$comments = [];
$assignment_files = $assignment->getAssignmentFiles($assignment_id);

if ($current_user['role'] === 'student') {
    $submission = $assignment->getSubmission($assignment_id, $current_user['id']);
    if ($submission) {
        $submission_files = $assignment->getSubmissionFiles($submission['id']);
        $comments = $comment->getSubmissionComments($submission['id']);
    }
} else {
    $submissions = $assignment->getSubmissions($assignment_id);
    $comments = $comment->getAssignmentComments($assignment_id);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($current_user['role'] === 'student' && isset($_POST['submit_assignment'])) {
        $files = [];
        if (!empty($_FILES['submission_files'])) {
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
                    if ($uploaded_file) {
                        $files[] = $uploaded_file;
                    }
                }
            }
        }
        
        $submission_id = $assignment->submitAssignment($assignment_id, $current_user['id'], $files);
        if ($submission_id) {
            setFlashMessage('success', 'Assignment submitted successfully!');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        }
    } elseif ($current_user['role'] === 'teacher' && isset($_POST['grade_submission'])) {
        $submission_id = intval($_POST['submission_id']);
        $grade = intval($_POST['grade']);
        $feedback = sanitize($_POST['feedback'] ?? '');
        
        if ($assignment->gradeSubmission($current_user['id'], $submission_id, $grade, $feedback)) {
            setFlashMessage('success', 'Grade submitted successfully!');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        }
    } elseif (isset($_POST['add_comment'])) {
        $comment_text = sanitize($_POST['comment_text'] ?? '');
        if (!empty($comment_text)) {
            if ($current_user['role'] === 'student' && $submission) {
                $comment->addComment($current_user['id'], $comment_text, null, $submission['id']);
            } elseif ($current_user['role'] === 'teacher') {
                $comment->addComment($current_user['id'], $comment_text, $assignment_id, null);
            }
            setFlashMessage('success', 'Comment added!');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        }
    }
}
require '../views/assignment.view.php';
?>