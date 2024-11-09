<?php

namespace App\Http\Livewire\Room;

use App\Models\Room;
use App\Models\Question;
use App\Models\Test;
use App\Models\Option;
use App\Models\Participant;
use App\Models\QuestionQuiz;
use App\Models\Answer;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Events\QuestionUpdated;
use App\Events\AnswerSubmitted;
use App\Events\ParticipantStatusUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PDF; // Ensure you have this import at the top
use Illuminate\Support\Facades\DB;

class RoomView extends Component
{
    public $room;
    public $roomId;
    public $startTime;
    public Collection $questions;
    public int $questionCount = 0;
    public $currentQuestionIndex = 0;
    public $currentQuestion;
    public $currentQuestionType;
    public bool $quizStarted = false;
    public bool $isAdmin = false;
    public $participant;
    public $participantAnswer = null;
    public $answerSubmitted = false;
    public $submittedAnswers = [];
    public $options = [];
    public $currentTimer = null; // Added to store the current timer
    public $leaderboard = [];
    public $currentDifficulty;
    public $hasParticipants = false;


    //Modals:
    public $showQuizModal = false;

    protected $listeners = [
        'refreshRoom' => 'refreshRoom',
        'submitAnswer' => 'submitAnswer',
        'participantStatusUpdated' => 'refreshRoom',
    ];

    public function mount($roomId)
    {
        \Log::info("Mounting RoomView component with room ID: {$roomId}");

        $this->roomId = $roomId;
        $this->room = Room::with('participantsRoom.participant')->findOrFail($roomId);

        // Check if the room has participants and set the property
        $this->hasParticipants = $this->room->participantsRoom->isNotEmpty();

        if (Auth::guard('web')->check()) {
            $this->isAdmin = true;
            \Log::info("User is an admin");
        } elseif (session()->has('participant_id')) {
            $this->participant = \App\Models\Participant::find(session('participant_id'));

            if (!$this->room->participantsRoom->contains('participant_id', $this->participant->id)) {
                \Log::warning("Unauthorized participant access attempt for room ID: {$roomId}");
                abort(403, 'Unauthorized');
            }

            \Log::info("Participant with ID {$this->participant->id} joined room ID: {$roomId}");
        } else {
            \Log::warning("Unauthorized access attempt for room ID: {$roomId}");
            abort(403, 'Unauthorized');
        }

        // Initialize question and answers for admin
        $this->setCurrentQuestion();
        $this->submittedAnswers = Answer::with('participant')
            ->where('question_id', $this->currentQuestion->id ?? 0)
            ->latest('created_at')
            ->get();
    }

    public function refreshRoom()
    {
        \Log::info("Refreshing RoomView for room ID: {$this->room->id}");

        // Refresh room and current question data
        $this->room = Room::with('participantsRoom.participant')->findOrFail($this->room->id);
        $this->setCurrentQuestion();
        $this->quizStarted = !is_null($this->currentQuestion);
        $this->answerSubmitted = false;

        // Update submitted answers for the current question
        $this->submittedAnswers = Answer::with('participant')
            ->where('question_id', $this->currentQuestion->id ?? 0)
            ->get();

        // Fetch and log leaderboard data
        $this->leaderboard = $this->fetchLeaderboard();

        // Emit event to update the leaderboard on the frontend
        $this->emit('updateLeaderboard', $this->leaderboard->toArray());

        $this->emitSelf('$refresh');
    }

    public function updateLeaderboard()
    {
        // Fetch the leaderboard from the database
        $this->leaderboard = Test::where('room_id', $this->room->id)
            ->join('participants', 'tests.participant_id', '=', 'participants.id') // Corrected to 'participants'
            ->select('participants.name', 'tests.score')
            ->orderByDesc('tests.score')
            ->get()
            ->toArray(); // Ensure this is an array of associative arrays

        \Log::info("Leaderboard updated for room ID: {$this->room->id}", [
            'leaderboard' => $this->leaderboard,
        ]);
    }

    public function fetchLeaderboard()
    {
        // Fetch participants and their scores, ordered by score in descending order
        $leaderboard = Test::where('room_id', $this->room->id)
            ->join('participants', 'tests.participant_id', '=', 'participants.id') // Use 'participants' instead of 'participant'
            ->select('participants.name as name', 'tests.score as score')
            ->orderBy('tests.score', 'desc')
            ->get()
            ->map(function ($entry, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $entry->name,
                    'score' => $entry->score,
                ];
            });

