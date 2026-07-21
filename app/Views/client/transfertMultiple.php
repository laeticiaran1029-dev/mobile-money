<?= $this->include('layout/header') ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="card-title mb-1">Transfert Multiple</h2>
                    <p class="text-muted small mb-4">Envoyer de l'argent depuis le compte <?= esc($compte['numeroTel']) ?></p>

                    <?php if (session()->getFlashdata('succes')) : ?>
                        <div class="alert alert-success"><?= session()->getFlashdata('succes') ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('erreur')) : ?>
                        <div class="alert alert-danger"><?= session()->getFlashdata('erreur') ?></div>
                    <?php endif; ?>

                    <div class="bg-primary text-white p-3 rounded mb-4">
                        <small class="d-block text-white-50">Solde disponible</small>
                        <span class="fs-3 fw-bold"><?= number_format($compte['solde'], 0, ',', ' ') ?> Ar</span>
                    </div>

                    <form action="<?= base_url('transfertMultiple') ?>" method="post">
                        
                        <div class="mb-4">
                            <label for="montant_global" class="form-label fw-bold">Montant global à diviser</label>
                            <div class="input-group">
                                <input type="number" name="montant_global" id="montant_global" class="form-control form-control-lg" required min="1">
                                <span class="input-group-text">Ar</span>
                            </div>
                            <small class="text-muted">Ce montant sera divisé équitablement entre chaque numéro valide saisi ci-dessous.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                Numéros des destinataires
                                <button type="button" id="btn-ajouter" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-lg"></i> Ajouter un numéro
                                </button>
                            </label>
                            
                            <div id="conteneur-numeros">
                                <div class="input-group mb-2 ligne-numero">
                                    <input type="text" name="numeros[]" class="form-control" placeholder="Ex: 0380000002" required>
                                    <button type="button" class="btn btn-outline-secondary disabled"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            <small class="text-muted small d-block mt-1">Tous réseaux autorisés. Les frais et commissions s'appliquent selon l'opérateur du destinataire.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Diviser et envoyer l'argent</button>
                            <a href="<?= base_url('transfert') ?>" class="btn btn-link text-decoration-none text-muted">Retour au transfert simple</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const conteneur = document.getElementById('conteneur-numeros');
    const btnAjouter = document.getElementById('btn-ajouter');

    btnAjouter.addEventListener('click', function() {
        const nouvelleLigne = document.createElement('div');
        nouvelleLigne.className = 'input-group mb-2 ligne-numero';
        nouvelleLigne.innerHTML = `
            <input type="text" name="numeros[]" class="form-control" placeholder="Ex: 0380000002" required>
            <button type="button" class="btn btn-outline-danger btn-supprimer"><i class="bi bi-trash"></i> Supprimer</button>
        `;
        conteneur.appendChild(nouvelleLigne);
    });

    conteneur.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-supprimer') || e.target.closest('.btn-supprimer')) {
            const ligne = e.target.closest('.ligne-numero');
            if (ligne) {
                ligne.remove();
            }
        }
    });
});
</script>

<?= $this->include('layout/footer') ?>