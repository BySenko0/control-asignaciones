{{-- resources/views/ordenes/checklist.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    @php
        $total = (int) ($solicitud->total_pasos ?? $solicitud->plantilla->pasos->count());
        $hechos = (int) ($solicitud->pasos_hechos_count ?? 0);

        // Mapa rápido para saber si un paso está hecho
        $doneMap = $solicitud->pasos->keyBy('plantilla_paso_id');
        $pct = $total > 0 ? intval(($hechos / $total) * 100) : 0;
        $puedeGestionar = $puedeGestionar ?? false;

        $estadoColor = [
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'en_proceso' => 'bg-blue-100 text-blue-800',
            'finalizado' => 'bg-green-100 text-green-800',
            'vencidas' => 'bg-red-100 text-red-800',
        ][$solicitud->estado] ?? 'bg-gray-100 text-gray-800';

        $fv = $solicitud->fecha_vencimiento;
        $venceTxt = $fv ? $fv->format('Y-m-d') : '—';
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-2">
                <h1 class="text-xl font-semibold text-gray-800">
                    Checklist – Orden #{{ $solicitud->id }}
                </h1>
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoColor }}">
                        {{ Str::of($solicitud->estado)->replace('_',' ')->ucfirst() }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                        Vence: {{ $venceTxt }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                        Progreso: {{ $hechos }} / {{ $total }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                @if($solicitud->estado === \App\Models\Solicitud::FINALIZADO)
                    <a href="{{ route('ticket.public', $solicitud) }}"
                       class="rounded-xl border border-emerald-300 text-emerald-700 px-4 py-2 text-sm hover:bg-emerald-50"
                       target="_blank">
                        Ticket PDF
                    </a>
                @endif
                <a href="{{ url('/ordenes/en_proceso') }}"
                   class="rounded-xl border px-4 py-2 text-sm bg-white text-gray-700 hover:bg-gray-50">Volver</a>

                @if($puedeGestionar)
                    <form method="POST" action="{{ route('ordenes.finalizar', $solicitud) }}">
                        @csrf
                        <button
                            class="rounded-xl px-4 py-2 text-sm {{ $total>0 && $hechos >= $total ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}"
                            {{ $total>0 && $hechos >= $total ? '' : 'disabled' }}>
                            Finalizar
                        </button>
                    </form>
                @else
                    <button
                        class="rounded-xl px-4 py-2 text-sm bg-gray-300 text-gray-600 cursor-not-allowed"
                        disabled>
                        Finalizar
                    </button>
                @endif
            </div>
        </div>

        @unless($puedeGestionar)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Solo la persona asignada puede marcar pasos o finalizar esta orden.
            </div>
        @endunless

        {{-- Tarjeta de info --}}
        <div class="rounded-2xl border bg-white p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <div class="text-sm text-gray-500">Cliente</div>
                <div class="font-medium">{{ $solicitud->cliente->nombre_cliente ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Asignado a</div>
                <div class="font-medium">{{ $solicitud->asignado->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Dispositivo</div>
                <div class="font-medium">{{ $solicitud->dispositivo }} · {{ $solicitud->modelo }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Servicio</div>
                <div class="font-medium">{{ $solicitud->tipo_servicio }}</div>
            </div>
        </div>

        {{-- Progreso --}}
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm text-gray-600">
                <span>Progreso</span>
                <span>{{ $hechos }} / {{ $total }} ({{ $pct }}%)</span>
            </div>
            <div class="h-3 w-full rounded-full bg-gray-200 overflow-hidden">
                <div class="h-full bg-indigo-600" style="width: {{ $pct }}%"></div>
            </div>
        </div>

        {{-- Lista de pasos --}}
        <div class="rounded-2xl border bg-white">
            <div class="px-4 py-3 border-b bg-gray-50 rounded-t-2xl font-medium">
                Pasos de la plantilla: {{ $solicitud->plantilla->nombre ?? '—' }}
            </div>

            <ul class="divide-y">
                @foreach($solicitud->plantilla->pasos as $pp)
                    @php
                        $sp = $doneMap->get($pp->id);
                        $estaHecho = (bool) ($sp->hecho ?? false);
                    @endphp
                    <li class="px-4 py-4">
                        @if($puedeGestionar)
                            <form method="POST"
                                  action="{{ route('ordenes.toggle', ['solicitud'=>$solicitud->id, 'paso'=>$pp->id]) }}"
                                  class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="mt-0.5 h-6 w-6 sm:h-5 sm:w-5 rounded border flex items-center justify-center
                                           {{ $estaHecho ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-transparent' }}">
                                    ✓
                                </button>

                                <div class="flex-1 space-y-2">
                                    <div class="font-medium text-gray-900">
                                        {{ $pp->numero }}. {{ $pp->titulo }}
                                        @if($estaHecho)
                                            <span class="ml-2 text-xs rounded bg-emerald-100 text-emerald-700 px-2 py-0.5">hecho</span>
                                        @endif
                                    </div>
                                    @if(!empty($sp?->notas))
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">Notas:</span> {{ $sp->notas }}
                                        </div>
                                    @endif

                                    {{-- Campo opcional para agregar/actualizar notas al marcar --}}
                                    <div>
                                        <input type="text" name="notas" placeholder="Agregar nota (opcional)"
                                               class="w-full rounded-lg border px-3 py-2 text-sm"
                                               value="{{ old('notas') }}">
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start opacity-70">
                                <span class="mt-0.5 h-6 w-6 sm:h-5 sm:w-5 rounded border flex items-center justify-center
                                             {{ $estaHecho ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-transparent' }}">
                                    ✓
                                </span>

                                <div class="flex-1 space-y-2">
                                    <div class="font-medium text-gray-900">
                                        {{ $pp->numero }}. {{ $pp->titulo }}
                                        @if($estaHecho)
                                            <span class="ml-2 text-xs rounded bg-emerald-100 text-emerald-700 px-2 py-0.5">hecho</span>
                                        @endif
                                    </div>
                                    @if(!empty($sp?->notas))
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">Notas:</span> {{ $sp->notas }}
                                        </div>
                                    @endif

                                    <div class="text-xs text-gray-500">
                                        Solo la persona asignada puede actualizar este paso.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
