<?php
    require "../views/components/header.php";
?>

    <main class="main-content">
        <div class="container">
            <div class="content" style="max-width: 600px; margin: 0 auto;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Izviedo jaunu kursu</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="createClassForm">
                        <div class="form-group">
                            <label for="name" class="form-label">Kursa nosaukums</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                   required maxlength="100">
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Kursa apraksts</label>
                            <textarea id="description" name="description" class="form-control" 
                                      rows="4" maxlength="500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                Izveidot kursu
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                Atcelt
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../../../../assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('createClassForm');
        validator.addRule('name', [
            { type: 'required', message: 'Nepieciešams nosaukums' },
            { type: 'minLength', value: 3, message: 'Kursa nosaukumam jāsastāv vismaz no 3 simboliem.' },
            { type: 'maxLength', value: 100, message: 'Kursa nosaukums nevar pārsniegt 100 simbolus.' }
        ]);

        // Character counter
        const nameInput = document.getElementById('name');
        const descInput = document.getElementById('description');

        function updateCharCounter(input, maxLength) {
            const currentLength = input.value.length;
            const remaining = maxLength - currentLength;
            
            let counter = input.parentNode.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.color = 'var(--text-tertiary)';
                counter.style.fontSize = '12px';
                counter.style.marginTop = '4px';
                input.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (remaining < 10) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-tertiary)';
            }
        }

        nameInput.addEventListener('input', () => updateCharCounter(nameInput, 100));
        descInput.addEventListener('input', () => updateCharCounter(descInput, 500));

        // Initialize counters
        updateCharCounter(nameInput, 100);
        updateCharCounter(descInput, 500);

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
</body>
</html>