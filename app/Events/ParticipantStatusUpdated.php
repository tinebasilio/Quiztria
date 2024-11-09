<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $roomId;
    public $participantId;
    public $isAtRoom;

    public function __construct($roomId, $participantId, $isAtRoom)
    {
        $this->roomId = $roomId;
        $this->participantId = $participantId;
        $this->isAtRoom = $isAtRoom;
    }

    public function broadcastOn()
    {
        // This ensures the event is broadcasted to a channel specific to the room
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'ParticipantStatusUpdated';
    }
}
