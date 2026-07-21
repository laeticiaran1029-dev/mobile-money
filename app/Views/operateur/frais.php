<?= $this->include('layout/header_operateur') ?>

<h1 class="h4 mb-4">Barèmes de frais</h1>

<div id="messageFrais" class="alert d-none" role="alert"></div>

<ul class="nav nav-tabs mb-4" role="tablist">
<?php foreach ($operateurs as $rang => $operateur) : ?>
    <?php $idOperateur = (int) $operateur['idOperateur']; ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $rang === 0 ? 'active' : '' ?>"
                data-bs-toggle="tab"
                data-bs-target="#operateur<?= $idOperateur ?>"
                type="button" role="tab">
            <?= esc($operateur['nom']) ?>
        </button>
    </li>
<?php endforeach ?>
</ul>

<div class="tab-content">
<?php foreach ($operateurs as $rang => $operateur) : ?>
    <?php $idOperateur = (int) $operateur['idOperateur']; ?>

    <div class="tab-pane fade <?= $rang === 0 ? 'show active' : '' ?>"
         id="operateur<?= $idOperateur ?>" role="tabpanel">

        <?php foreach ($operations as $operation) : ?>
            <?php
                $id     = (int) $operation['idOperation'];
                $bareme = $baremes[$idOperateur][$id] ?? [];
                $cle    = $idOperateur . '_' . $id;
            ?>

            <div class="card mb-4">
                <div class="card-body">

                    <h2 class="h5 mb-3">
                        <span class="mm-badge mm-badge-<?= esc($operation['type']) ?>">
                            <?= esc(\App\Models\OperationModel::libelle($operation['type'])) ?>
                        </span>
                    </h2>

                    <div class="table-responsive mb-3">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="mm-col-montant">De</th>
                                    <th class="mm-col-montant">À</th>
                                    <th style="width: 12rem;">Frais</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tranches<?= $cle ?>">
                            <?php foreach ($bareme as $tranche) : ?>
                                <tr>
                                    <td class="mm-col-montant"><?= number_format($tranche['montantMin'], 0, ',', ' ') ?> Ar</td>
                                    <td class="mm-col-montant"><?= number_format($tranche['montantMax'], 0, ',', ' ') ?> Ar</td>
                                    <td>
                                        <form action="<?= site_url('operateur/frais/modifier/' . $tranche['idFrais']) ?>"
                                              method="post" class="d-flex gap-2" data-enregistrer>
                                            <input class="form-control form-control-sm" type="number" name="frais"
                                                   min="0" step="1" value="<?= (int) $tranche['frais'] ?>" required>
                                            <button class="btn btn-sm btn-primary">OK</button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form action="<?= site_url('operateur/frais/supprimer/' . $tranche['idFrais']) ?>"
                                              method="post" data-enregistrer data-supprimer>
                                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                            <tbody class="mm-vide <?= $bareme === [] ? '' : 'd-none' ?>">
                                <tr>
                                    <td colspan="4" class="text-muted">
                                        Aucune tranche : cette opération est gratuite.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form action="<?= site_url('operateur/frais/ajouter') ?>" method="post"
                          class="row g-2 align-items-end"
                          data-enregistrer data-cible="tranches<?= $cle ?>">
                        <input type="hidden" name="idOperation" value="<?= $id ?>">
                        <input type="hidden" name="idOperateur" value="<?= $idOperateur ?>">
                        <div class="col-sm-3">
                            <label class="form-label" for="min<?= $cle ?>">Montant min</label>
                            <input class="form-control" type="number" id="min<?= $cle ?>" name="montantMin"
                                   min="0" step="1" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="max<?= $cle ?>">Montant max</label>
                            <input class="form-control" type="number" id="max<?= $cle ?>" name="montantMax"
                                   min="0" step="1" required>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="frais<?= $cle ?>">Frais</label>
                            <input class="form-control" type="number" id="frais<?= $cle ?>" name="frais"
                                   min="0" step="1" required>
                        </div>
                        <div class="col-sm-3">
                            <button class="btn btn-outline-primary w-100">Ajouter la tranche</button>
                        </div>
                    </form>

                </div>
            </div>
        <?php endforeach ?>

        <div class="card mb-4">
            <div class="card-body">

                <h2 class="h5 mb-3">Commissions sur les transferts sortants</h2>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Vers</th>
                                <th style="width: 14rem;">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($operateurs as $destinataire) : ?>
                            <?php
                                $idDest = (int) $destinataire['idOperateur'];
                                $taux   = $matrice[$idOperateur][$idDest] ?? 0.0;
                            ?>
                            <tr>
                                <td><?= esc($destinataire['nom']) ?></td>
                                <td>
                                    <form action="<?= site_url('operateur/frais/commission') ?>"
                                          method="post" class="d-flex gap-2" data-enregistrer>
                                        <input type="hidden" name="idOperateurSource" value="<?= $idOperateur ?>">
                                        <input type="hidden" name="idOperateurDestinataire" value="<?= $idDest ?>">
                                        <div class="input-group input-group-sm">
                                            <input class="form-control" type="number" name="taux"
                                                   min="0" max="100" step="0.1"
                                                   value="<?= $taux ?>" required>
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <button class="btn btn-sm btn-primary">OK</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
<?php endforeach ?>
</div>

