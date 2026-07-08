<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table      = 'suppliers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = ['name', 'enabled', 'wallet'];

    public function getEnabled(): array
    {
        return $this->where('enabled', 1)->orderBy('name', 'ASC')->findAll();
    }
}
