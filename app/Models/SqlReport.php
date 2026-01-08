<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SqlReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'chart_config' => 'array',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($report) {
            $query = strtolower($report->sql_query);
            $forbidden = ['insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'create', 'replace', 'grant', 'revoke'];

            foreach ($forbidden as $word) {
                if (preg_match('/\b' . $word . '\b/', $query)) {
                    throw new \Exception("Seguridad: La palabra clave '$word' no está permitida. Solo se permiten consultas SELECT de lectura.");
                }
            }
        });
    }
}
