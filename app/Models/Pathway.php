<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pathway extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'target_role',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(PathwayStep::class)->orderBy('position');
    }
}
