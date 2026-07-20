<?php

namespace App\Models;

use CodeIgniter\Model;

class FraisModel extends Model
{
    protected $table         = 'frais';
    protected $primaryKey    = 'idFrais';
    protected $returnType    = 'array';
    protected $allowedFields = ['idOperation', 'montantMin', 'montantMax', 'frais'];

    /**
     * Bareme complet d'une operation, tranches croissantes.
     */
    public function baremeDe(int $idOperation): array
    {
        return $this->where('idOperation', $idOperation)
                    ->orderBy('montantMin', 'ASC')
                    ->findAll();
    }

    /**
     * Frais applicables a un montant pour une operation donnee.
     *
     * Le bareme du sujet comporte des trous (rien entre 1000 et 1001, rien
     * au-dessus de 2 000 000). Plutot que de renvoyer 0 en silence :
     *   - au-dessus de la derniere tranche  -> frais de la tranche la plus haute
     *   - dans un trou entre deux tranches  -> frais de la tranche juste en dessous
     *   - en dessous de la premiere tranche -> 0
     */
    public function calculer(int $idOperation, float $montant): float
    {
        $tranche = $this->where('idOperation', $idOperation)
                        ->where('montantMin <=', $montant)
                        ->where('montantMax >=', $montant)
                        ->first();

        if ($tranche !== null) {
            return (float) $tranche['frais'];
        }

        $inferieure = $this->where('idOperation', $idOperation)
                           ->where('montantMax <', $montant)
                           ->orderBy('montantMax', 'DESC')
                           ->first();

        return $inferieure !== null ? (float) $inferieure['frais'] : 0.0;
    }

    /**
     * Une tranche en chevauche-t-elle une autre de la meme operation ?
     * Empeche l'operateur de creer un bareme ambigu.
     */
    public function chevauche(int $idOperation, float $min, float $max, ?int $idIgnore = null): bool
    {
        $builder = $this->where('idOperation', $idOperation)
                        ->where('montantMin <=', $max)
                        ->where('montantMax >=', $min);

        if ($idIgnore !== null) {
            $builder->where('idFrais !=', $idIgnore);
        }

        return $builder->countAllResults() > 0;
    }
}
