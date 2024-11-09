<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-full sm:max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo Section -->
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>
            </div>

            <!-- Hamburger Button for Mobile -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    @if(Auth::guard('participant')->check())
                        <x-nav-link :href="route('room.view', ['roomId' => Auth::user()->room_id ?? 1])" :active="request()->routeIs('room.view')">
                            My Room
                        </x-nav-link>
                    @elseif(Auth::user()->isAdmin())
                        <!-- Manage Dropdown for Admin -->
                        <x-dropdown align="right" width="48" class="mr-4">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                    <span>Manage</span>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a 1 0 111.414 1.414l-4 4a 1 0 01-1.414 0l-4-4a 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('admins')">Admins</x-dropdown-link>
                                <x-dropdown-link :href="route('quizzes')">Quizzes</x-dropdown-link>
                                <x-dropdown-link :href="route('rooms')">Rooms</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif

                    <!-- User Profile and Logout Dropdown -->
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a 1 0 111.414 1.414l-4 4a 1 0 01-1.414 0l-4-4a 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">My Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <x-nav-link :href="route('login')">Log In</x-nav-link>
                @endauth
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        @auth
            @if(Auth::guard('participant')->check())
                <x-nav-link :href="route('room.view', ['roomId' => Auth::user()->room_id ?? 1])" :active="request()->routeIs('room.view')">
                    My Room
                </x-nav-link>
            @elseif(Auth::user()->isAdmin())
                <div class="border-t border-gray-200">
                    <h4 class="px-4 py-2 font-semibold">Manage</h4>
                    <x-dropdown-link :href="route('admins')">Admins</x-dropdown-link>
                    <x-dropdown-link :href="route('quizzes')">Quizzes</x-dropdown-link>
                    <x-dropdown-link :href="route('rooms')">Rooms</x-dropdown-link>
                </div>
            @endif

            <!-- User Profile and Logout Link for Mobile -->
            <div class="border-t border-gray-200">
            <h4 class="px-4 py-2 font-semibold">{{ Auth::user()->name }}</h4>
                <x-dropdown-link :href="route('profile.edit')">My Profile</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-dropdown-link>
                </form>
            </div>
        @else
            <x-nav-link :href="route('login')">Log In</x-nav-link>
        @endauth
    </div>
</nav>