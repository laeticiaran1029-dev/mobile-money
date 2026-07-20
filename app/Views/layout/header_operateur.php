<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titre ?? 'Espace opérateur') ?> — Opérateur</title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap5_3/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mm-nav-operateur mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('operateur') ?>">
            Mobile Money <span class="mm-etiquette-operateur">Opérateur</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuOperateur">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuOperateur">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('operateur/prefixes') ?>">Préfixes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('operateur/frais') ?>">Barèmes de frais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('operateur/gains') ?>">Gains</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('operateur/comptes') ?>">Comptes clients</a>
                </li>
            </ul>

            <a class="btn btn-outline-light btn-sm" href="<?= site_url('operateur/deconnexion') ?>">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>

<div class="container">

    <?php if (session()->getFlashdata('succes')) : ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('succes')) ?></div>
    <?php endif ?>

    <?php if (session()->getFlashdata('erreur')) : ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('erreur')) ?></div>
    <?php endif ?>
