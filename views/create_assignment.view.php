
<?php
    require "../views/components/header.php";
?>

    <main class="main-content">
        <div class="container">
            <div class="content" style="max-width: 800px; margin: 0 auto;">
                <div style="margin-bottom: 20px;">
                    <a href="class.php?id=<?php echo $class_id; ?>" style="color: var(--text-secondary); text-decoration: none;">
                        ← Atpakaļ uz kursa pārskatu <?php echo htmlspecialchars($class['name']); ?>
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Izveido jaunu uzdevumu</h1>
                            <p class="card-subtitle">
                                <?php echo htmlspecialchars($class['name']); ?> • <?php echo $classroom->getClassMembers($class_id) ? count($classroom->getClassMembers($class_id)) : 0; ?> students
                            </p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="createAssignmentForm">
                        <div class="form-group">
                            <label for="title" class="form-label">Uzdevuma nosaukums</label>
                            <input type="text" id="title" name="title" class="form-control" 
                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                   required maxlength="200">
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Uzdevuma apraksts.</label>
                            <textarea id="description" name="description" class="form-control" 
                                      rows="6" ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>

                        <div class="grid grid-cols-2">
                            <div class="form-group">
                                <label for="due_date" class="form-label">Uzdevuma termiņš</label>
                                <input type="datetime-local" id="due_date" name="due_date" class="form-control" 
                                       value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>"
                                       min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="max_points" class="form-label">Iegūstamie punkti</label>
                                <input type="number" id="max_points" name="max_points" class="form-control" 
                                       value="<?php echo isset($_POST['max_points']) ? htmlspecialchars($_POST['max_points']) : '100'; ?>" 
                                       min="1" max="1000" required>
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="form-group">
                            <label class="form-label">Pievienot failu</label>
                            <div class="file-upload" id="dropZone">
                                <div style="font-size: 48px; margin-bottom: 16px;">:]</div>
                                <p style="color: var(--text-primary); margin-bottom: 8px; font-weight: 500;">
                                    Iemet vai izvēlies failus, kurus pievienot uzdevumam.
                                </p>
                                <input type="file" id="fileInput" name="attachments[]" multiple 
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.zip,.rar"
                                       style="display: none;">
                                <button type="button" onclick="document.getElementById('fileInput').click()" 
                                        class="btn btn-secondary" style="margin-top: 12px;">
                                    Izvēlēties failus
                                </button>
                            </div>
                            <div class="file-list" id="fileList"></div>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                Izveidot uzdevumu.
                            </button>
                            <a href="class.php?id=<?php echo $class_id; ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                    </div>
                
            </div>
        </div>
    </main>

    <script src="../../../../../../assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('createAssignmentForm');
        validator.addRule('title', [
            { type: 'required', message: 'Uzdevumam nepieciešams nosaukums' },
            { type: 'minLength', value: 3, message: 'Uzdevuma nosaukumam jāsastāv vismaz no 3simboliem.' },
            { type: 'maxLength', value: 200, message: 'Uzdevuma nosaukums nevar pārsniegt 200 simbolus.' }
        ]);


        const fileUploadManager = new FileUploadManager('fileInput', 'dropZone');


        const titleInput = document.getElementById('title');
        titleInput.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = 200;
            const remaining = maxLength - currentLength;
            
            let counter = this.parentNode.querySelector('.char-counter');
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.color = 'var(--text-tertiary)';
                counter.style.fontSize = '12px';
                counter.style.marginTop = '4px';
                counter.style.display = 'block';
                this.parentNode.appendChild(counter);
            }
            
            counter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (remaining < 20) {
                counter.style.color = 'var(--warning-color)';
            } else {
                counter.style.color = 'var(--text-tertiary)';
            }
        });


        const titleLength = titleInput.value.length;
        if (titleLength > 0) {
            titleInput.dispatchEvent(new Event('input'));
        }

        const dueDateInput = document.getElementById('due_date');
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        dueDateInput.min = now.toISOString().slice(0, 16);

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