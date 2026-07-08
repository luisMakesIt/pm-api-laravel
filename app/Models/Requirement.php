<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'priority',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(RequirementActa::class, 'requirement_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    // ----- Accessors -----

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pendiente' => 'Pending',
            'en_progreso' => 'In Progress',
            'completado' => 'Completed',
            'rechazado' => 'Rejected',
            default => $this->status,
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'alta' => 'High',
            'media' => 'Medium',
            'baja' => 'Low',
            default => $this->priority,
        };
    }
}
