<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requirement_id',
        'title',
        'description',
        'status',
        'fecha_inicio_planificada',
        'fecha_limite',
        'tiempo_estimado_horas',
        'tiempo_real_horas',
        'asignado_a',
    ];

    protected $casts = [
        'fecha_inicio_planificada' => 'date',
        'fecha_limite' => 'date',
        'tiempo_estimado_horas' => 'float',
        'tiempo_real_horas' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function project(): HasOneThrough
    {
        return $this->hasOneThrough(Project::class, Requirement::class, 'id', 'id', 'requirement_id', 'project_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function developmentLogs(): HasMany
    {
        return $this->hasMany(DevelopmentLog::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    // ----- Accessors -----

    public function getOverdueAttribute(): bool
    {
        if ($this->status === 'completada' || !$this->fecha_limite) {
            return false;
        }
        return now()->gt($this->fecha_limite);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendiente' => 'Pending',
            'en_progreso' => 'In Progress',
            'completada' => 'Completed',
            'bloqueada' => 'Blocked',
            default => $this->status,
        };
    }

    public function getCompletionPercentageAttribute(): int
    {
        return match($this->status) {
            'pendiente' => 0,
            'en_progreso' => 50,
            'completada' => 100,
            'bloqueada' => 0,
            default => 0,
        };
    }
}
