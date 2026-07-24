<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'description',
        'provider_name',
        'eligibility_criteria',
        'application_deadline',
        'external_link',
        'region_tags',
        'is_verified',
        'created_by',
    ];

    protected $casts = [
        'region_tags' => 'array',
        'application_deadline' => 'date',
        'is_verified' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(OpportunityApplication::class);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeRegion($query, string $region)
    {
        return $query->whereJsonContains('region_tags', $region);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereDate('application_deadline', '>=', today());
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('title', 'like', "%{$term}%");
    }
}
