<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use Livewire\Component;

class RoomView extends Component
{
    public $room;

    protected $listeners = [
        'refreshRoom' => 'refreshRoom'
    ];

    public function mount($roomId)
    {
        $this->room = Room::with('participantsRoom.participant')->findOrFail($roomId);
    }

    public function refreshRoom()
    {
        $this->room = Room::with('participantsRoom.participant')->findOrFail($this->room->id);
    }

    public function deleteRoom()
    {
        if ($this->room) {
            // Delete related entries in participants_room table
            \App\Models\ParticipantsRoom::where('room_id', $this->room->id)->delete();

            // Delete the room itself
            $this->room->delete();

            session()->flash('message', 'Room deleted successfully.');
            return redirect()->route('rooms');
        }
    }

    public function render()
    {
        return view('livewire.room.room-view', [
            'room' => $this->room,
        ]);
    }
}
