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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form wire:submit.prevent="save">

                        <!-- Title Input -->
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

                        <!-- Slug Input -->
                        <div class="mt-4">
                            <x-input-label for="slug" value="Slug" />
                            <x-text-input
                                wire:model="quiz.slug"
                                id="slug"
                                class="block mt-1 w-full"
                                type="text"
                                disabled
                            />
                            <x-input-error :messages="$errors->get('quiz.slug')" class="mt-2" />
                        </div>

                        <!-- Description Input -->
                        <div class="mt-4">
                            <x-input-label for="description" value="Description" />
                            <x-textarea
                                wire:model="quiz.description"
                                id="description"
                                class="block mt-1 w-full"
                            />
                            <x-input-error :messages="$errors->get('quiz.description')" class="mt-2" />
                        </div>

                       <!-- Participants Section -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold">Participants:</h3>

                            @if($editing && $quiz->participants->isNotEmpty())
                                <div class="mt-2">
                                    <table class="min-w-full border-collapse border border-gray-300">
                                        <thead>
                                            <tr>
                                                <th class="border border-gray-300 px-4 py-2">Name</th>
                                                <th class="border border-gray-300 px-4 py-2">Code</th>
                                                <th class="border border-gray-300 px-4 py-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($quiz->participants as $participant)
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->name }}</td>
                                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->code }}</td>
                                                    <td class="border border-gray-300 px-4 py-2">
                                                        <a href="{{ route('participant.edit', ['quiz' => $quiz->slug, 'participant' => $participant->id]) }}">
                                                            Edit
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="mt-2 text-gray-600">No participants are present for this quiz.</p>
                            @endif

                            <!-- Create Participant Button -->
                            @if($editing)
                                <div class="mt-4">
                                    <a href="{{ route('participant.create', ['quiz' => $quiz->slug]) }}"
                                    class="inline-block bg-green-500 text-white px-4 py-2 rounded">
                                        Create Participant
                                    </a>
                                </div>
                            @endif
                        </div>
                        <!-- Display Difficulties Section -->
                        @if($editing && $difficulties->isNotEmpty())
                            <div class="mt-6">
                                <h3 class="text-lg font-semibold text-gray-800">Difficulties</h3>
                                <table class="table-auto w-full border-collapse border border-gray-300 mt-4">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2">Name</th>
                                            <th class="border border-gray-300 px-4 py-2">Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($difficulties as $difficulty)
                                            <tr>
                                                <td class="border border-gray-300 px-4 py-2">{{ $difficulty->diff_name }}</td>
                                                <td class="border border-gray-300 px-4 py-2">{{ $difficulty->point }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="mt-4 text-right">
                                    <a href="{{ route('difficulties.edit', ['quiz_id' => $quiz->id]) }}"
                                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                        Edit Scores
                                    </a>
                                </div>
                            </div>
                        @else
                            <p>No difficulties found for this quiz.</p>
                        @endif

                        <!-- Questions Section -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold">Questions:</h3>

                            @if($editing && !empty($questionsByDifficulty))
                                @foreach($questionsByDifficulty as $difficultyName => $questions)
                                    <h4 class="text-md font-semibold mt-4">{{ ucfirst($difficultyName) }} Questions</h4>
                                    @if (!empty($questions))
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                                            @foreach($questions as $question)
                                                <a href="{{ route('question.edit', ['quiz' => $quiz->slug, 'question' => $question['id']]) }}"
                                                   class="block p-4 bg-gray-100 rounded-lg shadow-md hover:bg-gray-200 transition">
                                                    <h5 class="text-lg font-medium mb-2">{{ $question['text'] }}</h5>
                                                    <p class="text-sm text-gray-600">Click to edit this question</p>
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="mt-2 text-gray-600">No questions are present for {{ ucfirst($difficultyName) }} difficulty.</p>
                                    @endif
                                @endforeach
                            @else
                                <p class="mt-2 text-gray-600">No questions are present for this quiz.</p>
                            @endif

                            <!-- Conditional Button for Adding Questions -->
                            @if($editing && $quiz->id)
                                <div class="mt-4">
                                    <a href="{{ route('question.create', ['quiz' => $quiz->slug]) }}"
                                       class="inline-block bg-green-500 text-white px-4 py-2 rounded">
                                        Add Questions
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Published Checkbox -->
                        <div class="mt-4">
                            <div class="flex items-center">
                                <x-input-label for="published" value="Published" />
                                <input
                                    type="checkbox"
                                    id="published"
                                    class="mr-1 ml-2"
                                    wire:model="quiz.published"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('quiz.published')" class="mt-2" />
                        </div>

                        <!-- Public Checkbox -->
                        <div class="mt-4">
                            <div class="flex items-center">
                                <x-input-label for="public" value="Public" />
                                <input
                                    type="checkbox"
                                    id="public"
                                    class="mr-1 ml-2"
                                    wire:model="quiz.public"
                                />
                            </div>
                            <x-input-error :messages="$errors->get('quiz.public')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-primary-button>
                                Save
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
