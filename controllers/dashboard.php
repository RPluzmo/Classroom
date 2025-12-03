<?php
require_once '../functions.php';
requireAuth();

$current_user = $user->getCurrentUser();
$user_classes = [];
$user_assignments = [];

// Get data based on user role
if ($current_user['role'] === 'teacher') {
    $user_classes = $classroom->getTeacherClasses($current_user['id']);
    
    // Get recent assignments from all classes
    $recent_assignments = [];
    foreach ($user_classes as $class) {
        $class_assignments = $assignment->getAssignmentsByClass($class['id']);
        $recent_assignments = array_merge($recent_assignments, $class_assignments);
    }
    
    // Sort by creation date
    usort($recent_assignments, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    $user_assignments = array_slice($recent_assignments, 0, 5);
    
} elseif ($current_user['role'] === 'student') {
    $user_classes = $classroom->getStudentClasses($current_user['id']);
    
    // Get assignments from enrolled classes
    $all_assignments = [];
    foreach ($user_classes as $class) {
        $class_assignments = $assignment->getAssignmentsByClass($class['id']);
        foreach ($class_assignments as $assign) {
            $assign['class_name'] = $class['name'];
            $assign['submission'] = $assignment->getSubmission($assign['id'], $current_user['id']);
            $all_assignments[] = $assign;
        }
    }
    
    // Sort by due date and creation date
    usort($all_assignments, function($a, $b) {
        // First sort by submission status
        $a_submitted = $a['submission'] ? 1 : 0;
        $b_submitted = $b['submission'] ? 1 : 0;
        
        if ($a_submitted !== $b_submitted) {
            return $a_submitted - $b_submitted;
        }
        
        // Then sort by due date
        $a_due = $a['due_date'] ? strtotime($a['due_date']) : 9999999999;
        $b_due = $b['due_date'] ? strtotime($b['due_date']) : 9999999999;
        
        return $a_due - $b_due;
    });
    
    $user_assignments = array_slice($all_assignments, 0, 5);
} elseif ($current_user['role'] === 'admin') {
    $recent_users = array_slice($user->getAllUsers(), 0, 5);
    $action_history = $user->getActionHistory(10);
}

require '../views/dashboard.view.php';

?>