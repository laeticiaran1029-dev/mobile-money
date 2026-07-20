<?php

namespace App\Models;

use CodeIgniter\Model;

class HistoriqueModel extends Model
{
    protected $table         = 'historique_operation';
    protected $primaryKey    = 'idHistorique';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'idCompte',
        'idCompteDestinataire',
        'idOperation',
        'montant',
        'fraisTotal',
    ];

    /**
     * Transactions d'un compte : celles qu'il a initiees et celles qu'il a recues.
     */
    public function duCompte(int $idCompte): array
    {
        return $this->select('historique_operation.*, operation.type,
                              emetteur.numeroTel AS telEmetteur,
                              destinataire.numeroTel AS telDestinataire')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->join('comptes AS emetteur', 'emetteur.idCompte = historique_operation.idCompte')
                    ->join('comptes AS destinataire',
                           'destinataire.idCompte = historique_operation.idCompteDestinataire', 'left')
                    ->groupStart()
                        ->where('historique_operation.idCompte', $idCompte)
                        ->orWhere('historique_operation.idCompteDestinataire', $idCompte)
                    ->groupEnd()
                    ->orderBy('historique_operation.dateTransaction', 'DESC')
                    ->orderBy('historique_operation.idHistorique', 'DESC')
                    ->findAll();
    }

    /**
     * Gains de l'operateur, ventiles par type d'operation.
     * Les frais encaisses viennent du retrait et du transfert (depot gratuit).
     */
    public function gainsParOperation(): array
    {
        return $this->select('operation.type,
                              COUNT(*) AS nbOperations,
                              SUM(historique_operation.montant) AS volume,
                              SUM(historique_operation.fraisTotal) AS gains')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->groupBy('operation.type')
                    ->orderBy('gains', 'DESC')
                    ->findAll();
    }

    /**
     * Total des frais encaisses, toutes operations confondues.
     */
    public function gainsTotal(): float
    {
        $ligne = $this->selectSum('fraisTotal', 'total')->first();

        return (float) ($ligne['total'] ?? 0);
    }

    /**
     * Les N dernieres transactions, tous comptes confondus.
     */
    public function dernieres(int $limite = 10): array
    {
        return $this->select('historique_operation.*, operation.type,
                              emetteur.numeroTel AS telEmetteur,
                              destinataire.numeroTel AS telDestinataire')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->join('comptes AS emetteur', 'emetteur.idCompte = historique_operation.idCompte')
                    ->join('comptes AS destinataire',
                           'destinataire.idCompte = historique_operation.idCompteDestinataire', 'left')
                    ->orderBy('historique_operation.dateTransaction', 'DESC')
                    ->orderBy('historique_operation.idHistorique', 'DESC')
                    ->findAll($limite);
    }
}
