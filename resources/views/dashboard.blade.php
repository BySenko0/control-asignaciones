{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                    <p class="text-sm text-gray-500">Monitorea el estado general y accede a los listados clave.</p>
                </div>
            </div>

            {{-- KPIs --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Pendientes --}}
                <a href="{{ route('ordenes.pendientes') }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">Pendientes</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['pendientes'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Abiertas</span>
                    </div>
                </a>

                {{-- En proceso --}}
                <a href="{{ route('ordenes.en_proceso') }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">En proceso</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['en_proceso'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Trabajando</span>
                    </div>
                </a>

                {{-- Finalizadas --}}
                <a href="{{ route('ordenes.resueltas') }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">Finalizadas</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['finalizadas'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Cerradas</span>
                    </div>
                </a>

                {{-- Vencidas --}}
                <a href="{{ route('ordenes.vencidas') }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">Vencidas</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['vencidas'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Fuera de plazo</span>
                    </div>
                </a>

                {{-- Vencen hoy --}}
                <a href="{{ route('ordenes.vencidas') }}?hoy=1"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">Vencen hoy</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['vencen_hoy'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Prioridad</span>
                    </div>
                </a>

                {{-- No asignadas --}}
                <a href="{{ route('ordenes.pendientes') }}?sin_asignar=1"
                   class="block rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow">
                    <div class="text-xs uppercase text-gray-500">No asignadas</div>
                    <div class="mt-1 flex items-end justify-between">
                        <div class="text-3xl font-semibold text-gray-900">{{ $kpis['no_asignadas'] }}</div>
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">Por tomar</span>
                    </div>
                </a>
            </div>

            {{-- Aquí puedes agregar las listas/kanban/calendario después --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    ¡Bienvenido! Selecciona un KPI para ir al listado correspondiente.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
