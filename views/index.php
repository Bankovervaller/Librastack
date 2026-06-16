<?php
// Fix incorrect include path and render a simple landing that links to the list
$pageTitle = 'Overzicht';
require __DIR__ . '/../inc/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Boeken Overzicht</h1>
    <a href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?voegtoe=true" class="btn btn-primary">+ Nieuw boek</a>
</div>
<p class="text-muted">Ga naar de boekenlijst om alle items te bekijken.</p>
<p><a class="btn btn-outline-secondary" href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php">Naar boekenlijst</a></p>

<?php require __DIR__ . '/../inc/footer.php'; ?>
