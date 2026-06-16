<?php $pageTitle = 'Nieuw Boek'; ?>
<?php require 'inc/header.php'; ?>

<h1 class="h4">Nieuw boek</h1>
<form method="post" action="">
    <div class="mb-3">
        <label for="naam" class="form-label">Titel</label>
        <input type="text" class="form-control" id="naam" name="naam" required>
        <div class="form-text">Voer de volledige titel in.</div>
    </div>
    <div class="mb-3">
        <label for="auteur" class="form-label">Auteur</label>
        <input type="text" class="form-control" id="auteur" name="auteur" required>
        <div class="form-text">Voor- en achternaam van de auteur.</div>
    </div>
    <div class="mb-3">
        <label for="isbn" class="form-label">ISBN</label>
        <input type="text" class="form-control" id="isbn" name="isbn" required>
        <div class="form-text">Bijv. 978-3-16-148410-0</div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" name="knop" class="btn btn-success">Opslaan</button>
        <a href="index.php" class="btn btn-outline-secondary">Annuleren</a>
    </div>
</form>

<?php require 'inc/footer.php'; ?>