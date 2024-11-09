<div class="flex">
    <!-- Sidebar: Participants List -->
    <aside class="w-1/4 p-4 bg-gray-100 h-screen sticky top-0 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">Participants</h2>

        <ul class="mt-4 space-y-2">
            @foreach ($participantsList as $participant)
                <li>
                    <a href="#" wire:click.prevent="selectParticipant({{ $participant['id'] }})"
                       class="block p-2 bg-gray-200 hover:bg-gray-300 rounded transition">
                        {{ $participant['name'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Action Buttons -->
        <div class="mt-6 space-y-2">
            <button wire:click.prevent="confirmSave" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Save Participants
            </button>
        </div>
    </aside>

    <!-- Main Content: Add/Edit Participant Form -->
    <div class="w-3/4 p-6 bg-white rounded-lg shadow-md">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
            {{ $editing ? 'Edit Participant' : 'Add Participant' }}
        </h2>

        <form wire:submit.prevent="addParticipant">
            <!-- Participant Name -->
            <div class="mb-4">
                <x-input-label for="participant.name" value="Participant Name" />
                <x-text-input
                    wire:model="participant.name"
                    id="participant.name"
                    class="block mt-1 w-full"
                    type="text"
                    required
                />
                <x-input-error :messages="$errors->get('participant.name')" class="mt-2" />
            </div>

            <!-- Participant Code -->
            <div class="mb-4">
                <x-input-label for="participant.code" value="Participant Code" />
                <x-text-input
                    wire:model="participant.code"
                    id="participant.code"
                    class="block mt-1 w-full"
                    type="text"
                    readonly
                />
                <x-input-error :messages="$errors->get('participant.code')" class="mt-2" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Add Participant
            </button>

            @if($editing)
                <button wire:click.prevent="confirmRemoveParticipant({{ $selectedParticipantId }})" class="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Remove Participant
                </button>
            @endif
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to remove this participant?</p>

                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="removeParticipant" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Confirm</button>
                    <button wire:click="$set('showDeleteModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Save Confirmation Modal -->
    @if($showSaveModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Confirm Save</h2>
            <p>Are you sure you want to save the participants? This will save the changes and redirect to the quiz edit page.</p>

            <div class="mt-6 flex justify-end space-x-2">
                <button wire:click="saveParticipantsAndRedirect" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Save & Redirect</button>
                <button wire:click="$set('showSaveModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
