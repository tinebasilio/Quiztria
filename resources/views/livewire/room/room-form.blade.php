<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ !empty($roomId) ? 'Edit Room' : 'Create Room' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Flash message -->
                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    <!-- Room Form -->
                    <form wire:submit.prevent="saveRoom">

                        <!-- Room Name -->
                        <div class="mb-4">
                            <x-input-label for="room_name" value="Room Name"/>
                            <x-text-input wire:model.defer="room_name"
                                          id="room_name"
                                          class="block mt-1 w-full"
                                          placeholder="Enter room name"
                                          required/>
                            <x-input-error :messages="$errors->get('room_name')" class="mt-2"/>
                        </div>

                        <!-- Quiz Dropdown -->
                        <div class="mb-4">
                            <x-input-label for="quiz_id" value="Select Quiz"/>
                            <select wire:model.defer="quiz_id"
                                    id="quiz_id"
                                    class="block mt-1 w-full border-gray-300 rounded"
                                    required>
                                <option value="">Select Quiz</option>
                                @foreach($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('quiz_id')" class="mt-2"/>
                        </div>

                        <!-- Time Spent -->
                        <div class="mb-4">
                            <x-input-label for="time_spent" value="Time Spent (Minutes)"/>
                            <x-text-input wire:model.defer="time_spent"
                                          id="time_spent"
                                          type="number"
                                          class="block mt-1 w-full"
                                          placeholder="Enter time spent"
                                          min="0"/>
                            <x-input-error :messages="$errors->get('time_spent')" class="mt-2"/>
                        </div>

                        <!-- Save Button -->
                        <div class="mt-6 space-y-2">
                            <button type="submit"
                                    class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                {{ !empty($roomId) ? 'Update Room' : 'Create Room' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
