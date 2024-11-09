<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'participant_id',
        'quiz_id',
        'room_id',
        'score',
        'ip_address',
        'time_spent',

    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id'); // Ensure the foreign key is correct
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'answers', 'test_id', 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class); // Ensure this is correct
    }
}
