<?php

// app/Events/ParticipantStatusUpdated.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $roomId;

    public function __construct($roomId)
    {
        $this->roomId = $roomId;
    }

    public function broadcastOn()
    {
        return new Channel('room-updates');
    }

    public function broadcastAs()
    {
        return 'ParticipantStatusUpdated';
    }
}
