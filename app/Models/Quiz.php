<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'published',
        'public',
    ];

    protected $casts = [
        'published' => 'boolean',
        'public' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'slug'; // Use the slug for routing
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class);
    }

    public function difficulties()
    {
        return $this->hasMany(Difficulty::class, 'quiz_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'quiz_id'); // Relationship to participants
    }

    public function scopePublic($query)
    {
        return $query->where('public', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
