<?php

namespace App\Controllers;

use App\Models\CompteModel;

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

        return view('accueil', ['compte' => $compte]);
    }
}
