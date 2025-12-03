<?php
    require "../views/components/header.php";
?>

<div class="container mt-5">
    <?php echo getFlashMessage('success'); // Pievienojiet Flash ziņojumus ?>

    <table class="table table-striped table-hover">
        <thead>
            <ul>
                <li>ID</li>
                <li>Lietotājs (ID)</li>
                <li>Darbības veids</li>
                <li>Apraksts</li>
                <li>Mērķis</li>
                <li>Laiks</li>
                <li>IP Adrese</li>
</ul>
        </thead>
        <tbody>
            <?php if (empty($history_data)): ?>
                <tr>
                    <td colspan="7">Darbību vēsture nav atrasta.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($history_data as $action): ?>
                <tr>
                    <td><?php echo htmlspecialchars($action['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($action['first_name'] . ' ' . $action['last_name']); ?> 
                        (<?php echo htmlspecialchars($action['user_id']); ?>)
                    </td>
                    <td><strong><?php echo htmlspecialchars($action['action_type']); ?></strong></td>
                    <td><?php echo htmlspecialchars($action['action_description']); ?></td>
                    <td>
                        <?php 
                            $target = htmlspecialchars($action['target_type'] . ':' . $action['target_id']);
                            echo !empty($action['target_id']) ? $target : '-';
                        ?>
                    </td>
                    <td><?php echo formatDate($action['created_at']); ?></td>
                    <td><?php echo htmlspecialchars($action['ip_address']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
<?php include '../../views/components/footer.php'; ?>

