<?php
require_once 'includes/functions.php';
requireRole('teacher');

$current_user = $user->getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name)) {
        $error = 'Class name is required';
    } else {
        $class_id = $classroom->createClass($current_user['id'], $name, $description);
        if ($class_id) {
            setFlashMessage('success', 'Class created successfully!');
            header('Location: class.php?id=' . $class_id);
            exit();
        } else {
            $error = 'Failed to create class. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $_SESSION['dark_theme'] ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izveidot kursu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">

                <nav class="nav-menu">
                    <a href="dashboard.php" class="nav-link">Sākums</a>
                    <a href="classes.php" class="nav-link">Mani kursi</a>
                    <a href="create_class.php" class="nav-link active">Izveidot kursu</a>
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
                                Profils
                            </a>
                            <a href="settings.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                Iest
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
            <div class="content" style="max-width: 600px; margin: 0 auto;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Izviedo jaunu kursu</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="createClassForm">
                        <div class="form-group">
                            <label for="name" class="form-label">Kursa nosaukums</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                   required maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Kursa apraksts</label>
                            <textarea id="description" name="description" class="form-control" 
                                      rows="4" maxlength="500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div style="background: var(--bg-secondary); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                            <ul style="margin: 0; padding-left: 20px; color: var(--text-secondary); font-size: 14px; line-height: 1.6;">
                                <li>A unique class code will be generated automatically</li>
                                <li>Students can join using this code or QR code</li>
                                <li>You can create assignments and share materials</li>
                                <li>Track student progress and grade submissions</li>
                            </ul>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                <span>✨</span> Izveidot kursu
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Atcelt
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('createClassForm');
        validator.addRule('name', [
            { type: 'required', message: 'Class name is required' },
            { type: 'minLength', value: 3, message: 'Class name must be at least 3 characters' },
            { type: 'maxLength', value: 100, message: 'Class name must not exceed 100 characters' }
        ]);

        // Character counter
        const nameInput = document.getElementById('name');
        const descInput = document.getElementById('description');

        function updateCharCounter(input, maxLength) {
            const currentLength = input.value.length;
            const remaining = maxLength - currentLength;
            
            let counter = input.parentNode.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.color = 'var(--text-tertiary)';
                counter.style.fontSize = '12px';
                counter.style.marginTop = '4px';
                input.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (remaining < 10) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-tertiary)';
            }
        }

        nameInput.addEventListener('input', () => updateCharCounter(nameInput, 100));
        descInput.addEventListener('input', () => updateCharCounter(descInput, 500));

        // Initialize counters
        updateCharCounter(nameInput, 100);
        updateCharCounter(descInput, 500);

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