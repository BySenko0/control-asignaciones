<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    <div x-data="solicitudesUI({ clienteId: {{ isset($clienteSel) ? $clienteSel->id : 'null' }} })"
         class="mx-auto max-w-7xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800">
                Solicitudes
                @isset($clienteSel)
                    <span class="text-gray-400 text-base font-normal">/ {{ $clienteSel->nombre_cliente }}</span>
                @endisset
            </h1>
            <div class="flex items-center gap-2">
                @isset($clienteSel)
                <a href="{{ route('solicitudes.index') }}"
                   class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Quitar filtro
                </a>
                @endisset
                <button @click="openCreate()"
                        class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                    + Nueva solicitud
                </button>
            </div>
        </div>

        {{-- DataTables CSS --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

        {{-- Search + estilos tabla “card rows” --}}
        <style>
          .search-wrap{position:relative}
          .search-wrap .icon{position:absolute;inset-inline-start:.9rem;inset-block:0;display:flex;align-items:center;color:#9CA3AF}
          .search-wrap input{height:2.9rem;padding:.65rem .75rem .65rem 2.6rem;border:1px solid #D1D5DB;border-radius:.85rem}
          .search-wrap a.clear{position:absolute;inset-inline-end:.5rem;inset-block:0;display:flex;align-items:center;padding-inline:.5rem;color:#6B7280}

          table.dataTable { border-collapse:separate; border-spacing:0 12px !important; background:transparent }
          table.dataTable thead th{
            position:sticky; top:0; z-index:10;
            background:#F9FAFB !important; color:#6B7280; font-weight:600; text-transform:uppercase; font-size:.72rem;
            padding:.9rem 1rem; border:none !important; box-shadow:inset 0 -1px 0 #E5E7EB;
          }
          table.dataTable tbody tr{ background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); border-radius:.9rem }
          table.dataTable tbody tr td{ padding:1rem 1rem; border-top:1px solid #F3F4F6; border-bottom:1px solid #F3F4F6 }
          table.dataTable tbody tr td:first-child{ border-left:1px solid #F3F4F6; border-top-left-radius:.9rem; border-bottom-left-radius:.9rem }
          table.dataTable tbody tr td:last-child{ border-right:1px solid #F3F4F6; border-top-right-radius:.9rem; border-bottom-right-radius:.9rem }
          table.dataTable tbody tr:hover{ background:#F9FAFB }

          .length-menu .dataTables_length{display:flex;align-items:center;gap:.5rem}
          .length-menu select{
            appearance:none;-webkit-appearance:none;-moz-appearance:none;
            padding:.5rem 2.1rem .5rem .7rem;border:1px solid #D1D5DB;border-radius:.5rem;background:#fff;
            background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="%236B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
            background-repeat:no-repeat;background-position:right .5rem center
          }
          .dataTables_info{display:none}
          .dataTables_paginate .paginate_button{
            border:1px solid #E5E7EB;border-radius:.5rem;padding:.35rem .6rem;margin:0 .2rem;background:#fff;color:#374151
          }
          .dataTables_paginate .paginate_button.current{background:#111827;color:#fff;border-color:#111827}
          .dataTables_paginate .paginate_button:hover{background:#F3F4F6}
        </style>

        {{-- Buscador externo --}}
        <div class="search-wrap">
          <span class="icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
          <input id="searchSolicitudes" type="text" placeholder="Buscar por serie, dispositivo, cliente, estado, etc."
                 class="w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
          @if(!empty($q))
            <a class="clear" href="{{ isset($clienteSel) ? route('clientes.equipos-solicitudes',$clienteSel) : route('solicitudes.index') }}">Limpiar</a>
          @endif
        </div>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table id="tablaSolicitudes" class="min-w-full text-sm">
                <thead class="text-gray-600">
                    <tr>
                        <th class="text-left">No. serie</th>
                        <th class="text-left">Dispositivo</th>
                        <th class="text-left">Modelo</th>
                        <th class="text-left">Tipo de servicio</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Vence</th> {{-- NUEVA COLUMNA --}}
                        <th class="text-left">Cliente</th>
                        <th class="text-left">Asignado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                @forelse ($solicitudes as $s)
                    <tr>
                        <td>{{ $s->no_serie ?? '—' }}</td>
                        <td>{{ $s->dispositivo }}</td>
                        <td>{{ $s->modelo ?? '—' }}</td>
                        <td>{{ $s->tipo_servicio }}</td>
                        <td>
                            @php($color = [
                                'pendiente'  => 'bg-yellow-100 text-yellow-800',
                                'en_proceso' => 'bg-blue-100 text-blue-800',
                                'finalizado' => 'bg-green-100 text-green-800',
                            ][$s->estado] ?? 'bg-gray-100 text-gray-800')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
                                {{ Str::of($s->estado)->replace('_',' ')->ucfirst() }}
                            </span>
                        </td>
                        {{-- CELDA VENCE --}}
                        <td>
                            {{-- Defaults --}}
                            @php($fv    = $s->fecha_vencimiento)  {{-- Carbon|null (cast en el modelo) --}}
                            @php($texto = $fv ? $fv->format('Y-m-d') : '—')
                            @php($badge = 'bg-gray-100 text-gray-700')

                            @if($fv)
                                @if($s->estado !== 'finalizado')
                                    @if($fv->isBefore(today()))
                                        @php($badge = 'bg-red-100 text-red-800')     {{-- vencida --}}
                                    @elseif($fv->isSameDay(today()))
                                        @php($badge = 'bg-amber-100 text-amber-800') {{-- vence hoy --}}
                                    @else
                                        @php($badge = 'bg-sky-100 text-sky-800')     {{-- futura --}}
                                    @endif
                                @else
                                    @php($badge = 'bg-gray-100 text-gray-600')       {{-- ya finalizado --}}
                                @endif
                            @endif

                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge }}">
                                {{ $texto }}
                            </span>
                        </td>
                        <td>{{ optional($s->cliente)->nombre_cliente ?? '—' }}</td>
                        <td>{{ optional($s->asignado)->name ?? 'Sin asignar' }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                {{-- EDITAR: objeto seguro en x-data (incluye fecha_vencimiento) --}}
                                <button
                                    x-data="{ item: @js([
                                        'id'                 => $s->id,
                                        'cliente_id'         => $s->cliente_id,
                                        'no_serie'           => $s->no_serie,
                                        'dispositivo'        => $s->dispositivo,
                                        'modelo'             => $s->modelo,
                                        'plantilla_id'       => $s->plantilla_id,
                                        'tipo_servicio'      => $s->tipo_servicio,
                                        'estado'             => $s->estado,
                                        'descripcion'        => $s->descripcion,
                                        'fecha_vencimiento'  => optional($s->fecha_vencimiento)->format('Y-m-d'),
                                    ]) }"
                                    @click="openEdit(item)"
                                    class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-100">
                                    Editar
                                </button>

                                @role('admin')
                                <button @click="openAssign({ id: {{ $s->id }} })"
                                        class="rounded-lg border border-indigo-200 text-indigo-700 px-3 py-1.5 hover:bg-indigo-50">
                                    Asignar
                                </button>
                                @endrole

                                @if(auth()->user()->hasRole('virtuality') && !auth()->user()->hasRole('admin'))
                                    @if(is_null($s->asignado_a))
                                        <form method="POST" action="{{ route('solicitudes.assign', $s) }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                            <button type="submit"
                                                    class="rounded-lg border border-indigo-200 text-indigo-700 px-3 py-1.5 hover:bg-indigo-50">
                                                Tomar
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                @if($s->estado === 'finalizado')
                                    <a href="{{ route('ticket.public', $s) }}"
                                       class="rounded-lg border border-emerald-300 text-emerald-700 px-3 py-1.5 hover:bg-emerald-50"
                                       target="_blank">
                                        Ticket
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('solicitudes.destroy', $s) }}"
                                      onsubmit="return confirm('¿Eliminar esta solicitud?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg border border-red-300 text-red-700 px-3 py-1.5 hover:bg-red-50">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No hay solicitudes.</td></tr>
                @endforelse
                </tbody>
            </table>

            <div class="px-4 pb-5 pt-2">
                <div class="flex justify-between items-center">
                    <div class="length-menu"></div>
                    <div class="pagination-wrapper"></div>
                </div>
            </div>
        </div>

        @if(session('ok'))
            <div class="rounded-xl bg-green-50 text-green-800 px-4 py-2">{{ session('ok') }}</div>
        @endif

        {{-- ******** MODAL Crear/Editar ******** --}}
        <div x-cloak x-show="modalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="absolute inset-0 bg-black/40" @click="close()"></div>

          <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl p-6">
              <div class="flex items-center justify-between mb-4">
                  <h2 class="text-lg font-semibold"
                      x-text="mode==='create' ? 'Nueva solicitud' : 'Editar solicitud'"></h2>
                  <button class="p-2 rounded hover:bg-gray-100" @click="close()" aria-label="Cerrar">
                      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                  </button>
              </div>

              <form :action="formAction()" method="POST" class="grid gap-4 sm:grid-cols-2">
                  @csrf
                  <template x-if="mode==='edit'">
                      <input type="hidden" name="_method" value="PUT">
                  </template>

                  <input type="hidden" name="_edit_id" x-model="form.id">

                  {{-- Cliente --}}
                  <div class="sm:col-span-2">
                      <label class="text-sm text-gray-700">Cliente</label>
                      <select name="cliente_id" x-model="form.cliente_id"
                              class="mt-1 w-full rounded-xl border-gray-300">
                          @foreach (\App\Models\ClientesAsignacion::orderBy('nombre_cliente')->get(['id','nombre_cliente']) as $c)
                              <option value="{{ $c->id }}">{{ $c->nombre_cliente }}</option>
                          @endforeach
                      </select>
                  </div>

                  {{-- Estado --}}
                  <div>
                      <label class="text-sm text-gray-700">Estado</label>
                      <select name="estado" x-model="form.estado"
                              class="mt-1 w-full rounded-xl border-gray-300">
                          @foreach (['pendiente','en_proceso','finalizado'] as $estado)
                              <option value="{{ $estado }}">{{ ucfirst(str_replace('_',' ', $estado)) }}</option>
                          @endforeach
                      </select>
                  </div>

                  {{-- No. serie --}}
                  <div>
                      <label class="text-sm text-gray-700">No. serie</label>
                      <input name="no_serie" x-model="form.no_serie"
                             class="mt-1 w-full rounded-xl border-gray-300">
                  </div>

                  {{-- Dispositivo --}}
                  <div>
                      <label class="text-sm text-gray-700">Dispositivo</label>
                      <input name="dispositivo" x-model="form.dispositivo"
                             class="mt-1 w-full rounded-xl border-gray-300">
                  </div>

                  {{-- Modelo --}}
                  <div>
                      <label class="text-sm text-gray-700">Modelo</label>
                      <input name="modelo" x-model="form.modelo"
                             class="mt-1 w-full rounded-xl border-gray-300">
                  </div>

                  {{-- Fecha de vencimiento (opcional) --}}
                  <div x-data="{ sinVenc: false }">
                      <label class="text-sm text-gray-700">Fecha de vencimiento (opcional)</label>
                      <input type="date" name="fecha_vencimiento"
                             class="mt-1 w-full rounded-xl border-gray-300"
                             :disabled="sinVenc"
                             x-model="form.fecha_vencimiento"
                             @change="sinVenc = !form.fecha_vencimiento ? true : false">
                      <label class="mt-2 inline-flex items-center gap-2 text-sm select-none">
                        <input type="checkbox" x-model="sinVenc"
                               @change="if(sinVenc){ form.fecha_vencimiento=''; }"
                               class="rounded border-gray-300">
                        Sin vencimiento
                      </label>
                      <p class="mt-1 text-xs text-gray-500">Déjalo vacío si no aplica.</p>
                  </div>

                  {{-- Plantilla (select) --}}
                  <div>
                      <label class="text-sm text-gray-700">Tipo de servicio (Plantilla)</label>
                      <select name="plantilla_id" x-model="form.plantilla_id" @change="onPick()
                              " class="mt-1 w-full rounded-xl border-gray-300">
                          <option value="">Selecciona una plantilla…</option>
                          @foreach ($plantillas as $p)
                              <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                          @endforeach
                      </select>
                      <input type="hidden" name="tipo_servicio" x-model="form.tipo_servicio">
                  </div>

                  {{-- Descripción --}}
                  <div class="sm:col-span-2">
                      <label class="text-sm text-gray-700">Descripción</label>
                      <textarea name="descripcion" rows="3" x-model="form.descripcion"
                                class="mt-1 w-full rounded-xl border-gray-300"></textarea>
                  </div>

                  <div class="sm:col-span-2 mt-2 flex items-center justify-end gap-3">
                      <button type="button" @click="close()"
                              class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-100">
                          Cancelar
                      </button>
                      <button type="submit"
                              class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                          <span x-text="mode==='create' ? 'Crear' : 'Guardar cambios'"></span>
                      </button>
                  </div>
              </form>
          </div>
        </div>
        {{-- ******** FIN MODAL Crear/Editar ******** --}}

        {{-- ******** MODAL ASIGNAR (admin) ******** --}}
        @role('admin')
        <div x-cloak x-show="assignOpen"
             class="fixed inset-0 z-50 flex items-center justify-center">
          <div class="absolute inset-0 bg-black/40" @click="closeAssign()"></div>

          <div class="relative w-full max-w-md rounded-2xl bg-white shadow-xl p-6">
              <div class="flex items-center justify-between mb-4">
                  <h2 class="text-lg font-semibold">Asignar solicitud</h2>
                  <button class="p-2 rounded hover:bg-gray-100" @click="closeAssign()" aria-label="Cerrar">
                      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                  </button>
              </div>

              <form :action="assignAction()" method="POST" class="grid gap-4">
                  @csrf
                  <input type="hidden" x-model="assignId" name="_assign_id">

                  <div>
                      <label class="text-sm text-gray-700">Asignar a</label>
                      <select name="user_id" class="mt-1 w-full rounded-xl border-gray-300" required>
                          <option value="" disabled selected>Selecciona usuario (virtuality o admin)</option>
                          @foreach ($usuarios as $u)
                              <option value="{{ $u->id }}">{{ $u->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="mt-2 flex items-center justify-end gap-3">
                      <button type="button" @click="closeAssign()"
                              class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-100">
                          Cancelar
                      </button>
                      <button type="submit"
                              class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                          Guardar
                      </button>
                  </div>
              </form>
          </div>
        </div>
        @endrole
        {{-- ******** FIN MODAL ASIGNAR ******** --}}
    </div>

    @push('scripts')
    {{-- Plantillas para Alpine (id, nombre, descripcion) --}}
    <script>
      const PLANTILLAS = @js($plantillas->map(fn($p)=>['id'=>$p->id,'nombre'=>$p->nombre,'descripcion'=>$p->descripcion]));
    </script>

    {{-- Alpine: lógica de la vista --}}
    <script>
    function solicitudesUI({ clienteId = null } = {}){
        const blank = () => ({
            id:null,
            cliente_id: clienteId ?? '',
            no_serie:'',
            dispositivo:'',
            modelo:'',
            plantilla_id:'',
            tipo_servicio:'',
            estado:'pendiente',
            descripcion:'',
            fecha_vencimiento:'', // NUEVO
        });

        return {
            // dataset
            plantillas: PLANTILLAS,

            // modal crear/editar
            modalOpen:false,
            mode:'create',
            form: blank(),

            // modal asignar
            assignOpen:false,
            assignId:null,

            // abrir/cerrar
            openCreate(){
                this.mode='create';
                this.form = blank();
                this.modalOpen=true;
            },
            openEdit(item){
                this.mode='edit';
                this.form = {
                    ...blank(),
                    id: item.id ?? null,
                    cliente_id: item.cliente_id ?? (clienteId ?? ''),
                    no_serie: item.no_serie ?? '',
                    dispositivo: item.dispositivo ?? '',
                    modelo: item.modelo ?? '',
                    plantilla_id: item.plantilla_id ?? '',
                    tipo_servicio: item.tipo_servicio ?? '',
                    estado: item.estado ?? 'pendiente',
                    descripcion: item.descripcion ?? '',
                    fecha_vencimiento: item.fecha_vencimiento ?? '', // NUEVO
                };
                this.modalOpen=true;
            },
            close(){ this.modalOpen=false; },

            openAssign({id}){ this.assignId=id; this.assignOpen=true; },
            closeAssign(){ this.assignOpen=false; this.assignId=null; },

            // sincroniza nombre/descripcion al elegir plantilla
            onPick(){
                const p = this.plantillas.find(pp => String(pp.id) === String(this.form.plantilla_id));
                this.form.tipo_servicio = p?.nombre || '';
                if (!this.form.descripcion && p?.descripcion) {
                    this.form.descripcion = p.descripcion;
                }
            },

            // acciones
            formAction(){
                if(this.mode==='create'){
                    return @json(route('solicitudes.store'));
                }else{
                    const base = @json(route('solicitudes.update','__ID__'));
                    return base.replace('__ID__', this.form.id ?? '');
                }
            },
            assignAction(){
                const base = @json(url('solicitudes/__ID__/assign'));
                return base.replace('__ID__', this.assignId ?? '');
            },
        }
    }
    </script>

    {{-- jQuery + DataTables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
      $(function () {
        const table = $('#tablaSolicitudes').DataTable({
          dom: 't<"flex justify-between items-center mt-3 px-4"<"length-menu"l><"pagination-wrapper"p>>',
          pagingType: 'simple_numbers',
          pageLength: 10,
          lengthMenu: [[10,25,50,-1],[10,25,50,'Todos']],
          order: [[0,'asc']],
          autoWidth: false,
          language: {
            lengthMenu: 'Mostrar _MENU_ por página',
            zeroRecords: 'No se encontraron resultados',
            info: 'Mostrando página _PAGE_ de _PAGES_',
            infoEmpty: 'No hay registros disponibles',
            infoFiltered: '(filtrado de _MAX_ totales)',
            paginate: { previous: '<', next: '>' }
          },
          columnDefs: [
            { targets:[0,1,2,3,4,5,6,7,8], className:'align-middle' },
            { targets:[8], orderable:false } // acciones
          ]
        });

        // Buscador externo → DataTables
        $('#searchSolicitudes').on('input', function(){ table.search(this.value).draw(); });

        // precarga si venía ?q=
        @if(!empty($q))
          $('#searchSolicitudes').val(@json($q));
          table.search(@json($q)).draw();
        @endif
      });
    </script>
    @endpush
</x-app-layout>
