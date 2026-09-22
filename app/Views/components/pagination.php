<?php
$pager = $pager ?? null;
if ($pager):
    $pager->setSurroundCount(2);
?>
<nav aria-label="Page navigation" class="my-4">
    <ul class="pagination justify-content-center pagination-agro gap-1">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="<?= $pager->getFirst() ?>" aria-label="First">
                    <i class="bi bi-chevron-double-left"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="<?= $pager->getPrevious() ?>" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link): ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="<?= $link['uri'] ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="<?= $pager->getNext() ?>" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link rounded-circle d-flex align-items-center justify-content-center" href="<?= $pager->getLast() ?>" aria-label="Last">
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
<?php endif; ?>
