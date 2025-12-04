<?php
require "../views/components/header.php";
?>

<style>
    .history-card {
        background: var(--bg-primary);
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--shadow-md);
        margin-top: 25px;
    }

    .history-table thead th {
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-weight: 600;
        padding: 12px;
    }

    .history-table tbody tr:hover {
        background: var(--bg-hover);
        transition: 0.15s ease;
    }

    .history-meta {
        color: var(--text-muted);
        font-size: 14px;
        margin-top: -8px;
        margin-bottom: 15px;
    }

    .log-description {
        max-width: 350px;
        white-space: normal;
        line-height: 1.3em;
    }

    .log-target {
        color: var(--text-primary);
        font-weight: 600;
    }
</style>

<div class="container">
    <div class="history-card">
        <h2 style="margin-bottom: 5px;">Darbību vēsture</h2>
        <p class="history-meta">Šeit redzamas visas lietotāju veiktās sistēmas darbības.</p>

        <div class="table-responsive">
            <table class="table history-table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Lietotājs</th>
                        <th>Darbības tips</th>
                        <th>Apraksts</th>
                        <th>Mērķis</th>
                        <th>Datums</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?= $h['id'] ?></td>

                            <td>
                                <?= htmlspecialchars($h['username']) ?><br>
                                <span class="text-muted">
                                    <?= htmlspecialchars($h['first_name'] . ' ' . $h['last_name']) ?>
                                </span>
                            </td>

                            <td><strong><?= htmlspecialchars($h['action_type']) ?></strong></td>

                            <td class="log-description">
                                <?= nl2br(htmlspecialchars($h['action_description'])) ?>
                            </td>

                            <td>
                                <?php if ($h['target_type']): ?>
                                    <span class="log-target">
                                        <?= htmlspecialchars($h['target_type']) ?>
                                        #<?= htmlspecialchars($h['target_id']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="text-muted" style="font-size: 13px;">
                                    <?= date("Y-m-d H:i", strtotime($h['created_at'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Nav pieejamu darbību ierakstu.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
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
</script>
