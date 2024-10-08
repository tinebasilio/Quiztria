<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'text',
        'correct',
        'question_id',
    ];

    protected $casts = [
        'correct' => 'boolean'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
    
}
