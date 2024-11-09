<?php

namespace App\Http\Livewire\Difficulty;

use App\Models\Difficulty;
use Livewire\Component;

class DifficultyList extends Component
{
    public $difficulties;
    public $selectedDifficulty = 'All';


    public function mount()
    {
        $this->difficulties = Difficulty::all(); // Load all difficulties
        $this->selectedDifficulty = 'All';
    }

    // Handle the deletion of a difficulty record
    public function delete($id)
    {
        $difficulty = Difficulty::findOrFail($id);

        $difficulty->delete();

        // Refresh the list by removing the deleted item from the collection
        $this->difficulties = $this->difficulties->filter(fn($item) => $item->id !== $id);
        session()->flash('success', 'Difficulty deleted successfully!');
    }

    // Render the view
    public function render()
    {
        return view('livewire.difficulty.difficulty-list');
    }
}