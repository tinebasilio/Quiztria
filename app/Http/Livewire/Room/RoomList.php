<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use Livewire\Component;
use Livewire\WithPagination;

class RoomList extends Component
{
    use WithPagination;  // Required for pagination

    protected $paginationTheme = 'bootstrap';  // Optional: If you prefer Bootstrap styling

    public function deleteRoom($id)
    {
        $room = Room::find($id);
        if ($room) {
            $room->delete();
            session()->flash('message', 'Room deleted successfully.');
        }
    }

    public function render()
    {
        return view('livewire.room.room-list', [
            'rooms' => Room::paginate(10),  // Paginate rooms, displaying 10 per page
        ]);
    }
}
