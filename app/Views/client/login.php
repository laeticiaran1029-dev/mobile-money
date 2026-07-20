<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Mobile Money</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap5_3/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<div class="mm-login">
    <div class="mm-login-card">

        <h1 class="h4 mb-1">Mobile Money</h1>
        <p class="text-muted mb-4">Connectez-vous avec votre numéro de téléphone.</p>

        <?php if (session()->getFlashdata('erreur')) : ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('erreur')) ?></div>
        <?php endif ?>

        <form action="<?= site_url('connexion') ?>" method="post">
            <div class="mb-3">
                <label for="numeroTel" class="form-label">Numéro de téléphone</label>
                <input type="tel" name="numeroTel" id="numeroTel" class="form-control"
                       inputmode="numeric" maxlength="10" required
                       placeholder="0340000000" autofocus
                       style="font-variant-numeric: tabular-nums;">
                <div class="form-text">10 chiffres, préfixe autorisé par l'opérateur.</div>
            </div>

            <div class="row g-2">
                <div class="col-sm-6 mb-3">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control">
                </div>
                <div class="col-sm-6 mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control">
                </div>
            </div>
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>
        </form>

        <p class="text-center text-muted mt-4 mb-0">
            <a href="<?= site_url('operateur') ?>">Espace opérateur</a>
        </p>

    </div>
</div>

<script src="<?= base_url('assets/bootstrap5_3/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
