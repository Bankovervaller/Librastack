<?php
$pageTitle = $pageTitle ?? 'Wachtwoord herstellen';
include 'inc/header.php';
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h1 class="h3 mb-3">Wachtwoord herstellen</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($tokenValid)): ?>
            <form method="post" action="index.php?controller=user&action=reset" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label for="password" class="form-label">Nieuw wachtwoord</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Bevestig wachtwoord</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Wachtwoord wijzigen</button>
            </form>
        <?php else: ?>
            <p>De reset-link is ongeldig of verlopen.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'inc/footer.php'; ?>

