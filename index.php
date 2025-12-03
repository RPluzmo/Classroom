<?php
require_once 'includes/functions.php';

// Redirect if already logged in
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle demo login
$demo_type = $_GET['demo'] ?? '';
if (!empty($demo_type)) {
    switch ($demo_type) {
        case 'admin':
            $_POST['username'] = 'admin';
            $_POST['password'] = 'admin123';
            break;
        case 'teacher':
            $_POST['username'] = 'teacher';
            $_POST['password'] = 'teacher123';
            break;
        case 'student':
            $_POST['username'] = 'student';
            $_POST['password'] = 'student123';
            break;
    }
    
    if (!empty($_POST['username'])) {
        $login_result = $user->login($_POST['username'], $_POST['password']);
        if ($login_result) {
            header('Location: dashboard.php');
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $login_result = $user->login($username, $password);
        if ($login_result) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo ($_SESSION['dark_theme'] ?? false) ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loginc</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<header class="header">
        <div class="container">
            <div class="header-content">

                <div class="user-menu">
                    <button class="theme-toggle" title="Toggle theme">
                        <?php echo $_SESSION['dark_theme'] ? '☀️' : '🌙'; ?>
                    </button>
                    
                </div>
            </div>
        </div>
    </header>
<body>
    <div class="login-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; width: 100%; margin: 20px;">
            

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

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Lietotājvārds</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Parole</label>
                    <input type="text" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
                    Ienākt
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <div style="display: grid; gap: 8px; font-size: 14px;">
                    <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px;">
                         admin / qwe
                    </div>
                    <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px;">
                        teacher / qwe
                    </div>
                    <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px;">
                        student / qwe
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <p style="color: var(--text-secondary);">
                    Vai nēsi reģistrējies?? 
                    <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Sign up</a>
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>