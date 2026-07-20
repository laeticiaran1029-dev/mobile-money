<?php

namespace App\Controllers;

use App\Models\CompteModel;

class OperationController extends BaseController
{
    public function depot()
    {
        return $this->afficher('depot', 'Dépôt');
    }

    public function retrait()
    {
        return $this->afficher('retrait', 'Retrait');
    }

    public function transfert()
    {
        return $this->afficher('transfert', 'Transfert');
    }

    public function historique()
    {
        return $this->afficher('historique', 'Historique');
    }

    /**
     * Controle la session puis affiche la vue demandee.
     * Evite de repeter le meme garde dans chaque methode.
     */
    private function afficher(string $vue, string $titre)
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

        return view('client/' . $vue, [
            'compte' => $compte,
            'titre'  => $titre,
        ]);
    }
}
