<?php
require_once 'includes/functions.php';
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
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($class['name']); ?> - Classroom Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <?php if ($current_user['role'] === 'teacher'): ?>
                        <a href="classes.php" class="nav-link">My Classes</a>
                        <a href="create_class.php" class="nav-link">Create Class</a>
                    <?php else: ?>
                        <a href="join_class.php" class="nav-link">Join Class</a>
                    <?php endif; ?>
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
            <div class="content">
                <!-- Class Header -->
                <div class="card" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                        <div>
                            <h1 style="font-size: 28px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                                <?php echo htmlspecialchars($class['name']); ?>
                            </h1>
                            <p style="color: var(--text-secondary); margin-bottom: 16px;">
                                <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?>
                            </p>
                            <?php if ($class['description']): ?>
                                <p style="color: var(--text-primary); line-height: 1.6;"><?php echo htmlspecialchars($class['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">
                            <?php if ($current_user['role'] === 'teacher'): ?>
                                <button onclick="showQRCode()" class="btn btn-secondary">
                                    <span>📱</span> Show QR Code
                                </button>
                                <button onclick="copyClassCode()" class="btn btn-secondary copy-btn" data-copy="<?php echo htmlspecialchars($class['class_code']); ?>">
                                    <span>📋</span> Copy Code
                                </button>
                                <a href="create_assignment.php?class_id=<?php echo $class_id; ?>" class="btn btn-primary">
                                    <span>➕</span> Create Assignment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 24px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <div>
                            <div style="font-size: 24px; font-weight: 600; color: var(--primary-color);">
                                <?php echo count($class_members); ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Students</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 600; color: var(--secondary-color);">
                                <?php echo count($assignments); ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Assignments</div>
                        </div>
                        <div>
                            <div style="font-size: 24px; font-weight: 600; color: var(--warning-color);">
                                <?php 
                                $pending_count = 0;
                                if ($current_user['role'] === 'student') {
                                    foreach ($assignments as $assign) {
                                        $submission = $assignment->getSubmission($assign['id'], $current_user['id']);
                                        if (!$submission) $pending_count++;
                                    }
                                } else {
                                    foreach ($assignments as $assign) {
                                        $pending_count += $assign['submission_count'] - $assign['graded_count'];
                                    }
                                }
                                echo $pending_count;
                                ?>
                            </div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Pending</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 24px;">
                    <!-- Main Content -->
                    <div style="grid-column: span 2;">
                        <!-- Assignments Section -->
                        <div class="card">
                            <div class="card-header">
                                <h2 style="font-size: 20px; font-weight: 600;">Assignments</h2>
                                <?php if ($current_user['role'] === 'teacher'): ?>
                                    <a href="create_assignment.php?class_id=<?php echo $class_id; ?>" class="btn btn-primary btn-sm">
                                        <span>➕</span> Create
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($assignments)): ?>
                                <div style="text-align: center; padding: 40px;">
                                    <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                                    <h3 style="color: var(--text-primary); margin-bottom: 8px;">No assignments yet</h3>
                                    <p style="color: var(--text-secondary);">
                                        <?php if ($current_user['role'] === 'teacher'): ?>
                                            Create your first assignment to get started
                                        <?php else: ?>
                                            Your teacher will add assignments here soon
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($assignments as $assign): ?>
                                    <?php 
                                    $submission = null;
                                    if ($current_user['role'] === 'student') {
                                        $submission = $assignment->getSubmission($assign['id'], $current_user['id']);
                                    }
                                    ?>
                                    <div class="assignment-card" style="cursor: pointer;" onclick="window.location.href='assignment.php?id=<?php echo $assign['id']; ?>'">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                            <div style="flex: 1;">
                                                <h3 class="assignment-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                                                <p style="color: var(--text-secondary); font-size: 14px; margin: 8px 0;">
                                                    <?php 
                                                    $description = strip_tags($assign['description']);
                                                    echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                                    ?>
                                                </p>
                                            </div>
                                            <div style="text-align: right;">
                                                <?php if ($current_user['role'] === 'student'): ?>
                                                    <?php if ($submission): ?>
                                                        <?php if ($submission['grade'] !== null): ?>
                                                            <span class="assignment-status status-graded">Grade: <?php echo $submission['grade']; ?>/<?php echo $assign['max_points']; ?></span>
                                                        <?php else: ?>
                                                            <span class="assignment-status status-submitted">Submitted</span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="assignment-status status-pending">Not submitted</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span style="color: var(--text-secondary); font-size: 14px;">
                                                        <?php echo $assign['submission_count']; ?>/<?php echo count($class_members); ?> submitted
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="assignment-meta" style="margin-top: 12px;">
                                            <span>📅 Due: <?php echo $assign['due_date'] ? date('M j, Y g:i A', strtotime($assign['due_date'])) : 'No due date'; ?></span>
                                            <span>💯 Max points: <?php echo $assign['max_points']; ?></span>
                                            <?php if ($current_user['role'] === 'teacher' && $assign['submission_count'] > 0): ?>
                                                <span>✅ Graded: <?php echo $assign['graded_count']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div>
                        <!-- Class Code (Student View) -->
                        <?php if ($current_user['role'] === 'student'): ?>
                            <div class="card" style="margin-bottom: 20px;">
                                <h3 style="font-size: 16px; margin-bottom: 12px;">Class Information</h3>
                                <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px; text-align: center;">
                                    <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 4px;">Class Code</div>
                                    <div style="font-size: 20px; font-weight: 600; color: var(--primary-color); letter-spacing: 2px;">
                                        <?php echo htmlspecialchars($class['class_code']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Class Members -->
                        <?php if ($current_user['role'] === 'teacher' || count($class_members) <= 10): ?>
                            <div class="card">
                                <h3 style="font-size: 16px; margin-bottom: 12px;">
                                    <?php echo $current_user['role'] === 'teacher' ? 'Class Members' : 'Classmates'; ?>
                                </h3>
                                <?php if (empty($class_members)): ?>
                                    <p style="color: var(--text-secondary); text-align: center; padding: 20px;">
                                        Neviens nav pievienojies šim kursam
                                    </p>
                                <?php else: ?>
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        <?php foreach ($class_members as $member): ?>
                                            <div style="display: flex; align-items: center; gap: 8px; padding: 8px; border-radius: 6px; margin-bottom: 4px;">
                                                <img src="<?php echo $member['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                                     alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                                <div style="flex: 1; min-width: 0;">
                                                    <div style="font-size: 14px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                                    </div>
                                                    <div style="font-size: 12px; color: var(--text-secondary);">
                                                        Joined <?php echo formatDate($member['joined_at']); ?>
                                                    </div>
                                                </div>
                                                <?php if ($current_user['role'] === 'teacher'): ?>
                                                    <button onclick="removeStudent(<?php echo $member['id']; ?>)" class="btn btn-icon btn-sm" style="color: var(--danger-color);" title="Remove student">
                                                        ×
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($current_user['role'] === 'teacher' && count($class_members) > 10): ?>
                                        <div style="text-align: center; margin-top: 12px;">
                                            <a href="class_members.php?id=<?php echo $class_id; ?>" class="btn btn-secondary btn-sm">
                                                View All Members
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>


                        <div class="card" style="margin-top: 20px;">
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Quick Actions</h3>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <?php if ($current_user['role'] === 'teacher'): ?>
                                    <a href="create_assignment.php?class_id=<?php echo $class_id; ?>" class="btn btn-secondary" style="justify-content: center;">
                                        <span>➕</span> Create Assignment
                                    </a>
                                    <button onclick="showQRCode()" class="btn btn-secondary" style="justify-content: center;">
                                        <span>📱</span> Show QR Code
                                    </button>
                                <?php else: ?>
                                    <a href="profile.php" class="btn btn-secondary" style="justify-content: center;">
                                        <span>👤</span> My Profile
                                    </a>
                                <?php endif; ?>
                                <a href="dashboard.php" class="btn btn-secondary" style="justify-content: center;">
                                   
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- QR Code Modal -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Join Class</h2>
                <button class="modal-close" onclick="modalManager.close('qrModal')">×</button>
            </div>
            <div class="qr-code-container">
                <div style="text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 16px;">📱</div>
                    <p style="color: var(--text-secondary); margin-bottom: 16px;">
                        Students can scan this QR code or use the class code below to join
                    </p>
                    <div style="background: white; padding: 20px; border-radius: 8px; display: inline-block; margin-bottom: 16px;">
                        <code style="font-size: 24px; font-weight: bold; color: var(--primary-color); letter-spacing: 2px;">
                            <?php echo htmlspecialchars($class['class_code']); ?>
                        </code>
                    </div>
                    <div>
                        <button onclick="copyClassCode()" class="btn btn-primary copy-btn" data-copy="<?php echo htmlspecialchars($class['class_code']); ?>">
                            <span>📋</span> Copy Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function showQRCode() {
            QRCodeManager.generateClassCodeModal('<?php echo htmlspecialchars($class['class_code']); ?>', '<?php echo htmlspecialchars($class['name']); ?>');
        }

        function copyClassCode() {
            const code = '<?php echo htmlspecialchars($class['class_code']); ?>';
            utils.copyToClipboard(code);
        }

        function removeStudent(studentId) {
            utils.confirmAction('Are you sure you want to remove this student from the class?', () => {
                window.location.href = 'db/remove_student.php?class_id=<?php echo $class_id; ?>&student_id=' + studentId;
            });
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