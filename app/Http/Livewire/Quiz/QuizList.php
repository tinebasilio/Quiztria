<?php

namespace App\Http\Livewire\Quiz;

use App\Models\Quiz;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class QuizList extends Component
{
    public $showDeleteModal = false;
    public $showRestoreModal = false; // Added for restore modal
    public $quizToDelete;
    public $quizToRestore; // Added for restoring quiz
    public $filter = 'active';

    public function confirmDelete($quiz_id)
    {
        $this->quizToDelete = $quiz_id;
        $this->showDeleteModal = true;
    }

    public function confirmRestore($quiz_id)
    {
        $this->quizToRestore = $quiz_id;
        $this->showRestoreModal = true;
    }

    public function delete()
    {
        abort_if(!auth()->user()->is_admin, Response::HTTP_FORBIDDEN, 403);

        if ($this->quizToDelete) {
            $quiz = Quiz::find($this->quizToDelete);
            if ($quiz) {
                // Soft delete related questions, participants, and difficulties
                $quiz->questions()->each(function ($question) {
                    $question->delete();
                });

                $quiz->participants()->each(function ($participant) {
                    $participant->delete();
                });

                $quiz->difficulties()->each(function ($difficulty) {
                    $difficulty->delete();
                });

                // Soft delete the quiz itself
                $quiz->delete();
                session()->flash('success', 'Quiz and its related data deleted successfully.');
            }
        }

        $this->showDeleteModal = false;
        $this->quizToDelete = null;
    }

    public function restore()
    {
        abort_if(!auth()->user()->is_admin, Response::HTTP_FORBIDDEN, 403);

        if ($this->quizToRestore) {
            $quiz = Quiz::withTrashed()->find($this->quizToRestore);
            if ($quiz) {
                // Restore related questions, participants, and difficulties
                $quiz->questions()->withTrashed()->each(function ($question) {
                    $question->restore();
                });

                $quiz->participants()->withTrashed()->each(function ($participant) {
                    $participant->restore();
                });

                $quiz->difficulties()->withTrashed()->each(function ($difficulty) {
                    $difficulty->restore();
                });

                // Restore the quiz itself
                $quiz->restore();
                session()->flash('success', 'Quiz and its related data restored successfully.');
            }
        }

        $this->showRestoreModal = false;
        $this->quizToRestore = null;
    }


    public function render(): View
    {
        $quizzesQuery = Quiz::withCount('questions');

        if ($this->filter === 'active') {
            $quizzesQuery = $quizzesQuery->whereNull('deleted_at');
        } elseif ($this->filter === 'deleted') {
            $quizzesQuery = $quizzesQuery->onlyTrashed();
        }

        $quizzes = $quizzesQuery->latest()->paginate(10);

        return view('livewire.quiz.quiz-list', compact('quizzes'));
    }
}
