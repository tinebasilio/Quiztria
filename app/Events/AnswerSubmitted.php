<?php

namespace App\Events; // Ensure the correct namespace is set

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable; // Import Dispatchable trait
use Illuminate\Queue\SerializesModels;

class AnswerSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $questionId;
    public $answer;
    public $leaderboard;
    public $submittedAnswers;
    
    public function __construct($roomId, $questionId, $answer, $leaderboard, $submittedAnswers)
    {
        $this->roomId = $roomId;
        $this->questionId = $questionId;
        $this->answer = $answer;
        $this->leaderboard = $leaderboard;
        $this->submittedAnswers = $submittedAnswers;
    }

    public function broadcastOn()
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'AnswerSubmitted';
    }

    // Add the broadcastWith method to include additional data
    public function broadcastWith()
    {
        return [
            'leaderboard' => $this->leaderboard, // Include the leaderboard data
            'questionId' => $this->questionId,
            'answer' => $this->answer,
            'submittedAnswers' => $this->submittedAnswers, // Ensure submittedAnswers is included here
        ];
    }
}
