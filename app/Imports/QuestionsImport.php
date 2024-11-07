<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Option;
use App\Models\Difficulty;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuestionsImport implements WithMultipleSheets
{
    private int $quizId;

    public function __construct(int $quizId)
    {
        $this->quizId = $quizId;
    }

    public function sheets(): array
    {
        return [
            'Easy' => new DifficultySheetImport($this->quizId, 'Easy'),
            'Average' => new DifficultySheetImport($this->quizId, 'Average'),
            'Difficult' => new DifficultySheetImport($this->quizId, 'Difficult'),
            'Clincher' => new DifficultySheetImport($this->quizId, 'Clincher'),
        ];
    }
}

class DifficultySheetImport implements ToCollection
{
    private int $difficultyId;

    public function __construct(private int $quizId, private string $difficultyName)
    {
        // Find the `difficulty_id` for the given quiz and difficulty name
        $this->difficultyId = Difficulty::where('quiz_id', $this->quizId)
            ->where('diff_name', $this->difficultyName)
            ->value('id');
    }

    public function collection(Collection $rows)
    {
        if (!$this->difficultyId) {
            // Log or handle missing difficulty ID error
            return;
        }

        foreach ($rows->skip(2) as $row) { // Skip header row
            $question = Question::create([
                'text' => $row[0],
                'question_type' => $row[1],
                'difficulty_id' => $this->difficultyId,
            ]);

            // Insert into question_quiz table to associate question with quiz
            DB::table('question_quiz')->insert([
                'quiz_id' => $this->quizId,
                'question_id' => $question->id,
            ]);

            // Identify the correct option based on the number in `correct_answer` column
            $correctOptionIndex = $row[6] - 1; // Convert to zero-based index

            // Validate correct option index to ensure it's within range
            if ($correctOptionIndex < 0 || $correctOptionIndex > 3) {
                // Handle out-of-range correct answer index
                continue;
            }

            // Create options for the question
            $options = [
                ['text' => $row[2], 'correct' => ($correctOptionIndex === 0)],
                ['text' => $row[3], 'correct' => ($correctOptionIndex === 1)],
                ['text' => $row[4], 'correct' => ($correctOptionIndex === 2)],
                ['text' => $row[5], 'correct' => ($correctOptionIndex === 3)],
            ];

            foreach ($options as $optionData) {
                if (!empty($optionData['text'])) {
                    $question->options()->create($optionData);
                }
            }
        }
    }
}
