<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6">Admin Dashboard</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-purple-500 text-white rounded-lg p-6 shadow-lg flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Total Rooms</h3>
                        <p class="text-2xl">{{ $totalRooms }}</p>
                    </div>
                    <a href="{{ route('rooms') }}" class="mt-4 flex items-center justify-center text-white hover:text-gray-300">
                        <i class="fas fa-door-open text-3xl"></i>
                        <span class="ml-2">Rooms</span>
                    </a>
                </div>

                <div class="bg-yellow-500 text-white rounded-lg p-6 shadow-lg flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-semibold">Total Quizzes</h3>
                        <p class="text-2xl">{{ $totalQuizzes }}</p>
                    </div>
                    <a href="{{ route('quizzes') }}" class="mt-4 flex items-center justify-center text-white hover:text-gray-300">
                        <i class="fas fa-question-circle text-3xl"></i>
                        <span class="ml-2">Quizzes</span>
                    </a>
                </div>
            </div>

</div>

        </div>
        
    </div>
</x-app-layout>
