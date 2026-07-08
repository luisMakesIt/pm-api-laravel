<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'git_repo_url',
        'client_name',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function requirements(): HasMany
    {
        return $this->hasMany(Requirement::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    // ----- Accessors -----

    public function getProgresoAttribute(): float
    {
        $total = $this->requirements()->count();
        if ($total === 0) {
            return 0.0;
        }
        $completed = $this->requirements()->where('status', 'completado')->count();
        return round(($completed / $total) * 100, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planificacion' => 'Planning',
            'en_desarrollo' => 'In Development',
            'en_pruebas' => 'In Testing',
            'completado' => 'Completed',
            'cancelado' => 'Cancelled',
            default => $this->status,
        };
    }
}
