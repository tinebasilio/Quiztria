<?php

use App\Http\Controllers\ParticipantLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\DifficultyController;
use App\Http\Controllers\RoomNotificationController;
use App\Http\Livewire\Admin\AdminForm;
use App\Http\Livewire\Admin\AdminList;
use App\Http\Livewire\Admin\Tests\TestList;
use App\Http\Livewire\Front\Leaderboard;
use App\Http\Livewire\Front\Results\ResultList;
use App\Http\Livewire\Question\QuestionForm;
use App\Http\Livewire\Question\QuestionList;
use App\Http\Livewire\Quiz\QuizForm;
use App\Http\Livewire\Quiz\QuizList;
use App\Http\Livewire\Difficulty\DifficultyForm;
use App\Http\Livewire\Difficulty\DifficultyList;
use App\Http\Livewire\Participant\ParticipantForm;
use App\Http\Livewire\Participant\ParticipantList;
use App\Http\Livewire\Room\RoomList;
use App\Http\Livewire\Room\RoomForm;
use App\Http\Livewire\Room\RoomView;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// Test broadcast route
Route::get('/test-broadcast', function () {
    broadcast(new \App\Events\QuestionUpdated('Test question text', true, 1));
    return "Broadcast sent";
});

Route::get('/quiz-results/{roomId}/download', [RoomView::class, 'generateQuizResultsPdf'])->name('quiz.results.download')->middleware('isAdmin');

// Route for handling answer notifications
Route::post('/room/{roomId}/submit-answer-notification', [RoomNotificationController::class, 'handleSubmitAnswerNotification']);
Route::post('/room/{roomId}/participant-disconnect', [RoomNotificationController::class, 'handleParticipantDisconnect']);


// routes/web.php or routes/api.php (depending on your setup)
Route::post('/room/{roomId}/participant-disconnect', [RoomNotificationController::class, 'handleParticipantDisconnect'])
    ->middleware('auth:participant')
    ->name('participant.disconnect');

// Participant login routes
Route::get('/participant-login', [ParticipantLoginController::class, 'showLoginForm'])->name('participant.login');
Route::post('/participant-login', [ParticipantLoginController::class, 'login']);
Route::post('/participant-logout', [AuthenticatedSessionController::class, 'participantLogout'])->name('participant.logout');

// Room view route, accessible by both participants and admins
Route::middleware(['auth:web,participant'])->group(function () {
    Route::get('/room/{roomId}', RoomView::class)->name('room.view');
});

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware('throttle:1,1')->group(function () {
    Route::get('quiz/{quiz}', [HomeController::class, 'show'])->name('quiz.show');
});
Route::get('results/{test}', [ResultController::class, 'show'])->name('results.show');

// Protected routes for authenticated users
Route::middleware('auth')->group(function () {
    Route::get('leaderboard', Leaderboard::class)->name('leaderboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('myresults', ResultList::class)->name('myresults');

    // Admin-specific routes (only accessible to admins)
    Route::middleware('isAdmin')->group(function () {

        Route::get('/quiz/{quiz}/questions', QuestionForm::class)->name('quiz.questions');

        // Question routes
        Route::get('questions', QuestionList::class)->name('questions');
        Route::get('questions/{quiz}/{question}/edit', QuestionForm::class)->name('question.edit');
        Route::get('questions/{quiz}/create', QuestionForm::class)->name('question.create');

        // Difficulty routes
        Route::get('difficulties', DifficultyList::class)->name('difficulties');
        Route::get('difficulties/{quiz_id}', DifficultyForm::class)->name('difficulty.form');
        Route::get('/quiz/{quiz_id}/difficulties/edit', DifficultyForm::class)->name('difficulties.edit');

        // Participant management routes
        Route::get('participants', ParticipantList::class)->name('participants');
        Route::get('/quiz/{quiz}/participants/create', ParticipantForm::class)->name('participant.create');
        Route::get('/quiz/{quiz}/participants/edit', ParticipantForm::class)->name('participant.edit');

        // Room management routes
        Route::get('rooms', RoomList::class)->name('rooms');
        Route::get('rooms/create', RoomForm::class)->name('room.create');
        Route::get('/room/{roomId}/edit', RoomForm::class)->name('room.edit');

        // Quiz management routes
        Route::get('quizzes', QuizList::class)->name('quizzes');
        Route::get('quizzes/create', QuizForm::class)->name('quiz.create');
        Route::get('quizzes/{quiz:slug}/edit', QuizForm::class)->name('quiz.edit');

        // Admin management routes
        Route::get('admins', AdminList::class)->name('admins');
        Route::get('admins/create', AdminForm::class)->name('admin.create');

        // Test management routes
        Route::get('tests', TestList::class)->name('tests');
    });
});

require __DIR__ . '/auth.php';
