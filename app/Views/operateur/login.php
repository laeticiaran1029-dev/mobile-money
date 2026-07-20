<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titre ?? 'Connexion opérateur') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap5_3/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<div class="mm-login mm-login-operateur">
    <div class="mm-login-card">

        <h1 class="h4 mb-1">
            Mobile Money <span class="mm-etiquette-operateur">Opérateur</span>
        </h1>
        <p class="text-muted">Accès réservé à l'administration.</p>

        <?php if (session()->getFlashdata('erreur')) : ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= esc(session()->getFlashdata('erreur')) ?>
            </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('succes')) : ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-1"></i>
                <?= esc(session()->getFlashdata('succes')) ?>
            </div>
        <?php endif ?>

        <form action="<?= site_url('operateur/connexion') ?>" method="post">
            <div class="mb-3">
                <label for="motDePasse" class="form-label">Mot de passe</label>
                <input type="password" name="motDePasse" id="motDePasse"
                       class="form-control" required autofocus autocomplete="current-password">
            </div>

            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-shield-lock me-1"></i> Se connecter
                </button>
            </div>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
            <a href="<?= site_url('/') ?>">Retour à l'espace client</a>
        </p>

    </div>
</div>

</body>
</html>
