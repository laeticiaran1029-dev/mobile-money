<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $table         = 'operation';
    protected $primaryKey    = 'idOperation';
    protected $returnType    = 'array';
    protected $allowedFields = ['type'];

    /** Identifiants fixes, poses par base.sql */
    public const DEPOT     = 1;
    public const RETRAIT   = 2;
    public const TRANSFERT = 3;

    /**
     * Libelle affichable pour un type stocke en base.
     */
    public static function libelle(string $type): string
    {
        return match ($type) {
            'depot'     => 'Dépôt',
            'retrait'   => 'Retrait',
            'transfert' => 'Transfert',
            default     => ucfirst($type),
        };
    }
}
