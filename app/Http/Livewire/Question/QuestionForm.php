<?php

namespace App\Http\Livewire\Question;

use App\Models\Difficulty;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class QuestionForm extends Component
{
    public Quiz $quiz;
    public bool $editing = false;
    public array $currentQuestion = [
        'text' => '',
        'difficulty_id' => '',
        'code_snippet' => '',
        'question_type' => '',
        'options' => [],
    ];
    public Collection $difficulties;
    public string $selectedDifficulty = ''; // Store selected difficulty for filtering.
    public array $questions = []; // To store added questions
    public bool $showSaveModal = false;
    public bool $showRemoveModal = false;
    public ?int $questionToRemove = null;
    public bool $showEditModal = false;
    public ?string $correctAnswer = null;
    public bool $showGoBackModal = false; // To control the modal visibility

    protected $rules = [
        'currentQuestion.text' => 'required|string',
        'currentQuestion.difficulty_id' => 'required|exists:difficulties,id',
        'currentQuestion.code_snippet' => 'nullable|string',
        'currentQuestion.question_type' => 'required|string|in:Identification,Multiple Choice,True or False',
        'currentQuestion.options.*.text' => 'required|string',
    ];

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
        $this->editing = $quiz->exists;

        $this->questions = $quiz->questions()
            ->with('options')
            ->get()
            ->map(fn($question) => $this->transformQuestion($question))
            ->toArray();

        $this->difficulties = Difficulty::where('quiz_id', $quiz->id)->get();
        $this->currentQuestion['options'] = $this->defaultOptions($this->currentQuestion['question_type']);
    }

    public function filterQuestionsByDifficulty(): void
    {
        if ($this->selectedDifficulty) {
            $this->questions = $this->quiz->questions()
                ->whereHas('difficulty', function ($query) {
                    $query->where('diff_name', $this->selectedDifficulty);
                })
                ->with('options')
                ->get()
                ->map(fn($question) => $this->transformQuestion($question))
                ->toArray();
        } else {
            $this->questions = $this->quiz->questions()
                ->with('options')
                ->get()
                ->map(fn($question) => $this->transformQuestion($question))
                ->toArray();
        }
    }

    private function transformQuestion(Question $question): array
    {
        if ($question->question_type === 'True or False') {
            $this->correctAnswer = $question->options->firstWhere('correct', true)?->text; // Set the correct answer
        }

        return [
            'id' => $question->id,
            'text' => $question->text,
            'difficulty_id' => $question->difficulty_id,
            'code_snippet' => $question->code_snippet,
            'question_type' => $question->question_type,
            'options' => $question->options->map(fn($option) => [
                'id' => $option->id,
                'text' => $option->text,
                'correct' => $option->correct,
            ])->toArray(),
        ];
    }

    public function selectQuestion($questionId): void
    {
        $question = Question::with('options')->find($questionId);

        if ($question) {
            $this->currentQuestion = $this->transformQuestion($question);
        }
    }

    public function addOption(): void
    {
        if ($this->currentQuestion['question_type'] === 'Multiple Choice') {
            $this->currentQuestion['options'][] = ['text' => '', 'correct' => false];
        }
    }

    public function removeOption($optionIndex): void
    {
        unset($this->currentQuestion['options'][$optionIndex]);
        $this->currentQuestion['options'] = array_values($this->currentQuestion['options']);
    }
    public function saveCurrentQuestion(): void
    {
        $this->validate();

        $question = Question::create([
            'quiz_id' => $this->quiz->id,
            'text' => $this->currentQuestion['text'],
            'difficulty_id' => $this->currentQuestion['difficulty_id'],
            'code_snippet' => $this->currentQuestion['code_snippet'],
            'question_type' => $this->currentQuestion['question_type'],
        ]);

        foreach ($this->currentQuestion['options'] as $optionData) {
            if ($this->currentQuestion['question_type'] === 'True or False') {
                // Ensure only one option is marked as correct for True or False questions
                $optionData['correct'] = ($optionData['text'] === $this->correctAnswer);
            }
            $question->options()->create($optionData);
        }

        $this->quiz->questions()->attach($question->id);

        // Update the list of questions shown in the sidebar
        $this->questions = $this->quiz->questions()->with('options')->get()->toArray();

        // Reset the form for a new question
        $this->resetCurrentQuestionForm();

        session()->flash('success', 'Question added successfully.');
        $this->showSaveModal = false; // Close the save modal after saving
    }

    public function goBackToQuizEdit(): void
    {
        // Reset the form and hide the modal
        $this->resetCurrentQuestionForm();
        $this->showGoBackModal = false; // Ensure this modal state exists

        // Redirect to the quiz edit route
        redirect()->route('quiz.edit', ['quiz' => $this->quiz->slug]);
    }

    public function confirmGoBack(): void
{
    $this->showGoBackModal = true; // Open the modal to confirm navigation
}

    public function updateQuestion(): void
    {
        // Validate the current question data
        $this->validate();

        if (!empty($this->currentQuestion['id'])) {
            $question = Question::find($this->currentQuestion['id']);

            if ($question) {
                // Update the question with new data
                $question->update([
                    'text' => $this->currentQuestion['text'],
                    'difficulty_id' => $this->currentQuestion['difficulty_id'],
                    'code_snippet' => $this->currentQuestion['code_snippet'],
                    'question_type' => $this->currentQuestion['question_type'],
                ]);

                // Remove existing options and add new ones
                $question->options()->delete();
                foreach ($this->currentQuestion['options'] as $optionData) {
                    if ($this->currentQuestion['question_type'] === 'True or False') {
                        // Ensure only one option is marked as correct for True or False questions
                        $optionData['correct'] = ($optionData['text'] === $this->correctAnswer);
                    }
                    $question->options()->create($optionData);
                }

                // Refresh the questions list in the sidebar
                $this->questions = $this->quiz->questions()->with('options')->get()->toArray();
                session()->flash('success', 'Question updated successfully.');
            }

            // Reset the form and close the edit modal after saving
            $this->resetCurrentQuestionForm();
            $this->showEditModal = false;
        }
    }

    public function confirmRemove($questionId): void
    {
        $this->questionToRemove = $questionId;
        $this->showRemoveModal = true;
    }

    public function confirmEdit(): void
    {
        $this->showEditModal = true;
    }

    public function removeCurrentQuestion(): void
    {
        if ($this->questionToRemove) {
            $question = Question::find($this->questionToRemove);
            if ($question) {
                $question->delete();
                $this->questions = $this->quiz->questions()->with('options')->get()->toArray();
                $this->resetCurrentQuestionForm();
                session()->flash('success', 'Question removed successfully.');
            }
            $this->questionToRemove = null;
        }

        $this->showRemoveModal = false; // Close the remove modal after removing
    }
    public function resetCurrentQuestionForm(): void
    {
        $this->currentQuestion = [
            'text' => '',
            'difficulty_id' => '',
            'code_snippet' => '',
            'question_type' => '',
            'options' => $this->defaultOptions(''), // Reset options based on no type selected
        ];
        $this->correctAnswer = null; // Reset the correct answer for True/False questions
        $this->editing = false; // Ensure editing mode is turned off
    }

    public function updatedCurrentQuestionQuestionType($value): void
    {
        $this->currentQuestion['options'] = $this->defaultOptions($value);
    }

    private function defaultOptions($type): array
    {
        if ($type === 'True or False') {
            return [
                ['text' => 'True', 'correct' => false],
                ['text' => 'False', 'correct' => false],
            ];
        } elseif ($type === 'Identification') {
            return [['text' => '', 'correct' => true]];
        } else {
            // Multiple Choice
            return [
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
            ];
        }
    }

    public function render(): View
    {
        return view('livewire.question.question-form', [
            'questions' => $this->questions,
            'difficulties' => $this->difficulties,
        ]);
    }
}
