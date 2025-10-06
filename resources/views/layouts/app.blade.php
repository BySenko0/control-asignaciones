<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name','Control de Activos') }}</title>

  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- (opcional) estilos por-página --}}
  @stack('styles')

  {{-- Livewire (si lo usarás) --}}
  @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100">
  @include('layouts.navigation')

  <main class="px-4 sm:px-6 lg:px-8 py-6">
    {{ $slot }}
  </main>

  {{-- zona de modales que usa Breeze/Jetstream, deja el stack disponible --}}
  @stack('modals')

  {{-- scripts globales compilados por Vite ya están arriba en @vite --}}

  {{-- scripts por-página (usa @push en las vistas hijas) --}}
  @stack('scripts')

  {{-- alternativa clásica (una sola sección de scripts) --}}
  @yield('scripts')

  {{-- Livewire al final del body --}}
  
</body>
</html>
