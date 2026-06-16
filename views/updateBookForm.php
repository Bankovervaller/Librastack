<?php $pageTitle = 'Boek Aanpassen'; ?>
<?php include __DIR__ . '/../inc/header.php'; ?>

<?php $action = 'index.php'; if (isset($boek)) { $action = 'index.php?pasaan=' . $boek->id; } ?>

<?php if (isset($boek)): ?>
<h1 class="h4">Boek aanpassen</h1>
<form method="post" action="<?php echo $action; ?>">
    <input type="hidden" name="id" value="<?php echo $boek->id; ?>">
    <div class="mb-3">
        <label for="naam" class="form-label">Titel</label>
        <input type="text" class="form-control" id="naam" name="naam" required value="<?php echo htmlspecialchars_decode($boek->title); ?>">
        <div class="form-text">Wijzig de titel indien nodig.</div>
    </div>
    <div class="mb-3">
        <label for="auteur" class="form-label">Auteur</label>
        <input type="text" class="form-control" id="auteur" name="auteur" required value="<?php echo htmlspecialchars_decode($boek->author); ?>">
        <div class="form-text">Wijzig de naam van de auteur indien nodig.</div>
    </div>
    <div class="mb-3">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" class="form-control" id="isbn" name="isbn" required value="<?php echo $boek->isbn; ?>">
        <div class="form-text">Bijv. 978-3-16-148410-0</div>
    </div>
    <div class="d-flex gap-2">
        <input type="submit" name="aanpasknop" class="btn btn-warning" value="Opslaan">
        <a href="index.php" class="btn btn-outline-secondary">Annuleren</a>
    </div>
</form>
<?php else: ?>
<div class="alert alert-info" role="alert">Geen boekgegevens beschikbaar.</div>
<a href="index.php" class="btn btn-outline-secondary">Terug naar overzicht</a>
<?php endif; ?>

<?php include __DIR__ . '/../inc/footer.php'; ?>