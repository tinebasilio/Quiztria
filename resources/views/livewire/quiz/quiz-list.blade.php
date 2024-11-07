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
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4 flex justify-between">
                        <a href="{{ route('quiz.create') }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
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
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Slug</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Description</span>
                                    </th>
                                    <th class="bg-gray-50 px-6 py-3 text-left">
                                        <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Questions count</span>
                                    </th>
                                    @if($filter === 'deleted')
                                        <th class="bg-gray-50 px-6 py-3 text-left">
                                            <span class="text-xs font-medium uppercase leading-4 tracking-wider text-gray-500">Deleted Date</span>
                                        </th>
                                    @endif
                                    <th class="w-40 bg-gray-50 px-6 py-3 text-left"></th>
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
                                            {{ $quiz->slug }}
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
                                        <td>
                                            @if($filter === 'active' && is_null($quiz->deleted_at))
                                                <a href="{{ route('quiz.edit', $quiz) }}"
                                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                                                    Edit
                                                </a>
                                                <button wire:click="confirmDelete({{ $quiz->id }})"
                                                    class="rounded-md border border-transparent bg-red-200 px-4 py-2 text-xs uppercase text-red-500 hover:bg-red-300 hover:text-red-700">
                                                    Delete
                                                </button>
                                            @elseif($filter === 'deleted')
                                                <button wire:click="confirmRestore({{ $quiz->id }})"
                                                    class="rounded-md border border-transparent bg-green-200 px-4 py-2 text-xs uppercase text-green-500 hover:bg-green-300 hover:text-green-700">
                                                    Restore
                                                </button>
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
