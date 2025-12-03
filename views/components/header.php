<?php
$theme_state = ($_SESSION['dark_theme'] ?? 0) ? 'dark' : 'light';
?>
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
        <div class="container">
            <div class="header-content">
                <nav class="nav-menu">
                        <ul>
                            <a href="../../controllers/dashboard.php" class="nav-link">Dashboard</a>
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <a href="../../controllers/users.php" class="nav-link">Lietotāji</a>
                                <a href="../../controllers/history.php" class="nav-link">Iepriekšējās darbības</a>
                            <?php elseif ($current_user['role'] === 'teacher'): ?>
                                <a href="../controllers/classes.php" class="nav-link">Mani kursi</a>
                                <a href="../../controllers/create_class.php" class="nav-link">Pievienot kursu</a>
                             <?php elseif ($current_user['role'] === 'student'): ?>
                                <a href="../../controllers/join_class.php" class="nav-link">Pievienoties kursam</a>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <div class="user-menu">
                    <button class="theme-toggle" title="Toggle theme">
                        <?php echo ($_SESSION['dark_theme'] ?? 0) ? '☀️' : '🌙'; ?>
                    </button>
                    
                    <div style="position: relative;">
                        <img src="<?php 
    // Attēla ceļš no datubāzes (jāuzglabā kā /uploads/avatars/...)
    $image_url = $current_user['profile_picture'];

    // Ja lietotāja attēls nav, izmanto noklusējuma attēlu, 
    // norādot absolūto ceļu no vietnes saknes (http://localhost/assets/...)
    if (empty($image_url)) {
        $image_url = '/assets/images/default-avatar.png'; 
    }
    echo $image_url; 
?>" 
     alt="piss" class="user-avatar" onclick="toggleUserMenu()">
                        
                        <div id="userMenu" class="d-none" style="position: absolute; right: 0; top: 50px; background: var(--bg-primary); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 200px; z-index: 1001;">
                            <a href="../../../../../controllers/profile.php" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);">
                                Profils
                            </a>
                            <a href="../../../../../controllers/logout.php" style="display: block; padding: 12px 16px; color: var(--danger-color); text-decoration: none;">
                                Iziet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>