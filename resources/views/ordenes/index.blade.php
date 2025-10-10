<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header + tabs --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800">Órdenes de servicio / {{ $titulo }}</h1>

            <div class="flex gap-2">
                <a href="{{ route('ordenes.pendientes') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm {{ $estado==='pendiente' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    Pendientes
                </a>
                <a href="{{ route('ordenes.en_proceso') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm {{ $estado==='en_proceso' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    En proceso
                </a>
                <a href="{{ route('ordenes.resueltas') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm {{ $estado==='finalizado' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    Resueltas
                </a>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" class="grid gap-3 sm:grid-cols-3 items-end">
            <div class="sm:col-span-2">
                <label class="text-sm text-gray-700">Buscar</label>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Serie, dispositivo, modelo, servicio…"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="text-sm text-gray-700">Cliente</label>
                <select name="cliente_id" class="mt-1 w-full rounded-xl border-gray-300">
                    <option value="">Todos</option>
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}" @selected($clienteId===$c->id)>{{ $c->nombre_cliente }}</option>
                    @endforeach
                </select>
            </div>

            @if($isAdmin)
                <div class="sm:col-span-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="solo_mias" value="1" @checked($soloMias)
                               class="rounded border-gray-300">
                        Solo mis asignadas
                    </label>
                </div>
            @endif

            <div class="sm:col-span-3 flex items-center gap-2">
                <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Aplicar</button>
                {{-- Limpiar filtros: vuelve a la URL actual sin query string --}}
                <a href="{{ url()->current() }}"
                   class="rounded-xl border border-gray-300 bg-white px-4 py-2 hover:bg-gray-50">Limpiar</a>
            </div>
        </form>

        {{-- Lista tipo “cards” --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="px-4 py-3 border-b bg-gray-900 text-white rounded-t-2xl">
                • {{ $titulo }}
            </div>

            @forelse($solicitudes as $s)
                <div class="px-4 py-3 border-b last:border-b-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="grid sm:grid-cols-2 gap-x-10 gap-y-1 text-sm text-gray-800">
                            <div>• <span class="text-gray-500">Dispositivo:</span> {{ $s->dispositivo }}</div>
                            <div>• <span class="text-gray-500">Empleado asignado:</span> {{ optional($s->asignado)->name ?? '—' }}</div>

                            <div>• <span class="text-gray-500">Cliente:</span> {{ optional($s->cliente)->nombre_cliente ?? '—' }}</div>
                            <div>• <span class="text-gray-500">ID equipo:</span> {{ $s->no_serie ?: '—' }}</div>

                            <div>• <span class="text-gray-500">Tipo de servicio:</span> {{ $s->tipo_servicio }}</div>
                            <div>
                                • <span class="text-gray-500">Pasos hechos:</span>
                                {{ $s->pasos_hechos_count ?? 0 }}/{{ $s->total_pasos ?? 0 }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Resolver/Continuar → checklist --}}
                            <a href="{{ route('ordenes.checklist', $s) }}"
                               class="rounded-lg bg-gray-900 text-white px-3 py-1.5 hover:bg-black">
                                {{ $estado==='pendiente' ? 'Resolver' : ($estado==='en_proceso' ? 'Continuar' : 'Ver') }}
                            </a>

                            {{-- Editar (lleva a /solicitudes con filtro por serie/ID si usas modal) --}}
                            <a href="{{ route('solicitudes.index', ['q'=>$s->no_serie, 'open'=>$s->id]) }}"
                               class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-100">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-gray-500">No hay órdenes en este estado.</div>
            @endforelse

            <div class="px-4 py-3">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
