<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table         = 'operateurs';
    protected $primaryKey    = 'idOperateur';
    protected $returnType    = 'array';
    protected $allowedFields = ['nom'];

    /**
     * Operateur des 3 premiers chiffres d'un numero, si le prefixe est actif.
     */
    public function parPrefixe(string $prefixe): ?array
    {
        return $this->select('operateurs.*')
                    ->join('prefixes', 'prefixes.idOperateur = operateurs.idOperateur')
                    ->where('prefixes.valeur', $prefixe)
                    ->where('prefixes.statut', 1)
                    ->first();
    }

    public function tous(): array
    {
        return $this->orderBy('nom', 'ASC')->findAll();
    }

    /**
     * Chaque operateur avec ses prefixes en une chaine, pour l'affichage.
     */
    public function avecPrefixes(): array
    {
        return $this->select('operateurs.*, GROUP_CONCAT(prefixes.valeur) AS prefixes')
                    ->join('prefixes', 'prefixes.idOperateur = operateurs.idOperateur', 'left')
                    ->where('prefixes.statut', 1)
                    ->groupBy('operateurs.idOperateur')
                    ->orderBy('operateurs.nom', 'ASC')
                    ->findAll();
    }
}
