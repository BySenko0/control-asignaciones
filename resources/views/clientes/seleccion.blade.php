<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-5">
        <h1 class="text-xl font-semibold text-gray-800">Selección de solicitud de clientes</h1>

        <form method="GET" action="{{ route('clientes.seleccion') }}">
            <div class="relative">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                       placeholder="Buscar por RFC, nombre, empresa o correo…"
                       class="w-full rounded-xl border border-gray-300 bg-white pl-11 pr-4 py-2.5
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                
            </div>
        </form>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="rounded-t-2xl bg-[#1f262b] px-4 py-2 text-center text-sm font-medium text-gray-100">
                Seleccionar cliente
            </div>

            <div class="p-4 space-y-3">
                @forelse ($clientes as $c)
                    <div class="flex items-center gap-3 rounded-xl bg-gray-100 px-3 py-2.5">
                        <img src="{{ $c->imagen ? asset($c->imagen) : asset('img/no-image.png') }}"
                             alt="logo" class="h-8 w-8 rounded-full object-cover"
                             onerror="this.src='{{ asset('img/no-image.png') }}';" />

                        <div class="flex-1 min-w-0">
                            <div class="truncate font-medium text-gray-900">{{ $c->nombre_cliente }}</div>
                            <div class="truncate text-xs text-gray-500">{{ $c->nombre_empresa }}</div>
                            <div class="truncate text-xs text-gray-400">{{ $c->correo_empresa }}</div>
                        </div>

                        <a href="#" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-800 hover:bg-gray-200">
                            Ir
                        </a>
                    </div>
                @empty
                    <div class="rounded-xl bg-white p-6 text-center text-gray-500">
                        No hay registros en clientes_asignaciones.
                    </div>
                @endforelse
            </div>

            <div class="px-4 pb-4">
                {{ $clientes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
