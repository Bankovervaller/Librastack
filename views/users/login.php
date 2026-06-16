<?php
$pageTitle = $pageTitle ?? 'Inloggen';
include 'inc/header.php';
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h1 class="h3 mb-3">Inloggen</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?controller=user&action=login" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary">Inloggen</button>
                <a href="index.php?controller=user&action=forgot">Wachtwoord vergeten?</a>
            </div>
        </form>
        <p class="mt-3">Nog geen account? <a href="index.php?controller=user&action=register">Registreren</a></p>
    </div>
</div>
<?php include 'inc/footer.php'; ?>

