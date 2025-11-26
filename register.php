<?php
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Lūdzu ievadi gan lietotājvārdu, gan paroli.';
    } else {
        // Dummy dati obligātajiem laukiem
        $email = $username . '@vtdt.edu.lv';
        $first_name = '';
        $last_name = '';
        $role = 'student'; // visiem jaunajiem lietotājiem student

        $userObj = new User();
        $register_result = $userObj->register($username, $email, $password, $first_name, $last_name, $role);

        if ($register_result) {
            $success = 'Reģistrācija veiksmīga.';
        } else {
            $error = 'Iespējams lietotājvārds jau eksistē.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo ($_SESSION['dark_theme'] ?? false) ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācija</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 400px; width: 100%; margin: 20px;">
            
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

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username" class="form-label">Lietotājvārds</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Parole</label>
                    <input type="text" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 24px;">
                    Reģistrēties
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px;">
                <p style="color: var(--text-secondary);">
                    Mby tu tomēr esi reģistrējies?
                    <a href="login.php" style="color: var(--primary-color); text-decoration: none;">Ienākt</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
