<div class="flex">
    <!-- Sidebar: Participants List -->
    <aside class="w-1/4 p-4 bg-gray-100 h-screen sticky top-0 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">Participants</h2>

        <ul class="mt-4 space-y-2">
            @foreach ($participants as $index => $participant)
                <li>
                    <a href="#participant-{{ $index }}"
                       class="block p-2 bg-gray-200 hover:bg-gray-300 rounded transition">
                        Participant #{{ $index + 1 }}: {{ $participant['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Buttons Section -->
        <div class="mt-6 space-y-2">
            <button
                wire:click.prevent="addParticipant"
                class="w-full bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Add Another Participant
            </button>
            <button
                wire:click.prevent="save"
                class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Save Participants
            </button>
        </div>
    </aside>

    <!-- Main Content: Participants Form -->
    <div class="w-3/4 p-6">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $editing ? 'Edit Participants for ' . $quiz->title : 'Create Participants for ' . $quiz->title }}
            </h2>
        </x-slot>

        <form wire:submit.prevent="save">
            @foreach ($participants as $index => $participant)
                <div id="participant-{{ $index }}" class="border p-4 mb-6 rounded-lg shadow-md">
                    <h3 class="font-semibold text-lg mb-2">Participant #{{ $index + 1 }}</h3>

                    <!-- Participant Name -->
                    <div class="mb-4">
                        <x-input-label for="participants.{{ $index }}.name" value="Participant Name" />
                        <x-text-input
                            wire:model="participants.{{ $index }}.name"
                            id="participants.{{ $index }}.name"
                            class="block mt-1 w-full"
                            type="text"
                            required
                        />
                        <x-input-error :messages="$errors->get('participants.' . $index . '.name')" class="mt-2" />
                    </div>

                    <!-- Participant Code -->
                    <div class="mb-4">
                        <x-input-label for="participants.{{ $index }}.code" value="Participant Code" />
                        <x-text-input
                            wire:model="participants.{{ $index }}.code"
                            id="participants.{{ $index }}.code"
                            class="block mt-1 w-full"
                            type="text"
                            required
                        />
                        <x-input-error :messages="$errors->get('participants.' . $index . '.code')" class="mt-2" />
                        <button type="button" wire:click="generateCode({{ $index }})" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Generate Code
                        </button>
                    </div>

                    <!-- Remove Participant Button -->
                    <button
                        wire:click.prevent="removeParticipant({{ $index }})"
                        class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                        Remove Participant
                    </button>
                </div>
            @endforeach
        </form>
    </div>
</div>
