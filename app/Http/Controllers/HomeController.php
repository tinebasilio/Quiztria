<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Quiz;
use App\Models\Participant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $query = Quiz::whereHas('questions')
            ->withCount('questions')
            ->when(auth()->guest() || !auth()->user()->is_admin, function ($query) {
                return $query->where('published', 1);
            })
            ->get();

        $public_quizzes = $query->where('public', 1);
        // $registered_only_quizzes = $query->where('public', 0);

        // Fetch data for the dashboard
        $totalRooms = Room::count();
        $totalQuizzes = Quiz::count();
        $totalParticipants = Participant::count();

        return view('home', compact('totalRooms', 'totalQuizzes', 'totalParticipants'));
    }

    public function show(Quiz $quiz)
    {
        return view('front.quizzes.show', compact('quiz'));
    }

    public function welcome()
    {
        return view('welcome');
    }
}

