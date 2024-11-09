<?php

namespace App\Http\Livewire\Quiz;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\Difficulty;
use App\Models\Participant;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class QuizForm extends Component
{
    public Quiz $quiz;
    public bool $editing = false;
    public array $listsForFields = [];
    public array $participants = [];
    public array $questionsByDifficulty = [];
    public Collection $difficulties;
    public string $activeView = 'details'; // Property for managing views
    public string $selectedDifficulty = 'Easy'; // New property for filtering questions
    public bool $showSaveModal = false;

    protected $rules = [
        'quiz.title' => 'required|string',
        'quiz.slug' => 'string',
        'quiz.description' => 'required|string', // Make description required
        'quiz.published' => 'boolean|nullable',
        'quiz.public' => 'boolean|nullable',
    ];

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz;
        $this->editing = $quiz->exists;

        $this->participants = $quiz->participants()->get()->toArray();

        if (empty($this->participants)) {
            $this->addParticipant(); // Add an initial participant
        }

        if (!$this->editing) {
            $this->quiz->published = false;
            $this->quiz->public = false;
        }

        $this->loadQuestionsByDifficulty();
        $this->loadDifficulties();
        $this->initListsForFields();
    }

    public function loadDifficulties(): void
    {
        $this->difficulties = Difficulty::where('quiz_id', $this->quiz->id)->get();
    }

    public function loadQuestionsByDifficulty(): void
    {
        $questions = $this->quiz->questions()->with('difficulty')->get();

        $this->questionsByDifficulty = [
            'Easy' => $questions->where('difficulty.diff_name', 'Easy')->values()->all(),
            'Average' => $questions->where('difficulty.diff_name', 'Average')->values()->all(),
            'Difficult' => $questions->where('difficulty.diff_name', 'Difficult')->values()->all(),
            'Clincher' => $questions->where('difficulty.diff_name', 'Clincher')->values()->all(),
        ];

        // Filter based on the selected difficulty
        if ($this->selectedDifficulty !== 'All') {
            $this->questionsByDifficulty = [
                $this->selectedDifficulty => $this->questionsByDifficulty[$this->selectedDifficulty] ?? [],
            ];
        }
    }

    public function updatedSelectedDifficulty($value)
    {
        $this->loadQuestionsByDifficulty();

        if ($value !== 'All') {
            $this->questionsByDifficulty = [
                $value => $this->questionsByDifficulty[$value] ?? [],
            ];
        }
    }

    public function updatedQuizTitle($value)
    {
        $this->quiz->slug = \Str::slug($value);
    }

    public function confirmSave()
    {
        $this->showSaveModal = true; // Open the save confirmation modal
    }

    public function save()
    {
        $this->validate();
        $this->quiz->save();

        $this->showSaveModal = false; // Close the save modal after saving

        // Redirect to the 'quiz.list' route
        return redirect()->route('quizzes')
            ->with('success', 'Quiz created successfully.');
    }

    public function addParticipant()
    {
        $this->participants[] = ['name' => '', 'code' => '']; // Add new participant
    }

    public function removeParticipant($index)
    {
        unset($this->participants[$index]); // Remove participant
        $this->participants = array_values($this->participants);
    }

    public function saveParticipants()
    {
        foreach ($this->participants as $participantData) {
            Participant::updateOrCreate(
                ['id' => $participantData['id'] ?? null],
                ['quiz_id' => $this->quiz->id, 'name' => $participantData['name'], 'code' => $participantData['code']]
            );
        }
    }

    public function switchView($view)
    {
        $this->activeView = $view;
    }

    protected function initListsForFields()
    {
        $this->listsForFields['questions'] = Question::pluck('text', 'id')->toArray();
    }

    public function render(): View
    {
        return view('livewire.quiz.quiz-form', [
            'editing' => $this->editing,
            'difficulties' => $this->difficulties,
            'questionsByDifficulty' => $this->questionsByDifficulty,
            'activeView' => $this->activeView, // Pass active view to the Blade template
            'selectedDifficulty' => $this->selectedDifficulty, // Pass selected difficulty to the Blade template
        ]);
    }
}
