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
    public array $questions = [];
    public Collection $difficulties;
    public string $selectedDifficulty = ''; // Store selected difficulty for filtering.

    protected $rules = [
        'questions.*.text' => 'required|string',
        'questions.*.difficulty_id' => 'required|exists:difficulties,id',
        'questions.*.code_snippet' => 'nullable|string',
        'questions.*.question_type' => 'required|string|in:Identification,Multiple Choice,True or False',
        'questions.*.options.*.text' => 'required|string',
    ];

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
        $this->editing = $quiz->exists;

        if ($this->editing) {
            $this->questions = $quiz->questions()
                ->with('options')
                ->get()
                ->map(fn($question) => $this->transformQuestion($question))
                ->toArray();
        } else {
            $this->addQuestion();
        }

        $this->difficulties = Difficulty::where('quiz_id', $quiz->id)->get();
    }

    private function transformQuestion(Question $question): array
    {
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

    public function addQuestion(): void
    {
        $this->questions[] = [
            'id' => null,
            'text' => '',
            'difficulty_id' => '',
            'code_snippet' => '',
            'question_type' => 'Multiple Choice',
            'options' => $this->defaultOptions('Multiple Choice'), // Initialize options
        ];
    }

    public function addOption($questionIndex): void
    {
        $this->questions[$questionIndex]['options'][] = ['text' => '', 'correct' => false];
    }

    public function removeOption($questionIndex, $optionIndex): void
    {
        unset($this->questions[$questionIndex]['options'][$optionIndex]);
        $this->questions[$questionIndex]['options'] = array_values($this->questions[$questionIndex]['options']);
    }

    public function removeQuestion($index): void
    {
        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    public function updated($name, $value)
    {
        if (str_contains($name, 'question_type')) {
            [$field, $questionIndex] = explode('.', $name);
            $this->questions[$questionIndex]['options'] = $this->defaultOptions($value);
        }
    }


    private function defaultOptions($type): array
    {
        if ($type == 'True or False') {
            return [
                ['text' => 'True', 'correct' => false],
                ['text' => 'False', 'correct' => false],
            ];
        } elseif ($type == 'Identification') {
            return [['text' => '', 'correct' => true]];
        } else {
            // Multiple Choice
            return [
                ['text' => '', 'correct' => false], 
                ['text' => '', 'correct' => false],
                ['text' => '', 'correct' => false], 
                ['text' => '', 'correct' => false]
            ];
        }
    }


    public function save()
    {
        $this->validate();

        foreach ($this->questions as $questionData) {
            $question = Question::updateOrCreate(
                ['id' => $questionData['id']],
                [
                    'text' => $questionData['text'],
                    'difficulty_id' => $questionData['difficulty_id'],
                    'code_snippet' => $questionData['code_snippet'],
                    'question_type' => $questionData['question_type'],
                ]
            );

            $question->options()->delete();

            foreach ($questionData['options'] as $optionData) {
                $question->options()->create($optionData);
            }

            $this->quiz->questions()->syncWithoutDetaching([$question->id]);
        }

        return redirect()->route('quiz.edit', $this->quiz->slug)
            ->with('success', 'Questions saved successfully.');
    }

    public function render(): View
    {
        return view('livewire.question.question-form', [
            'filteredQuestions' => $this->filteredQuestions,
            'difficulties' => $this->difficulties,
        ]);
    }

    public function getFilteredQuestionsProperty(): array
    {
        if (empty($this->selectedDifficulty)) {
            return $this->questions;
        }

        return array_filter($this->questions, function ($question) {
            $difficulty = Difficulty::find($question['difficulty_id']);
            return $difficulty && $difficulty->diff_name === $this->selectedDifficulty;
        });
    }
}
