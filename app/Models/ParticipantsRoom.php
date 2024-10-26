<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipantsRoom extends Model
{
    use HasFactory, SoftDeletes;

    // Explicitly define the table name
    protected $table = 'participants_room';

    protected $fillable = [
        'room_id',
        'participant_id',
        'Is_at_room',
    ];

    /**
     * Define the relationship with the Room model.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id'); // Ensure the foreign key is correct
    }

    /**
     * Define the relationship with the Participant model.
     */

     public function participant()
     {
         return $this->belongsTo(Participant::class, 'participant_id');
     }
}
