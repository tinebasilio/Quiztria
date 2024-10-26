<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Welcome, {{ $participant->name }}</h2>

    <!-- Room Information -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold">Room:</h3>
        <p class="text-gray-700">
            {{ $room ? $room->room_name : 'No room assigned' }}
        </p>
    </div>

    <!-- Quiz Information -->
    <div class="mb-4">
        <h3 class="text-lg font-semibold">Quiz:</h3>
        <p class="text-gray-700">
            {{ $quiz ? $quiz->title : 'No quiz assigned' }}
        </p>
    </div>
</div>