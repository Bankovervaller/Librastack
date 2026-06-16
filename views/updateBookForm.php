<?php $pageTitle = 'Boek Aanpassen'; ?>
<?php include __DIR__ . '/../inc/header.php'; ?>

<?php $action = 'index.php'; if (isset($boek)) { $action = 'index.php?pasaan=' . $boek->id; } ?>

<?php if (isset($boek)): ?>
<h1 class="h4">Boek aanpassen</h1>
<form method="post" action="<?php echo $action; ?>" data-google-books-form>
    <input type="hidden" name="id" value="<?php echo $boek->id; ?>">
    <div class="mb-3 google-books-field">
        <label for="naam" class="form-label">Titel</label>
        <input type="text" class="form-control google-books-input" id="naam" name="naam" required autocomplete="off" data-google-books-type="all" value="<?php echo htmlspecialchars_decode($boek->title); ?>">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
    </div>
    <div class="mb-3 google-books-field">
        <label for="auteur" class="form-label">Auteur</label>
        <input type="text" class="form-control google-books-input" id="auteur" name="auteur" required autocomplete="off" data-google-books-type="all" value="<?php echo htmlspecialchars_decode($boek->author); ?>">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
    </div>
    <div class="mb-3 google-books-field">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" class="form-control google-books-input" id="isbn" name="isbn" required autocomplete="off" data-google-books-type="all" value="<?php echo $boek->isbn; ?>">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
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

<script src="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/assets/js/google-books-autocomplete.js"></script>

<?php include __DIR__ . '/../inc/footer.php'; ?>
