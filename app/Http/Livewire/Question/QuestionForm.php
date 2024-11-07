<?php

namespace App\Http\Livewire\Question;

use App\Models\Difficulty;
use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;

class QuestionForm extends Component
{
    use WithFileUploads;

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
    public string $selectedDifficulty = '';
    public array $questions = [];
    public bool $showSaveModal = false;
    public bool $showRemoveModal = false;
    public ?int $questionToRemove = null;
    public bool $showEditModal = false;
    public ?string $correctAnswer = null;
    public bool $showGoBackModal = false;
    public $file;
    public bool $showImportModal = false;

    protected $rules = [
        'currentQuestion.text' => 'required|string',
        'currentQuestion.difficulty_id' => 'required|exists:difficulties,id',
        'currentQuestion.code_snippet' => 'nullable|string',
        'currentQuestion.question_type' => 'required|string|in:Identification,Multiple Choice,True or False',
        'currentQuestion.options.*.text' => 'required|string',
        'file' => 'nullable|file|mimes:xlsx,csv', // Validation rule for Excel file
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

    public function importQuestions(): void
    {
        // Validate that a file is selected and is of the correct type
        $this->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        try {
            // Attempt to import the file and associate questions with the quiz ID
            Excel::import(new QuestionsImport($this->quiz->id), $this->file->getRealPath());

            // Reload the questions list to reflect imported questions
            $this->questions = $this->quiz->questions()->with('options')->get()->toArray();

            // Display a success message
            session()->flash('success', 'Questions imported successfully!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors within the file (e.g., missing required data)
            $failures = $e->failures();
            $errorMessage = "File import failed due to validation errors:\n";
            foreach ($failures as $failure) {
                $errorMessage .= "Row {$failure->row()}: ";
                $errorMessage .= implode(', ', $failure->errors()) . "\n";
            }
            session()->flash('error', $errorMessage);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., invalid data structure or incorrect foreign key relationships)
            session()->flash('error', 'Database error: Unable to save some records. Please check the file data format.');
        } catch (\Exception $e) {
            // Catch any other general errors
            session()->flash('error', 'An error occurred during import. Please check the file format and data structure.');
        } finally {
            // Close the modal, whether import was successful or not
            $this->closeImportModal();
        }
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/public/templates/Quiztria_Spreadsheet_Template.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'Quiztria_Spreadsheet_Template.xlsx');
        }

        session()->flash('error', 'Template file not found.');
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
            $this->correctAnswer = $question->options->firstWhere('correct', true)?->text;
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
                $optionData['correct'] = ($optionData['text'] === $this->correctAnswer);
            }
            $question->options()->create($optionData);
        }

        $this->quiz->questions()->attach($question->id);

        $this->questions = $this->quiz->questions()->with('options')->get()->toArray();

        $this->resetCurrentQuestionForm();

        session()->flash('success', 'Question added successfully.');
        $this->showSaveModal = false;
    }

    public function goBackToQuizEdit(): void
    {
        $this->resetCurrentQuestionForm();
        $this->showGoBackModal = false;

        redirect()->route('quiz.edit', ['quiz' => $this->quiz->slug]);
    }

    public function confirmGoBack(): void
    {
        $this->showGoBackModal = true;
    }

    public function updateQuestion(): void
    {
        $this->validate();

        if (!empty($this->currentQuestion['id'])) {
            $question = Question::find($this->currentQuestion['id']);

            if ($question) {
                $question->update([
                    'text' => $this->currentQuestion['text'],
                    'difficulty_id' => $this->currentQuestion['difficulty_id'],
                    'code_snippet' => $this->currentQuestion['code_snippet'],
                    'question_type' => $this->currentQuestion['question_type'],
                ]);

                $question->options()->delete();
                foreach ($this->currentQuestion['options'] as $optionData) {
                    if ($this->currentQuestion['question_type'] === 'True or False') {
                        $optionData['correct'] = ($optionData['text'] === $this->correctAnswer);
                    }
                    $question->options()->create($optionData);
                }

                $this->questions = $this->quiz->questions()->with('options')->get()->toArray();
                session()->flash('success', 'Question updated successfully.');
            }

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

    public function openImportModal(): void
    {
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
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

        $this->showRemoveModal = false;
    }

    public function resetCurrentQuestionForm(): void
    {
        $this->currentQuestion = [
            'text' => '',
            'difficulty_id' => '',
            'code_snippet' => '',
            'question_type' => '',
            'options' => $this->defaultOptions(''),
        ];
        $this->correctAnswer = null;
        $this->editing = false;
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
            return [
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false],
            ];
        }
    }

    public function setCorrectOption(int $optionIndex): void
    {
        if ($this->currentQuestion['question_type'] === 'Multiple Choice') {
            foreach ($this->currentQuestion['options'] as $index => &$option) {
                $option['correct'] = ($index === $optionIndex);
            }
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
