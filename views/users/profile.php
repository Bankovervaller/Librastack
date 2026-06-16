<?php
$pageTitle = $pageTitle ?? 'Profiel';
include 'inc/header.php';
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <h1 class="h3 mb-3">Profiel</h1>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?controller=user&action=profile" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">E-mailadres</label>
                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->email); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="display_name" class="form-label">Naam</label>
                <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo htmlspecialchars($user->display_name); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Opslaan</button>
        </form>
        <form method="post" action="index.php?controller=user&action=logout" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" class="btn btn-outline-danger">Uitloggen</button>
        </form>
    </div>
</div>
<?php include 'inc/footer.php'; ?>

