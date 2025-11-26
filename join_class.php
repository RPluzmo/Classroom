<?php
require_once 'includes/functions.php';
requireRole('student');

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_code = sanitize($_POST['class_code'] ?? '');
    
    if (empty($class_code)) {
        $error = 'Class code is required';
    } else {
        if ($classroom->joinClass($current_user['id'], $class_code)) {
            setFlashMessage('success', 'Successfully joined the class!');
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid class code or you are already enrolled in this class';
        }
    }
}

// Handle QR code join (GET parameter)
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $class_code = sanitize($_GET['code']);
    if ($classroom->joinClass($current_user['id'], $class_code)) {
        setFlashMessage('success', 'Successfully joined the class!');
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid class code or you are already enrolled in this class';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Class - Classroom Clone</title>
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
                    <a href="join_class.php" class="nav-link active">Join Class</a>
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
            <div class="content" style="max-width: 600px; margin: 0 auto;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Join a Class</h1>
                            <p class="card-subtitle">Enter the class code provided by your teacher</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (getFlashMessage('success')): ?>
                        <div class="alert alert-success">
                            <?php echo getFlashMessage('success'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="joinClassForm">
                        <div class="form-group">
                            <label for="class_code" class="form-label">Class Code *</label>
                            <input type="text" id="class_code" name="class_code" class="form-control" 
                                   value="<?php echo isset($_POST['class_code']) ? htmlspecialchars($_POST['class_code']) : (isset($_GET['code']) ? htmlspecialchars($_GET['code']) : ''); ?>" 
                                   required maxlength="10" 
                                   style="text-transform: uppercase; font-size: 18px; text-align: center; letter-spacing: 2px; font-weight: 600;"
                                   placeholder="ABC123">
                            <small style="color: var(--text-tertiary); font-size: 12px;">
                                Enter the 6-character class code
                            </small>
                        </div>

                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                            <h3 style="font-size: 16px; margin-bottom: 12px; color: var(--text-primary);">
                                🔍 How to get a class code?
                            </h3>
                            <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                                <li>Ask your teacher for the class code</li>
                                <li>Scan the QR code provided by your teacher</li>
                                <li>Check your email for an invitation with the code</li>
                                <li>Look for the code on classroom materials or syllabus</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <span>🚀</span> Join Class
                        </button>
                    </form>
                </div>

                <!-- QR Scanner Section -->
                <div class="card" style="margin-top: 20px;">
                    <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--text-primary);">
                        📱 Alternative Ways to Join
                    </h3>
                    <div class="grid grid-cols-2" style="gap: 16px;">
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 32px; margin-bottom: 12px;">📷</div>
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                Scan QR Code
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Scan your teacher's QR code to automatically join the class
                            </p>
                        </div>
                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; text-align: center;">
                            <div style="font-size: 32px; margin-bottom: 12px;">🔗</div>
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--primary-color);">
                                Direct Link
                            </h4>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                                Click on the invitation link sent by your teacher
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Classes Preview -->
                <?php 
                $recent_classes = $classroom->getStudentClasses($current_user['id']);
                if (!empty($recent_classes)): 
                ?>
                    <div class="card" style="margin-top: 20px;">
                        <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--text-primary);">
                            📚 My Classes
                        </h3>
                        <div class="grid grid-cols-2" style="gap: 12px;">
                            <?php foreach (array_slice($recent_classes, 0, 4) as $class): ?>
                                <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px;">
                                    <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 4px; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </h4>
                                    <p style="font-size: 12px; color: var(--text-secondary); margin: 0;">
                                        <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($recent_classes) > 4): ?>
                            <div style="text-align: center; margin-top: 12px;">
                                <a href="dashboard.php" class="btn btn-secondary btn-sm">
                                    View All Classes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('joinClassForm');
        validator.addRule('class_code', [
            { type: 'required', message: 'Class code is required' },
            { type: 'minLength', value: 6, message: 'Class code must be at least 6 characters' },
            { type: 'maxLength', value: 10, message: 'Class code must not exceed 10 characters' },
            { type: 'pattern', value: '^[A-Z0-9]+$', message: 'Class code can only contain letters and numbers' }
        ]);

        // Auto-format class code
        const classCodeInput = document.getElementById('class_code');
        classCodeInput.addEventListener('input', function(e) {
            // Convert to uppercase and remove invalid characters
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Limit to 6 characters
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });

        // Handle paste events
        classCodeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let pastedData = (e.clipboardData || window.clipboardData).getData('text');
            pastedData = pastedData.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Insert at cursor position
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            const newValue = currentValue.substring(0, start) + pastedData + currentValue.substring(end);
            
            // Limit to 6 characters
            this.value = newValue.substring(0, 6);
            
            // Move cursor to end of pasted text
            const newCursorPos = Math.min(start + pastedData.length, 6);
            this.setSelectionRange(newCursorPos, newCursorPos);
        });

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

        // Simulate QR code scanner (in production, you'd use a proper QR scanning library)
        function simulateQRScan() {
            // For demo purposes, you can test with: ABC123
            const testCodes = ['ABC123', 'XYZ789', 'DEF456'];
            const randomCode = testCodes[Math.floor(Math.random() * testCodes.length)];
            
            utils.showNotification(`Scanned QR code: ${randomCode}`, 'success');
            document.getElementById('class_code').value = randomCode;
        }
    </script>
</body>
</html>