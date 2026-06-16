<?php $pageTitle = 'Boek Verwijderd'; ?>
<?php require 'inc/header.php'; ?>

<?php if (isset($result)): ?>
<div class="alert alert-success" role="alert">
    <strong>Verwijderd:</strong> <?php echo $result; ?>
</div>
<?php else: ?>
<div class="alert alert-info" role="alert">Actieresultaat niet beschikbaar.</div>
<?php endif; ?>
<div class="d-flex gap-2">
    <a href="index.php" class="btn btn-primary">Terug naar overzicht</a>
</div>

<?php require 'inc/footer.php'; ?>