        // Log the leaderboard fetch or persistence
        if ($leaderboard->isEmpty() && !empty($this->leaderboard)) {
            \Log::info("No new leaderboard data; using last known leaderboard.");
        } else {
            \Log::info("Fetched new leaderboard data for room ID: {$this->room->id}", [
                'leaderboard' => $leaderboard
            ]);
        }

        return $leaderboard;
    }

    public function startQuiz()
    {
        if (!$this->isAdmin) abort(403, 'Unauthorized action');

        $this->startTime = Carbon::now();
        $this->quizStarted = true;

        // Fetch questions with related data (timer and options)
        $this->questions = Question::query()
            ->whereRelation('quizzes', 'id', $this->room->quiz_id)
            ->join('difficulties', 'questions.difficulty_id', '=', 'difficulties.id')
            ->orderByRaw("FIELD(difficulties.diff_name, 'Easy', 'Average', 'Difficult', 'Clincher')")
            ->with(['options', 'difficulty']) // Load related data
            ->select('questions.*', 'difficulties.timer as difficulty_timer') // Include timer in selection
            ->get();

        $this->questionCount = $this->questions->count();
        $this->currentQuestionIndex = 0;
        $this->setCurrentQuestion();

        // Initialize the leaderboard with all participants with zero score
        $this->leaderboard = $this->fetchLeaderboard();

        // Broadcast the initialized leaderboard
        broadcast(new AnswerSubmitted($this->room->id, null, null, $this->leaderboard, null))
            ->toOthers();

        // Ensure current question and difficulty are set
        if ($this->currentQuestion) {
            $options = $this->currentQuestion->options->sortBy('id')->pluck('text')->toArray();
            $this->currentTimer = $this->currentQuestion->difficulty->timer ?? null;
            $this->currentDifficulty = $this->currentQuestion->difficulty->diff_name ?? 'Unknown';

            $this->submittedAnswers = Answer::with('participant')
                ->where('question_id', $this->currentQuestion->id)
                ->get();

            // Broadcast event with current question, options, difficulty, and timer
            broadcast(new QuestionUpdated(
                $this->currentQuestion->text,
                true,
                $this->room->id,
                $options,
                $this->currentQuestionType,
                $this->currentQuestion->id,
                $this->currentDifficulty,
                null,
                null,
                null,
                $this->submittedAnswers->map(function ($answer) {
                    return [
                        'participantName' => $answer->participant->name ?? 'Unknown',
                        'answer' => $answer->sub_answer,
                        'isCorrect' => $answer->correct,
                    ];
                }),
                $this->currentTimer, // Pass the timer
            ));
        } else {
            \Log::error("Error: currentQuestion is null after startQuiz initialization.");
            session()->flash('error', 'Failed to start the quiz. No questions found.');
        }
    }

    public function nextQuestion()
    {
        if (!$this->isAdmin) abort(403, 'Unauthorized action');

        $this->currentQuestionIndex++;

        if ($this->currentQuestionIndex < $this->questions->count()) {
            $this->setCurrentQuestion();

            if ($this->currentQuestion) {
                $this->participantAnswer = null;
                $this->answerSubmitted = false;

                $this->submittedAnswers = Answer::with('participant')
                    ->where('question_id', $this->currentQuestion->id)
                    ->get();

                $options = $this->currentQuestion->options->sortBy('id')->pluck('text')->toArray();
                $this->currentTimer = $this->currentQuestion->difficulty->timer ?? null;
                $this->currentDifficulty = $this->currentQuestion->difficulty->diff_name ?? 'Unknown';

                // Update and fetch the latest leaderboard
                $this->updateLeaderboard(); // Fetch the latest scores
                $leaderboard = $this->leaderboard; // Retrieve updated leaderboard

                // Broadcast the new question with updated leaderboard
                broadcast(new QuestionUpdated(
                    $this->currentQuestion->text,
                    true,
                    $this->room->id,
                    $options,
                    $this->currentQuestionType,
                    $this->currentQuestion->id,
                    $this->currentDifficulty,
                    null,
                    null,
                    null,
                    $this->submittedAnswers->map(function ($answer) {
                        return [
                            'participantName' => $answer->participant->name ?? 'Unknown',
                            'answer' => $answer->sub_answer,
                            'isCorrect' => $answer->correct,
                        ];
                    }),
                    $this->currentTimer // Pass the timer
                ));

                // Broadcast the updated leaderboard
                broadcast(new AnswerSubmitted($this->room->id, null, null, $leaderboard, null))
                    ->toOthers();

            } else {
                \Log::error("Error: currentQuestion is null after nextQuestion initialization.");
                session()->flash('error', 'Failed to load the next question.');
            }
        } else {
            $this->stopQuiz();
            session()->flash('message', 'No more questions. Quiz stopped.');
        }
    }

    public function stopQuiz()
    {
        if (!$this->isAdmin) abort(403, 'Unauthorized action');

        if ($this->startTime) {
            // Calculate the time spent on the quiz
            $timeSpentInSeconds = $this->startTime->diffInSeconds(Carbon::now());
            $formattedTimeSpent = gmdate('H:i:s', $timeSpentInSeconds);

            // Set the room's `is_active` status to false and save the time spent
            $this->room->is_active = false;
            $this->room->time_spent = $formattedTimeSpent;
            $this->room->save();

            // Clear current question and quiz state
            $this->currentQuestion = null;
            $this->currentQuestionType = null;
            $this->quizStarted = false;
            $this->questionCount = 0;

            // Broadcast the QuestionUpdated event to clear the frontend view
            broadcast(new QuestionUpdated(
                '',                    // questionText
                false,                 // quizStarted
                $this->room->id,       // roomId
                [],                    // options
                null,                  // questionType
                null,                  // questionId
                null,                  // currentDifficulty
                null,                  // participantName
                null,                  // answer
                null,                  // isCorrect
                [],                    // submittedAnswers
                null                   // timer
            ));

            session()->flash('message', 'Quiz stopped. Results will be announced soon.');
        } else {
            session()->flash('error', 'Quiz was not started.');
        }
    }

    public function submitAnswer($answer = null, $questionId = null)
    {
        \Log::info("Submitting answer for question ID: {$questionId} by participant ID: {$this->participant->id}");

        // Validate participant and question ID
        if (!$this->participant || is_null($questionId)) {
            \Log::warning("Submit attempt failed: Participant or question ID not set", [
                'participant' => $this->participant,
                'answer' => $answer,
                'questionId' => $questionId,
            ]);
            return;
        }

        // Fetch the current question
        $this->currentQuestion = Question::with('difficulty')->find($questionId);

        if (is_null($this->currentQuestion)) {
            \Log::warning("Question not found with provided question ID", [
                'questionId' => $questionId,
            ]);
            return;
        }

        $this->currentQuestionType = $this->currentQuestion->question_type ?? null;

        // Retrieve or create the test record for the participant
        $testRecord = Test::firstOrCreate(
            [
                'participant_id' => $this->participant->id,
                'room_id' => $this->room->id,
                'quiz_id' => $this->room->quiz_id,
            ],
            [
                'score' => 0 // Initialize score if the record is newly created
            ]
        );

        // Check if the testRecord is still null (should not happen with firstOrCreate)
        if (is_null($testRecord)) {
            \Log::error("Failed to retrieve or create Test record for participant ID: {$this->participant->id} in room ID: {$this->room->id}");
            return;
        }

        // Delete any existing answer for this participant's current question
        Answer::where('participant_id', $this->participant->id)
            ->where('question_id', $this->currentQuestion->id)
            ->delete();

        // Initialize variables for answer evaluation
        $isCorrect = false;
        $pointsEarned = 0;
        $answerText = $answer === null ? 'No answer submitted (time ran out)' : ($answer === '' ? 'No answer submitted (empty)' : $answer);

        // Evaluate the answer if provided
        if ($answer !== null && $answer !== '') {
            if ($this->currentQuestion->question_type === 'Identification') {
                $correctAnswer = strtolower($this->currentQuestion->options->first()->text);
                $isCorrect = (trim(strtolower($answer)) === $correctAnswer);
            } elseif (in_array($this->currentQuestion->question_type, ['True or False', 'Multiple Choice'])) {
                $selectedOption = Option::where('text', $answer)->first();
                $isCorrect = $selectedOption && $selectedOption->correct;
            }

            // Points awarded for correct answers
            if ($isCorrect) {
                $pointsEarned = $this->currentQuestion->difficulty->point ?? 0;
                \Log::info("Answer is correct. Points earned:", ['points' => $pointsEarned]);
            } else {
                \Log::info("Answer is incorrect. No points earned.");
            }
        } else {
            \Log::info("Auto-submitted as '{$answerText}' due to time running out or empty response.");
        }

        // Save the answer to the database
        Answer::create([
            'participant_id' => $this->participant->id,
            'test_id' => $testRecord->id,
            'question_id' => $this->currentQuestion->id,
            'sub_answer' => $answerText,
            'correct' => $isCorrect ? 1 : 0,
        ]);

        // Update the participant's score
        $updatedScore = $testRecord->score + $pointsEarned;
        $testRecord->update(['score' => $updatedScore]);

        // Clear participant's answer for UI reset
        $this->answerSubmitted = true;
        $this->participantAnswer = null;

        // Update only this participant's submitted answers list
        $this->submittedAnswers = Answer::with('participant')
            ->where('question_id', $this->currentQuestion->id)
            ->latest('created_at')
            ->get()
            ->toArray();

        $this->updateLeaderboard(); // Update the leaderboard
        $leaderboardData = $this->leaderboard;

        // Make sure submittedAnswersArray is already an array
        $submittedAnswersArray = $this->submittedAnswers;

        // Broadcast the event
        broadcast(new AnswerSubmitted($this->room->id, $questionId, $answerText, $leaderboardData, $submittedAnswersArray))
            ->toOthers();

        \Log::info("AnswerSubmitted event emitted for room ID: {$this->room->id}");
    }

    private function setCurrentQuestion()
    {
        $this->currentQuestion = $this->questions[$this->currentQuestionIndex] ?? null;

        // If there's no current question, exit the method early
        if (!$this->currentQuestion) {
            return;
        }

        $this->currentQuestionType = $this->currentQuestion->question_type ?? null;
        $this->participantAnswer = null;
        $this->currentDifficulty = $this->currentQuestion->difficulty->diff_name ?? 'Unknown'; // Set the difficulty level

        if ($this->currentQuestionType === 'Multiple Choice') {
            // Define labels for multiple-choice options
            $labels = ['A', 'B', 'C', 'D'];

            // Retrieve and format multiple-choice options with labels
            $this->options = $this->currentQuestion->options()
                ->orderBy('id', 'asc')
                ->get()
                ->map(function ($option, $index) use ($labels) {
                    return [
                        'label' => $labels[$index] ?? '',
                        'text' => $option->text,
                        'id' => $option->id,
                    ];
                })
                ->toArray();
        } else {
            // For other question types, retrieve options as they are
            $this->options = $this->currentQuestion ? $this->currentQuestion->options->pluck('text')->toArray() : [];
        }

        // Set the timer based on the current question's difficulty level
        $this->currentTimer = $this->currentQuestion->difficulty->timer ?? null;

        // Retrieve submitted answers for the current question
        $this->submittedAnswers = Answer::with('participant')
            ->where('question_id', $this->currentQuestion->id)
            ->get();

        // Broadcast the question update event, including difficulty level
        broadcast(new QuestionUpdated(
            $this->currentQuestion->text,
            $this->quizStarted,
            $this->room->id,
            array_column($this->options, 'text'),
            $this->currentQuestionType,
            $this->currentQuestion->id,
            $this->currentDifficulty,  // Pass the current difficulty level
            null,
            null,
            null,
            $this->submittedAnswers,
            $this->currentTimer
        ));
    }

    public function generateQuizResultsPdf($roomId)
    {
        // Fetch room, participants, and their tests
        $room = Room::with('participantsRoom.participant.tests.answers')->findOrFail($roomId);
        \Log::info("Generating quiz results for room ID: {$roomId}");

        // Initialize the data array
        $data = [
            'participants' => [],
            'questionsCount' => [],
            'totalScores' => [
                'Easy' => 0,
                'Average' => 0,
                'Difficult' => 0,
                'Clincher' => 0,
            ],
        ];

        // Count the number of questions per difficulty level and store points
        $questions = QuestionQuiz::with('question.difficulty')
            ->where('quiz_id', $room->quiz_id)
            ->get();

        foreach ($questions as $questionQuiz) {
            $difficultyName = $questionQuiz->question->difficulty->diff_name;
            $points = $questionQuiz->question->difficulty->point;

            if (!isset($data['questionsCount'][$difficultyName])) {
                $data['questionsCount'][$difficultyName] = [
                    'count' => 0,
                    'points' => $points
                ];
            }
            $data['questionsCount'][$difficultyName]['count']++;
        }

        // Gather participant scores and answers
        foreach ($room->participantsRoom as $participantRoom) {
            $participant = $participantRoom->participant;
            $test = $participant->tests()->where('room_id', $room->id)->first();

            $scores = [
                'Easy' => 0,
                'Average' => 0,
                'Difficult' => 0,
                'Clincher' => 0,
            ];
            $participantAnswers = [];

            if ($test) {
                foreach ($test->answers as $answer) {
                    $difficultyName = $answer->question->difficulty->diff_name;
                    $points = $answer->correct ? $answer->question->difficulty->point : 0;

                    $scores[$difficultyName] += $points;
                    $participantAnswers[$difficultyName][] = [
                        'question' => $answer->question->text,
                        'answer' => $answer->sub_answer,
                        'isCorrect' => $answer->correct,
                        'points' => $points,
                    ];
                }
            }

            \Log::info("Participant processed: {$participant->name}, Scores: " . json_encode($scores));

            foreach ($scores as $difficulty => $score) {
                $data['totalScores'][$difficulty] += $score;
            }

            $data['participants'][] = [
                'name' => $participant->name,
                'scores' => $scores,
                'answers' => $participantAnswers,
                'totalScore' => array_sum($scores),
            ];
        }

        usort($data['participants'], function ($a, $b) {
            return $b['totalScore'] <=> $a['totalScore'];
        });

        foreach ($data['participants'] as $index => &$participant) {
            $participant['rank'] = $index + 1;
        }

        \Log::info("Loading PDF view with data for room ID: {$room->id}");

        // Load the view in landscape orientation
        $pdf = PDF::loadView('pdf.quiz_results', compact('data', 'room'))
                  ->setPaper('a4', 'landscape'); // Set to landscape

        \Log::info("PDF generated successfully for room ID: {$room->id}");

        return $pdf->download('quiz_results_' . $room->id . '.pdf');
    }

    //Modal Sections:
    // Show the confirmation modal
    public function showStartQuizModal()
    {
        $this->showQuizModal = true;
    }

    // Hide the confirmation modal
    public function hideStartQuizModal()
    {
        $this->showQuizModal = false;
    }

    // Confirm starting the quiz with participant check
    public function confirmStartQuiz()
    {
        // Check if there are participants in the room
        $participantCount = Participant::whereRelation('participantsRoom', 'room_id', $this->room->id)->count();

        if ($participantCount === 0) {
            session()->flash('error', 'Cannot start quiz. No participants in the room.');
            $this->showQuizModal = false;
            return;
        }

        // Start the quiz and close the modal
        $this->startQuiz();
        $this->showQuizModal = false;
    }

    //Render
    public function render()
    {
        \Log::info("Rendering RoomView component for room ID: {$this->roomId}");

        $this->submittedAnswers = Answer::with('participant')
            ->where('question_id', $this->currentQuestion->id ?? 0)
            ->latest('created_at')
            ->get();

        return view('livewire.room.room-view', [
            'room' => $this->room,
            'roomId' => $this->roomId,
            'questions' => $this->questions ?? [],
            'currentQuestion' => $this->currentQuestion,
            'currentQuestionType' => $this->currentQuestionType,
            'isAdmin' => $this->isAdmin,
            'participantAnswer' => $this->participantAnswer,
            'quizStarted' => $this->quizStarted,
            'answerSubmitted' => $this->answerSubmitted,
            'submittedAnswers' => $this->submittedAnswers,
            'leaderboard' => $this->leaderboard,
        ]);
    }
}
