<x-guest-layout>
    <div class="mb-4 text-center">
        <h1 class="text-xl font-semibold text-gray-800">Admin Login</h1>
        <p class="mt-1 text-sm text-gray-600">Gunakan akun yang memiliki role Admin.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-between">
            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">Login pengguna</a>
            <x-primary-button>{{ __('Log in as Admin') }}</x-primary-button>
        </div>
    </form>
    @if (config('app.demo_login'))
        <div class="mt-6 border-t pt-4">
            <p class="mb-2 text-sm font-medium text-gray-700">Login instan akun demo</p>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('demo.login', 'admin') }}">@csrf
                    <button type="submit" class="rounded-md bg-red-600 px-3 py-2 text-xs font-semibold uppercase text-white hover:bg-red-700">Login sebagai Admin</button>
                </form>
                <form method="POST" action="{{ route('demo.login', 'staf') }}">@csrf
                    <button type="submit" class="rounded-md bg-gray-700 px-3 py-2 text-xs font-semibold uppercase text-white hover:bg-gray-800">Login sebagai Staf</button>
                </form>
            </div>
        </div>
    @endif
</x-guest-layout>
