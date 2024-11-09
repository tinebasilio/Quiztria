<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Quizzes
        </h2>
    </x-slot>

    <x-slot name="title">
        Quizzes
    </x-slot>

    <div class="py-12">
        <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex justify-between">
                        <a href="{{ route('quiz.create') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-purple-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-purple-600">
                            Create Quiz
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
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Title</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Description</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Questions</span>
                                    </th>
                                    @if($filter === 'deleted')
                                        <th class="bg-gray-50 px-6 py-3 text-left">
                                            <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Date Deleted</span>
                                        </th>
                                    @endif
                                    <th class="w-40 bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Action</span>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 divide-solid">
                                @forelse($quizzes as $quiz)
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $quiz->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $quiz->title }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $quiz->description }}
                                        </td>
                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            {{ $quiz->questions_count }}
                                        </td>
                                        @if($filter === 'deleted' && $quiz->deleted_at)
                                            <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                                {{ $quiz->deleted_at->format('Y-m-d H:i:s') }}
                                            </td>
                                        @endif

                                        <td class="px-6 py-4 text-sm leading-5 text-gray-900 whitespace-no-wrap">
                                            @if($filter === 'active' && is_null($quiz->deleted_at))
                                                <i class="fas fa-edit text-gray-800 hover:text-gray-600 cursor-pointer mr-4"
                                                onclick="window.location.href='{{ route('quiz.edit', $quiz) }}'"></i>

                                                <i class="fas fa-trash-alt text-red-500 hover:text-red-700 cursor-pointer"
                                                wire:click="confirmDelete({{ $quiz->id }})"></i>

                                            @elseif($filter === 'deleted')
                                                <i class="fas fa-undo text-green-500 hover:text-green-700 cursor-pointer"
                                                wire:click="confirmRestore({{ $quiz->id }})"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-6 py-4 text-center leading-5 text-gray-900 whitespace-no-wrap">
                                            No quizzes were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $quizzes->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
                <p>Are you sure you want to delete this quiz? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="delete" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete</button>
                    <button wire:click="$set('showDeleteModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Restore Confirmation Modal -->
    @if($showRestoreModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                <h2 class="text-xl font-semibold mb-4">Confirm Restore</h2>
                <p>Are you sure you want to restore this quiz?</p>
                <div class="mt-6 flex justify-end space-x-2">
                    <button wire:click="restore" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Restore</button>
                    <button wire:click="$set('showRestoreModal', false)" class="bg-gray-300 text-black px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
                </div>
            </div>
        </div>
    @endif
</div>
