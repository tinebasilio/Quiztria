<?php

namespace App\Http\Livewire\Participant;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ParticipantsRoom;

class ParticipantDashboard extends Component
{
    public $participant;
    public $room;
    public $quiz;

    public function mount()
    {
        // Retrieve the logged-in participant
        $this->participant = Auth::guard('participant')->user();

        if (!$this->participant) {
            return redirect()->route('participant.login');
        }

        // Retrieve the participant's associated room
        $participantRoom = $this->participant->participantsRoom()->with('room')->first();

        // Check if a participantRoom record is found and if it has an associated room
        if ($participantRoom && $participantRoom->room) {
            $this->room = $participantRoom->room;

            // Set the is_at_room column to true (1) when the participant logs in
            $participantRoom->is_at_room = 1;
            $participantRoom->save(); // Save the update to the database
        } else {
            $this->room = null;
        }

        // Retrieve the participant's quiz
        $this->quiz = $this->participant->quiz ?? null;
    }

    public function render()
    {
        return view('livewire.participant.participant-dashboard', [
            'participant' => $this->participant,
            'room' => $this->room,
            'quiz' => $this->quiz,
        ]);
    }
}
