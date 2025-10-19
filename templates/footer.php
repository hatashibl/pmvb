<?php
// templates/footer.php
$copyright = get_setting('copyright');
?>
<footer class="bg-light p-3 mt-auto border-top">
    <div class="text-center text-muted">
        <?= htmlspecialchars($copyright) ?>
    </div>
</footer>