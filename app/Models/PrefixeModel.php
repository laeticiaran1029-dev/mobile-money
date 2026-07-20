<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeModel extends Model
{
    protected $table         = 'prefixes';
    protected $primaryKey    = 'idPrefixe';
    protected $returnType    = 'array';
    protected $allowedFields = ['valeur', 'statut'];

    /**
     * Tous les prefixes, actifs en premier.
     */
    public function tous(): array
    {
        return $this->orderBy('statut', 'DESC')
                    ->orderBy('valeur', 'ASC')
                    ->findAll();
    }

    /**
     * Le prefixe (3 premiers chiffres) est-il autorise a se connecter ?
     * Utilise par l'authentification cote client.
     */
    public function estActif(string $numeroTel): bool
    {
        $prefixe = substr($numeroTel, 0, 3);

        return $this->where('valeur', $prefixe)
                    ->where('statut', 1)
                    ->countAllResults() > 0;
    }
}
