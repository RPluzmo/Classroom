<?php
require_once 'includes/functions.php';
requireAuth();

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($email)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else {
            if ($user->updateProfile($current_user['id'], $first_name, $last_name, $email)) {
                $success = 'Profile updated successfully!';
                $current_user = $user->getCurrentUser(); // Refresh user data
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    } elseif (isset($_POST['upload_avatar'])) {
        if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowed_types)) {
                $error = 'Invalid file type. Please upload JPG, PNG, or GIF image.';
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                $error = 'File too large. Maximum size is 5MB.';
            } else {
                $uploaded_file = handleFileUpload($file, 'uploads/avatars/');
                if ($uploaded_file) {
                    if ($user->updateProfilePicture($current_user['id'], $uploaded_file['path'])) {
                        $success = 'Profile picture updated successfully!';
                        $current_user = $user->getCurrentUser(); // Refresh user data
                    } else {
                        $error = 'Failed to update profile picture.';
                    }
                } else {
                    $error = 'Failed to upload file.';
                }
            }
        } else {
            $error = 'Please select a file to upload.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Classroom Clone</title>
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
                    <?php if ($current_user['role'] === 'teacher'): ?>
                        <a href="classes.php" class="nav-link">My Classes</a>
                        <a href="create_class.php" class="nav-link">Create Class</a>
                    <?php elseif ($current_user['role'] === 'student'): ?>
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
            <div class="content" style="max-width: 800px; margin: 0 auto;">
                <div class="card-header">
                    <div>
                        <h1 class="card-title">My Profile</h1>
                        <p class="card-subtitle">Manage your account information and preferences</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-3" style="gap: 24px;">
                    <!-- Profile Picture Section -->
                    <div>
                        <div class="card">
                            <h3 style="font-size: 16px; margin-bottom: 16px; text-align: center;">Profile Picture</h3>
                            
                            <div style="text-align: center; margin-bottom: 20px;">
                                <img src="<?php echo $current_user['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                     alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-color); margin-bottom: 12px;">
                                <div style="font-weight: 500; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                                </div>
                                <div style="font-size: 14px; color: var(--text-secondary); text-transform: capitalize;">
                                    <?php echo getRoleDisplayName($current_user['role']); ?>
                                </div>
                            </div>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="avatar" class="form-label" style="text-align: center; display: block;">Update Picture</label>
                                    <input type="file" id="avatar" name="avatar" class="form-control" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif"
                                           onchange="previewAvatar(this)">
                                </div>
                                <button type="submit" name="upload_avatar" class="btn btn-primary" style="width: 100%;">
                                    <span>📷</span> Upload
                                </button>
                            </form>
                        </div>

                        <!-- Account Info -->
                        <div class="card" style="margin-top: 20px;">
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Account Info</h3>
                            <div style="font-size: 14px; color: var(--text-secondary);">
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Username:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($current_user['username']); ?></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Role:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo getRoleDisplayName($current_user['role']); ?></span>
                                </div>
                                <div>
                                    <span style="color: var(--text-tertiary);">Member Since:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;">
                                        <?php echo date('F j, Y', strtotime($current_user['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information Section -->
                    <div style="grid-column: span 2;">
                        <div class="card">
                            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;">Profile Information</h2>
                            
                            <form method="POST" id="profileForm">
                                <div class="grid grid-cols-2" style="gap: 16px;">
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($current_user['first_name']); ?>" 
                                               required maxlength="50">
                                    </div>

                                    <div class="form-group">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($current_user['last_name']); ?>" 
                                               required maxlength="50">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($current_user['email']); ?>" 
                                           required maxlength="100">
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <span>💾</span> Save Changes
                                </button>
                            </form>
                        </div>

                        <!-- Statistics -->
                        <?php if ($current_user['role'] === 'teacher'): ?>
                            <?php
                            $teacher_classes = $classroom->getTeacherClasses($current_user['id']);
                            $total_students = 0;
                            $total_assignments = 0;
                            
                            foreach ($teacher_classes as $class) {
                                $total_students += $class['student_count'];
                                $class_assignments = $assignment->getAssignmentsByClass($class['id']);
                                $total_assignments += count($class_assignments);
                            }
                            ?>
                            
                            <div class="card" style="margin-top: 20px;">
                                <h3 style="font-size: 18px; margin-bottom: 16px;">Teaching Statistics</h3>
                                <div class="grid grid-cols-3" style="gap: 16px;">
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--primary-color); margin-bottom: 4px;">
                                            <?php echo count($teacher_classes); ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Classes</div>
                                    </div>
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--secondary-color); margin-bottom: 4px;">
                                            <?php echo $total_students; ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Students</div>
                                    </div>
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--warning-color); margin-bottom: 4px;">
                                            <?php echo $total_assignments; ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Assignments</div>
                                    </div>
                                </div>
                            </div>

                        <?php elseif ($current_user['role'] === 'student'): ?>
                            <?php
                            $student_classes = $classroom->getStudentClasses($current_user['id']);
                            $total_assignments = 0;
                            $completed_assignments = 0;
                            
                            foreach ($student_classes as $class) {
                                $class_assignments = $assignment->getAssignmentsByClass($class['id']);
                                foreach ($class_assignments as $assign) {
                                    $total_assignments++;
                                    $submission = $assignment->getSubmission($assign['id'], $current_user['id']);
                                    if ($submission) {
                                        $completed_assignments++;
                                    }
                                }
                            }
                            ?>
                            
                            <div class="card" style="margin-top: 20px;">
                                <h3 style="font-size: 18px; margin-bottom: 16px;">Learning Statistics</h3>
                                <div class="grid grid-cols-3" style="gap: 16px;">
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--primary-color); margin-bottom: 4px;">
                                            <?php echo count($student_classes); ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Classes</div>
                                    </div>
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--secondary-color); margin-bottom: 4px;">
                                            <?php echo $completed_assignments; ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Completed</div>
                                    </div>
                                    <div style="text-align: center; padding: 16px; background: var(--bg-secondary); border-radius: 8px;">
                                        <div style="font-size: 24px; font-weight: 600; color: var(--warning-color); margin-bottom: 4px;">
                                            <?php echo $total_assignments - $completed_assignments; ?>
                                        </div>
                                        <div style="font-size: 14px; color: var(--text-secondary);">Pending</div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Actions -->
                        <div class="card" style="margin-top: 20px;">
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Quick Actions</h3>
                            <div class="grid grid-cols-2" style="gap: 12px;">
                                <a href="settings.php" class="btn btn-secondary" style="justify-content: center;">
                                    <span>⚙️</span> Settings
                                </a>
                                <a href="dashboard.php" class="btn btn-secondary" style="justify-content: center;">
                                    <span>🏠</span> Dashboard
                                </a>
                                <?php if ($current_user['role'] === 'teacher'): ?>
                                    <a href="create_class.php" class="btn btn-secondary" style="justify-content: center;">
                                        <span>➕</span> Create Class
                                    </a>
                                    <a href="classes.php" class="btn btn-secondary" style="justify-content: center;">
                                        <span>📚</span> My Classes
                                    </a>
                                <?php elseif ($current_user['role'] === 'student'): ?>
                                    <a href="join_class.php" class="btn btn-secondary" style="justify-content: center;">
                                        <span>➕</span> Join Class
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('profileForm');
        validator.addRule('first_name', [
            { type: 'required', message: 'First name is required' },
            { type: 'minLength', value: 2, message: 'First name must be at least 2 characters' }
        ]);
        validator.addRule('last_name', [
            { type: 'required', message: 'Last name is required' },
            { type: 'minLength', value: 2, message: 'Last name must be at least 2 characters' }
        ]);
        validator.addRule('email', [
            { type: 'required', message: 'Email is required' },
            { type: 'email', message: 'Please enter a valid email address' }
        ]);

        // Avatar preview
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const avatarImg = document.querySelector('.card img');
                    if (avatarImg) {
                        avatarImg.src = e.target.result;
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
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