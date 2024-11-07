<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Room Details: {{ $room->room_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(auth()->guard('participant')->check())
                        @if(session()->has('participant_id'))
                            <div>
                                <script>
                                    window.roomId = @json($room->id);
                                    window.isAdmin = false; // Explicitly set to false for participant view
                                    window.quizStarted = @json($quizStarted);
                                    window.participantId = @json(session('participant_id'));
                                </script>

                                <div id="waitingMessage" class="{{ $quizStarted ? 'hidden' : '' }}">
                                    In room, waiting for admin to start the quiz bee.
                                </div>
                                <div id="countdownDisplay" class="hidden text-lg font-semibold mb-4 text-red-500"></div>

                                <div class="flex justify-between items-center mt-4">
                                    <div id="questionDisplay" class="text-center text-lg font-semibold {{ $quizStarted && $currentQuestion ? '' : 'hidden' }}">
                                        {{ $currentQuestion->text ?? 'Waiting for question...' }}
                                    </div>
                                    <div id="difficultyDisplay" class="text-right text-lg font-semibold text-gray-700 {{ $quizStarted && $currentQuestion ? '' : 'hidden' }}">
                                        Round: {{ $currentDifficulty ?? 'N/A' }}
                                    </div>
                                </div>

                                <div id="optionsContainer" class="text-left mt-4 {{ $quizStarted && $currentQuestion ? '' : 'hidden' }}">
                                    @if($currentQuestionType === 'Identification')
                                        <input type="text"
                                            wire:model.defer="participantAnswer"
                                            id="participantAnswer"
                                            class="border p-2 w-full rounded"
                                            placeholder="Type your answer here"
                                            @if($answerSubmitted) disabled @endif />
                                    @elseif($currentQuestionType === 'True or False')
                                        <div class="mt-2">
                                            <label class="flex items-center space-x-2 mb-2">
                                                <input type="radio" wire:model.defer="participantAnswer" value="True" class="mr-2" @if($answerSubmitted) disabled @endif />
                                                <span>True</span>
                                            </label>
                                            <label class="flex items-center space-x-2">
                                                <input type="radio" wire:model.defer="participantAnswer" value="False" class="mr-2" @if($answerSubmitted) disabled @endif />
                                                <span>False</span>
                                            </label>
                                        </div>
                                    @elseif($currentQuestionType === 'Multiple Choice')
                                        <div class="mt-2">
                                            @foreach($options as $option)
                                                <label class="flex items-center space-x-2 mb-2" wire:key="option-{{ $option }}">
                                                    <input type="radio" wire:model.defer="participantAnswer" value="{{ $option }}" class="mr-2" @if($answerSubmitted) disabled @endif />
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($quizStarted && !is_null($currentQuestion) && !$answerSubmitted)
                                        <button wire:click="submitAnswer"
                                                wire:loading.attr="disabled"
                                                id="submitAnswer"
                                                class="mt-4 bg-blue-500 text-white px-4 py-2 rounded disabled:opacity-50">
                                            Submit Answer
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-red-500">You are not authorized to view this content.</p>
                        @endif
                    @else
                    <!-- Admin View -->
                        <script>
                            window.isAdmin = true; // Explicitly set to true for admin view
                            window.roomId = @json($room->id);
                            window.quizStarted = @json($quizStarted);
                        </script>

                        <h3 class="text-lg font-semibold mb-4">Room Information</h3>
                        <div class="my-4">
                            <p><strong>Room Name:</strong> {{ $room->room_name }}</p>
                            <!-- <p><strong>Time Spent:</strong> {{ $room->time_spent ?? 'N/A' }}</p> -->
                            @if($room->quiz)
                                <p><strong>Quiz Name:</strong> {{ $room->quiz->title }}</p>
                            @else
                                <p class="text-red-500"><strong>Quiz:</strong> Quiz data not available</p>
                            @endif

                            <!-- Display Active/Inactive Status Badge -->
                            <div class="status-container mt-2">
                                <span class="status-badge {{ $room->is_active ? 'active' : 'inactive' }}">
                                    {{ $room->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <!-- Participants Section -->
                        <div class="my-4">
                            <h3 class="text-lg font-semibold mb-2">Participants</h3>
                            @if($room->participantsRoom->isNotEmpty())
                                <ul class="space-y-2">
                                    @foreach($room->participantsRoom as $participantsRoom)
                                        <li class="inline-block px-4 py-2 rounded-md font-semibold text-left {{ $participantsRoom->is_at_room ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                            {{ $participantsRoom->participant->name ?? 'Participant Name Not Available' }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500">No participants available for this room.</p>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>




                        <!-- Leaderboard Section -->

                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6 text-gray-900">
                                    <h6 class="text-xl font-bold">Leaderboard</h6>

                                    <table id="leaderboardContainer" class="table mt-4 w-full table-view">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Rank</th>
                                                <th class="text-left">Username</th>
                                                <th class="text-left">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            @if (isset($leaderboard) && count($leaderboard) > 0)
                                                @foreach ($leaderboard as $index => $entry)
                                                    <tr class="{{ $loop->iteration % 2 == 0 ? 'bg-gray-100' : '' }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $entry['name'] }}</td>
                                                        <td>{{ $entry['score'] }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="3" class="text-center text-gray-500">Waiting for Submissions</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Participants' Answers Section -->
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6 text-gray-900">
                                    <h3 class="text-lg font-semibold mb-2">Participants' Answers for Current Question</h3>
                                    <div class="p-4 border rounded bg-gray-100">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Answer</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                                </tr>
                                            </thead>
                                            <tbody id="answersContainer" class="bg-white divide-y divide-gray-200">
                                                @foreach($submittedAnswers as $answer)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $answer->participant->name ?? 'Unknown' }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $answer->sub_answer }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <span class="{{ $answer->correct ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ $answer->correct ? 'Correct' : 'Incorrect' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Controls -->
                        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
                            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                                <div class="p-6 text-gray-900">
                                @if ($isAdmin)
                                    <h3 class="text-lg font-semibold mb-2">Admin Control Panel</h3>

                                    <div class="my-4">
                                        <h4 class="text-md font-semibold">Current Question:</h4>
                                        <p>{{ $currentQuestion->text ?? 'No question loaded.' }}</p>
                                        <div id="countdownDisplay" class="hidden text-lg font-semibold mb-4 text-red-500"></div>
                                    </div>

                                    <div class="flex space-x-4">
                                        <div class="buttons">
                                            @if($room->is_active) {{-- Check if the quiz is active --}}
                                                @if(!$quizStarted) {{-- Check if the quiz has not started --}}
                                                    <button
                                                        wire:click="showStartQuizModal"
                                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                                        @if(!$hasParticipants) disabled @endif
                                                    >
                                                        Start Quiz
                                                    </button>
                                                @else {{-- If the quiz has started --}}
                                                    <button wire:click="nextQuestion" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">Next Question</button>
                                                @endif
                                            @else
                                                <!-- "Download Results" button, always visible -->
                                                <a href="{{ route('quiz.results.download', $room->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Download Results</a>
                                            @endif

                                            <!-- Tooltip or message when button is disabled -->
                                            @if(!$hasParticipants)
                                                <p class="text-red-500 mt-2">No participants available to start the quiz.</p>
                                            @endif

                                            <!-- Start Quiz Confirmation Modal -->
                                            @if($showQuizModal)
                                                <div class="fixed inset-0 flex items-center justify-center z-50">
                                                    <!-- Background overlay -->
                                                    <div class="bg-gray-900 opacity-50 fixed inset-0" wire:click="hideStartQuizModal"></div>

                                                    <!-- Modal content -->
                                                    <div class="bg-white rounded-lg shadow-lg overflow-hidden w-1/3 z-10">
                                                        <div class="px-6 py-4">
                                                            <h5 class="text-lg font-semibold">Start Quiz Confirmation</h5>
                                                            <p class="mt-4">Are you sure you want to start the quiz? Make sure all participants are ready.</p>
                                                        </div>
                                                        <div class="px-6 py-4 flex justify-end">
                                                            <button wire:click="hideStartQuizModal" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">Cancel</button>
                                                            <button wire:click="confirmStartQuiz" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Start Quiz</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                    @if (session()->has('message'))
                                        <div class="mt-4 text-green-500 font-semibold">
                                            {{ session('message') }}
                                        </div>
                                    @endif
                                    @if (session()->has('error'))
                                        <div class="mt-4 text-red-500 font-semibold">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                        <p class="mt-2">Total Questions Collected: {{ $questionCount }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-container {
display: flex;
justify-content: start; /* Aligns the badge with the text */
margin-top: 0.5rem; /* Adjusts spacing between items */
}

.status-badge {
padding: 0.3em 1em;
font-size: 0.875rem;
font-weight: bold;
border-radius: 20px;
color: #fff;
display: inline-block;
text-align: center;
width: fit-content; /* Keeps the badge width minimal */
}

.status-badge.active {
background-color: #4CAF50; /* Green for Active */
}

.status-badge.inactive {
background-color: #FF6347; /* Red for Inactive */
}
</style>
