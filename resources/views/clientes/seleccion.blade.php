<x-app-layout>
    @push('styles')
    <style>
        .card { background: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb; }
        .card-dark { background: #1f262b; color:#e5e7eb; }
        .btn-soft { border-radius: 0.75rem; padding: .35rem .8rem; }
    </style>
    @endpush

    <div class="space-y-4">
        {{-- Título en la barra superior lo manejas en el topbar. Si quieres aquí: --}}
        <h1 class="text-xl font-semibold text-gray-800">Selección de solicitud de clientes</h1>

        {{-- Barra de búsqueda --}}
        <form method="GET" action="{{ route('clientes.seleccion') }}">
            <input type="text" name="q" value="{{ $q ?? '' }}"
                   placeholder="Buscar por RFC, nombre o empresa…"
                   class="w-full rounded-xl border-gray-300 bg-white focus:ring-indigo-500 focus:border-indigo-500" />
        </form>

        {{-- Tarjeta contenedora --}}
        <div class="card p-4">
            <div class="card-dark rounded-xl px-4 py-2 text-center text-sm font-medium">
                Seleccionar cliente
            </div>

            <div class="mt-3 space-y-3">
                @forelse ($clientes as $c)
                    <div class="flex items-center gap-3 rounded-xl bg-gray-100 p-3">
                        {{-- imagen --}}
                        <img src="{{ asset($c->imagen ?: 'img/no-image.png') }}"
                             alt="logo" class="h-8 w-8 rounded-full object-cover">

                        <div class="flex-1">
                            <div class="font-medium text-gray-800">{{ $c->nombre_cliente }}</div>
                            <div class="text-xs text-gray-500">{{ $c->nombre_empresa }}</div>
                        </div>

                        {{-- botón IR (de momento sin ruta final) --}}
                        <a href="#"
                           class="btn-soft bg-white border border-gray-300 text-gray-800 hover:bg-gray-200">
                           Ir
                        </a>
                    </div>
                @empty
                    <div class="rounded-xl bg-white p-6 text-center text-gray-500">
                        Sin resultados.
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $clientes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
