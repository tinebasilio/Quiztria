<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\ParticipantStatusUpdated;
use App\Models\ParticipantsRoom;

class RoomNotificationController extends Controller
{
    public function handleSubmitAnswerNotification(Request $request, $roomId)
    {
        // Your notification handling logic.
        \Log::info('Answer submission notification received for room: ' . $roomId);
        return response()->json(['message' => 'Notification received']);
    }

    public function handleParticipantDisconnect(Request $request, $roomId)
    {
        $participantId = $request->input('participant_id');

        if ($participantId) {
            // Set is_at_room to false for the participant in the specified room
            \App\Models\ParticipantsRoom::where('room_id', $roomId)
                ->where('participant_id', $participantId)
                ->update(['is_at_room' => false]);

            // Broadcast the ParticipantStatusUpdated event to notify other participants
            event(new ParticipantStatusUpdated($roomId, $participantId, false));

            \Log::info("Participant ID {$participantId} marked as disconnected from room ID {$roomId}");
            return response()->json(['message' => 'Participant status updated']);
        }

        return response()->json(['message' => 'Participant not found or not in the room'], 404);
    }


}
