<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ !empty($roomId) ? 'Edit Room' : 'Create Room' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    <form wire:submit.prevent="saveRoom">
                        <div class="flex flex-col lg:flex-row lg:space-x-4">
                            <!-- Room Name Input -->
                            <div class="mb-4 flex-1">
                                <x-input-label for="room_name" value="Room Name" />
                                <x-text-input wire:model.defer="room_name" id="room_name" class="block mt-1 w-full"
                                    placeholder="Enter room name" required />
                                <x-input-error :messages="$errors->get('room_name')" class="mt-2" />
                            </div>

                            <!-- Quiz Dropdown -->
                            <div class="mb-4 flex-1">
                                <x-input-label for="quiz_id" value="Select Quiz" />
                                <select wire:model.defer="quiz_id" id="quiz_id"
                                    class="block mt-1 w-full border-gray-300 rounded" required>
                                    <option value="">Select Quiz</option>
                                    @foreach($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}">{{ $quiz->title }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('quiz_id')" class="mt-2" />
                            </div>
                        </div>
                    </form>

                    <!-- Save Button -->
                    <div class="space-y-2">
                        <x-primary-button type="submit" class="mt-4 w-full sm:w-auto uppercase">
                            {{ !empty($roomId) ? 'Update Room' : 'Create Room' }}
                        </x-primary-button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>