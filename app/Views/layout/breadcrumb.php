<?php
$items = $items ?? [];
if (!empty($items)):
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small bg-white px-3 py-2 rounded-pill shadow-sm border d-inline-flex">
        <li class="breadcrumb-item">
            <a href="<?= base_url('/') ?>" class="text-success text-decoration-none">
                <i class="bi bi-house-door-fill me-1"></i> Beranda
            </a>
        </li>
        <?php foreach ($items as $label => $link): ?>
            <?php if (!empty($link)): ?>
                <li class="breadcrumb-item">
                    <a href="<?= $link ?>" class="text-success text-decoration-none"><?= esc($label) ?></a>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item active text-muted" aria-current="page"><?= esc($label) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
