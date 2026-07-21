<?php

namespace App\Models;

use CodeIgniter\Model;


class CommissionModel extends Model
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'idOperateurSource'; // cle composite : find() n'est pas utilisable
    protected $returnType    = 'array';
    protected $allowedFields = ['idOperateurSource', 'idOperateurDestinataire', 'taux'];

    public function taux(int $idSource, int $idDestinataire): float
    {
        $ligne = $this->where('idOperateurSource', $idSource)
                    ->where('idOperateurDestinataire', $idDestinataire)
                ->first();

        return $ligne !== null ? (float) $ligne['taux'] : 0.0;
    }

    public function matrice(): array
    {
        $matrice = [];

        foreach ($this->findAll() as $ligne) {
            $matrice[(int) $ligne['idOperateurSource']][(int) $ligne['idOperateurDestinataire']]
                = (float) $ligne['taux'];
        }

        return $matrice;
    }


    public function definir(int $idSource, int $idDestinataire, float $taux): void
    {
        $existe = $this->where('idOperateurSource', $idSource)
                    ->where('idOperateurDestinataire', $idDestinataire)
                    ->countAllResults() > 0;

        if ($existe) {
            $this->where('idOperateurSource', $idSource)
                ->where('idOperateurDestinataire', $idDestinataire)
                ->set('taux', $taux)
                ->update();

            return;
        }

        $this->insert([
            'idOperateurSource'       => $idSource,
            'idOperateurDestinataire' => $idDestinataire,
            'taux'                    => $taux,
        ]);
    }
}
