<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeModel extends Model
{
    protected $table         = 'prefixes';
    protected $primaryKey    = 'idPrefixe';
    protected $returnType    = 'array';
    protected $allowedFields = ['valeur', 'idOperateur', 'statut'];


    public function tous(): array
    {
        return $this->select('prefixes.*, operateurs.nom AS nomOperateur')
                    ->join('operateurs', 'operateurs.idOperateur = prefixes.idOperateur', 'left')
                    ->orderBy('prefixes.statut', 'DESC')
                    ->orderBy('operateurs.nom', 'ASC')
                    ->orderBy('prefixes.valeur', 'ASC')
                    ->findAll();
    }


    public function estActif(string $numeroTel): bool
    {
        return $this->operateurDe($numeroTel) !== null;
    }


    public function operateurDe(string $numeroTel): ?array
    {
        return $this->select('prefixes.idPrefixe, prefixes.valeur,
                              operateurs.idOperateur, operateurs.nom')
                    ->join('operateurs', 'operateurs.idOperateur = prefixes.idOperateur')
                    ->where('prefixes.valeur', substr($numeroTel, 0, 3))
                    ->where('prefixes.statut', 1)
                    ->first();
    }
}
