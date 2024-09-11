<?php

namespace App\Http\Livewire\Admin\Tests;

use App\Models\Quiz;
use App\Models\Test;
use Illuminate\Support\Collection;
use Livewire\Component;

class TestList extends Component
{
    public Collection $quizzes;

    public int $quiz_id = 0;

    // fetches published quizzes and assigns them to the $quizzes property
    public function mount()
    {
        $this->quizzes = Quiz::published()->get();
    }

    // retrieves quizzes based on the selected quiz ID
    public function render()
    {
        $tests = Test::when($this->quiz_id > 0, function ($query) {
            $query->where('quiz_id', $this->quiz_id);
        })
            ->with(['user', 'quiz'])
            ->withCount('questions')
            ->latest()
            ->paginate();

        return view('livewire.admin.tests.test-list', [
            'tests' => $tests
        ]);
    }
}
