<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\QuoteStatus;
use App\Enums\SalesTerm;

class Quote extends Model
{
    /**
     * La conexión a base de datos que usa este modelo.
     */
    protected $connection = 'erp_db';

    protected $table = 'quotes';
    protected $primaryKey = 'id';
    public $incrementing = false; // Importante para IDs alfanuméricos como COT00001
    protected $keyType = 'string';

    // Deshabilitar timestamps si la tabla no tiene created_at/updated_at estándar
    public $timestamps = false;

    public function customer()
    {
        // Relación: quotes.Cliente -> customers.id
        return $this->belongsTo(Customer::class, 'Cliente', 'id');
    }

    protected function casts(): array
    {
        return [
            'Status' => QuoteStatus::class,
            'SalesTerm' => SalesTerm::class,
        ];
    }

    /**
     * Bloquear escrituras (Read Only)
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            return false;
        });

        static::deleting(function ($model) {
            return false;
        });
    }

    /**
     * Relación con WorkOrder.
     * Asumiendo que WorkOrder tiene 'quote_id' o similar.
     */
    public function workOrder(): HasOne
    {
        // Relación: bills_work_orders.Invoice -> quotes.id
        // El usuario indicó: "la relacion es invoice en bills_workorders y id de quotes"
        return $this->hasOne(WorkOrder::class, 'Invoice', 'id');
    }

    /**
     * Accessor para antigüedad en días
     */
    public function getDaysOldAttribute()
    {
        // Campo real según dump: "Date" (Capitalized)
        // Pero en la propiedad del modelo, Eloquent suele permitir lowercase si no es estricto, 
        // pero usaremos el nombre exacto del atributo si es posible o el standard accessor.
        // En el dump: "Date"
        $date = $this->Date ?? $this->date;

        if (!$date) {
            return 0;
        }

        return \Carbon\Carbon::parse($date)->diffInDays(now());
    }

    /**
     * Scope para filtrar pendientes sin WO
     */
    public function scopeApprovedWithoutWorkOrder($query)
    {
        // En la BD el estado es "APROVED" (typo) o "ACTIVE" (para las vivas)
        return $query->whereIn('Status', ['APROVED', 'ACTIVE'])->doesntHave('workOrder');
    }
}
