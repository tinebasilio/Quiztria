<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Participants List
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session()->has('success'))
                        <div class="mb-4 text-green-600">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table-auto w-full border-collapse border border-gray-300 text-center"> <!-- Added text-center to center the table content -->
                        <thead>
                            <tr>
                                <th class="border border-gray-300 px-4 py-2">ID</th>
                                <th class="border border-gray-300 px-4 py-2">Name</th>
                                <th class="border border-gray-300 px-4 py-2">Code</th>
                                <th class="border border-gray-300 px-4 py-2">Score</th>
                                <th class="border border-gray-300 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($participants as $participant)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->id }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->name }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->code }}</td>
                                    <td class="border border-gray-300 px-4 py-2">{{ $participant->score }}</td>
                                    <td class="border border-gray-300 px-4 py-2">
                                        <a href="{{ route('participant.edit', $participant->id) }}" class="text-blue-500 hover:underline">Edit</a>
                                        <button wire:click="delete({{ $participant->id }})"
                                                class="ml-4 text-red-500 hover:underline"
                                                onclick="return confirm('Are you sure you want to delete this participant?')">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="border border-gray-300 px-4 py-2 text-center">
                                        No participants found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
