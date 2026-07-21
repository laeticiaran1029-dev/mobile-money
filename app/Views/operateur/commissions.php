<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-1">Commissions inter-opérateurs</h1>
<p class="text-muted mb-4">
    Vu d'ensemble des <a href="<?= site_url('operateur/frais') ?>">Barèmes de frais</a>.
</p>

<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>De \ Vers</th>
            <?php foreach ($operateurs as $destinataire) : ?>
                <th class="mm-col-montant"><?= esc($destinataire['nom']) ?></th>
            <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($operateurs as $source) : ?>
            <?php $idSource = (int) $source['idOperateur']; ?>
            <tr>
                <th scope="row"><?= esc($source['nom']) ?></th>

            <?php foreach ($operateurs as $destinataire) : ?>
                <?php
                    $idDest = (int) $destinataire['idOperateur'];
                    $taux   = $matrice[$idSource][$idDest] ?? 0.0;
                ?>
                <td class="mm-col-montant">
                    <?php if ($taux > 0) : ?>
                        <?= esc(rtrim(rtrim(number_format($taux, 2, ',', ' '), '0'), ',')) ?> %
                    <?php else : ?>
                        <span class="text-muted">—</span>
                    <?php endif ?>
                </td>
            <?php endforeach ?>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>


<?= $this->include('layout/footer') ?>
