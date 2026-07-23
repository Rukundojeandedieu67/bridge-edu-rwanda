<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorshipRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'mentor_id',
        'status',
        'topic_of_interest',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}
