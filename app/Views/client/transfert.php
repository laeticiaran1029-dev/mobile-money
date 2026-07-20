<?= $this->include('layout/header') ?>

<h1 class="h3">Bonjour <?= esc($compte['prenom']) ?> <?= esc($compte['nom']) ?></h1>
<p class="text-muted"><?= esc($compte['numeroTel']) ?></p>



<?= $this->include('layout/footer') ?>