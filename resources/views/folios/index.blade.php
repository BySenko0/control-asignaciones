<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800">Folios de reparación</h1>
        </div>

        <form method="GET" class="grid gap-3 sm:grid-cols-3 items-end">
            <div class="sm:col-span-2">
                <label class="text-sm text-gray-700">Buscar</label>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Dispositivo, modelo, serie o problema"
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

            <div class="sm:col-span-3 flex items-center gap-2">
                <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Aplicar</button>
                <a href="{{ url()->current() }}"
                   class="rounded-xl border border-gray-300 bg-white px-4 py-2 hover:bg-gray-50">Limpiar</a>
            </div>
        </form>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="px-4 py-3 border-b bg-gray-900 text-white rounded-t-2xl flex items-center justify-between">
                <span>Dispositivos reparados</span>
                @unless($isAdmin)
                    <span class="text-xs text-gray-200">Solo ves tus asignaciones</span>
                @endunless
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Folio</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Dispositivo</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Cliente</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Solicitado</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Reparado</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Reparó</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Problema</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Ticket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($folios as $folio)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900">#{{ $folio->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $folio->dispositivo }}</div>
                                    <div class="text-xs text-gray-500">{{ $folio->modelo ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">Serie: {{ $folio->no_serie ?: '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-800">{{ optional($folio->cliente)->nombre_cliente ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ optional($folio->created_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ optional($folio->updated_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ optional($folio->asignado)->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700 max-w-xs">
                                    <div class="text-sm break-words">{{ $folio->descripcion ?: 'Sin descripción' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Servicio: {{ $folio->tipo_servicio }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('ticket.public', $folio) }}" target="_blank"
                                       class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-emerald-700 hover:bg-emerald-100">
                                        Ver ticket
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-500">No hay dispositivos reparados con los filtros seleccionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $folios->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
