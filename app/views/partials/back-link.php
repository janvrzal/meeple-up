<?php
/** @var string $fallback  URL použité, když není kam jít zpět (přímý vstup) */
/** @var string $label */
$label = $label ?? 'Back';
?>
<a href="<?= htmlspecialchars($fallback) ?>" class="btn btn-ghost btn-sm gap-1 mb-3">
    <i class="ti ti-arrow-left"></i> <?= htmlspecialchars($label) ?>
</a>
<script>
(function () {
    var link = document.currentScript.previousElementSibling;
    if (!link) return;
    var ref = document.referrer;
    var external = false;
    try {
        // jen pokud uživatel přišel z CIZÍHO webu, nepoužívej history (vyhodilo by ho ven)
        external = ref && new URL(ref).origin !== location.origin;
    } catch (e) {}
    if (!external && history.length > 1) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            history.back();   // skutečně tam, odkud přišel (i scroll pozice)
        });
    }
    // jinak zůstane fallback href
})();
</script>
