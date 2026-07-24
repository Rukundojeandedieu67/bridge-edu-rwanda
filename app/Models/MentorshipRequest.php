<?php

namespace App\Models;

use App\Models\MentorshipMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MentorshipRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mentor_id',
        'status',
        'topic_of_interest',
        'assigned_by_admin_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MentorshipMessage::class, 'mentorship_request_id');
    }
}
