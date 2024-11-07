<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Rounds for {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form wire:submit.prevent="confirmSave">
                        @foreach($difficulties as $index => $difficulty)
                            <div class="border p-4 mb-6 rounded-lg shadow-md">
                                <h3 class="font-semibold text-lg mb-4">{{ ucfirst($difficulty['diff_name']) }} Round</h3>

                                <!-- Hidden diff_name Input -->
                                <input
                                    wire:model.defer="difficulties.{{ $index }}.diff_name"
                                    type="hidden"
                                />

                                <!-- Points Input -->
                                <x-input-label for="difficulties.{{ $index }}.point" value="Points" />
                                <x-text-input
                                    wire:model.defer="difficulties.{{ $index }}.point"
                                    id="difficulties.{{ $index }}.point"
                                    class="block mt-1 w-full"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <x-input-error :messages="$errors->get('difficulties.' . $index . '.point')" class="mt-2" />

                                <!-- Timer Input -->
                                <x-input-label for="difficulties.{{ $index }}.timer" value="Timer (seconds)" />
                                <x-text-input
                                    wire:model.defer="difficulties.{{ $index }}.timer"
                                    id="difficulties.{{ $index }}.timer"
                                    class="block mt-1 w-full"
                                    type="number"
                                    min="0"
                                    required
                                />
                                <x-input-error :messages="$errors->get('difficulties.' . $index . '.timer')" class="mt-2" />
                            </div>
                        @endforeach

                        <!-- Save Button -->
                        <div class="mt-6 text-right">
                            <x-primary-button>
                                Save Rounds
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Confirmation Modal -->
    @if($showSaveModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Confirm Save</h2>
                <p>Are you sure you want to save the changes?</p>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="save" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Confirm</button>
                    <button wire:click="$set('showSaveModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
