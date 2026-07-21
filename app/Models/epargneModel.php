<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteModel extends Model
{
    protected $table         = 'epargne';
    protected $primaryKey    = 'idEpargne';
    protected $returnType    = 'array';
    protected $allowedFields = ['idCompte','pourcent_epargne'];

    
}