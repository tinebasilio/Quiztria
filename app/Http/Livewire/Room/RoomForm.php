<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use App\Models\Test;
use App\Models\Quiz;
use Livewire\Component;

class RoomForm extends Component
{
    public $roomId;
    public $room_name;
    public $quiz_id;
    public $time_spent;

    protected $rules = [
        'room_name' => 'required|string|max:255',
        'quiz_id' => 'required|exists:quizzes,id',
        'time_spent' => 'nullable|integer|min:0',
    ];

    // Load room data if roomId is provided
    public function mount($roomId = null)
    {
        if ($roomId) {
            $room = Room::findOrFail($roomId);
            $this->roomId = $room->id;
            $this->room_name = $room->room_name;
            $this->quiz_id = $room->quiz_id;
            $this->time_spent = $room->time_spent;
        }
    }

    public function saveRoom()
    {   
        $this->validate();

        // Check if the room ID exists for updating, otherwise create a new room
        if ($this->roomId) {
            $room = Room::findOrFail($this->roomId);
            $room->update([
                'room_name' => $this->room_name,
                'quiz_id' => $this->quiz_id,
                'time_spent' => $this->time_spent,
            ]);
            session()->flash('message', 'Room updated successfully.');
        } else {
            $room = Room::create([
                'room_name' => $this->room_name,
                'quiz_id' => $this->quiz_id,
                'time_spent' => $this->time_spent,
            ]);
            session()->flash('message', 'Room created successfully.');
        }

        // Associate participants from the quiz with the room
        $participants = \App\Models\Participant::where('quiz_id', $this->quiz_id)->get();

        // Debug check for participants
        if ($participants->isEmpty()) {
            dd("No participants found for quiz_id {$this->quiz_id}");
        }

        foreach ($participants as $participant) {
            // Create or update the ParticipantsRoom record
            \App\Models\ParticipantsRoom::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'participant_id' => $participant->id
                ],
                [
                    'is_at_room' => 0, // or set this based on your logic
                ]
            );

            // Create or update the Tests record for each participant
            Test::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'participant_id' => $participant->id,
                    'quiz_id' => $this->quiz_id // Ensure quiz_id is included
                ],
                [
                    'score' => 0, // Set initial score to 0 or any default value
                    'time_spent' => '00:00:00' // Set initial time spent if applicable
                ]
            );
        }

        // Redirect to the room view after saving
        return redirect()->route('room.view', ['roomId' => $room->id]);
    }


    public function render()
    {
        return view('livewire.room.room-form', [
            'quizzes' => Quiz::all() // Pass quizzes to the view
        ]);
    }
}
