<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable; // This is the correct class

class Participant extends Authenticatable // Change from Model to Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'quiz_id',
    ];

    /**
     * A participant belongs to a quiz.
     *
     * @return BelongsTo
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participantsRoom()
    {
        return $this->hasMany(ParticipantsRoom::class, 'participant_id'); // Foreign key is participant_id here
    }

    public function tests(): HasMany
    {
        return $this->hasMany(Test::class, 'participant_id');
    }
}
