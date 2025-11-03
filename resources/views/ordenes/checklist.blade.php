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
    @endphp

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">
                Checklist – Orden #{{ $solicitud->id }}
            </h1>

            <div class="flex gap-2">
                @if($solicitud->estado === \App\Models\Solicitud::FINALIZADO)
                    <a href="{{ route('ordenes.ticket', $solicitud) }}"
                       class="rounded-xl border border-emerald-300 text-emerald-700 px-4 py-2 hover:bg-emerald-50"
                       target="_blank">
                        Ticket PDF
                    </a>
                @endif
                <a href="{{ url('/ordenes/en_proceso') }}"
                   class="rounded-xl border px-4 py-2 bg-white text-gray-700 hover:bg-gray-50">Volver</a>

                @if($puedeGestionar)
                    <form method="POST" action="{{ route('ordenes.finalizar', $solicitud) }}">
                        @csrf
                        <button
                            class="rounded-xl px-4 py-2 {{ $total>0 && $hechos >= $total ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-gray-300 text-gray-600 cursor-not-allowed' }}"
                            {{ $total>0 && $hechos >= $total ? '' : 'disabled' }}>
                            Finalizar
                        </button>
                    </form>
                @else
                    <button
                        class="rounded-xl px-4 py-2 bg-gray-300 text-gray-600 cursor-not-allowed"
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
        <div class="rounded-2xl border bg-white p-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
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
                    <li class="px-4 py-3 flex items-start gap-3">
                        @if($puedeGestionar)
                            <form method="POST"
                                  action="{{ route('ordenes.toggle', ['solicitud'=>$solicitud->id, 'paso'=>$pp->id]) }}"
                                  class="flex items-start gap-3 w-full">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="mt-0.5 h-5 w-5 rounded border flex items-center justify-center
                                           {{ $estaHecho ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-transparent' }}">
                                    ✓
                                </button>

                                <div class="flex-1">
                                    <div class="font-medium">
                                        {{ $pp->numero }}. {{ $pp->titulo }}
                                        @if($estaHecho)
                                            <span class="ml-2 text-xs rounded bg-emerald-100 text-emerald-700 px-2 py-0.5">hecho</span>
                                        @endif
                                    </div>
                                    @if(!empty($sp?->notas))
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="font-medium">Notas:</span> {{ $sp->notas }}
                                        </div>
                                    @endif

                                    {{-- Campo opcional para agregar/actualizar notas al marcar --}}
                                    <div class="mt-2">
                                        <input type="text" name="notas" placeholder="Agregar nota (opcional)"
                                               class="w-full rounded-lg border px-3 py-2 text-sm"
                                               value="{{ old('notas') }}">
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="flex items-start gap-3 w-full opacity-70">
                                <span class="mt-0.5 h-5 w-5 rounded border flex items-center justify-center
                                             {{ $estaHecho ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-gray-300 text-transparent' }}">
                                    ✓
                                </span>

                                <div class="flex-1">
                                    <div class="font-medium">
                                        {{ $pp->numero }}. {{ $pp->titulo }}
                                        @if($estaHecho)
                                            <span class="ml-2 text-xs rounded bg-emerald-100 text-emerald-700 px-2 py-0.5">hecho</span>
                                        @endif
                                    </div>
                                    @if(!empty($sp?->notas))
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="font-medium">Notas:</span> {{ $sp->notas }}
                                        </div>
                                    @endif

                                    <div class="mt-2 text-xs text-gray-500">
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
