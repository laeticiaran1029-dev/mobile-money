<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteModel extends Model
{
    protected $table         = 'comptes';
    protected $primaryKey    = 'idCompte';
    protected $returnType    = 'array';
    protected $allowedFields = ['numeroTel', 'solde', 'nom', 'prenom'];

    /**
     * Situation des comptes clients pour l'operateur : chaque compte avec
     * son nombre de transactions et les frais qu'il a genere.
     */
    public function situation(): array
    {
        return $this->select('comptes.*,
                              COUNT(historique_operation.idHistorique) AS nbOperations,
                              COALESCE(SUM(historique_operation.fraisTotal), 0) AS fraisGeneres')
                    ->join('historique_operation',
                           'historique_operation.idCompte = comptes.idCompte', 'left')
                    ->groupBy('comptes.idCompte')
                    ->orderBy('comptes.solde', 'DESC')
                    ->findAll();
    }

    /**
     * Somme de l'argent detenu par l'ensemble des clients.
     */
    public function masseMonetaire(): float
    {
        $ligne = $this->selectSum('solde', 'total')->first();

        return (float) ($ligne['total'] ?? 0);
    }

    public function parNumero(string $numeroTel): ?array
    {
        return $this->where('numeroTel', $numeroTel)->first();
    }
}
