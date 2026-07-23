<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PathwayStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'pathway_id',
        'position',
        'title',
        'description',
        'resource_link',
        'estimated_hours',
    ];

    public function pathway(): BelongsTo
    {
        return $this->belongsTo(Pathway::class);
    }
}
