<?php $pageTitle = 'Nieuw Boek'; ?>
<?php require 'inc/header.php'; ?>

<h1 class="h4">Nieuw boek</h1>
<form method="post" action="" data-google-books-form>
    <div class="mb-3 google-books-field">
        <label for="naam" class="form-label">Titel</label>
        <input type="text" class="form-control google-books-input" id="naam" name="naam" required autocomplete="off" data-google-books-type="all">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
    </div>
    <div class="mb-3 google-books-field">
        <label for="auteur" class="form-label">Auteur</label>
        <input type="text" class="form-control google-books-input" id="auteur" name="auteur" required autocomplete="off" data-google-books-type="all">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
    </div>
    <div class="mb-3 google-books-field">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" class="form-control google-books-input" id="isbn" name="isbn" required autocomplete="off" data-google-books-type="all">
        <div class="list-group google-books-suggestions" role="listbox" aria-label="Google Books suggesties"></div>
        <div class="form-text">Typ een titel, auteur of ISBN en kies een suggestie om alles in te vullen.</div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" name="knop" class="btn btn-success">Opslaan</button>
        <a href="index.php" class="btn btn-outline-secondary">Annuleren</a>
    </div>
</form>

<script src="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/assets/js/google-books-autocomplete.js"></script>

<?php require 'inc/footer.php'; ?>
