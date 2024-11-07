<div class="flex">
    <!-- Sidebar: List of Questions -->
    <aside class="w-1/4 p-4 bg-gray-100 h-screen sticky top-0 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">Questions</h2>

        <!-- Difficulty Filter Dropdown -->
        <label class="block mb-2 font-medium text-gray-700">Filter by Round</label>
        <select wire:model="selectedDifficulty" wire:change="filterQuestionsByDifficulty" class="block w-full p-2 mb-4 rounded border-gray-300">
            <option value="">All Questions</option>
            <option value="Easy">Easy</option>
            <option value="Average">Average</option>
            <option value="Difficult">Difficult</option>
            <option value="Clincher">Clincher</option>
        </select>

        <!-- Table of Contents -->
        <ul class="mt-4 space-y-2">
            @foreach ($questions as $question)
                <li>
                    <button wire:click="selectQuestion({{ $question['id'] }})"
                            class="block p-2 bg-gray-200 hover:bg-gray-300 rounded transition w-full text-left">
                        {{ \Illuminate\Support\Str::limit($question['text'], 50) }}
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Import Questions Button -->
        <div class="mt-4">
            <button wire:click="openImportModal" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                Import Questions
            </button>
        </div>

        <!-- Return Button -->
        <div class="mt-6">
            <button wire:click="confirmGoBack" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Save & Return to Quiz Edit
            </button>
        </div>
    </aside>

    <!-- Main Content: Question Form -->
    <div class="w-3/4 p-6">
        <form wire:submit.prevent="{{ !empty($currentQuestion['id']) ? 'updateQuestion' : 'saveCurrentQuestion' }}">
            <div class="border p-4 mb-6 rounded-lg shadow-md">
                <h3 class="font-semibold text-lg mb-2">
                    {{ !empty($currentQuestion['id']) ? 'Edit Question' : 'New Question' }}
                </h3>

                <!-- Question Type Dropdown -->
                <div class="mb-4">
                    <x-input-label for="currentQuestion.question_type" value="Question Type" />
                    <select wire:model="currentQuestion.question_type" id="currentQuestion.question_type" class="block mt-1 w-full border-gray-300 rounded" required>
                        <option value="">Select Question Type</option>
                        <option value="Identification">Identification</option>
                        <option value="Multiple Choice">Multiple Choice</option>
                        <option value="True or False">True or False</option>
                    </select>
                    <x-input-error :messages="$errors->get('currentQuestion.question_type')" class="mt-2" />
                </div>

                <!-- Ensure question type is selected before showing other fields -->
                @if ($currentQuestion['question_type'])
                    <!-- Question Text -->
                    <div class="mb-4">
                        <x-input-label for="currentQuestion.text" value="Question Text" />
                        <x-textarea wire:model.defer="currentQuestion.text" id="currentQuestion.text" class="block mt-1 w-full" required />
                        <x-input-error :messages="$errors->get('currentQuestion.text')" class="mt-2" />
                    </div>

                    <!-- Difficulty Dropdown -->
                    <div class="mb-4">
                        <x-input-label for="currentQuestion.difficulty_id" value="Round" />
                        <select wire:model.defer="currentQuestion.difficulty_id" id="currentQuestion.difficulty_id" class="block mt-1 w-full border-gray-300 rounded" required>
                            <option value="">Select Round</option>
                            @foreach($difficulties as $difficulty)
                                <option value="{{ $difficulty->id }}">{{ $difficulty->diff_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('currentQuestion.difficulty_id')" class="mt-2" />
                    </div>

                    <!-- Dynamic Options Based on Question Type -->
                    @if ($currentQuestion['question_type'] == 'Multiple Choice')
                        <div class="mb-4">
                            <x-input-label value="Options" />
                            @foreach ($currentQuestion['options'] as $optionIndex => $option)
                                <div class="flex items-center mt-2">
                                    <!-- Option Text Input -->
                                    <x-text-input wire:model.defer="currentQuestion.options.{{ $optionIndex }}.text" class="w-full" placeholder="Option text" />

                                    <!-- Radio Button for Selecting Correct Answer -->
                                    <input type="radio"
                                        wire:click="setCorrectOption({{ $optionIndex }})"
                                        name="correctOption"
                                        class="ml-2"
                                        @if($option['correct']) checked @endif />

                                    <span class="ml-2">Correct</span>

                                    <!-- Remove Option Button -->
                                    <button wire:click.prevent="removeOption({{ $optionIndex }})" type="button" class="ml-4 text-red-500 hover:underline">Remove</button>
                                </div>
                            @endforeach

                            <!-- Add Option Button -->
                            <button wire:click.prevent="addOption" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add Option</button>
                        </div>
                    @elseif ($currentQuestion['question_type'] == 'True or False')
                        <div class="mb-4">
                            <x-input-label value="Options" />
                            <div class="flex items-center mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model="correctAnswer" value="True" class="form-radio" />
                                    <span class="text-sm">True</span>
                                </label>
                            </div>
                            <div class="flex items-center mt-2">
                                <label class="flex items-center space-x-2">
                                    <input type="radio" wire:model="correctAnswer" value="False" class="form-radio" />
                                    <span class="text-sm">False</span>
                                </label>
                            </div>
                        </div>
                    @elseif ($currentQuestion['question_type'] == 'Identification')
                        <div class="mb-4">
                            <x-input-label value="Answer" />
                            <div class="flex items-center mt-2">
                                <x-text-input wire:model.defer="currentQuestion.options.0.text" class="w-full" placeholder="Answer" />
                                <input type="checkbox" wire:model.defer="currentQuestion.options.0.correct" class="ml-2" checked readonly />
                                <span class="ml-2">Correct</span>
                            </div>
                        </div>
                    @endif

                    <!-- Add and Remove Buttons -->
                    <div class="flex space-x-2 mt-4">
                        @if (!empty($currentQuestion['id']))
                            <!-- Save Edit Button -->
                            <button type="button" wire:click="confirmEdit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                                Save Edit
                            </button>

                            <!-- Cancel Edit Button -->
                            <button type="button" wire:click="resetCurrentQuestionForm" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                                Cancel Edit
                            </button>
                        @else
                            <!-- Add Question Button -->
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                                Add Question
                            </button>
                        @endif

                        <!-- Remove Button -->
                        @if (!empty($currentQuestion['id']))
                            <button type="button" wire:click="confirmRemove({{ $currentQuestion['id'] }})" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                Remove Question
                            </button>
                        @endif
                    </div>

                @endif
            </div>
        </form>
    </div>


    <!-- Save Confirmation Modal -->
    @if($showSaveModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Confirm Save</h2>
            <p>Are you sure you want to save the changes? This action will update the database.</p>
            <div class="mt-6 flex justify-end space-x-2">
                <button wire:click="saveCurrentQuestion" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Save</button>
                <button wire:click="$set('showSaveModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Remove Confirmation Modal -->
    @if($showRemoveModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Remove Question</h2>
            <p>Are you sure you want to remove this question? This action cannot be undone.</p>
            <div class="mt-6 flex justify-end space-x-2">
                <button wire:click="removeCurrentQuestion" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Remove</button>
                <button wire:click="$set('showRemoveModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Save Edit Confirmation Modal -->
    @if($showEditModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Confirm Edit</h2>
            <p>Are you sure you want to save the changes to this question?</p>
            <div class="mt-6 flex justify-end space-x-2">
                <button wire:click="updateQuestion" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Save</button>
                <button wire:click="$set('showEditModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Go Back Confirmation Modal -->
    @if($showGoBackModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Confirm Navigation</h2>
            <p>Are you sure you want to return to the quiz edit? Any unsaved changes will be lost.</p>
            <div class="mt-6 flex justify-end space-x-2">
                <button wire:click="goBackToQuizEdit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Yes, Go Back
                </button>
                <button wire:click="$set('showGoBackModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Import Questions Modal -->
    @if($showImportModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Import Questions</h2>
            <p>Select an Excel file to import questions for the quiz.</p>

            <!-- File Input for Import -->
            <input type="file" wire:model="file" class="block w-full p-2 my-2 border rounded">
            @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <!-- Import Button -->
            <button wire:click="importQuestions" class="mt-2 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 w-full">
                Import
            </button>

            <!-- Download Template Button -->
            <button wire:click="downloadTemplate" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 w-full">
                Download Template
            </button>

            <!-- Close Modal Button -->
            <button wire:click="closeImportModal" class="mt-4 bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400 w-full">
                Cancel
            </button>
        </div>
    </div>
    @endif


</div>


