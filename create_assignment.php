<?php
require_once 'includes/functions.php';
requireRole('teacher');

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    header('Location: dashboard.php');
    exit();
}

$class = $classroom->getClassById($class_id);
if (!$class || $class['teacher_id'] != $user->getCurrentUser()['id']) {
    setFlashMessage('error', 'Class not found or access denied');
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
            
            setFlashMessage('success', 'Assignment created successfully!');
            header('Location: assignment.php?id=' . $assignment_id);
            exit();
        } else {
            $error = 'Failed to create assignment. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment - Classroom Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">
                    <div class="logo-icon">📚</div>
                    <span>Classroom</span>
                </a>

                <nav class="nav-menu">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="classes.php" class="nav-link">My Classes</a>
                    <a href="create_class.php" class="nav-link">Create Class</a>
                </nav>

                <div class="user-menu">
                    <button class="theme-toggle" title="Toggle theme">
                        <?php echo $_SESSION['dark_theme'] ? '☀️' : '🌙'; ?>
                    </button>
                    
                    <div style="position: relative;">
                        <img src="<?php echo $current_user['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                             alt="Profile" class="user-avatar" onclick="toggleUserMenu()">
                        
                        <div id="userMenu" class="d-none" style="position: absolute; right: 0; top: 50px; background: var(--bg-primary); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 200px; z-index: 1001;">
                            <a href="profile.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                👤 Profile
                            </a>
                            <a href="settings.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                ⚙️ Settings
                            </a>
                            <a href="logout.php" style="display: block; padding: 12px 16px; color: var(--danger-color); text-decoration: none;">
                                🚪 Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="content" style="max-width: 800px; margin: 0 auto;">
                <!-- Breadcrumb -->
                <div style="margin-bottom: 20px;">
                    <a href="class.php?id=<?php echo $class_id; ?>" style="color: var(--text-secondary); text-decoration: none;">
                        ← Back to <?php echo htmlspecialchars($class['name']); ?>
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Create Assignment</h1>
                            <p class="card-subtitle">
                                <?php echo htmlspecialchars($class['name']); ?> • <?php echo $classroom->getClassMembers($class_id) ? count($classroom->getClassMembers($class_id)) : 0; ?> students
                            </p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="createAssignmentForm">
                        <div class="form-group">
                            <label for="title" class="form-label">Assignment Title *</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   required maxlength="200" placeholder="e.g., Chapter 5 Quiz, Essay Assignment">
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" 
                                      rows="6" placeholder="Provide detailed instructions for this assignment..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="datetime-local" id="due_date" name="due_date" class="form-control" 
                                       value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>"
                                       min="<?php echo date('Y-m-d\TH:i'); ?>">
                                <small style="color: var(--text-tertiary); font-size: 12px;">
                                    Leave empty for no due date
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="max_points" class="form-label">Maximum Points</label>
                                <input type="number" id="max_points" name="max_points" class="form-control" 
                                       value="<?php echo isset($_POST['max_points']) ? htmlspecialchars($_POST['max_points']) : '100'; ?>" 
                                       min="1" max="1000" required>
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="form-group">
                            <label class="form-label">Attachments</label>
                            <div class="file-upload" id="dropZone">
                                <div style="font-size: 48px; margin-bottom: 16px;">📎</div>
                                <p style="color: var(--text-primary); margin-bottom: 8px; font-weight: 500;">
                                    Drop files here or click to upload
                                </p>
                                <p style="color: var(--text-secondary); font-size: 14px;">
                                    Support for PDF, DOC, DOCX, TXT, images, ZIP (max 10MB each)
                                </p>
                                <input type="file" id="fileInput" name="attachments[]" multiple 
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar"
                                       style="display: none;">
                                <button type="button" onclick="document.getElementById('fileInput').click()" 
                                        class="btn btn-secondary" style="margin-top: 12px;">
                                    Choose Files
                                </button>
                            </div>
                            <div class="file-list" id="fileList"></div>
                        </div>

                        <!-- Assignment Settings -->
                        <div style="background: var(--bg-secondary); padding: 20px; border-radius: 8px; margin: 24px 0;">
                            <h3 style="font-size: 16px; margin-bottom: 16px; color: var(--text-primary);">
                                ⚙️ Assignment Settings
                            </h3>
                            <div class="grid grid-cols-2" style="gap: 16px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="allow_late_submission" checked>
                                    <span style="color: var(--text-primary); font-size: 14px;">Allow late submissions</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                    <input type="checkbox" name="send_notification" checked>
                                    <span style="color: var(--text-primary); font-size: 14px;">Notify students</span>
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <span>✨</span> Create Assignment
                            </button>
                            <a href="class.php?id=<?php echo $class_id; ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tips Section -->
                <div class="card" style="margin-top: 20px;">
                    <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--text-primary);">
                        💡 Tips for effective assignments
                    </h3>
                    <div class="grid grid-cols-2" style="gap: 16px;">
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px;">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                📝 Clear Instructions
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Provide step-by-step instructions and expected outcomes
                            </p>
                        </div>
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px;">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                📅 Realistic Deadlines
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Give students enough time to complete the work thoughtfully
                            </p>
                        </div>
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px;">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                📎 Helpful Resources
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Attach reference materials, templates, or examples
                            </p>
                        </div>
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px;">
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                💯 Clear Grading
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Explain how the assignment will be evaluated
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('createAssignmentForm');
        validator.addRule('title', [
            { type: 'required', message: 'Assignment title is required' },
            { type: 'minLength', value: 3, message: 'Title must be at least 3 characters' },
            { type: 'maxLength', value: 200, message: 'Title must not exceed 200 characters' }
        ]);

        // File upload management
        const fileUploadManager = new FileUploadManager('fileInput', 'dropZone');

        // Character counter for title
        const titleInput = document.getElementById('title');
        titleInput.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = 200;
            const remaining = maxLength - currentLength;
            
            let counter = this.parentNode.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.color = 'var(--text-tertiary)';
                counter.style.fontSize = '12px';
                counter.style.marginTop = '4px';
                counter.style.display = 'block';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (remaining < 20) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-tertiary)';
            }
        });

        // Initialize character counter
        const titleLength = titleInput.value.length;
        if (titleLength > 0) {
            titleInput.dispatchEvent(new Event('input'));
        }

        // Set minimum due date to current time
        const dueDateInput = document.getElementById('due_date');
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        dueDateInput.min = now.toISOString().slice(0, 16);

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