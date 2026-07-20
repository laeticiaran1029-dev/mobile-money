<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\FraisModel;
use App\Models\HistoriqueModel;
use App\Models\OperationModel;

class OperationController extends BaseController
{
    public function depot()
    {
        return $this->afficher('depot', 'Dépôt');
    }

    public function retrait()
    {
        return $this->afficher('retrait', 'Retrait', [
            'bareme' => (new FraisModel())->baremeDe(OperationModel::RETRAIT),
        ]);
    }

    public function transfert()
    {
        return $this->afficher('transfert', 'Transfert', [
            'bareme' => (new FraisModel())->baremeDe(OperationModel::TRANSFERT),
        ]);
    }

    public function historique()
    {
        $idCompte = session()->get('idCompte');

        if (! $idCompte) {
            return redirect()->to('/');
        }

        return $this->afficher('historique', 'Historique', [
            'transactions' => (new HistoriqueModel())->duCompte((int) $idCompte),
        ]);
    }

    /**
     * Depot : credite le compte, sans frais.
     */
    public function effectuerDepot()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du dépôt doit être supérieur à 0.');
        }

        $this->enregistrer(
            OperationModel::DEPOT,
            (int) $compte['idCompte'],
            null,
            $montant,
            0.0,
            (float) $compte['solde'] + $montant,
            null
        );

        return redirect()->to('depot')->with('succes',
            'Dépôt de ' . $this->formater($montant) . ' Ar effectué.');
    }

    /**
     * Retrait : debite le montant et les frais de la tranche correspondante.
     */
    public function effectuerRetrait()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du retrait doit être supérieur à 0.');
        }

        $frais = (new FraisModel())->calculer(OperationModel::RETRAIT, $montant);
        $solde = (float) $compte['solde'];

        if ($montant + $frais > $solde) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($montant + $frais)
                . ' Ar nécessaires (frais compris), ' . $this->formater($solde) . ' Ar disponibles.');
        }

        $this->enregistrer(
            OperationModel::RETRAIT,
            (int) $compte['idCompte'],
            null,
            $montant,
            $frais,
            $solde - $montant - $frais,
            null
        );

        return redirect()->to('retrait')->with('succes',
            'Retrait de ' . $this->formater($montant) . ' Ar effectué'
            . ($frais > 0 ? ' (frais : ' . $this->formater($frais) . ' Ar).' : '.'));
    }

    /**
     * Transfert : l'emetteur paie montant + frais, le destinataire recoit le
     * montant nominal. Les deux ecritures sont dans la meme transaction.
     */
    public function effectuerTransfert()
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        $montant   = (float) $this->request->getPost('montant');
        $numeroTel = preg_replace('/\D/', '', (string) $this->request->getPost('numeroDestinataire'));

        if ($montant <= 0) {
            return redirect()->back()->with('erreur', 'Le montant du transfert doit être supérieur à 0.');
        }

        if ($numeroTel === $compte['numeroTel']) {
            return redirect()->back()->with('erreur', 'Un transfert vers son propre numéro est impossible.');
        }

        $destinataire = (new CompteModel())->parNumero($numeroTel);

        if ($destinataire === null) {
            return redirect()->back()->with('erreur', "Le numéro {$numeroTel} n'a pas de compte.");
        }

        $frais = (new FraisModel())->calculer(OperationModel::TRANSFERT, $montant);
        $solde = (float) $compte['solde'];

        if ($montant + $frais > $solde) {
            return redirect()->back()->with('erreur',
                'Solde insuffisant : ' . $this->formater($montant + $frais)
                . ' Ar nécessaires (frais compris), ' . $this->formater($solde) . ' Ar disponibles.');
        }

        $this->enregistrer(
            OperationModel::TRANSFERT,
            (int) $compte['idCompte'],
            (int) $destinataire['idCompte'],
            $montant,
            $frais,
            $solde - $montant - $frais,
            (float) $destinataire['solde'] + $montant
        );

        return redirect()->to('transfert')->with('succes',
            $this->formater($montant) . ' Ar envoyés à '
            . $destinataire['prenom'] . ' ' . $destinataire['nom'] . '.');
    }

    /**
     * Ecrit les nouveaux soldes et la ligne d'historique en une seule
     * transaction : un plantage entre le debit et le credit ferait
     * autrement disparaitre de l'argent.
     */
    private function enregistrer(
        int $idOperation,
        int $idEmetteur,
        ?int $idDestinataire,
        float $montant,
        float $frais,
        float $soldeEmetteur,
        ?float $soldeDestinataire
    ): void {
        $db = \Config\Database::connect();
        $db->transStart();

        $compteModel = new CompteModel();
        $compteModel->update($idEmetteur, ['solde' => $soldeEmetteur]);

        if ($idDestinataire !== null && $soldeDestinataire !== null) {
            $compteModel->update($idDestinataire, ['solde' => $soldeDestinataire]);
        }

        (new HistoriqueModel())->insert([
            'idCompte'             => $idEmetteur,
            'idCompteDestinataire' => $idDestinataire,
            'idOperation'          => $idOperation,
            'montant'              => $montant,
            'fraisTotal'           => $frais,
        ]);

        $db->transComplete();
    }

    /**
     * Compte de la session, ou la redirection a renvoyer si elle est invalide.
     *
     * @return array|\CodeIgniter\HTTP\RedirectResponse
     */
    private function compteConnecte()
    {
        $idCompte = session()->get('idCompte');

        if (! $idCompte) {
            return redirect()->to('/');
        }

        $compte = (new CompteModel())->find($idCompte);

        if (! $compte) {
            session()->destroy();

            return redirect()->to('/');
        }

        return $compte;
    }

    /**
     * Controle la session puis affiche la vue demandee.
     * Evite de repeter le meme garde dans chaque methode.
     */
    private function afficher(string $vue, string $titre, array $donnees = [])
    {
        $compte = $this->compteConnecte();

        if (! is_array($compte)) {
            return $compte;
        }

        return view('client/' . $vue, array_merge([
            'compte' => $compte,
            'titre'  => $titre,
        ], $donnees));
    }

    private function formater(float $montant): string
    {
        return number_format($montant, 0, ',', ' ');
    }
}
