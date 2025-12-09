<?php
require "../views/components/header.php";
?>

<style>
    .users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
        margin-top: 20px;
    }

    .user-card {
        background-color: var(--bg-primary);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        padding: 16px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .user-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .user-card .field {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    .user-card .field strong {
        color: var(--text-primary);
    }

    .user-card select, 
    .user-card input {
        width: 100%;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        font-size: 13px;
        margin-top: 4px;
    }

    .user-card button {
        margin-top: 8px;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .users-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }

    @media (max-width: 412px) {
        .users-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container mt-5">

    <?php if ($message): ?>
        <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 15px; border-radius: 4px;"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 15px; border-radius: 4px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="users-grid">
        <?php 
            $default_avatar = '../assets/images/default-avatar.png'; 

            foreach ($users as $u): 
                if (empty($image_url)) {
                    $image_url = $default_avatar; 
                }
        ?>
        <div class="user-card">
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

            <form id="edit-form-<?php echo $u['id']; ?>" method="POST" style="display: contents;">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="admin_update_user">
                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">

                <div class="field"><strong>ID:</strong> <?php echo $u['id']; ?></div>

                <div class="field">
                    <strong>Lietotājvārds:</strong>
                    <span id="username-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></span>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>"
                           id="username-input-<?php echo $u['id']; ?>" style="display:none;" required>
                </div>

                <div class="field">
                    <strong>Vārds:</strong>
                    <span id="first-name-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['first_name']); ?></span>
                    <input type="text" name="first_name" value="<?php echo htmlspecialchars($u['first_name']); ?>"
                           id="first-name-input-<?php echo $u['id']; ?>" style="display:none;" required>
                </div>

                <div class="field">
                    <strong>Uzvārds:</strong>
                    <span id="last-name-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['last_name']); ?></span>
                    <input type="text" name="last_name" value="<?php echo htmlspecialchars($u['last_name']); ?>"
                           id="last-name-input-<?php echo $u['id']; ?>" style="display:none;" required>
                </div>

                <div class="field">
                    <strong>E-pasts:</strong>
                    <span id="email-view-<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['email']); ?></span>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>"
                           id="email-input-<?php echo $u['id']; ?>" style="display:none;" required>
                </div>

                

                <div class="field">
                    <button type="button" id="edit-btn-<?php echo $u['id']; ?>" onclick="toggleEdit(<?php echo $u['id']; ?>)"
                            class="btn btn-info btn-sm">Rediģēt</button>

                    <button type="submit" id="save-btn-<?php echo $u['id']; ?>"
                            style="display:none;" class="btn btn-success btn-sm">Saglabāt</button>

                    <button type="button" id="cancel-btn-<?php echo $u['id']; ?>"
                            onclick="toggleEdit(<?php echo $u['id']; ?>)"
                            style="display:none;" class="btn btn-secondary btn-sm">Atcelt</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
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
function toggleEdit(userId) {
    const fields = ['username', 'first-name', 'last-name', 'email'];
    const editBtn = document.getElementById('edit-btn-' + userId);
    const saveBtn = document.getElementById('save-btn-' + userId);
    const cancelBtn = document.getElementById('cancel-btn-' + userId);

    if (editBtn.style.display !== 'none') {
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
        editBtn.style.display = 'inline';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
        fields.forEach(field => {
            const viewSpan = document.getElementById(field + '-view-' + userId);
            const editInput = document.getElementById(field + '-input-' + userId);
            if (viewSpan && editInput) {
                viewSpan.style.display = 'inline';
                editInput.style.display = 'none';
                editInput.value = viewSpan.textContent;
            }
        });
    }
}
</script>

<?php include '../views/components/footer.php'; ?>
