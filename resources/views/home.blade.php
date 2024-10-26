<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold">Select how you would like to proceed:</h6>

                    <div class="d-flex justify-content-around mt-4">
                        <a href="{{ route('login') }}"
                           class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-blue-600">
                            Admin/User Login
                        </a>
                        <a href="{{ route('participant.login') }}"
                           class="inline-block bg-green-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-green-600">
                            Participant Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold">Public quizzes</h6>

                    @forelse($public_quizzes as $quiz)
                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                            <div
                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0">
                                <div class="flex-auto p-4">
                                    <a href="{{ route('quiz.show', $quiz->slug) }}"
                                       class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-blue-600">
                                        {{ $quiz->title }}
                                    </a>
                                    <p class="text-sm mt-2">Questions: <span>{{ $quiz->questions_count }}</span></p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="mt-2">No public quizzes found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold">Quizzes for Registered Users</h6>

                    @forelse($registered_only_quizzes as $quiz)
                        <div class="px-4 py-2 w-full lg:w-6/12 xl:w-3/12">
                            <div
                                class="flex relative flex-col mb-6 min-w-0 break-words bg-white rounded shadow-lg xl:mb-0">
                                <div class="flex-auto p-4">
                                    <a href="{{ route('quiz.show', $quiz->slug) }}"
                                       class="inline-block bg-green-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-green-600">
                                        {{ $quiz->title }}
                                    </a>
                                    <p class="text-sm mt-2">Questions: <span>{{ $quiz->questions_count }}</span></p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="mt-2">No quizzes for registered users found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
