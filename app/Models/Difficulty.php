<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Difficulty extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'diff_name',
        'point',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
