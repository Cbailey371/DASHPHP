<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomWidget extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sqlReport()
    {
        return $this->belongsTo(SqlReport::class);
    }
    //
}
