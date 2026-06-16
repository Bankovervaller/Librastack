<?php $pageTitle = 'Boek Aangepast'; ?>
<?php include __DIR__ . '/../inc/header.php'; ?>

<?php if (isset($result)): ?>
<div class="alert alert-success" role="alert">
    <strong>Gelukt:</strong> <?php echo $result; ?>
</div>
<?php else: ?>
<div class="alert alert-info" role="alert">Actieresultaat niet beschikbaar.</div>
<?php endif; ?>
<div class="d-flex gap-2">
    <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php" class="btn btn-primary">Terug naar overzicht</a>
</div>

<?php include __DIR__ . '/../inc/footer.php'; ?>