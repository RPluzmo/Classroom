<?php
require "../views/components/header.php";
?>

<div class="container mt-4">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h1>Mani kursi</h1>
        <a href="create_class.php" class="btn btn-primary">
            <span>➕</span> Pievienot kursu
        </a>
    </div>

    <?php if (empty($user_classes)): ?>
        <div style="text-align: center; padding: 40px;">
            <h3 style="color: var(--text-primary); margin-bottom: 8px;">Te nekā nav</h3>
            <p style="color: var(--text-secondary); margin-bottom: 16px;">Šeit parādīsies visi jūsu kursi.</p>
            <a href="teacher/create_class.php" class="btn btn-primary">Pievienot kursu</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-3 gap-6">
            <?php foreach ($user_classes as $class): ?>
                <div class="class-card" onclick="window.location.href='class.php?id=<?php echo $class['id']; ?>'" 
                     style="cursor: pointer; border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; transition: transform 0.2s;">
                    <div class="class-info">
                        <h3 class="class-name" style="font-size: 18px; font-weight: 600; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($class['name']); ?>
                        </h3>
                        <p style="font-size: 14px; color: var(--text-secondary); margin-bottom: 8px;">
                            <?php echo $class['student_count']; ?> Skolnieki
                        </p>
                        <span style="font-size: 12px; color: var(--text-secondary); background: var(--bg-secondary); padding: 4px 8px; border-radius: 4px;">
                            <?php echo htmlspecialchars($class['class_code']); ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../../../../../../assets/js/script.js"></script>
    <script>
function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('d-none');
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userMenu');
            const avatar = document.querySelector('.user-avatar');
            
            if (!avatar.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('d-none');
            }
        });

    document.querySelectorAll('.class-card').forEach(card => {
        card.addEventListener('mouseover', () => card.style.transform = 'scale(1.02)');
        card.addEventListener('mouseout', () => card.style.transform = 'scale(1)');
    });
</script>
