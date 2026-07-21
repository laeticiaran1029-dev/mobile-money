<?php

namespace App\Controllers;

use App\Models\epargneModel;

class EpargneController extends BaseController
{

// public function insererPourcentage(): void
//     {
//         $pourcent   =  $this->request->getPost('epargne');
//         $existe = $this->where('idOperateurSource', $idSource)
//                     ->where('idOperateurDestinataire', $idDestinataire)
//                     ->countAllResults() > 0;

//         if ($existe) {
//             $this->where('idOperateurSource', $idSource)
//                 ->where('idOperateurDestinataire', $idDestinataire)
//                 ->set('taux', $taux)
//                 ->update();

//             return;
//         }

//         $this->insert([
//             'idOperateurSource'       => $idSource,
//             'idOperateurDestinataire' => $idDestinataire,
//             'taux'                    => $taux,
//         ]);
//     }
}