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
                    <!-- Room Details -->
                    <h3 class="text-lg font-semibold mb-4">Room Information</h3>
                    <div class="my-4">
                        <p><strong>Room Name:</strong> {{ $room->room_name }}</p>
                        <p><strong>Time Spent:</strong> {{ $room->time_spent ?? 'N/A' }} minutes</p>
                        @if($room->quiz)
                            <p><strong>Quiz:</strong> {{ $room->quiz->title }}</p>
                        @else
                            <p class="text-red-500"><strong>Quiz:</strong> Quiz data not available</p>
                        @endif
                    </div>

                    <!-- Participants Section -->
                    <div class="my-4">
                        <h3 class="text-lg font-semibold mb-2">Participants</h3>

                        @if($room->participantsRoom->isNotEmpty())
                            <ul class="list-disc list-inside">
                                @foreach($room->participantsRoom as $participantsRoom)
                                    <li>
                                        {{ $participantsRoom->participant->name ?? 'Participant Name Not Available' }}
                                        ({{ $participantsRoom->Is_at_room ? 'At Room' : 'Not at Room' }})
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">No participants available for this room.</p>
                        @endif
                    </div>

                    <!-- Edit and Delete Buttons -->
                    <div class="flex space-x-4">
                        <a href="{{ route('room.edit', $room->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Room
                        </a>

                        <button wire:click="deleteRoom" class="bg-red-500 text-white px-4 py-2 rounded">
                            Delete Room
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
