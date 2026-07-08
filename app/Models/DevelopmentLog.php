<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevelopmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'developer_name',
        'developer_email',
        'tipo_accion',
        'descripcion',
        'tiempo_gastado_minutos',
        'fecha_registro',
        'link_o_ref',
        'developer_id',
    ];

    protected $casts = [
        'tiempo_gastado_minutos' => 'float',
        'fecha_registro' => 'datetime',
    ];

    // ----- Relationships -----

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'developer_id');
    }

    // ----- Accessors -----

    public function getTipoAccLabelAttribute(): string
    {
        return match($this->tipo_accion) {
            'commit' => 'Commit',
            'fix' => 'Fix',
            'feature' => 'Feature',
            'review' => 'Review',
            'deploy' => 'Deploy',
            default => $this->tipo_accion,
        };
    }
}
