<?php

namespace App\Http\Livewire\Difficulty;

use App\Models\Difficulty;
use Livewire\Component;

class DifficultyList extends Component
{
    public $difficulties;

    public function mount()
    {
        $this->difficulties = Difficulty::all(); // Load all difficulties
    }

    public function delete($id)
    {
        $difficulty = Difficulty::findOrFail($id);
        $difficulty->delete();

        // Refresh the list after deletion
        $this->difficulties = Difficulty::all();
        session()->flash('success', 'Difficulty deleted successfully!');
    }

    public function render()
    {
        return view('livewire.difficulty.difficulty-list');
    }
}
