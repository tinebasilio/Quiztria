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
    public bool $showSaveModal = false; // New property for showing the modal

    protected $rules = [
        'difficulties.*.point' => 'required|integer|min:0',
        'difficulties.*.timer' => 'required|integer|min:0',
    ];

    public function mount(int $quiz_id): void
    {
        $this->quiz = Quiz::findOrFail($quiz_id);
        $this->editing = true;

        $this->difficulties = [
            ['diff_name' => 'Easy', 'point' => 1, 'timer' => 10],
            ['diff_name' => 'Average', 'point' => 3, 'timer' => 20],
            ['diff_name' => 'Difficult', 'point' => 5, 'timer' => 45],
            ['diff_name' => 'Clincher', 'point' => 0, 'timer' => 45],
        ];

        foreach ($this->quiz->difficulties as $difficulty) {
            foreach ($this->difficulties as &$d) {
                if ($d['diff_name'] === $difficulty->diff_name) {
                    $d['point'] = $difficulty->point;
                    $d['timer'] = $difficulty->timer;
                }
            }
        }
    }

    public function confirmSave()
    {
        $this->showSaveModal = true; // Show the modal before saving
    }

    public function save()
    {
        $this->validate();

        foreach ($this->difficulties as $difficultyData) {
            $existingDifficulty = Difficulty::where('quiz_id', $this->quiz->id)
                ->where('diff_name', $difficultyData['diff_name'])
                ->first();

            if ($existingDifficulty) {
                $existingDifficulty->point = $difficultyData['point'];
                $existingDifficulty->timer = $difficultyData['timer'];
                $existingDifficulty->save();
            } else {
                Difficulty::create(array_merge($difficultyData, ['quiz_id' => $this->quiz->id]));
            }
        }

        $this->showSaveModal = false; // Hide the modal after saving

        return redirect()->route('quiz.edit', $this->quiz->slug)
            ->with('success', 'Difficulties saved successfully.');
    }

    public function render()
    {
        return view('livewire.difficulty.difficulty-form');
    }
}
