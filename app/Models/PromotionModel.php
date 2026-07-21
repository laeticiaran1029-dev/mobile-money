<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionModel extends Model
{
    protected $table         = 'promotion';
    protected $primaryKey    = 'idPromotion';
    protected $returnType    = 'array';
    protected $allowedFields = ['nomOperateur', 'idOperateur', 'fraisPromo'];


    public function promotion(): array
    {
        $promotion = [];

        foreach ($this->findAll() as $ligne) {
            $promotion[(String) $ligne['nomOperateur']][(int) $ligne['idOperateur_prom']]
                = (float) $ligne['fraisPromo'];
        }

        return $promotion;
    }


}
