<?php
session_start();

if (!isset($_SESSION['dark_theme'])) {
    $_SESSION['dark_theme'] = 0;
}

require_once __DIR__ . '/config.php'; 
require_once __DIR__ . '/classes/User.php'; 
require_once __DIR__ . '/classes/Classroom.php';
require_once __DIR__ . '/classes/Assignment.php';
require_once __DIR__ . '/classes/Comment.php';

$user = new User();
$classroom = new Classroom();
$assignment = new Assignment();
$comment = new Comment();



function requireAuth() {
    global $user;
    if (!$user->isAuthenticated()) {
        header('Location: login.php');
        exit();
    }
}


function requireRole($role) {
    global $user;
    requireAuth();
    
    $current_user = $user->getCurrentUser();
    if (!$current_user || $current_user['role'] !== $role) {
        header('Location: dashboard.php');
        exit();
    }
}

function canAccess($resource_type, $resource_id) {
    global $user;
    $current_user = $user->getCurrentUser();
    
    if (!$current_user) {
        return false;
    }

    // Admin can access everything
    if ($current_user['role'] === 'admin') {
        return true;
    }

    switch ($resource_type) {
        case 'class':
            return canAccessClass($current_user['id'], $resource_id);
        case 'assignment':
            return canAccessAssignment($current_user['id'], $resource_id);
        case 'submission':
            return canAccessSubmission($current_user['id'], $resource_id);
        default:
            return false;
    }
}

function canAccessClass($user_id, $class_id) {
    global $classroom, $db;
    
    $class = $classroom->getClassById($class_id);
    if (!$class) {
        return false;
    }

    // Teacher can access their own classes
    if ($class['teacher_id'] == $user_id) {
        return true;
    }

    // Check if student is enrolled
    // Piezīme: Ja $db nav globāls, tas var radīt kļūdu. Jūsu esošajā kodā $db ir izveidots, 
    // bet šeit tiek mēģināts tam piekļūt. Iesaku to izveidot canAccessClass iekšpusē, ja tā nav globālā mainīgā, kas satur DB savienojumu.
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->execute([$class_id, $user_id]);
    return $stmt->fetch() !== false;
}

function canAccessAssignment($user_id, $assignment_id) {
    global $assignment;
    
    $assignment_data = $assignment->getAssignmentById($assignment_id);
    if (!$assignment_data) {
        return false;
    }

    return canAccessClass($user_id, $assignment_data['class_id']);
}

function canAccessSubmission($user_id, $submission_id) {
    global $assignment;
    
    $conn = (new Database())->connect();
    $stmt = $conn->prepare("
        SELECT s.*, a.class_id, a.teacher_id 
        FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$submission_id]);
    $submission = $stmt->fetch();

    if (!$submission) {
        return false;
    }

    // Student can access their own submissions
    if ($submission['student_id'] == $user_id) {
        return canAccessClass($user_id, $submission['class_id']);
    }

    // Teacher can access submissions in their classes
    // Piezīme: Nav 'teacher_id' submissions tabulā. Tāpēc SQL vaicājumā bija pareizi pievienots JOIN.
    if ($submission['teacher_id'] == $user_id) {
        return true;
    }

    return false;
}

// File upload helper
function handleFileUpload($file, $upload_dir = 'uploads/') {
// (Pārējais kods paliek nemainīgs)
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $filename = time() . '_' . uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;

    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }

    // Check file type (basic validation)
    $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        return null;
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'name' => $file['name'],
            'path' => $filepath,
            'size' => $file['size']
        ];
    }

    return null;
}

// Format date helper
function formatDate($date_string) {
// (Pārējais kods paliek nemainīgs)
    if (!$date_string) return 'N/A';
    
    $date = new DateTime($date_string);
    $now = new DateTime();
    $diff = $now->diff($date);

    if ($diff->days == 0) {
        if ($diff->h == 0) {
            return $diff->i . ' minutes ago';
        }
        return $diff->h . ' hours ago';
    } elseif ($diff->days == 1) {
        return 'Yesterday';
    } elseif ($diff->days < 7) {
        return $diff->days . ' days ago';
    } else {
        return $date->format('M j, Y');
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get user role display name
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
        'student' => 'Student'
    ];
    return $roles[$role] ?? ucfirst($role);
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function getFlashMessage($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}