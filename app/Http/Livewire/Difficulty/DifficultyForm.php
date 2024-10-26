<?php

namespace App\Http\Livewire\Difficulty;

use App\Models\Difficulty;
use App\Models\Quiz;
use Livewire\Component;

class DifficultyForm extends Component
{
    public Quiz $quiz;
    public array $difficulties = [];
    public bool $editing = false;

    protected $rules = [
        'difficulties.*.point' => 'required|integer|min:0',
    ];

    public function mount(int $quiz_id): void
    {
        $this->quiz = Quiz::findOrFail($quiz_id);
        $this->editing = true; // Set editing to true since we are editing difficulties

        // Initialize fixed difficulties
        $this->difficulties = [
            ['diff_name' => 'easy', 'point' => 0],
            ['diff_name' => 'average', 'point' => 0],
            ['diff_name' => 'hard', 'point' => 0],
            ['diff_name' => 'clincher', 'point' => 0],
        ];

        // Load existing difficulties if editing
        foreach ($this->quiz->difficulties as $difficulty) {
            foreach ($this->difficulties as &$d) {
                if ($d['diff_name'] === $difficulty->diff_name) {
                    $d['point'] = $difficulty->point;
                }
            }
        }
    }

    public function save()
    {
        $this->validate();

        foreach ($this->difficulties as $difficultyData) {
            // Check if the difficulty already exists
            $existingDifficulty = Difficulty::where('quiz_id', $this->quiz->id)
                ->where('diff_name', $difficultyData['diff_name'])
                ->first();

            if ($existingDifficulty) {
                // Update existing difficulty
                $existingDifficulty->point = $difficultyData['point'];
                $existingDifficulty->save();
            } else {
                // Create new difficulty
                Difficulty::create(array_merge($difficultyData, ['quiz_id' => $this->quiz->id]));
            }
        }

        return redirect()->route('quiz.edit', $this->quiz->slug)
            ->with('success', 'Difficulties saved successfully.');
    }

    public function render()
    {
        return view('livewire.difficulty.difficulty-form');
    }
}
