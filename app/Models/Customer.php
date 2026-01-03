<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'erp_db';
    protected $table = 'customers';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Campos que podrían ser útiles
    protected $fillable = [
        'id',
        'Empresa',
        'Email',
        'Contact',
        // ... otros campos si fueran necesarios
    ];
}
