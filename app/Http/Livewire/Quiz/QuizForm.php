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

    protected $rules = [
        'quiz.title' => 'required|string',
        'quiz.slug' => 'string',
        'quiz.description' => 'nullable|string',
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
            'easy' => $questions->where('difficulty.diff_name', 'easy')->values()->all(),
            'average' => $questions->where('difficulty.diff_name', 'average')->values()->all(),
            'hard' => $questions->where('difficulty.diff_name', 'hard')->values()->all(),
            'clincher' => $questions->where('difficulty.diff_name', 'clincher')->values()->all(),
        ];
    }

    public function updatedQuizTitle($value)
    {
        $this->quiz->slug = \Str::slug($value);
    }

    public function save()
    {
        $this->validate();
        $this->quiz->save();

        return redirect()->route('difficulty.form', ['quiz_id' => $this->quiz->id])
            ->with('success', 'Quiz created successfully. Now set up the difficulties.');
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
        ]);
    }
}
