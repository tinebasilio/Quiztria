<x-guest-layout>
    <div class="py-12">
        <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h6 class="text-xl font-bold">Front page</h6>

                    <div class="d-flex justify-content-around mt-4">
                        <a href="{{ route('login') }}"
                           class="inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-blue-600">
                            Admin/User Login
                        </a>
                        <a href="{{ route('participant.login') }}"
                           class="inline-block bg-green-500 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out hover:bg-green-600">
                            Participant Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
