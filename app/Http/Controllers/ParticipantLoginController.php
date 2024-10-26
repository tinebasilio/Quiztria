<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use Illuminate\Support\Facades\Auth;

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

        // Authenticate the participant using the participant guard
        $participant = Participant::where('code', $request->code)->first();

        if ($participant) {
            // Login participant using the participant guard
            Auth::guard('participant')->login($participant);
            return redirect()->route('participant.dashboard');
        }

        return back()->withErrors(['code' => 'Invalid participant code.']);
    }
}
