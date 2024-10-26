<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Difficulties for {{ $quiz->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form wire:submit.prevent="save">
                        @foreach($difficulties as $index => $difficulty)
                            <div class="border p-4 mb-6 rounded-lg shadow-md">
                                <h3 class="font-semibold text-lg mb-4">{{ ucfirst($difficulty['diff_name']) }} Difficulty</h3>

                                <!-- Name Input (fixed) -->
                                <x-input-label for="difficulties.{{ $index }}.diff_name" value="Name" />
                                <x-text-input
                                    wire:model.defer="difficulties.{{ $index }}.diff_name"
                                    id="difficulties.{{ $index }}.diff_name"
                                    class="block mt-1 w-full"
                                    type="text"
                                    required
                                    disabled
                                />
                                <x-input-error :messages="$errors->get('difficulties.' . $index . '.diff_name')" class="mt-2" />

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
                            </div>
                        @endforeach

                        <!-- Save Button -->
                        <div class="mt-6 text-right">
                            <x-primary-button>
                                Save Difficulties
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
