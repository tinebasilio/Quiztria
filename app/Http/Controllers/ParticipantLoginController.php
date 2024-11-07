<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantsRoom;
use App\Models\Room; // Add this to access the Room model
use Illuminate\Support\Facades\Auth;
use App\Events\ParticipantStatusUpdated;

class ParticipantLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.participant-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'code' => 'required|exists:participants,code',
        ]);

        $participant = Participant::where('code', $request->code)->first();

        if ($participant) {
            // Find the participant's room
            $participantRoom = ParticipantsRoom::where('participant_id', $participant->id)->first();

            if ($participantRoom) {
                // Check if the room is active
                $room = Room::find($participantRoom->room_id);
                if (!$room || !$room->is_active) {
                    return back()->withErrors(['code' => 'The room is currently inactive. Please try again later.']);
                }

                // Set participant ID in the session to persist across page reloads
                session(['participant_id' => $participant->id]);

                // Login the participant using a custom guard
                Auth::guard('participant')->login($participant);

                // Update the participant's room status
                $participantRoom->update(['is_at_room' => true]);

                // Define the required variables
                $roomId = $participantRoom->room_id;
                $participantId = $participant->id;
                $isAtRoom = true;

                // Dispatch the event to update room in real-time
                broadcast(new ParticipantStatusUpdated($roomId, $participantId, $isAtRoom));

                // Redirect participant directly to the room view
                return redirect()->route('room.view', ['roomId' => $roomId]);
            }
        }

        return back()->withErrors(['code' => 'Invalid participant code.']);
    }
}
