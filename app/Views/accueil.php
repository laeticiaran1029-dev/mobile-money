<?= $this->include('layout/header') ?>

<h1 class="h3">Bonjour <?= esc($compte['prenom']) ?> <?= esc($compte['nom']) ?></h1>
<p class="text-muted"><?= esc($compte['numeroTel']) ?></p>

<div class="card text-bg-primary mb-4" style="max-width: 22rem;">
    <div class="card-body">
        <div class="small">Solde disponible</div>
        <div class="fs-2 fw-bold"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</div>
    </div>
</div>

<?= $this->include('layout/footer') ?>
