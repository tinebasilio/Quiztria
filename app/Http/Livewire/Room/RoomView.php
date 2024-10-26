<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use Livewire\Component;

class RoomView extends Component
{
    public $room;

    // Load the room data based on the roomId passed in the route
    public function mount($roomId)
    {
        $this->room = Room::with('participantsRoom.participant')->findOrFail($roomId);
    }

    public function render()
    {
        return view('livewire.room.room-view', [
            'room' => $this->room,
        ]);
    }
}
