<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Selección de solicitud de clientes</h1>
                <p class="text-sm text-gray-500">Elige un cliente para gestionar sus equipos y solicitudes.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('clientes.seleccion') }}">
            <div class="relative">
                <input
                    type="text"
                    name="q"
                    value="{{ $q ?? '' }}"
                    placeholder="Buscar por RFC, nombre, empresa, correo o teléfono…"
                    class="w-full rounded-xl border border-gray-300 bg-white pl-4 pr-4 py-2.5
                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
        </form>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="rounded-t-2xl bg-[#1f262b] px-4 py-2 text-center text-sm font-medium text-gray-100">
                Seleccionar cliente
            </div>

            {{-- Tabla desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                            <th class="px-4 py-3 text-left font-semibold">Identificador</th>
                            <th class="px-4 py-3 text-left font-semibold">Contacto</th>
                            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($clientes as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $c->imagen ? asset($c->imagen) : asset('img/sin-imagen.png') }}"
                                            alt="Logo de {{ $c->nombre_cliente }}"
                                            class="h-9 w-9 rounded-full object-cover bg-gray-200"
                                            onerror="this.onerror=null;this.src='{{ asset('img/sin-imagen.png') }}';"
                                        />
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $c->nombre_cliente }}</div>
                                            <div class="text-xs text-gray-500">{{ $c->nombre_empresa }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 uppercase">{{ $c->rfc ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    <div class="text-sm">{{ $c->correo_empresa }}</div>
                                    <div class="text-xs text-gray-500">{{ $c->telefono }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('clientes.equipos-solicitudes', $c->id) }}"
                                           class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700">
                                            Seleccionar
                                        </a>
                                        <a href="{{ route('clientes.equipos-solicitudes', $c->id) }}"
                                           class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-800 hover:bg-gray-100">
                                            Ver equipos/solicitudes
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">No hay registros en clientes_asignaciones.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cards móvil --}}
            <div class="md:hidden p-4 space-y-3">
                @forelse ($clientes as $c)
                    <div class="rounded-xl bg-gray-50 px-3 py-3 ring-1 ring-gray-200">
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ $c->imagen ? asset($c->imagen) : asset('img/sin-imagen.png') }}"
                                alt="Logo de {{ $c->nombre_cliente }}"
                                class="h-9 w-9 rounded-full object-cover bg-gray-200"
                                onerror="this.onerror=null;this.src='{{ asset('img/sin-imagen.png') }}';"
                            />

                            <div class="flex-1 min-w-0">
                                <div class="truncate font-medium text-gray-900">{{ $c->nombre_cliente }}</div>
                                <div class="truncate text-xs text-gray-500">{{ $c->nombre_empresa }}</div>
                                <div class="truncate text-xs text-gray-400">{{ $c->correo_empresa }}</div>
                                <div class="truncate text-xs text-gray-400">{{ $c->telefono }}</div>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('clientes.equipos-solicitudes', $c->id) }}"
                               class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700">
                                Seleccionar
                            </a>
                            <a href="{{ route('clientes.equipos-solicitudes', $c->id) }}"
                               class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-800 hover:bg-gray-100">
                                Ver equipos/solicitudes
                            </a>
                        </div>
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
