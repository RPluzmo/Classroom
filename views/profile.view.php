<?php
    require "../views/components/header.php";
?>

    <main class="main-content">
        <div class="container">
            <div class="content" style="max-width: 1025px; margin: 0 auto;">
                <div class="card-header">
                    <div>
                        <h1 class="card-title">Mans profils</h1>
                    </div>
                </div>

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

                <div class="grid grid-cols-3" style="gap: 24px;">
                    <div>
                        <div class="card">
                            <h3 style="font-size: 16px; margin-bottom: 16px; text-align: center;">Profila attēls</h3>
                            
                            <div style="text-align: center; margin-bottom: 20px;">
                                <img src="<?php echo $current_user['profile_picture'] ?: 'assets/images/default-avatar.png'; ?>" 
                                     alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border-color); margin-bottom: 12px;">
                                <div style="font-weight: 500; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                                </div>
                                <div style="font-size: 14px; color: var(--text-secondary); text-transform: capitalize;">
                                    <?php echo getRoleDisplayName($current_user['role']); ?>
                                </div>
                            </div>

                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="avatar" class="form-label" style="text-align: center; display: block;">Nomainīt attēlu</label>
                                    <input type="file" id="avatar" name="avatar" class="form-control" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif"
                                           onchange="previewAvatar(this)">
                                </div>
                                <button type="submit" name="upload_avatar" class="btn btn-primary" style="width: 100%;">
                                    Augšupielādēt
                                </button>
                            </form>
                        </div>

                        <div class="card" style="margin-top: 20px;">
                        <form method="POST" id="profileForm">
                            <h3>Rediģēt</h3>
                                <div class="grid grid-cols-2" style="gap: 16px;">
                                    
                                    <div class="form-group">
                                        <label for="first_name" class="form-label">Vārds</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($current_user['first_name']); ?>" 
                                               required maxlength="50">
                                    </div>

                                    <div class="form-group">
                                        <label for="last_name" class="form-label">Uzvārds</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control" 
                                               value="<?php echo htmlspecialchars($current_user['last_name']); ?>" 
                                               required maxlength="50">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">E-pasts</label>
                                    <input type="email" id="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($current_user['email']); ?>" 
                                           required maxlength="100">
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    Saglabāt
                                </button>
                            </form>
                    </div>
                </div>


                    <div style="grid-column: span 2;">
                        <div class="card">
                            <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 20px;"></h2>
                            
                            
                            <h3 style="font-size: 16px; margin-bottom: 12px;">Informācija par lietotāju</h3>
                            <div style="font-size: 14px; color: var(--text-secondary);">
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Vārds:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($current_user['first_name']); ?></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Uzvārds:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($current_user['last_name']); ?></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Lietotājvārds:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($current_user['username']); ?></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">E-pasts:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($current_user['email']); ?></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <span style="color: var(--text-tertiary);">Role:</span><br>
                                    <span style="color: var(--text-primary); font-weight: 500;"><?php echo getRoleDisplayName($current_user['role']); ?></span>
                                </div>
                            </div>
                        </div>

                            
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../../../assets/js/script.js"></script>
    <script>
        const validator = new FormValidator('profileForm');
        validator.addRule('first_name', [
            { type: 'required', message: 'Aizmisāt savu vārdu' },
            { type: 'minLength', value: 2, message: 'Vismaz 2 simboli yknow kā CJ' }
        ]);
        validator.addRule('last_name', [
            { type: 'required', message: 'Aizmisāt savu uzvārdu' },
            { type: 'minLength', value: 2, message: 'Vismaz 2 simboli' }
        ]);
        validator.addRule('email', [
            { type: 'required', message: 'Ievadiet e-pastu' },
            { type: 'email', message: 'Tikai atbilstošu epastu' }
        ]);

        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const avatarImg = document.querySelector('.card img');
                    if (avatarImg) {
                        avatarImg.src = e.target.result;
                    }
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

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
    </script>
</body>
</html>