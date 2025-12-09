<?php
    require "../views/components/header.php";
?>

<main class="main-content">
        <?php if ($current_user['role'] === 'admin'): ?>
            
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Admin skats</h1>
                            <h2>Čau <?php echo htmlspecialchars($current_user['username']); ?>!</h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-4" style="margin-bottom: 30px;">
                        <?php
                        $total_users = count($user->getAllUsers());
                        $total_classes = 0;
                        $total_assignments = 0;
                        
                        $conn = (new Database())->connect();
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM classes");
                        $total_classes = $stmt->fetch()['count'];
                        
                        $stmt = $conn->query("SELECT COUNT(*) as count FROM assignments");
                        $total_assignments = $stmt->fetch()['count'];
                        ?>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--primary-color); margin-bottom: 8px;"><?php echo $total_users; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Lietotāju skaits</div>
                        </div>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--secondary-color); margin-bottom: 8px;"><?php echo $total_classes; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Pieejamie kursi</div>
                        </div>
                        
                        <div class="card" style="text-align: center;">
                            <div style="font-size: 32px; color: var(--warning-color); margin-bottom: 8px;"><?php echo $total_assignments; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px;">Pieejamie priekšmeti</div>
                        </div>
                        
                        
                    </div>


                    </div>
                </div>
            </div>

        <?php elseif ($current_user['role'] === 'teacher'): ?>
            <!-- Teacher Dashboard -->
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        <div>
                            <h1 class="card-title">Skolotāju skats</h1>
                           <h2>Čau <?php echo htmlspecialchars($current_user['username']); ?>!</h2>
                        </div>
                        <a href="../controllers/create_class.php" class="btn btn-primary">
                            <span>➕</span> Pievienot kursu
                        </a>
                    </div>

                    <div class="card">
                        <h2 style="font-size: 18px; margin-bottom: 16px;">Mani kursi</h2>
                        <?php if (empty($user_classes)): ?>
                            <div style="text-align: center; padding: 40px;">
                                <div style="font-size: 48px; margin-bottom: 16px;"></div>
                                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Te nekā nav</h3>
                                <a href="controllers/teacher/create_class.php" class="btn btn-primary">Pienienot kursu</a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-3">
                                <?php foreach ($user_classes as $class): ?>
                                    <div class="class-card" onclick="window.location.href='class.php?id=<?php echo $class['id']; ?>'">
                                        <div class="class-header"></div>
                                        <div class="class-info">
                                            <h3 class="class-name"><?php echo htmlspecialchars($class['name']); ?></h3>
                                            <p class="class-teacher"><?php echo $class['student_count']; ?> skolnieki</p>
                                            <span class="class-code"><?php echo htmlspecialchars($class['class_code']); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($user_assignments)): ?>
                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Jaunākie uzdevimu</h2>
                            <?php foreach ($user_assignments as $assign): ?>
                                <div class="assignment-card">
                                    <h3 class="assignment-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                                    <div class="assignment-meta">
                                        <span>📁 <?php echo htmlspecialchars($assign['title']);?></span>
                                        <span>📅 <?php echo $assign['due_date'] ? date('M j, Y', strtotime($assign['due_date'])) : 'No due date'; ?></span>
                                        <span>✅ <?php echo $assign['graded_count']; ?>/<?php echo $assign['submission_count']; ?> Novērtēti</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Student Dashboard -->
            <div class="container">
                <div class="content">
                    <div class="card-header">
                        
                        <div>
                            <h1 class="card-title">Skolnieka skats</h1>
                            <h2>Čau <?php echo htmlspecialchars($current_user['username']); ?>!</h2>
                        </div>
                        <a href="../controllers/join_class.php" class="btn btn-primary">
                            <span>➕</span> Pievienoties kursam
                        </a>
                    </div>

                    <div class="card">
                        <h2 style="font-size: 18px; margin-bottom: 16px;">Mani kursi</h2>
                        <?php if (empty($user_classes)): ?>
                            <div style="text-align: center; padding: 40px;">
                                <h3 style="color: var(--text-primary); margin-bottom: 8px;">Te nekā nav</h3>
                                <a href="../controllers/join_class.php" class="btn btn-primary">Pievienoties kursam</a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-3">
                                <?php foreach ($user_classes as $class): ?>
                                    <div class="class-card" onclick="window.location.href='class.php?id=<?php echo $class['id']; ?>'">
                                        <div class="class-header"></div>
                                        <div class="class-info">
                                            <h3 class="class-name"><?php echo htmlspecialchars($class['name']); ?></h3>
                                            <p class="class-teacher"><?php echo htmlspecialchars($class['teacher_first_name'] . ' ' . $class['teacher_last_name']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($user_assignments)): ?>
                        <div class="card">
                            <h2 style="font-size: 18px; margin-bottom: 16px;">Jaunākie iesniegumu</h2>
                            <?php foreach ($user_assignments as $assign): ?>
                                <div class="assignment-card">
                                    <h3 class="assignment-title"><?php echo htmlspecialchars($assign['title']); ?></h3>
                                    <div class="assignment-meta">
                                        <span>📁 <?php echo htmlspecialchars($assign['class_name']); ?></span>
                                        <span>📅 <?php echo $assign['due_date'] ? date('M j, Y', strtotime($assign['due_date'])) : 'No due date'; ?></span>
                                        <?php if ($assign['submission']): ?>
                                            <?php if ($assign['submission']['grade'] !== null): ?>
                                                <span class="assignment-status status-graded">Grade: <?php echo $assign['submission']['grade']; ?></span>
                                            <?php else: ?>
                                                <span class="assignment-status status-submitted">Iesniegts</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="assignment-status status-pending">Neiesniegts</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/script.js"></script>
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
    </script>

<?php require "../views/components/footer.php"; ?>