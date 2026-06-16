<?php
$pageTitle = $pageTitle ?? 'Wachtwoord vergeten';
include 'inc/header.php';
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h1 class="h3 mb-3">Wachtwoord vergeten</h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-info" role="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?controller=user&action=forgot" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary">Verstuur link</button>
        </form>
    </div>
</div>
<?php include 'inc/footer.php'; ?>

