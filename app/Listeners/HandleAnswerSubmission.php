<?php

namespace App\Listeners;

use App\Events\AnswerSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Test;

class HandleAnswerSubmission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AnswerSubmitted  $event
     * @return void
     */
    public function handle(AnswerSubmitted $event)
    {
        \Log::info('Handling AnswerSubmitted event', ['questionId' => $event->questionId, 'answer' => $event->answer]);

        // Optionally: Logic to update leaderboard based on answer submission
        // For example, you might retrieve the test record associated with the participant
        $testRecord = Test::where('participant_id', $event->participantId)
            ->where('room_id', $event->roomId)
            ->first();

        // Logic to recalculate score or leaderboard could be placed here
        // Update leaderboard logic if necessary
    }
}
