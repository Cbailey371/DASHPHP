<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $connection = 'erp_db';
    protected $table = 'bills_work_orders';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
        static::saving(fn() => false);
        static::deleting(fn() => false);
    }

    // Relación inversa si fuera necesaria
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
