<?php
$pageTitle = $pageTitle ?? 'Registreren';
include 'inc/header.php';
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h1 class="h3 mb-3">Registreren</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?controller=user&action=register" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="display_name" class="form-label">Naam</label>
                <input type="text" class="form-control" id="display_name" name="display_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="form-text">Minimaal 8 tekens</div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Bevestig wachtwoord</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Account aanmaken</button>
        </form>
        <p class="mt-3">Al een account? <a href="index.php?controller=user&action=login">Inloggen</a></p>
    </div>
</div>
<?php include 'inc/footer.php'; ?>

