<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementActa extends Model
{
    use HasFactory;

    protected $table = 'requirement_actas';

    protected $fillable = [
        'requirement_id',
        'fecha_sesion',
        'cliente_nombre',
        'cliente_email',
        'cliente_empresa',
        'participantes',
        'notas',
        'firmas',
        'acuerdos',
        'fecha_firma_acta',
        'estado_firma',
    ];

    protected $casts = [
        'fecha_sesion' => 'date',
        'fecha_firma_acta' => 'datetime',
        'participantes' => 'array',
        'acuerdos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class, 'requirement_id');
    }
}
