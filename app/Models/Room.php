<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_name',
        'quiz_id',
        'participant_id',
        'is_at_room',
        'time_spent',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
    public function participantsRoom()
    {
        return $this->hasMany(ParticipantsRoom::class, 'participant_id');
    }
}
