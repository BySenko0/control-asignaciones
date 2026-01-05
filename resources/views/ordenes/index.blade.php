<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Header + tabs --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Órdenes de servicio / {{ $titulo }}</h1>
                <p class="text-sm text-gray-500">Gestiona órdenes por estado y accede a sus acciones clave.</p>
            </div>

            <div class="flex flex-wrap gap-2">
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
                <a href="{{ route('ordenes.vencidas') }}"
                   class="px-3 py-1.5 rounded-lg border text-sm {{ $estado==='vencidas' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-300 hover:bg-gray-50' }}">
                    Vencidas
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
                <a href="{{ url()->current() }}"
                   class="rounded-xl border border-gray-300 bg-white px-4 py-2 hover:bg-gray-50">Limpiar</a>
            </div>
        </form>

        {{-- Listado híbrido --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="px-4 py-3 border-b bg-gray-900 text-white rounded-t-2xl">
                • {{ $titulo }}
            </div>

            {{-- Tabla desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Folio</th>
                            <th class="px-4 py-3 text-left font-semibold">Cliente</th>
                            <th class="px-4 py-3 text-left font-semibold">Dispositivo / Modelo / Serie</th>
                            <th class="px-4 py-3 text-left font-semibold">Tipo de servicio</th>
                            <th class="px-4 py-3 text-left font-semibold">Asignado a</th>
                            <th class="px-4 py-3 text-left font-semibold">Vence</th>
                            <th class="px-4 py-3 text-left font-semibold">Estado</th>
                            <th class="px-4 py-3 text-right font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse($solicitudes as $s)
                            @php
                                $estadoColor = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800',
                                    'en_proceso' => 'bg-blue-100 text-blue-800',
                                    'finalizado' => 'bg-green-100 text-green-800',
                                    'vencidas' => 'bg-red-100 text-red-800',
                                ][$s->estado] ?? 'bg-gray-100 text-gray-800';

                                $fv = $s->fecha_vencimiento;
                                $venceTxt = $fv ? $fv->format('Y-m-d') : '—';
                                $venceBadge = 'bg-gray-100 text-gray-700';
                                if($fv){
                                    if($s->estado!=='finalizado'){
                                        if($fv->isBefore(today())) $venceBadge='bg-red-100 text-red-800';
                                        elseif($fv->isSameDay(today())) $venceBadge='bg-amber-100 text-amber-800';
                                        else $venceBadge='bg-sky-100 text-sky-800';
                                    } else {
                                        $venceBadge='bg-gray-100 text-gray-600';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">#{{ $s->id }}</td>
                                <td class="px-4 py-3">{{ optional($s->cliente)->nombre_cliente ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $s->dispositivo }}</div>
                                    <div class="text-xs text-gray-500">Modelo: {{ $s->modelo ?: '—' }}</div>
                                    <div class="text-xs text-gray-500">Serie: {{ $s->no_serie ?: '—' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $s->tipo_servicio }}</td>
                                <td class="px-4 py-3">{{ optional($s->asignado)->name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $venceBadge }}">
                                        {{ $venceTxt }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor }}">
                                        {{ Str::of($s->estado)->replace('_',' ')->ucfirst() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('ordenes.checklist', $s) }}"
                                           class="rounded-lg bg-gray-900 text-white px-3 py-1.5 hover:bg-black">
                                            {{
                                                $estado==='pendiente'   ? 'Resolver'  :
                                                ($estado==='en_proceso' ? 'Continuar' :
                                                ($estado==='vencidas'   ? 'Atender'   : 'Ver'))
                                            }}
                                        </a>

                                        @if($s->estado === 'finalizado')
                                            <a href="{{ route('ticket.public', $s) }}"
                                               class="rounded-lg border border-emerald-300 text-emerald-700 px-3 py-1.5 hover:bg-emerald-50"
                                               target="_blank">
                                                Ticket PDF
                                            </a>
                                        @endif

                                        @if($estado==='pendiente')
                                            <a href="{{ route('solicitudes.index', ['q'=>$s->no_serie, 'open'=>$s->id]) }}"
                                               class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-100">
                                                Editar
                                            </a>
                                        @endif

                                        @if($s->should_resend_whatsapp_ticket)
                                            <form action="{{ route('ordenes.reenviar_whatsapp', $s) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 px-3 py-1.5 hover:bg-indigo-100">
                                                    Volver a enviar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-500">No hay órdenes en este estado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cards móvil --}}
            <div class="md:hidden divide-y">
                @forelse($solicitudes as $s)
                    @php
                        $estadoColor = [
                            'pendiente' => 'bg-yellow-100 text-yellow-800',
                            'en_proceso' => 'bg-blue-100 text-blue-800',
                            'finalizado' => 'bg-green-100 text-green-800',
                            'vencidas' => 'bg-red-100 text-red-800',
                        ][$s->estado] ?? 'bg-gray-100 text-gray-800';

                        $fv = $s->fecha_vencimiento;
                        $venceTxt = $fv ? $fv->format('Y-m-d') : '—';
                        $venceBadge = 'bg-gray-100 text-gray-700';
                        if($fv){
                            if($s->estado!=='finalizado'){
                                if($fv->isBefore(today())) $venceBadge='bg-red-100 text-red-800';
                                elseif($fv->isSameDay(today())) $venceBadge='bg-amber-100 text-amber-800';
                                else $venceBadge='bg-sky-100 text-sky-800';
                            } else {
                                $venceBadge='bg-gray-100 text-gray-600';
                            }
                        }
                    @endphp
                    <div class="px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-gray-900">Orden #{{ $s->id }}</div>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor }}">
                                    {{ Str::of($s->estado)->replace('_',' ')->ucfirst() }}
                                </span>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $venceBadge }}">
                                {{ $venceTxt }}
                            </span>
                        </div>

                        <div class="mt-3 grid gap-1 text-sm text-gray-700">
                            <div><span class="text-gray-500">Cliente:</span> {{ optional($s->cliente)->nombre_cliente ?? '—' }}</div>
                            <div><span class="text-gray-500">Dispositivo:</span> {{ $s->dispositivo }} · {{ $s->modelo ?: '—' }}</div>
                            <div><span class="text-gray-500">Serie:</span> {{ $s->no_serie ?: '—' }}</div>
                            <div><span class="text-gray-500">Servicio:</span> {{ $s->tipo_servicio }}</div>
                            <div><span class="text-gray-500">Asignado:</span> {{ optional($s->asignado)->name ?? '—' }}</div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('ordenes.checklist', $s) }}"
                               class="rounded-lg bg-gray-900 text-white px-3 py-1.5 text-sm hover:bg-black">
                                {{
                                    $estado==='pendiente'   ? 'Resolver'  :
                                    ($estado==='en_proceso' ? 'Continuar' :
                                    ($estado==='vencidas'   ? 'Atender'   : 'Ver'))
                                }}
                            </a>

                            @if($s->estado === 'finalizado')
                                <a href="{{ route('ticket.public', $s) }}"
                                   class="rounded-lg border border-emerald-300 text-emerald-700 px-3 py-1.5 text-sm hover:bg-emerald-50"
                                   target="_blank">
                                    Ticket PDF
                                </a>
                            @endif

                            @if($estado==='pendiente')
                                <a href="{{ route('solicitudes.index', ['q'=>$s->no_serie, 'open'=>$s->id]) }}"
                                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-100">
                                    Editar
                                </a>
                            @endif

                            @if($s->should_resend_whatsapp_ticket)
                                <form action="{{ route('ordenes.reenviar_whatsapp', $s) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 px-3 py-1.5 text-sm hover:bg-indigo-100">
                                        Volver a enviar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-12 text-center text-gray-500">No hay órdenes en este estado.</div>
                @endforelse
            </div>

            <div class="px-4 py-3">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
