<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use App\Models\Answer;
use App\Models\Test;
use Livewire\Component;
use Livewire\WithPagination;

class RoomList extends Component
{
    use WithPagination;

    public $showDeleteModal = false;
    public $roomToDelete;
    public $filter = 'active';

    protected $paginationTheme = 'bootstrap';

    public function confirmDelete($id)
    {
        $this->roomToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteRoom()
    {
        if ($this->roomToDelete) {
            $room = Room::find($this->roomToDelete);
            if ($room) {
                $room->delete();

                // Delete entries in participants_room associated with this room
                \App\Models\ParticipantsRoom::where('room_id', $this->roomToDelete)->delete();

                // Delete answers associated with tests in this room
                Answer::whereIn('test_id', function ($query) {
                    $query->select('id')
                        ->from(with(new Test)->getTable())
                        ->where('room_id', $this->roomToDelete);
                })->delete();

                // Delete the tests associated with this room
                Test::where('room_id', $this->roomToDelete)->delete();

                session()->flash('message', 'Room and all associated data deleted successfully.');
            }
        }

        $this->showDeleteModal = false;
        $this->roomToDelete = null;
    }

    public function render()
    {
        $rooms = $this->filter === 'deleted'
            ? Room::onlyTrashed()->paginate(10)
            : Room::paginate(10);

        return view('livewire.room.room-list', [
            'rooms' => $rooms,
        ]);
    }
}
