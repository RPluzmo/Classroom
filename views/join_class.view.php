<?php
    require "../views/components/header.php";
?>

    <main class="main-content">
        <div class="container">
            <div class="content" style="max-width: 600px; margin: 0 auto;">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Pievienoties kursam</h1>
                            <p class="card-subtitle">Ievadi kursa kodu</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (getFlashMessage('success')): ?>
                        <div class="alert alert-success">
                            <?php echo getFlashMessage('success'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="joinClassForm">
                        <div class="form-group">
                            <label for="class_code" class="form-label">KODS</label>
                            <input type="text" id="class_code" name="class_code" class="form-control" 
                                   value="<?php echo isset($_POST['class_code']) ? htmlspecialchars($_POST['class_code']) : (isset($_GET['code']) ? htmlspecialchars($_GET['code']) : ''); ?>" 
                                   required maxlength="10" 
                                   style="text-transform: uppercase; font-size: 18px; text-align: center; letter-spacing: 2px; font-weight: 600;"
                                   placeholder="ABC123">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            Pievienoties kursam
                        </button>
                    </form>
                </div>

                <?php 
                $recent_classes = $classroom->getStudentClasses($current_user['id']);
                if (!empty($recent_classes)): 
                ?>
                    <div class="card" style="margin-top: 20px;">
                        <h3 style="font-size: 18px; margin-bottom: 16px; color: var(--text-primary);">
                            Mani Kursi
                        </h3>
                        <div class="grid grid-cols-2" style="gap: 12px;">
                            <?php foreach (array_slice($recent_classes, 0, 4) as $class): ?>
                                <div style="background: var(--bg-secondary); padding: 12px; border-radius: 8px;">
                                    <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 4px; color: var(--text-primary);">
                                        <?php echo htmlspecialchars($class['name']); ?>
                                    </h4>
                                    <p style="font-size: 12px; color: var(--text-secondary); margin: 0;">
                                        <?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($recent_classes) > 4): ?>
                            <div style="text-align: center; margin-top: 12px;">
                                <a href="dashboard.php" class="btn btn-secondary btn-sm">
                                    View All Classes
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../../../../../../../assets/js/script.js"></script>
    <script>
        // Form validation
        const validator = new FormValidator('joinClassForm');
        validator.addRule('class_code', [
            { type: 'required', message: 'Tu aizmirsi kodu' },
            { type: 'minLength', value: 6, message: 'Kods sastāv no 6 simboliem.. nopietni' },
            { type: 'maxLength', value: 10, message: 'Par tādu kodu nemaz neiedomājos.. 6 simboli MAX' },
            { type: 'pattern', value: '^[A-Z0-9]+$', message: 'Tikai burti un cipari' }
        ]);

        // Auto-format class code
        const classCodeInput = document.getElementById('class_code');
        classCodeInput.addEventListener('input', function(e) {
            // Convert to uppercase and remove invalid characters
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Limit to 6 characters
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
        });

        // Handle paste events
        classCodeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let pastedData = (e.clipboardData || window.clipboardData).getData('text');
            pastedData = pastedData.toUpperCase().replace(/[^A-Z0-9]/g, '');
            
            // Insert at cursor position
            const start = this.selectionStart;
            const end = this.selectionEnd;
            const currentValue = this.value;
            const newValue = currentValue.substring(0, start) + pastedData + currentValue.substring(end);
            
            // Limit to 6 characters
            this.value = newValue.substring(0, 6);
            
            // Move cursor to end of pasted text
            const newCursorPos = Math.min(start + pastedData.length, 6);
            this.setSelectionRange(newCursorPos, newCursorPos);
        });

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

        // Simulate QR code scanner (in production, you'd use a proper QR scanning library)
        function simulateQRScan() {
            // For demo purposes, you can test with: ABC123
            const testCodes = ['ABC123', 'XYZ789', 'DEF456'];
            const randomCode = testCodes[Math.floor(Math.random() * testCodes.length)];
            
            utils.showNotification(`Scanned QR code: ${randomCode}`, 'success');
            document.getElementById('class_code').value = randomCode;
        }
    </script>
</body>
</html>