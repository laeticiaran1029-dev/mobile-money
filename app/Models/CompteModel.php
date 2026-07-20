<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteModel extends Model
{
    protected $table         = 'comptes';
    protected $primaryKey    = 'idCompte';
    protected $returnType    = 'array';
    protected $allowedFields = ['numeroTel', 'solde', 'nom', 'prenom'];
}
