<?php
require_once 'includes/functions.php';
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
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assignment_data['title']); ?> - Classroom Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">
                </a>

                <nav class="nav-menu">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="class.php?id=<?php echo $assignment_data['class_id']; ?>" class="nav-link"><?php echo htmlspecialchars($class['name']); ?></a>
                </nav>

                <div class="user-menu">
                    <button class="theme-toggle" title="Toggle theme">
                        <?php echo $_SESSION['dark_theme'] ? '☀️' : '🌙'; ?>
                    </button>
                    
                    <div style="position: relative;">
                        <img src="<?php echo $current_user['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                             alt="=)" class="user-avatar" onclick="toggleUserMenu()">
                        
                        <div id="userMenu" class="d-none" style="position: absolute; right: 0; top: 50px; background: var(--bg-primary); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 200px; z-index: 1001;">
                            <a href="profile.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                Profils
                            </a>
                            <a href="settings.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                Opcijas
                            </a>
                            <a href="logout.php" style="display: block; padding: 12px 16px; color: var(--danger-color); text-decoration: none;">
                                Iziet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="content">
                <!-- Assignment Header -->
                <div class="card" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                        <div>
                            <h1 style="font-size: 28px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                                <?php echo htmlspecialchars($assignment_data['title']); ?>
                            </h1>
                            <p style="color: var(--text-secondary); margin-bottom: 16px;">
                                <a href="class.php?id=<?php echo $assignment_data['class_id']; ?>" style="color: var(--primary-color); text-decoration: none;">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </a>
                            </p>
                        </div>
                        
                        <div style="text-align: right;">
                            <div style="font-size: 24px; font-weight: 600; color: var(--primary-color); margin-bottom: 4px;">
                                <?php echo $assignment_data['max_points']; ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Points</div>
                            
                            <?php if ($current_user['role'] === 'student' && $submission && $submission['grade'] !== null): ?>
                                <div style="margin-top: 12px; padding: 8px 16px; background: var(--success-color); color: white; border-radius: 20px; font-weight: 500;">
                                    Your Grade: <?php echo $submission['grade']; ?>/<?php echo $assignment_data['max_points']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 24px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <div>
                            <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 4px;">Due Date</div>
                            <div style="font-weight: 500;">
                                <?php echo $assignment_data['due_date'] ? date('M j, Y g:i A', strtotime($assignment_data['due_date'])) : 'No due date'; ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 4px;">Status</div>
                            <div style="font-weight: 500;">
                                <?php if ($current_user['role'] === 'student'): ?>
                                    <?php if ($submission): ?>
                                        <?php if ($submission['grade'] !== null): ?>
                                            <span style="color: var(--success-color);">✅ Graded</span>
                                        <?php else: ?>
                                            <span style="color: var(--primary-color);">📤 Submitted</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: var(--warning-color);">⏳ Not submitted</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--primary-color);">
                                        <?php echo count($submissions); ?>/<?php echo count($classroom->getClassMembers($assignment_data['class_id'])); ?> submitted
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 24px;">
                    <!-- Main Content -->
                    <div style="grid-column: span 2;">
                        <!-- Assignment Description -->
                        <div class="card" style="margin-bottom: 24px;">
                            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">Instructions</h2>
                            <div style="color: var(--text-primary); line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($assignment_data['description'])); ?>
                            </div>
                            
                            <?php if (!empty($assignment_files)): ?>
                                <h3 style="font-size: 16px; margin-top: 20px; margin-bottom: 12px;">Attachments</h3>
                                <?php foreach ($assignment_files as $file): ?>
                                    <div class="file-item">
                                        <span class="file-icon">📄</span>
                                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                        <span class="file-size"><?php echo round($file['file_size'] / 1024, 1); ?> KB</span>
                                        <a href="<?php echo $file['file_path']; ?>" class="btn btn-secondary btn-sm" download>Download</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if ($current_user['role'] === 'student'): ?>
                            <!-- Student Submission -->
                            <div class="card">
                                <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">Your Work</h2>
                                
                                <?php if ($submission): ?>
                                    <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div>
                                                <div style="font-weight: 500; color: var(--text-primary);">Submitted</div>
                                                <div style="font-size: 14px; color: var(--text-secondary);">
                                                    <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                                </div>
                                            </div>
                                            <?php if ($submission['grade'] !== null): ?>
                                                <div style="text-align: right;">
                                                    <div style="font-size: 24px; font-weight: 600; color: var(--success-color);">
                                                        <?php echo $submission['grade']; ?>/<?php echo $assignment_data['max_points']; ?>
                                                    </div>
                                                    <div style="font-size: 14px; color: var(--text-secondary);">Grade</div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (!empty($submission_files)): ?>
                                            <div style="margin-top: 12px;">
                                                <div style="font-weight: 500; margin-bottom: 8px;">Submitted Files:</div>
                                                <?php foreach ($submission_files as $file): ?>
                                                    <div class="file-item">
                                                        <span class="file-icon">📄</span>
                                                        <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                                        <span class="file-size"><?php echo round($file['file_size'] / 1024, 1); ?> KB</span>
                                                        <a href="<?php echo $file['file_path']; ?>" class="btn btn-secondary btn-sm" download>Download</a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($submission['feedback']): ?>
                                            <div style="margin-top: 12px; padding: 12px; background: var(--bg-primary); border-radius: 6px; border-left: 4px solid var(--success-color);">
                                                <div style="font-weight: 500; margin-bottom: 4px;">Teacher Feedback:</div>
                                                <div style="color: var(--text-primary); line-height: 1.5;">
                                                    <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <?php echo $submission ? 'Resubmit Work' : 'Submit Your Work'; ?>
                                        </label>
                                        <div class="file-upload" id="dropZone">
                                            <div style="font-size: 48px; margin-bottom: 16px;">📤</div>
                                            <p style="color: var(--text-primary); margin-bottom: 8px; font-weight: 500;">
                                                Drop files here or click to upload
                                            </p>
                                            <p style="color: var(--text-secondary); font-size: 14px;">
                                                Support for PDF, DOC, DOCX, TXT, images, ZIP (max 10MB each)
                                            </p>
                                            <input type="file" id="fileInput" name="submission_files[]" multiple 
                                                   accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar"
                                                   style="display: none;">
                                            <button type="button" onclick="document.getElementById('fileInput').click()" 
                                                    class="btn btn-secondary" style="margin-top: 12px;">
                                                Choose Files
                                            </button>
                                        </div>
                                        <div class="file-list" id="fileList"></div>
                                    </div>

                                    <button type="submit" name="submit_assignment" class="btn btn-primary">
                                        <span>📤</span> <?php echo $submission ? 'Resubmit' : 'Submit'; ?>
                                    </button>
                                </form>
                            </div>

                        <?php else: ?>
                            <!-- Teacher View - Submissions -->
                            <div class="card">
                                <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 16px;">Student Submissions</h2>
                                
                                <?php if (empty($submissions)): ?>
                                    <p style="color: var(--text-secondary); text-align: center; padding: 40px;">
                                        No submissions yet
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <div class="assignment-card">
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                                <div style="flex: 1;">
                                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                                        <img src="<?php echo $sub['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                                             alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                                        <div>
                                                            <h3 style="font-size: 16px; font-weight: 500; margin: 0;">
                                                                <?php echo htmlspecialchars($sub['first_name'] . ' ' . $sub['last_name']); ?>
                                                            </h3>
                                                            <p style="font-size: 14px; color: var(--text-secondary); margin: 0;">
                                                                Submitted <?php echo formatDate($sub['submitted_at']); ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php 
                                                    $sub_files = $assignment->getSubmissionFiles($sub['id']);
                                                    if (!empty($sub_files)): 
                                                    ?>
                                                        <div style="margin: 12px 0;">
                                                            <div style="font-weight: 500; margin-bottom: 8px;">Files:</div>
                                                            <?php foreach ($sub_files as $file): ?>
                                                                <div class="file-item" style="display: inline-flex; margin-right: 8px; margin-bottom: 4px;">
                                                                    <span class="file-icon">📄</span>
                                                                    <span class="file-name"><?php echo htmlspecialchars($file['file_name']); ?></span>
                                                                    <a href="<?php echo $file['file_path']; ?>" class="btn btn-secondary btn-sm" download>Download</a>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($sub['feedback']): ?>
                                                        <div style="margin-top: 12px; padding: 12px; background: var(--bg-secondary); border-radius: 6px;">
                                                            <div style="font-weight: 500; margin-bottom: 4px;">Your Feedback:</div>
                                                            <div style="color: var(--text-primary); line-height: 1.5;">
                                                                <?php echo nl2br(htmlspecialchars($sub['feedback'])); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div style="text-align: right;">
                                                    <?php if ($sub['grade'] !== null): ?>
                                                        <div style="font-size: 24px; font-weight: 600; color: var(--success-color); margin-bottom: 4px;">
                                                            <?php echo $sub['grade']; ?>/<?php echo $assignment_data['max_points']; ?>
                                                        </div>
                                                        <button onclick="editGrade(<?php echo $sub['id']; ?>, <?php echo $sub['grade']; ?>, '<?php echo htmlspecialchars($sub['feedback']); ?>')" 
                                                                class="btn btn-secondary btn-sm">
                                                            Edit Grade
                                                        </button>
                                                    <?php else: ?>
                                                        <button onclick="showGradeForm(<?php echo $sub['id']; ?>)" 
                                                                class="btn btn-primary btn-sm">
                                                            Grade
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div>
                        <!-- Comments Section -->
                        <div class="card">
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Comments</h3>
                            
                            <?php if (empty($comments)): ?>
                                <p style="color: var(--text-secondary); text-align: center; padding: 20px; font-size: 14px;">
                                    No comments yet
                                </p>
                            <?php else: ?>
                                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 16px;">
                                    <?php foreach ($comments as $comment_item): ?>
                                        <div class="comment">
                                            <img src="<?php echo $comment_item['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                                 alt="Avatar" class="comment-avatar">
                                            <div class="comment-content">
                                                <div class="comment-header">
                                                    <span class="comment-author">
                                                        <?php echo htmlspecialchars($comment_item['first_name'] . ' ' . $comment_item['last_name']); ?>
                                                    </span>
                                                    <span class="comment-time"><?php echo formatDate($comment_item['created_at']); ?></span>
                                                </div>
                                                <div class="comment-text">
                                                    <?php echo nl2br(htmlspecialchars($comment_item['comment_text'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="form-group">
                                    <textarea name="comment_text" class="form-control" rows="3" 
                                              placeholder="Add a comment..." required></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="btn btn-primary btn-sm">
                                    Post Comment
                                </button>
                            </form>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card" style="margin-top: 20px;">
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Quick Actions</h3>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <a href="class.php?id=<?php echo $assignment_data['class_id']; ?>" class="btn btn-secondary" style="justify-content: center;">
                                    <span>🏠</span> Back to Class
                                </a>
                                <?php if ($current_user['role'] === 'teacher'): ?>
                                    <a href="create_assignment.php?class_id=<?php echo $assignment_data['class_id']; ?>" class="btn btn-secondary" style="justify-content: center;">
                                        <span>➕</span> Create Assignment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Grade Modal -->
    <div id="gradeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Grade Submission</h2>
                <button class="modal-close" onclick="modalManager.close('gradeModal')">×</button>
            </div>
            <form method="POST" id="gradeForm">
                <input type="hidden" name="submission_id" id="submission_id">
                <div class="form-group">
                    <label for="grade" class="form-label">Grade</label>
                    <input type="number" id="grade" name="grade" class="form-control" 
                           min="0" max="<?php echo $assignment_data['max_points']; ?>" required>
                    <small style="color: var(--text-tertiary); font-size: 12px;">
                        Out of <?php echo $assignment_data['max_points']; ?> points
                    </small>
                </div>
                <div class="form-group">
                    <label for="feedback" class="form-label">Feedback</label>
                    <textarea id="feedback" name="feedback" class="form-control" rows="4" 
                              placeholder="Provide feedback to the student..."></textarea>
                </div>
                <button type="submit" name="grade_submission" class="btn btn-primary" style="width: 100%;">
                    Submit Grade
                </button>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        // File upload management
        const fileUploadManager = new FileUploadManager('fileInput', 'dropZone');

        function showGradeForm(submissionId) {
            document.getElementById('submission_id').value = submissionId;
            document.getElementById('grade').value = '';
            document.getElementById('feedback').value = '';
            modalManager.open('gradeModal');
        }

        function editGrade(submissionId, currentGrade, currentFeedback) {
            document.getElementById('submission_id').value = submissionId;
            document.getElementById('grade').value = currentGrade;
            document.getElementById('feedback').value = currentFeedback || '';
            modalManager.open('gradeModal');
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('d-none');
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const avatar = document.querySelector('.user-avatar');
            
            if (!avatar.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('d-none');
            }
        });
    </script>
</body>
</html>