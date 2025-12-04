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
        background: var(--bg-tertiary);
        transition: 0.15s ease;
    }

    .history-meta {
        color: var(--text-secondary);
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

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .history-card {
            padding: 12px;
        }
        .history-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .history-table thead {
            display: none;
        }
        .history-table tbody tr {
            display: block;
            margin-bottom: 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            background-color: var(--bg-primary);
        }
        .history-table tbody td {
            display: flex;
            justify-content: space-between;
            padding: 6px 8px;
            font-size: 13px;
        }
        .history-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--text-secondary);
            flex: 1;
        }
    }
</style>

<div class="container">
    <div class="history-card">
        <h2 style="margin-bottom: 5px;">Darbību vēsture</h2>
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
                            <td data-label="#"> <?= $h['id'] ?></td>
                            <td data-label="Lietotājs">
                                <?= htmlspecialchars($h['username']) ?><br>
                                <span class="text-muted">
                                    <?= htmlspecialchars($h['first_name'] . ' ' . $h['last_name']) ?>
                                </span>
                            </td>
                            <td data-label="Darbības tips"><strong><?= htmlspecialchars($h['action_type']) ?></strong></td>
                            <td data-label="Apraksts" class="log-description">
                                <?= nl2br(htmlspecialchars($h['action_description'])) ?>
                            </td>
                            <td data-label="Mērķis">
                                <?php if ($h['target_type']): ?>
                                    <span class="log-target">
                                        <?= htmlspecialchars($h['target_type']) ?>
                                        #<?= htmlspecialchars($h['target_id']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Datums">
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
