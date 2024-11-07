<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Participant;
use App\Models\ParticipantsRoom;
use App\Events\ParticipantStatusUpdated;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // General logout for users like admins, redirecting to login page
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home'); // Redirects to login
    }

    public function participantLogout(Request $request)
    {
        // Retrieve the authenticated participant
        $participant = Auth::guard('participant')->user();

        if ($participant) {
            // Find the participant's room record and update `is_at_room`
            $participantsRoom = ParticipantsRoom::where('participant_id', $participant->id)->first();

            if ($participantsRoom) {
                $participantsRoom->update(['is_at_room' => false]);

                // Broadcast the event with the correct arguments
                event(new ParticipantStatusUpdated($participantsRoom->room_id, $participant->id, false));
            }
        }

        // Log out the participant and invalidate the session
        Auth::guard('participant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the home page or participant login
        return redirect()->route('home'); // Change to the appropriate route if needed
    }
}
