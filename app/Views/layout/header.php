<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titre ?? 'Mobile Money') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap5_3/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mm-nav-operateur mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('accueil') ?>">
            Mobile Money <span class="mm-etiquette-client">Client</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('depot') ?>">Dépôt</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('retrait') ?>">Retrait</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('transfert') ?>">Transfert</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('historique') ?>">Historique</a>
                </li>
            </ul>

            <a class="btn btn-outline-light btn-sm" href="<?= site_url('logout') ?>">Déconnexion</a>
        </div>
    </div>
</nav>

<div class="container">
