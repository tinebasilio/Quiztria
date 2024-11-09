<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Rooms
        </h2>
    </x-slot>

    <x-slot name="title">
        Rooms
    </x-slot>

    <div class="py-12">
        <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex justify-between">
                        <a href="{{ route('room.create') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-purple-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-purple-600">
                            Create Room
                        </a>

                        <div>
                            <label for="filter" class="mr-2">Filter:</label>
                            <select wire:model="filter" id="filter" class="border-gray-300 rounded">
                                <option value="active">Active</option>
                                <option value="deleted">Deleted</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4 min-w-full overflow-hidden overflow-x-auto align-middle sm:rounded-md">
                        <table class="min-w-full border divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="w-16 bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">ID</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Room Name</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Quiz Name</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Status</span>
                                    </th>
                        
                                    @if($filter === 'deleted')
                                        <th class="bg-gray-50 px-6 py-3 text-left">
                                            <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Deleted Date</span>
                                        </th>
                                    @endif
                                    <th class="w-40 bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Action</span>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($rooms as $room)
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $room->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $room->room_name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $room->quiz->title ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                                                {{ $room->is_active ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                                {{ $room->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        @if($filter === 'deleted' && $room->deleted_at)
                                            <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                {{ $room->deleted_at->format('Y-m-d H:i:s') }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            @if(is_null($room->deleted_at))
                                                <i class="fas fa-eye text-gray-800 hover:text-gray-600 cursor-pointer mr-4"
                                                onclick="window.location.href='{{ route('room.view', $room->id) }}'"></i>

                                                <i class="fas fa-trash-alt text-red-500 hover:text-red-700 cursor-pointer"
                                                wire:click="confirmDelete({{ $room->id }})"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center leading-5 text-gray-900 whitespace-no-wrap">
                                            No rooms were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $rooms->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this room? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="deleteRoom" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete</button>
                    <button wire:click="$set('showDeleteModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
