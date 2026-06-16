<?php $pageTitle = 'Bevestig Verwijderen'; ?>
<?php require 'inc/header.php'; ?>

<?php if (isset($boek)): ?>
<div class="alert alert-warning" role="alert">
    Weet u zeker dat u het boek "<?php echo htmlspecialchars_decode($boek->title); ?>" wilt verwijderen?
</div>
<form method="post" action="index.php?verwijder=<?php echo $boek->id; ?>" class="d-flex gap-2">
    <input type="hidden" name="id" value="<?php echo $boek->id; ?>">
    <button type="submit" class="btn btn-danger">Ja, verwijderen</button>
    <a href="index.php" class="btn btn-outline-secondary">Nee, terug naar lijst</a>
</form>
<?php else: ?>
<div class="alert alert-info" role="alert">Geen boekgegevens beschikbaar.</div>
<a href="index.php" class="btn btn-outline-secondary">Terug naar overzicht</a>
<?php endif; ?>

<?php require 'inc/footer.php'; ?>