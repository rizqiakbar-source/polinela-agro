<div class="modal fade" id="<?= $id ?? 'agroModal' ?>" tabindex="-1" aria-labelledby="<?= ($id ?? 'agroModal') ?>Label" aria-hidden="true">
    <div class="modal-dialog <?= $dialogClass ?? '' ?> modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header <?= $headerBg ?? 'bg-white' ?> border-bottom">
                <h5 class="modal-title fw-bold" id="<?= ($id ?? 'agroModal') ?>Label">
                    <?= $title ?? 'Modal Title' ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <?= $body ?? '' ?>
            </div>
            <?php if (!isset($hideFooter) || !$hideFooter): ?>
            <div class="modal-footer border-top bg-light p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <?= $closeText ?? 'Tutup' ?>
                </button>
                <?php if (isset($submitBtn)): ?>
                    <?= $submitBtn ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
