<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'name',
        'description',
        'type',
        'url_or_path',
        'version',
        'created_by',
        'created_at',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ----- Relationships -----

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ----- Accessors -----

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'documento' => 'Document',
            'codigo' => 'Code',
            'diseno' => 'Design',
            'testcase' => 'Test Case',
            'configuracion' => 'Configuration',
            default => $this->type,
        };
    }
}
