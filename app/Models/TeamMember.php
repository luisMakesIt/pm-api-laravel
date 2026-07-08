<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'email',
        'role',
        'nivel_experiencia',
        'avatar_url',
        'estado',
        'joined_date',
        'git_username',
        'github_url',
        'dev_id',
    ];

    protected $casts = [
        'joined_date' => 'date',
    ];

    // ----- Relationships -----

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'dev_id');
    }

    // ----- Accessors -----

    public function getStatusLabelAttribute(): string
    {
        return match($this->estado) {
            'disponible' => 'Available',
            'en_tarea' => 'In Task',
            'ocupado' => 'Busy',
            'fuera' => 'Away',
            default => $this->estado,
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'developer' => 'Developer',
            'designer' => 'Designer',
            'tester' => 'Tester',
            'tech_lead' => 'Tech Lead',
            default => $this->role,
        };
    }
}
