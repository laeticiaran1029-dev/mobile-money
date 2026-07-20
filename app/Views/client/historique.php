<?= $this->include('layout/header') ?>

<h1 class="h4 mb-1">Historique</h1>
<p class="text-muted mb-4" style="font-variant-numeric: tabular-nums;">
    <?= esc($compte['numeroTel']) ?> — <?= count($transactions) ?> transaction<?= count($transactions) > 1 ? 's' : '' ?>
</p>

<?php if ($transactions === []) : ?>
    <p class="text-muted">Aucune transaction enregistrée.</p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Contrepartie</th>
                    <th class="mm-col-montant">Montant</th>
                    <th class="mm-col-montant">Frais</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $ligne) : ?>
                <?php
                    // Credit si le compte a recu un transfert, ou s'il s'agit d'un depot.
                    $estRecu   = (int) ($ligne['idCompteDestinataire'] ?? 0) === (int) $compte['idCompte'];
                    $estCredit = $ligne['type'] === 'depot' || $estRecu;

                    // Les frais d'un transfert sont a la charge de l'emetteur seul.
                    $contrepartie = $estRecu ? $ligne['telEmetteur'] : ($ligne['telDestinataire'] ?? null);
                    $frais        = $estRecu ? 0 : (float) $ligne['fraisTotal'];
                ?>
                <tr>
                    <td><?= esc(date('d/m/Y H:i', strtotime($ligne['dateTransaction']))) ?></td>
                    <td>
                        <span class="mm-badge mm-badge-<?= esc($ligne['type']) ?>">
                            <?= esc(\App\Models\OperationModel::libelle($ligne['type'])) ?>
                            <?php if ($ligne['type'] === 'transfert') : ?>
                                <?= $estRecu ? ' reçu' : ' envoyé' ?>
                            <?php endif ?>
                        </span>
                    </td>
                    <td style="font-variant-numeric: tabular-nums;">
                        <?= esc($contrepartie ?? '—') ?>
                    </td>
                    <td class="mm-col-montant <?= $estCredit ? 'mm-credit' : 'mm-debit' ?>">
                        <?= $estCredit ? '+' : '−' ?><?= number_format($ligne['montant'], 0, ',', ' ') ?> Ar
                    </td>
                    <td class="mm-col-montant">
                        <?= $frais > 0 ? '−' . number_format($frais, 0, ',', ' ') . ' Ar' : '—' ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php endif ?>

<div class="mt-3">
    <a href="<?= site_url('accueil') ?>" class="btn btn-outline-primary">Retour au tableau de bord</a>
</div>

<?= $this->include('layout/footer') ?>
