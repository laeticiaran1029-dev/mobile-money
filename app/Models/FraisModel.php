<?php

namespace App\Models;

use CodeIgniter\Model;

class FraisModel extends Model
{
    protected $table          = 'frais';
    protected $primaryKey     = 'idFrais';
    protected $returnType     = 'array';
    protected $allowedFields  = ['idOperation', 'idOperateur', 'montantMin', 'montantMax', 'frais'];


    public function baremeDe(int $idOperation, int $idOperateur = 1): array
    {
        return $this->where('idOperation', $idOperation)
                    ->where('idOperateur', $idOperateur)
                    ->orderBy('montantMin', 'ASC')
                    ->findAll();
    }

    public function calculer(int $idOperation, float $montant, int $idOperateur = 1): float
    {
       
        $tranche = $this->where('idOperation', $idOperation)
                        ->where('idOperateur', $idOperateur)
                        ->where('montantMin <=', $montant)
                        ->where('montantMax >=', $montant)
                        ->first();

        if ($tranche !== null) {
            return (float) $tranche['frais'];
        }

      
        $inferieure = $this->where('idOperation', $idOperation)
                           ->where('idOperateur', $idOperateur)
                           ->where('montantMax <', $montant)
                           ->orderBy('montantMax', 'DESC')
                           ->first();

        return $inferieure !== null ? (float) $inferieure['frais'] : 0.0;
    }

    
    public function chevauche(int $idOperation, int $idOperateur, float $min, float $max, ?int $idIgnore = null): bool
    {
        $builder = $this->where('idOperation', $idOperation)
                        ->where('idOperateur', $idOperateur)
                        ->where('montantMin <=', $max)
                        ->where('montantMax >=', $min);

        if ($idIgnore !== null) {
            $builder->where('idFrais !=', $idIgnore);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Cout complet d'un transfert entre deux numeros.
     *
     * Deux composantes qui s'additionnent :
     *  - les frais du bareme par paliers de l'operateur emetteur, toujours dus ;
     *  - une commission en % si l'emetteur et le destinataire ne sont pas chez
     *    le meme operateur, lue dans la matrice croisee.
     *
     * @return array{type:string, frais:float, commission:float, taux:float,
     *               operateurSource:?array, operateurDestinataire:?array, total:float}
     */
    public function calculerFraisEtCommission(
        string $numeroExpediteur,
        string $numeroDestinataire,
        float $montant,
        int $idOperation = 3
    ): array {
        $prefixes = new PrefixeModel();
        $opSource = $prefixes->operateurDe($numeroExpediteur);
        $opDest   = $prefixes->operateurDe($numeroDestinataire);

        $idSource = $opSource !== null ? (int) $opSource['idOperateur'] : 0;
        $idDest   = $opDest !== null ? (int) $opDest['idOperateur'] : 0;

        // Le bareme de l'emetteur s'applique dans tous les cas. Si son operateur
        // n'a pas de bareme propre, calculer() retombe sur 0 sans planter.
        $frais = $this->calculer($idOperation, $montant, $idSource);

        // La matrice fait autorite : un couple sans regle ne coute rien de plus.
        // C'est ce qui rend le transfert interne (Yas -> Yas) gratuit de
        // commission, il n'a pas de ligne dans la matrice.
        $taux       = ($idSource !== 0 && $idDest !== 0)
            ? (new CommissionModel())->taux($idSource, $idDest)
            : 0.0;
        // L'ariary n'a pas de subdivision : on arrondit, sinon le total debite
        // ne correspond plus aux montants affiches au client.
        $commission = round(($montant * $taux) / 100);

        return [
            'type'                  => $taux > 0 ? 'commissionne' : 'bareme',
            'frais'                 => $frais,
            'commission'            => $commission,
            'taux'                  => $taux,
            'operateurSource'       => $opSource,
            'operateurDestinataire' => $opDest,
            'total'                 => $frais + $commission,
        ];
    }
}