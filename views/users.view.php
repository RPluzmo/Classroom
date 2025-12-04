<?php
// views/admin/users.view.php

    require "../views/components/header.php";
?>

<div class="container mt-5">
    <h2><?php echo $page_title; ?></h2>
    
    <?php if ($message): ?>
        <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 15px; border-radius: 4px;"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; border: 1px solid #ddd; table-layout: fixed;">
        <thead>
            <tr style="background-color: #777777ff;">
                <th style="padding: 8px; border: 1px solid #ddd; width: 5%;">ID</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 15%;">Lietotājvārds</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 15%;">Vārds</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 15%;">Uzvārds</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 15%;">E-pasts</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 12%;">Loma</th>
                <th style="padding: 8px; border: 1px solid #ddd; width: 15%;">Darbības</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $default_avatar = '../assets/images/default-avatar.png'; 

                foreach ($users as $u): 
                    // Šeit tiek pielietota jūsu piemērā norādītā attēla loģika
                   
                    if (empty($image_url)) {
                        $image_url = $default_avatar; 
                    }
            ?>
            <tr id="user-row-<?php echo $u['id']; ?>">

    <form id="edit-form-<?php echo $u['id']; ?>" method="POST" style="display: contents;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="admin_update_user">
        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">

        <td><?php echo $u['id']; ?></td>

        <td>
            <span id="username-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></span>
            <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>"
                   id="username-input-<?php echo $u['id']; ?>" style="display:none;" required>
        </td>

        <td>
            <span id="first-name-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['first_name']); ?></span>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($u['first_name']); ?>"
                   id="first-name-input-<?php echo $u['id']; ?>" style="display:none;" required>
        </td>

        <td>
            <span id="last-name-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['last_name']); ?></span>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($u['last_name']); ?>"
                   id="last-name-input-<?php echo $u['id']; ?>" style="display:none;" required>
        </td>

        <td>
            <span id="email-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['email']); ?></span>
            <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>"
                   id="email-input-<?php echo $u['id']; ?>" style="display:none;" required>
        </td>

        <td>
            <button type="button" id="edit-btn-<?php echo $u['id']; ?>" onclick="toggleEdit(<?php echo $u['id']; ?>)"
                    class="btn btn-info btn-sm">Rediģēt</button>

            <button type="submit" id="save-btn-<?php echo $u['id']; ?>"
                    style="display:none;" class="btn btn-success btn-sm">Saglabāt</button>

            <button type="button" id="cancel-btn-<?php echo $u['id']; ?>"
                    onclick="toggleEdit(<?php echo $u['id']; ?>)"
                    style="display:none;" class="btn btn-secondary btn-sm">Atcelt</button>
        </td>

    </form>

    <td>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
            
            <select name="role" onchange="this.form.submit()">
                <option value="admin"   <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                <option value="teacher" <?= $u['role']=='teacher'?'selected':'' ?>>Teacher</option>
                <option value="student" <?= $u['role']=='student'?'selected':'' ?>>Student</option>
            </select>
        </form>
    </td>

</tr>
            <?php endforeach; ?>
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
    function toggleEdit(userId) {
        // Laukus, kurus mēs pārslēgsim
        const fields = ['username', 'first-name', 'last-name', 'email'];
        
        // Pārslēdz pogu redzamību
        const editBtn = document.getElementById('edit-btn-' + userId);
        const saveBtn = document.getElementById('save-btn-' + userId);
        const cancelBtn = document.getElementById('cancel-btn-' + userId);

        if (editBtn.style.display !== 'none') {
            // Pārejam uz rediģēšanas režīmu (View -> Edit)
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline';
            cancelBtn.style.display = 'inline';
            
            fields.forEach(field => {
                const viewSpan = document.getElementById(field + '-view-' + userId);
                const editInput = document.getElementById(field + '-input-' + userId);
                
                if (viewSpan && editInput) {
                    viewSpan.style.display = 'none';
                    editInput.style.display = 'inline-block';
                }
            });

        } else {
            // Atgriežamies pie skatīšanas režīma (Edit -> View)
            editBtn.style.display = 'inline';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
            
            fields.forEach(field => {
                const viewSpan = document.getElementById(field + '-view-' + userId);
                const editInput = document.getElementById(field + '-input-' + userId);
                
                if (viewSpan && editInput) {
                    viewSpan.style.display = 'inline';
                    editInput.style.display = 'none';
                    
                    // Atgriežam input lauku sākotnējo vērtību
                    editInput.value = viewSpan.textContent;
                }
            });
        }
    }
</script>


<?php include '../views/components/footer.php'; ?>