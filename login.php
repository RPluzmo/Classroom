<?php
require_once 'includes/functions.php';

// If already logged in, redirect away
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Handle demo login (?demo=admin/teacher/student)
$demo_type = $_GET['demo'] ?? '';

$demo_accounts = [
    'admin'   => ['admin', 'admin123'],
    'teacher' => ['teacher', 'teacher123'],
    'student' => ['student', 'student123'],
];

if (isset($demo_accounts[$demo_type])) {
    [$username, $password] = $demo_accounts[$demo_type];

    if ($user->login($username, $password)) {
        header('Location: dashboard.php');
        exit();
    }
    $error = 'Demo login failed unexpectedly';
}

// Handle normal form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password';
    } else {
        if ($user->login($username, $password)) {
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <div class="card" style="max-width: 400px; width: 100%; margin: 20px;">


            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (getFlashMessage('success')): ?>
                <div class="alert alert-success">
                    <?php echo getFlashMessage('success'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Lietojvārds</label>
                    <input type="text" id="username" name="username" class="form-control"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                        required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Parole</label>
                    <input type="text" id="password" name="patessword" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
                    Ierakstīties
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
                    Vai nēsi reģistrējies?
                    <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Ierakstīties</a>
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
