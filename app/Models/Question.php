<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'text',
        'code_snippet',
        'answer_explanation',
        'more_info_link',
        'difficulty_id',
        'question_type', // Add question_type here
    ];

    public function difficulty()
    {
        return $this->belongsTo(Difficulty::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->inRandomOrder();
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class);
    }

    public function questionQuizzes()
    {
        return $this->hasMany(QuestionQuiz::class, 'question_id'); // Assuming 'question_id' is the foreign key in the 'question_quiz' table
    }
}
