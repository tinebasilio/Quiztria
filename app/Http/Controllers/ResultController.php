<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Answer;
use Illuminate\Contracts\View\View;

class ResultController extends Controller
{
    public function show(Test $test): View
    {
        // Calculate the total number of questions in the quiz
        $questions_count = $test->quiz->questions->count();

        // Fetch results (answers) for the given quiz
        $results = Answer::where('test_id', $test->id)
            ->with('question.options')
            ->get();

        // If the quiz is not public, only retrieve a leaderboard of other users who have taken the same quiz
        if (!$test->quiz->public) {
            $leaderboard = Test::query()
                ->where('quiz_id', $test->quiz_id)
                ->whereHas('user')
                ->with(['user' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->orderBy('result', 'desc')
                ->orderBy('time_spent')
                ->get();

            return view('front.quizzes.result', compact('test', 'questions_count', 'results', 'leaderboard'));
        }

        // If the quiz is public, return the view without the leaderboard
        return view('front.quizzes.result', compact('test', 'questions_count', 'results'));
    }
}
