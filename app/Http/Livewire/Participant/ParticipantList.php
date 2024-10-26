<?php

namespace App\Http\Livewire\Participant;

use App\Models\Participant;
use Livewire\Component;

class ParticipantList extends Component
{
    public $participants;

    public function mount()
    {
        $this->participants = Participant::all(); // Load all participants
    }

    public function delete($id)
    {
        Participant::findOrFail($id)->delete();
        $this->participants = Participant::all(); // Refresh the list after deletion
    }

    public function render()
    {
        return view('livewire.participant.participant-list');
    }
}
