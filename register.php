<?php
require_once 'includes/functions.php';

// Redirect if logged in
if ($user->isAuthenticated()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name  = sanitize($_POST['first_name'] ?? '');
    $last_name   = sanitize($_POST['last_name'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';

    // Validation
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        $errors[] = "Visi lauki ir obligāti.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Neatbilstošs e-pasta formāts.";
    }

    if ($user->exists('email', $email)) {
        $errors[] = "E-pasts jau ir reģistrēts.";
    }

    if (empty($errors)) {

        // Generate username automatically
        $username = strtolower($first_name . "." . $last_name);

        // Make username unique
        $base_username = $username;
        $i = 1;
        while ($user->exists('username', $username)) {
            $username = $base_username . $i;
            $i++;
        }

        // Hash password
       $hashed_password = $password;
        // DB connection
        $db = $user->conn;

        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, first_name, last_name, role)
            VALUES (?, ?, ?, ?, ?, 'student')
        ");
        $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name]);

        $user_id = $db->lastInsertId();

        // Create default settings
        $stmt2 = $db->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
        $stmt2->execute([$user_id]);

        // Success message and redirect
        setFlashMessage('success', 'Reģistrācija izdevās! Tagad vari pieslēgties.');
        header('Location: login.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo ($_SESSION['dark_theme'] ?? false) ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Classroom Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
    <div class="card" style="max-width: 450px; width: 100%; padding: 20px;">

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Vārds</label>
                <input type="text" name="first_name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Uzvārds</label>
                <input type="text" name="last_name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Epasts</label>
                <input type="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Parole</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 20px;">
                Reģistrēties
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p style="color: var(--text-secondary);">
                Jau ir lietotājprofils? <a href="login.php" style="color: var(--primary-color);">Pieslēgties</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
