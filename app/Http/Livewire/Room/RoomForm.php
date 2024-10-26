<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
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
            $room = Room::findOrFail($roomId); // Find the room by its ID (or slug)
            $this->roomId = $room->id;
            $this->room_name = $room->room_name;
            $this->quiz_id = $room->quiz_id;
            $this->time_spent = $room->time_spent;
        }
    }

    public function saveRoom()
    {
        $this->validate();

        if ($this->roomId) {
            // If roomId exists, update the existing room
            $room = Room::findOrFail($this->roomId);
            $room->update([
                'room_name' => $this->room_name,
                'quiz_id' => $this->quiz_id,
                'time_spent' => $this->time_spent,
            ]);

            session()->flash('message', 'Room updated successfully.');
        } else {
            // If no roomId, create a new room
            $room = Room::create([
                'room_name' => $this->room_name,
                'quiz_id' => $this->quiz_id,
                'time_spent' => $this->time_spent,
            ]);

            session()->flash('message', 'Room created successfully.');
        }

        // Redirect to the room view after saving
        return redirect()->route('room.view', ['roomId' => $room->id]);
    }

    public function render()
    {
        return view('livewire.room.room-form', [
            'quizzes' => \App\Models\Quiz::all(), // Get all quizzes for the dropdown
        ]);
    }
}
