<div class="flex">
    <!-- Sidebar: Table of Contents -->
    <aside class="w-1/4 p-4 bg-gray-100 h-screen sticky top-0 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">Questions</h2>

        <!-- Difficulty Filter Dropdown -->
        <label class="block mb-2 font-medium text-gray-700">Filter by Difficulty</label>
        <select wire:model="selectedDifficulty" class="block w-full p-2 mb-4 rounded border-gray-300">
            <option value="">All Questions</option>
            <option value="easy">Easy</option>
            <option value="average">Average</option>
            <option value="hard">Hard</option>
            <option value="clincher">Clincher</option>
        </select>

        <!-- Table of Contents -->
        <ul class="mt-4 space-y-2">
            @foreach ($filteredQuestions as $index => $question)
                <li>
                    <a href="#question-{{ $index }}"
                       class="block p-2 bg-gray-200 hover:bg-gray-300 rounded transition">
                        Question #{{ $index + 1 }}
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Buttons Section -->
        <div class="mt-6 space-y-2">
            <button wire:click.prevent="addQuestion"
                    class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Add Another Question
            </button>
            <button wire:click.prevent="save"
                    class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Save Questions
            </button>
        </div>
    </aside>

    <!-- Main Content: Questions Form -->
    <div class="w-3/4 p-6">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $editing ? 'Edit Questions for ' . $quiz->title : 'Create Questions for ' . $quiz->title }}
            </h2>
        </x-slot>

        <form wire:submit.prevent="save">
            @foreach ($filteredQuestions as $index => $question)
                <div id="question-{{ $index }}" class="border p-4 mb-6 rounded-lg shadow-md">
                    <h3 class="font-semibold text-lg mb-2">Question #{{ $index + 1 }}</h3>

                    <!-- Question Text -->
                    <div class="mb-4">
                        <x-input-label for="questions.{{ $index }}.text" value="Question Text"/>
                        <x-textarea wire:model.defer="questions.{{ $index }}.text"
                                    id="questions-{{ $index }}-text"
                                    class="block mt-1 w-full"
                                    required/>
                        <x-input-error :messages="$errors->get('questions.' . $index . '.text')" class="mt-2"/>
                    </div>

                    <!-- Difficulty Dropdown -->
                    <div class="mb-4">
                        <x-input-label for="questions.{{ $index }}.difficulty_id" value="Difficulty"/>
                        <select wire:model.defer="questions.{{ $index }}.difficulty_id"
                                id="questions-{{ $index }}-difficulty"
                                class="block mt-1 w-full border-gray-300 rounded"
                                required>
                            <option value="">Select Difficulty</option>
                            @foreach($difficulties as $difficulty)
                                <option value="{{ $difficulty->id }}">{{ $difficulty->diff_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Question Type Dropdown -->
                    <div class="mb-4">
                        <x-input-label for="questions.{{ $index }}.question_type" value="Question Type"/>
                        <select wire:model="questions.{{ $index }}.question_type"
                                id="questions-{{ $index }}-type"
                                class="block mt-1 w-full border-gray-300 rounded"
                                required>
                            <option value="">Select Question Type</option>
                            <option value="Identification">Identification</option>
                            <option value="Multiple Choice">Multiple Choice</option>
                            <option value="True or False">True or False</option>
                        </select>
                        <x-input-error :messages="$errors->get('questions.' . $index . '.question_type')" class="mt-2"/>
                    </div>

                    <!-- Dynamic Options Section Based on Question Type -->
                    @if ($questions[$index]['question_type'] == 'Multiple Choice')
                        <div class="mb-4">
                            <x-input-label value="Options"/>
                            @foreach ($question['options'] as $optionIndex => $option)
                                <div class="flex items-center mt-2">
                                    <x-text-input wire:model.defer="questions.{{ $index }}.options.{{ $optionIndex }}.text"
                                                  class="w-full"
                                                  placeholder="Option text"/>
                                    <input type="checkbox"
                                           wire:model.defer="questions.{{ $index }}.options.{{ $optionIndex }}.correct"
                                           class="ml-2"/>
                                    <span class="ml-2">Correct</span>
                                    <button wire:click.prevent="removeOption({{ $index }}, {{ $optionIndex }})"
                                            type="button"
                                            class="ml-4 text-red-500 hover:underline">
                                        Remove
                                    </button>
                                </div>
                            @endforeach

                            <button wire:click.prevent="addOption({{ $index }})"
                                    class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Add Option
                            </button>
                        </div>
                        @elseif ($questions[$index]['question_type'] == 'True or False')
                            <div class="mb-4">
                                <x-input-label value="Options"/>
                                <div class="flex items-center mt-2">
                                    <!-- True Option -->
                                    <x-text-input value="True" class="w-full" readonly/>
                                    <input type="checkbox"
                                        wire:model.defer="questions.{{ $index }}.options.0.correct"
                                        class="ml-2"/>
                                    <span class="ml-2">Correct</span>
                                </div>
                                <div class="flex items-center mt-2">
                                    <!-- False Option -->
                                    <x-text-input value="False" class="w-full" readonly/>
                                    <input type="checkbox"
                                        wire:model.defer="questions.{{ $index }}.options.1.correct"
                                        class="ml-2"/>
                                    <span class="ml-2">Correct</span>
                                </div>
                            </div>

                    @elseif ($questions[$index]['question_type'] == 'Identification')
                        <div class="mb-4">
                            <x-input-label value="Answer"/>
                            <div class="flex items-center mt-2">
                                <x-text-input wire:model.defer="questions.{{ $index }}.options.0.text"
                                              class="w-full"
                                              placeholder="Answer"/>
                                <input type="checkbox"
                                       wire:model.defer="questions.{{ $index }}.options.0.correct"
                                       class="ml-2" checked readonly/>
                                <span class="ml-2">Correct</span>
                            </div>
                        </div>
                    @endif

                    <!-- Remove Question Button -->
                    <button wire:click.prevent="removeQuestion({{ $index }})"
                            class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Remove Question
                    </button>
                </div>
            @endforeach
        </form>
    </div>
</div>
