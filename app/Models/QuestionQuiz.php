<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionQuiz extends Model
{
    use HasFactory;

    protected $table = 'question_quiz';

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id'); // Assuming 'question_id' is the foreign key in the 'question_quiz' table
    }
}

