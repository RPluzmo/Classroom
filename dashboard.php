<?php
require_once 'includes/functions.php';
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
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Classroom Clone</title>
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
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <?php if ($current_user['role'] === 'admin'): ?>
                        <a href="admin/users.php" class="nav-link">Users</a>
                        <a href="admin/history.php" class="nav-link">History</a>
                    <?php elseif ($current_user['role'] === 'teacher'): ?>
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
        <?php if ($current_user['role'] === 'admin'): ?>
            <!-- Admin Dashboard -->
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Admin Dashboard</h1>
                            <p class="card-subtitle">System Overview</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-4" style="margin-bottom: 30px;">
                        <?php
                        $total_users = count($user->getAllUsers());
                        $total_classes = 0;
                        $total_assignments = 0;
                        
                        $conn = (new Database())->connect();
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM classes");
                        $total_classes = $stmt->fetch()['count'];
                        
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM assignments");
                        $total_assignments = $stmt->fetch()['count'];
                        ?>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--primary-color); margin-bottom: 8px;"><?php echo $total_users; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Total Users</div>
                        </div>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--secondary-color); margin-bottom: 8px;"><?php echo $total_classes; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Total Classes</div>
                        </div>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--warning-color); margin-bottom: 8px;"><?php echo $total_assignments; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Total Assignments</div>
                        </div>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--success-color); margin-bottom: 8px;"><?php echo count($action_history); ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Today's Actions</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2">
                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Recent Users</h2>
                            <?php if (empty($recent_users)): ?>
                                <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No users found</p>
                            <?php else: ?>
                                <?php foreach ($recent_users as $user_item): ?>
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; margin-bottom: 8px; background: var(--bg-secondary);">
                                        <img src="<?php echo $user_item['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                             alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                                        <div style="flex: 1;">
                                            <div style="font-weight: 500;"><?php echo htmlspecialchars($user_item['first_name'] . ' ' . $user_item['last_name']); ?></div>
                                            <div style="font-size: 12px; color: var(--text-secondary);"><?php echo getRoleDisplayName($user_item['role']); ?></div>
                                        </div>
                                        <div style="font-size: 12px; color: var(--text-tertiary);"><?php echo formatDate($user_item['created_at']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Recent Actions</h2>
                            <?php if (empty($action_history)): ?>
                                <p style="color: var(--text-secondary); text-align: center; padding: 20px;">No recent actions</p>
                            <?php else: ?>
                                <?php foreach ($action_history as $action): ?>
                                    <div style="padding: 12px; border-radius: 8px; margin-bottom: 8px; background: var(--bg-secondary);">
                                        <div style="font-weight: 500; margin-bottom: 4px;"><?php echo htmlspecialchars($action['first_name'] . ' ' . $action['last_name']); ?></div>
                                        <div style="font-size: 14px; color: var(--text-secondary);"><?php echo htmlspecialchars($action['action_description']); ?></div>
                                        <div style="font-size: 12px; color: var(--text-tertiary); margin-top: 4px;"><?php echo formatDate($action['created_at']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_user['role'] === 'teacher'): ?>
            <!-- Teacher Dashboard -->
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Teacher Dashboard</h1>
                            <p class="card-subtitle">Welcome back, <?php echo htmlspecialchars($current_user['first_name']); ?>!</p>
                        </div>
                        <a href="create_class.php" class="btn btn-primary">
                            <span>➕</span> Create Class
                        </a>
                    </div>

                    <div class="card">
                        <h2 style="font-size: 18px; margin-bottom: 16px;">My Classes</h2>
                        <?php if (empty($user_classes)): ?>
                            <div style="text-align: center; padding: 40px;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                                <h3 style="color: var(--text-primary); margin-bottom: 8px;">No classes yet</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 20px;">Create your first class to get started</p>
                                <a href="create_class.php" class="btn btn-primary">Create Class</a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-3">
                                <?php foreach ($user_classes as $class): ?>
                                    <div class="class-card" onclick="window.location.href='class.php?id=<?php echo $class['id']; ?>'">
                                        <div class="class-header"></div>
                                        <div class="class-info">
                                            <h3 class="class-name"><?php echo htmlspecialchars($class['name']); ?></h3>
                                            <p class="class-teacher"><?php echo $class['student_count']; ?> students</p>
                                            <span class="class-code"><?php echo htmlspecialchars($class['class_code']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($user_assignments)): ?>
                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Recent Assignments</h2>
                            <?php foreach ($user_assignments as $assign): ?>
                                <div class="assignment-card">
                                    <h3 class="assignment-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                                    <div class="assignment-meta">
                                        <span>📁 <?php echo htmlspecialchars($assign['title']); // This should be class name ?></span>
                                        <span>📅 <?php echo $assign['due_date'] ? date('M j, Y', strtotime($assign['due_date'])) : 'No due date'; ?></span>
                                        <span>✅ <?php echo $assign['graded_count']; ?>/<?php echo $assign['submission_count']; ?> graded</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Student Dashboard -->
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Student Dashboard</h1>
                            <p class="card-subtitle">Welcome back, <?php echo htmlspecialchars($current_user['first_name']); ?>!</p>
                        </div>
                        <a href="join_class.php" class="btn btn-primary">
                            <span>➕</span> Join Class
                        </a>
                    </div>

                    <div class="card">
                        <h2 style="font-size: 18px; margin-bottom: 16px;">My Classes</h2>
                        <?php if (empty($user_classes)): ?>
                            <div style="text-align: center; padding: 40px;">
                                <div style="font-size: 48px; margin-bottom: 16px;">🎓</div>
                                <h3 style="color: var(--text-primary); margin-bottom: 8px;">No classes yet</h3>
                                <p style="color: var(--text-secondary); margin-bottom: 20px;">Join a class to get started</p>
                                <a href="join_class.php" class="btn btn-primary">Join Class</a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-3">
                                <?php foreach ($user_classes as $class): ?>
                                    <div class="class-card" onclick="window.location.href='class.php?id=<?php echo $class['id']; ?>'">
                                        <div class="class-header"></div>
                                        <div class="class-info">
                                            <h3 class="class-name"><?php echo htmlspecialchars($class['name']); ?></h3>
                                            <p class="class-teacher"><?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($user_assignments)): ?>
                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Recent Assignments</h2>
                            <?php foreach ($user_assignments as $assign): ?>
                                <div class="assignment-card">
                                    <h3 class="assignment-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                                    <div class="assignment-meta">
                                        <span>📁 <?php echo htmlspecialchars($assign['class_name']); ?></span>
                                        <span>📅 <?php echo $assign['due_date'] ? date('M j, Y', strtotime($assign['due_date'])) : 'No due date'; ?></span>
                                        <?php if ($assign['submission']): ?>
                                            <?php if ($assign['submission']['grade'] !== null): ?>
                                                <span class="assignment-status status-graded">Grade: <?php echo $assign['submission']['grade']; ?></span>
                                            <?php else: ?>
                                                <span class="assignment-status status-submitted">Submitted</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="assignment-status status-pending">Pending</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
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