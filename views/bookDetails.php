<?php $pageTitle = 'Boekdetails'; ?>
<?php require 'inc/header.php'; ?>

<?php if (isset($boek)): ?>
<div class="card">
    <div class="card-body">
        <h1 class="h4"><?= htmlspecialchars_decode($boek->title) ?></h1>
        <div class="row g-3 mt-2">
            <div class="col-12 col-md-6">
                <div><span class="text-muted">ID:</span> <?= $boek->id ?></div>
                <div><span class="text-muted">Auteur:</span> <?= htmlspecialchars_decode($boek->author) ?></div>
            </div>
            <div class="col-12 col-md-6">
                <div><span class="text-muted">ISBN:</span> <?= htmlspecialchars_decode($boek->isbn) ?></div>
                <div><span class="text-muted">Datum toegevoegd:</span> <?= htmlspecialchars_decode($boek->date_added) ?></div>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <div class="btn-group" role="group" aria-label="Acties">
            <a href="index.php?pasaan=<?= $boek->id ?>" class="btn btn-warning">Boek aanpassen</a>
            <a href="index.php?verwijder=<?= $boek->id ?>" class="btn btn-danger">Boek verwijderen</a>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">Terug naar overzicht</a>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info" role="alert">Geen boekgegevens beschikbaar.</div>
<a href="index.php" class="btn btn-outline-secondary">Terug naar overzicht</a>
<?php endif; ?>

<?php require 'inc/footer.php'; ?>