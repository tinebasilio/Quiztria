<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $editing ? 'Edit Quiz' : 'Create Quiz' }}
        </h2>
    </x-slot>

    <x-slot name="title">
        {{ $editing ? 'Edit Quiz ' . $quiz->title : 'Create Quiz' }}
    </x-slot>

    <div class="py-12">
    <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                    <!-- Tabs -->
                    <div class="mb-6 border-b">
                        <ul class="flex flex-wrap sm:flex-nowrap space-x-6">
                            <li>
                                <div wire:click="$set('activeView', 'details')" class="px-4 py-2 inline-block text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-all {{ $activeView === 'details' ? 'border-b-2 border-purple-500 text-purple-600' : '' }}">
                                    General Info
                                </div>
                            </li>
                            <li>
                                <div wire:click="$set('activeView', 'questions')" class="px-4 py-2 inline-block text-sm font-semibold text-gray-700 cursor-pointer hover:bg-gray-100 transition-all {{ $difficulties->isEmpty() ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }} {{ $activeView === 'questions' ? 'border-b-2 border-purple-500 text-purple-600' : '' }}">
                                    Manage Questions
                                </div>
                            </li>
                        </ul>
                    </div>

                    @if($activeView === 'details')
                        <!-- General Info tab -->
                        <form wire:submit.prevent="save">
                            <!-- Title -->
                            <div>
                                <x-input-label for="title" value="Title" />
                                <x-text-input
                                    wire:model="quiz.title"
                                    id="title"
                                    class="block mt-1 w-full"
                                    type="text"
                                    required
                                />
                                <x-input-error :messages="$errors->get('quiz.title')" class="mt-2" />
                            </div>

                            <!-- Description -->
                            <div class="mt-4">
                                <x-input-label for="description" value="Description" />
                                <x-textarea
                                    wire:model="quiz.description"
                                    id="description"
                                    class="block mt-1 w-full"
                                />
                                <x-input-error :messages="$errors->get('quiz.description')" class="mt-2" />
                            </div>

                            <!-- Rounds -->
                            @if($editing && $difficulties->isNotEmpty())
                                <div class="mt-6">
                                    <h3 class="text-lg font-semibold text-gray-800">Rounds</h3>
                                    <table class="table-auto w-full border-collapse border border-gray-300 mt-4">
                                        <thead>
                                            <tr>
                                                <th class="border border-gray-300 px-4 py-2">Name</th>
                                                <th class="border border-gray-300 px-4 py-2">Points</th>
                                                <th class="border border-gray-300 px-4 py-2">Timer (seconds)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($difficulties as $difficulty)
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">{{ $difficulty->diff_name }}</td>
                                                    <td class="border border-gray-300 px-4 py-2">{{ $difficulty->point }}</td>
                                                    <td class="border border-gray-300 px-4 py-2">{{ $difficulty->timer }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    @if($quiz->id) <!-- Ensure quiz ID exists -->
                                        <div class="mt-4 text-right">
                                            <a href="{{ route('difficulties.edit', ['quiz_id' => $quiz->id]) }}"
                                               class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                                Edit Round Points and Timer
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                @if($quiz->id) <!-- Ensure quiz ID exists -->
                                    <div class="mt-4">
                                        <a href="{{ route('difficulty.form', ['quiz_id' => $quiz->id]) }}"
                                           class="inline-block bg-green-500 text-white px-4 py-2 rounded">
                                            Go to Rounds
                                        </a>
                                        <p class="mt-2 text-red-600">No rounds have been found for this quiz. Please create rounds to continue to add questions.</p>

                                    </div>
                                @endif
                            @endif

                            <!-- Participants -->
                            @if($quiz->id) <!-- Ensure quiz ID exists -->
                                <div class="mt-6">
                                    <h3 class="text-lg font-semibold">Participants</h3>

                                    @if($editing && $quiz->participants->isNotEmpty())
                                        <div class="mt-2">
                                            <table class="min-w-full border-collapse border border-gray-300">
                                                <thead>
                                                    <tr>
                                                        <th class="border border-gray-300 px-4 py-2">Name</th>
                                                        <th class="border border-gray-300 px-4 py-2">Code</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($quiz->participants as $participant)
                                                        <tr>
                                                            <td class="border border-gray-300 px-4 py-2">{{ $participant->name }}</td>
                                                            <td class="border border-gray-300 px-4 py-2">{{ $participant->code }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="mt-2 text-gray-600">No participants found for this quiz.</p>
                                    @endif

                                    <!-- Edit Participant Button -->
                                    <div class="mt-4 text-right">
                                        <a href="{{ route('participant.create', ['quiz' => $quiz->slug]) }}"
                                            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                            Edit Participants
                                        </a>

                                    </div>
                                </div>
                            @endif

                            <!-- Save Quiz Button -->
                            <div class="mt-4">
                                <x-primary-button class="mt-4 w-full sm:w-auto uppercase" wire:click="confirmSave">
                                    Save Quiz
                                </x-primary-button>
                            </div>

                            @if($showSaveModal)
                                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                                    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                                        <h2 class="text-xl font-semibold mb-4">Confirm Save</h2>
                                        <p>Are you sure you want to save the quiz? This action will update the quiz in the database.</p>
                                        <div class="mt-6 flex justify-end space-x-2">
                                            <button wire:click="save" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Confirm</button>
                                            <button wire:click="$set('showSaveModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    @elseif($activeView === 'questions')

                        <div class="mt-6">
                        <!-- Filter by Round -->
                        <div class="flex flex-col sm:flex-row items-center gap-4 mb-6">
                            <label for="filter" class="text-gray-700">Filter:</label>
                            <select wire:model="selectedDifficulty" id="filter" class="border-gray-300 rounded py-2">
                                <option value="All">All</option>
                                <option value="Easy">Easy</option>
                                <option value="Average">Average</option>
                                <option value="Difficult">Difficult</option>
                                <option value="Clincher">Clincher</option>
                            </select>
                                <a href="{{ route('question.create', ['quiz' => $quiz->slug]) }}" class="bg-green-500 text-white px-4 py-2 rounded">
                                    Add Questions
                                </a>
                            </div>

                            <h3 class="text-lg font-semibold mt-4">Questions:</h3>

                            <!-- Questions List -->
                            @if($editing && !empty($questionsByDifficulty))
                                @foreach($questionsByDifficulty as $difficultyName => $questions)
                                    @if($selectedDifficulty === 'All' || $selectedDifficulty === $difficultyName)
                                        <div x-data="{ open: false }" class="mt-4">
                                            <div class="flex justify-between items-center cursor-pointer" @click="open = !open">
                                                <h4 class="text-md font-semibold">
                                                    {{ ucfirst($difficultyName) }} Questions
                                                    <span class="text-sm text-gray-500">({{ count($questions) }})</span>
                                                </h4>
                                                <span :class="{'rotate-180': open}" class="transition-transform duration-300 transform">↑</span>
                                            </div>

                                            <div x-show="open" x-collapse class="mt-2">
                                                @if (!empty($questions))
                                                    <ul class="list-disc list-inside">
                                                        @foreach($questions as $question)
                                                            <li class="mb-2">{{ $question['text'] }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mt-2 text-gray-600">No questions added yet.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <p class="mt-2 text-gray-600">The quiz is currently empty. Please add questions.</p>
                            @endif

                            <!-- Save Quiz Button -->
                            <div class="mt-4">
                                <x-primary-button class="mt-4 w-full sm:w-auto uppercase" wire:click="confirmSave">
                                    Save Quiz
                                </x-primary-button>
                            </div>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>