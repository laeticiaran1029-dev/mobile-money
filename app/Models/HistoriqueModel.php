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
        'numeroDestinataire',
        'idOperateurDestinataire',
        'commission',
        'fraisRetraitInclus',
    ];


    public function duCompte(int $idCompte): array
    {
        return $this->select('historique_operation.*, operation.type,
                              emetteur.numeroTel AS telEmetteur,
                              COALESCE(destinataire.numeroTel,
                                    historique_operation.numeroDestinataire) AS telDestinataire,
                              operateurDest.nom AS operateurDestinataire')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->join('comptes AS emetteur', 'emetteur.idCompte = historique_operation.idCompte')
                    ->join('comptes AS destinataire',
                           'destinataire.idCompte = historique_operation.idCompteDestinataire', 'left')
                    ->join('operateurs AS operateurDest',
                           'operateurDest.idOperateur = historique_operation.idOperateurDestinataire', 'left')
                    ->groupStart()
                        ->where('historique_operation.idCompte', $idCompte)
                        ->orWhere('historique_operation.idCompteDestinataire', $idCompte)
                    ->groupEnd()
                    ->orderBy('historique_operation.dateTransaction', 'DESC')
                    ->orderBy('historique_operation.idHistorique', 'DESC')
                    ->findAll();
    }


    public function gainsParOperation(): array
    {
        return $this->select('operation.type,
                              COUNT(*) AS nbOperations,
                              SUM(historique_operation.montant) AS volume,
                              SUM(historique_operation.fraisTotal) AS gains,
                              SUM(historique_operation.commission) AS commissions')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->groupBy('operation.type')
                    ->orderBy('gains', 'DESC')
                    ->findAll();
    }


    public function gainsTotal(): float
    {
        $ligne = $this->selectSum('fraisTotal', 'total')->first();

        return (float) ($ligne['total'] ?? 0);
    }


    public function commissionsTotal(): float
    {
        $ligne = $this->selectSum('commission', 'total')->first();

        return (float) ($ligne['total'] ?? 0);
    }

    /**
     * Gains ventiles par operateur emetteur : qui a genere les frais et les
     * commissions que la plateforme encaisse. L'operateur se deduit du prefixe
     * du numero qui a lance l'operation.
     */
    public function gainsParOperateur(): array
    {
        return $this->db->table('operateurs')
                        ->select('operateurs.nom,
                                  COUNT(historique_operation.idHistorique) AS nbOperations,
                                  COALESCE(SUM(historique_operation.montant), 0) AS volume,
                                  COALESCE(SUM(historique_operation.fraisTotal), 0) AS frais,
                                  COALESCE(SUM(historique_operation.commission), 0) AS commissions')
                        ->join('prefixes', 'prefixes.idOperateur = operateurs.idOperateur', 'left')
                        ->join('comptes',
                               'SUBSTR(comptes.numeroTel, 1, 3) = prefixes.valeur', 'left')
                        ->join('historique_operation',
                               'historique_operation.idCompte = comptes.idCompte', 'left')
                        ->groupBy('operateurs.idOperateur')
                        ->orderBy('operateurs.nom', 'ASC')
                        ->get()
                        ->getResultArray();
    }

    /**
     * Fonds a reverser a chaque operateur : le total transfere vers ses
     * abonnes, que la plateforme a credite elle-meme.
     */
    public function montantsDusParOperateur(): array
    {
        return $this->db->table('operateurs')
                        ->select('operateurs.idOperateur, operateurs.nom,
                                  COUNT(historique_operation.idHistorique) AS nbTransferts,
                                  COALESCE(SUM(historique_operation.montant), 0) AS montantDu,
                                  COALESCE(SUM(historique_operation.commission), 0) AS commissionsEncaissees')
                        ->join('historique_operation',
                               'historique_operation.idOperateurDestinataire = operateurs.idOperateur',
                               'left')
                        ->groupBy('operateurs.idOperateur')
                        ->orderBy('montantDu', 'DESC')
                        ->get()
                        ->getResultArray();
    }


    public function dernieres(int $limite = 10): array
    {
        return $this->select('historique_operation.*, operation.type,
                              emetteur.numeroTel AS telEmetteur,
                              COALESCE(destinataire.numeroTel,
                                       historique_operation.numeroDestinataire) AS telDestinataire,
                              operateurDest.nom AS operateurDestinataire')
                    ->join('operation', 'operation.idOperation = historique_operation.idOperation')
                    ->join('comptes AS emetteur', 'emetteur.idCompte = historique_operation.idCompte')
                    ->join('comptes AS destinataire',
                           'destinataire.idCompte = historique_operation.idCompteDestinataire', 'left')
                    ->join('operateurs AS operateurDest',
                           'operateurDest.idOperateur = historique_operation.idOperateurDestinataire', 'left')
                    ->orderBy('historique_operation.dateTransaction', 'DESC')
                    ->orderBy('historique_operation.idHistorique', 'DESC')
                    ->findAll($limite);
    }
}
