<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/css/style.css">
    <title><?php echo $pageTitle ?? 'Boeken Applicatie'; ?></title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand"
           href="https://librastack.xo.je/">
            <img src="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/assets/img/librastack_white.svg" alt="Boeken App" height="45" class="d-inline-block align-text-top">
        </a>
        <div class="d-flex ms-auto align-items-center gap-2">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a class="btn btn-outline-light" href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?voegtoe=true">+ Nieuw boek</a>
                <a class="btn btn-outline-light" href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?controller=user&action=profile">Profiel</a>
                <form method="post" action="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?controller=user&action=logout" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-warning">Uitloggen</button>
                </form>
            <?php else: ?>
                <a class="btn btn-outline-light" href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?controller=user&action=login">Inloggen</a>
                <a class="btn btn-outline-light" href="<?php echo dirname($_SERVER['SCRIPT_NAME']) === '/' ? '' : dirname($_SERVER['SCRIPT_NAME']); ?>/index.php?controller=user&action=register">Registreren</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">