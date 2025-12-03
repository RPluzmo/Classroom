<?php
    require "../views/components/header.php";
?>

<div class="container mt-5">
    <h2><?php echo $page_title; ?></h2>
    
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Lietotājvārds</th>
                <th>Vārds, Uzvārds</th>
                <th>E-pasts</th>
                <th>Loma</th>
                <th>Darbības</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo htmlspecialchars($u['id']); ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        
                        <?php if ($u['id'] == $current_user['id']): ?>
                            <span class="badge badge-primary"><?php echo getRoleDisplayName($u['role']); ?></span>
                        <?php else: ?>
                            <select name="role" class="form-control-sm" onchange="this.form.submit()">
                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="teacher" <?php echo $u['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                <option value="student" <?php echo $u['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                            </select>
                        <?php endif; ?>
                    </form>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editUserModal-<?php echo $u['id']; ?>">
                        Rediģēt
                    </button>

                    <div class="modal fade" id="editUserModal-<?php echo $u['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel-<?php echo $u['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editUserModalLabel-<?php echo $u['id']; ?>">Rediģēt lietotāju: <?php echo htmlspecialchars($u['username']); ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="admin_update_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        
                                        <div class="form-group">
                                            <label for="first_name_<?php echo $u['id']; ?>">Vārds</label>
                                            <input type="text" class="form-control" id="first_name_<?php echo $u['id']; ?>" name="first_name" value="<?php echo htmlspecialchars($u['first_name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="last_name_<?php echo $u['id']; ?>">Uzvārds</label>
                                            <input type="text" class="form-control" id="last_name_<?php echo $u['id']; ?>" name="last_name" value="<?php echo htmlspecialchars($u['last_name']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password_<?php echo $u['id']; ?>">Jaunā parole (atstājiet tukšu, ja nemaināt)</label>
                                            <input type="password" class="form-control" id="password_<?php echo $u['id']; ?>" name="password">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Aizvērt</tbutton>
                                        <button type="submit" class="btn btn-primary">Saglabāt izmaiņas</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
    </script>
<?php include '../views/components/footer.php'; ?>
