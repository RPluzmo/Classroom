<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel='stylesheet' href='/assets/css/style.css'>
</head>
<body>
<header class="header">
    <div class="header-content">
                    <div class="user-menu">
                        <button class="theme-toggle" title="Toggle theme">
                            <?php echo ($_SESSION['dark_theme'] ?? 0) ? '☀️' : '🌙'; ?>
                        </button>
                    </div>
    </div>
</header>
    <div class="login-container" style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
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
                Varbūt jums jau ir lietotājprofils? <a href="../controllers/login.php" style="color: var(--primary-color);">Pieslēgties</a>
            </p>
        </div>
    </div>
<script src="../../../../../../assets/js/script.js"></script>
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

