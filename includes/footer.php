</main>
<footer class="site-footer">
    <p>&copy; <?= date('Y') ?> Student Hub Portal &mdash; College Project (Practical 15)</p>
</footer>
<script src="<?= h($base ?? '') ?>assets/js/nav.js"></script>
<?php if (!empty($extraScripts)) { echo $extraScripts; } ?>
</body>
</html>
