<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\HistoriqueModel;

class Accueil extends BaseController
{
    public function index()
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

        $transactions = (new HistoriqueModel())->duCompte((int) $idCompte);

        // Frais supportes par ce compte : uniquement sur les operations
        // qu'il a initiees, pas sur les transferts qu'il a recus.
        $fraisPayes = 0.0;
        foreach ($transactions as $ligne) {
            if ((int) $ligne['idCompte'] === (int) $idCompte) {
                $fraisPayes += (float) $ligne['fraisTotal'];
            }
        }

        return view('client/accueil', [
            'compte'         => $compte,
            'titre'          => 'Accueil',
            // Les 5 derniers mouvements suffisent ici, le detail complet
            // est sur la page historique.
            'transactions'   => array_slice($transactions, 0, 5),
            'nbTransactions' => count($transactions),
            'fraisPayes'     => $fraisPayes,
        ]);
    }
}