<script>
// Les formulaires marques data-enregistrer postent en arriere-plan : la page
// ne se recharge pas, l'onglet courant et la saisie en cours sont conserves.
(function () {
    const message = document.getElementById('messageFrais');
    let effacer;

    const format = (montant) =>
        new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(montant) + ' Ar';

    function annoncer(texte, succes) {
        message.textContent = texte;
        message.className = 'alert alert-' + (succes ? 'success' : 'danger');
        clearTimeout(effacer);
        effacer = setTimeout(() => message.classList.add('d-none'), 4000);
    }

    function ligneTranche(tranche, urlBase) {
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td class="mm-col-montant">' + format(tranche.montantMin) + '</td>'
            + '<td class="mm-col-montant">' + format(tranche.montantMax) + '</td>'
            + '<td><form action="' + urlBase + 'modifier/' + tranche.idFrais + '"'
            + ' method="post" class="d-flex gap-2" data-enregistrer>'
            + '<input class="form-control form-control-sm" type="number" name="frais"'
            + ' min="0" step="1" value="' + tranche.frais + '" required>'
            + '<button class="btn btn-sm btn-primary">OK</button></form></td>'
            + '<td class="text-end"><form action="' + urlBase + 'supprimer/' + tranche.idFrais + '"'
            + ' method="post" data-enregistrer data-supprimer>'
            + '<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td>';
        return tr;
    }

    // Le tableau vide affiche une ligne d'attente : on la masque des qu'il y a
    // une tranche, on la remontre quand la derniere disparait.
    function majLigneVide(corps) {
        const vide = corps.parentElement.querySelector('.mm-vide');
        if (vide) {
            vide.classList.toggle('d-none', corps.rows.length > 0);
        }
    }

    document.addEventListener('submit', async (evenement) => {
        const formulaire = evenement.target;

        if (! formulaire.matches('[data-enregistrer]')) {
            return;
        }

        evenement.preventDefault();

        if (formulaire.hasAttribute('data-supprimer')
            && ! confirm('Supprimer cette tranche ?')) {
            return;
        }

        const bouton = formulaire.querySelector('button');
        bouton.disabled = true;

        try {
            const reponse = await fetch(formulaire.action, {
                method: 'POST',
                body: new FormData(formulaire),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const donnees = await reponse.json();

            if (! reponse.ok) {
                annoncer(donnees.erreur || 'Enregistrement impossible.', false);
                return;
            }

            if (formulaire.hasAttribute('data-supprimer')) {
                const corps = formulaire.closest('tbody');
                formulaire.closest('tr').remove();
                majLigneVide(corps);
            }

            if (donnees.tranche) {
                const corps = document.getElementById(formulaire.dataset.cible);
                const base  = formulaire.action.replace(/ajouter$/, '');
                corps.appendChild(ligneTranche(donnees.tranche, base));
                majLigneVide(corps);
                formulaire.querySelectorAll('input[type="number"]')
                          .forEach((champ) => { champ.value = ''; });
            }

            annoncer(donnees.succes, true);
        } catch (erreur) {
            annoncer('Le serveur ne répond pas.', false);
        } finally {
            bouton.disabled = false;
        }
    });
})();
</script>

<?= $this->include('layout/footer') ?>
