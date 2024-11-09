<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $questionText;
    public $quizStarted;
    public $roomId;
    public $options;
    public $questionType;
    public $questionId;
    public $participantName;
    public $answer;
    public $isCorrect;
    public $submittedAnswers;
    public $timer;
    public $currentDifficulty; // New property for difficulty level

    public function __construct(
        $questionText,
        $quizStarted,
        $roomId,
        $options = [],
        $questionType = 'Multiple Choice',
        $questionId,
        $currentDifficulty = 'Unknown', // New parameter for difficulty level
        $participantName = null,
        $answer = null,
        $isCorrect = null,
        $submittedAnswers = [],
        $timer = null
    ) {
        $this->questionText = $questionText;
        $this->quizStarted = $quizStarted;
        $this->roomId = $roomId;
        $this->options = $options;
        $this->questionType = $questionType;
        $this->questionId = $questionId;
        $this->currentDifficulty = $currentDifficulty; // Assign the difficulty level
        $this->participantName = $participantName;
        $this->answer = $answer;
        $this->isCorrect = $isCorrect;
        $this->submittedAnswers = $submittedAnswers;
        $this->timer = $timer;

        \Log::info("QuestionUpdated event triggered", [
            'questionText' => $questionText,
            'quizStarted' => $quizStarted,
            'roomId' => $roomId,
            'options' => $options,
            'questionType' => $questionType,
            'questionId' => $questionId,
            'currentDifficulty' => $currentDifficulty, // Log the difficulty level
            'submittedAnswers' => $submittedAnswers,
            'timer' => $timer,
        ]);
    }

    public function broadcastOn()
    {
        return new Channel('room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'QuestionUpdated';
    }
}
