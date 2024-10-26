<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\ParticipantsRoom;
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
            Auth::guard('participant')->login($participant);

            // Update the participant's room status
            $participantRoom = ParticipantsRoom::where('participant_id', $participant->id)->first();
            if ($participantRoom) {
                $participantRoom->update(['Is_at_room' => true]);

                // Dispatch the broadcasting event to refresh room in real-time
                event(new ParticipantStatusUpdated($participantRoom->room_id));
            }

            return redirect()->route('participant.dashboard');
        }

        return back()->withErrors(['code' => 'Invalid participant code.']);
    }
}
