<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('participant.login') }}">
        @csrf

        <!-- Participant Code -->
        <div>
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required
                autofocus placeholder="Enter a join code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="w-full">
                {{ __('Join') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
