<x-guest-layout>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-10">
        <div class="w-full max-w-2xl">
            {{-- Tarjeta --}}
            <div class="mx-auto rounded-2xl border border-gray-300 bg-white shadow-sm p-8 sm:p-10">
                {{-- Logo --}}
                <div class="flex flex-col items-center gap-2">
                    <img src="{{ asset('img/virtuality-logo.png') }}" alt="Virtuality" class="h-16">
                    <h1 class="text-3xl font-semibold tracking-tight">Login</h1>
                </div>

                {{-- Estado de sesión / errores globales --}}
                <x-auth-session-status class="mt-6" :status="session('status')" />
                @if ($errors->any())
                    <div class="mt-4 rounded-lg bg-red-50 text-red-700 p-3 text-sm">
                        {{ __('Revisa los campos e inténtalo de nuevo.') }}
                    </div>
                @endif

                {{-- Formulario --}}
                <form class="mt-8" method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Usuario (email) --}}
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        {{ __('Usuario') }}
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="correo@dominio.com"
                        class="mt-1 w-full rounded-xl border-gray-300 bg-gray-100/80 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />

                    {{-- Contraseña --}}
                    <label for="password" class="mt-6 block text-sm font-medium text-gray-700">
                        {{ __('Contraseña') }}
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="mt-1 w-full rounded-xl border-gray-300 bg-gray-100/80 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />

                    {{-- Recordarme --}}
                    <label for="remember_me" class="mt-4 inline-flex items-center gap-2 text-sm text-gray-600">
                        <input id="remember_me" name="remember" type="checkbox"
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>{{ __('Recordarme') }}</span>
                    </label>

                    {{-- Botones --}}
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button type="submit"
                                class="inline-flex justify-center rounded-xl bg-gray-800 py-2.5 px-4 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400">
                            {{ __('Iniciar sesión') }}
                        </button>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="inline-flex justify-center rounded-xl border border-gray-300 bg-gray-100 py-2.5 px-4 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                {{ __('Recuperar') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
