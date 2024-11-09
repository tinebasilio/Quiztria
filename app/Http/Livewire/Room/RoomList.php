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
                            // Delete answers associated with tests in this room
                Answer::whereIn('test_id', function ($query) {
                    $query->select('id')
                        ->from(with(new Test)->getTable())
                        ->where('room_id', $this->id);
                })->delete();

                // Delete the tests associated with this room
                Test::where('room_id', $this->id)->delete();
                session()->flash('message', 'Room deleted successfully.');
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
