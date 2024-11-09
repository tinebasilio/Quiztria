<button
    {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full flex justify-center px-6 py-3 bg-purple-700 hover:bg-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wider transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>