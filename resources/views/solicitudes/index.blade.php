{{-- resources/views/solicitudes/index.blade.php --}}
<x-app-layout>
    {{-- Evita parpadeo de modals al cargar --}}
    <style>[x-cloak]{display:none!important}</style>

    <div class="mx-auto max-w-6xl space-y-6"
         x-data="solicitudesUI({{ json_encode([
             'clienteId'   => isset($clienteSel) ? $clienteSel->id : null,
             'clienteName' => isset($clienteSel) ? $clienteSel->nombre_cliente : null,
         ]) }})"
         x-init="showCreate=false; showEdit=false; showAssign=false">

        {{-- Título + acción principal --}}
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
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Quitar filtro
                </a>
                @endisset

                <button @click="openCreate()"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    + Nueva solicitud
                </button>
            </div>
        </div>

        {{-- Barra de búsqueda --}}
        <form method="GET"
              action="{{ isset($clienteSel) ? route('clientes.equipos-solicitudes', $clienteSel) : route('solicitudes.index') }}">
            <input type="text" name="q" value="{{ $q ?? '' }}"
                   placeholder="Buscar por RFC, nombre, empresa o correo..."
                   class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
        </form>

        @if(session('ok'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-800">
                {{ session('ok') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-medium">Revisa la información proporcionada:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabla tarjeta --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-4 py-3">No. serie</th>
                        <th class="px-4 py-3">Dispositivo</th>
                        <th class="px-4 py-3">Modelo</th>
                        <th class="px-4 py-3">Tipo de servicio</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Asignado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    @forelse($solicitudes as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $s->no_serie ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $s->dispositivo }}</td>
                            <td class="px-4 py-3">{{ $s->modelo ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $s->tipo_servicio }}</td>
                            <td class="px-4 py-3">
                                @php($color = [
                                    'pendiente'   => 'bg-yellow-100 text-yellow-800',
                                    'en_proceso'  => 'bg-blue-100 text-blue-800',
                                    'finalizado'  => 'bg-green-100 text-green-800',
                                ][$s->estado] ?? 'bg-gray-100 text-gray-800')
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $color }}">
                                    {{ Str::of($s->estado)->replace('_',' ')->ucfirst() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ optional($s->cliente)->nombre_cliente ?? '—' }}</td>
                            <td class="px-4 py-3">{{ optional($s->asignado)->name ?? 'Sin asignar' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50"
                                        @click='openEdit(@js([
                                            "id" => $s->id,
                                            "cliente_id" => $s->cliente_id,
                                            "no_serie" => $s->no_serie,
                                            "dispositivo" => $s->dispositivo,
                                            "modelo" => $s->modelo,
                                            "tipo_servicio" => $s->tipo_servicio,
                                            "estado" => $s->estado,
                                            "descripcion" => $s->descripcion,
                                        ]))'>
                                        Editar
                                    </button>

                                    @role('admin')
                                    <button
                                        class="rounded-lg border border-indigo-200 bg-white px-3 py-1.5 text-xs text-indigo-700 hover:bg-indigo-50"
                                        @click="openAssign({ id: {{ $s->id }} })">
                                        Asignar
                                    </button>
                                    @endrole

                                    <form method="POST" action="{{ route('solicitudes.destroy', $s) }}"
                                          onsubmit="return confirm('¿Borrar solicitud?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs text-red-700 hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                No hay solicitudes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="px-4 py-3">
                {{ $solicitudes->links() }}
            </div>
        </div>
    </div>

    {{-- ===================== MODALS ===================== --}}

    {{-- CREAR --}}
    <div x-cloak x-show="showCreate" x-trap.noscroll="showCreate"
         @keydown.window.escape="showCreate=false"
         @click.self="showCreate=false"
         class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
            <div class="text-lg font-semibold text-gray-800">Agregar solicitud</div>
            <form method="POST" action="{{ route('solicitudes.store') }}" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Cliente</label>
                        <select name="cliente_id" x-ref="createCliente"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500">
                            @foreach(\App\Models\ClientesAsignacion::orderBy('nombre_cliente')->get(['id','nombre_cliente']) as $c)
                                <option value="{{ $c->id }}"
                                    @if(isset($clienteSel) && $clienteSel->id === $c->id) selected @endif>
                                    {{ $c->nombre_cliente }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Estado</label>
                        <select name="estado"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500">
                            @foreach (['pendiente','en_proceso','finalizado'] as $estado)
                                <option value="{{ $estado }}">{{ ucfirst(str_replace('_',' ', $estado)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">No. serie</label>
                        <input name="no_serie"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Dispositivo</label>
                        <input name="dispositivo"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Modelo</label>
                        <input name="modelo"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Tipo de servicio</label>
                        <input name="tipo_servicio"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Descripción</label>
                        <textarea name="descripcion" rows="3"
                                  class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="showCreate=false">Cancelar</button>
                    <button
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDITAR --}}
    <div x-cloak x-show="showEdit" x-trap.noscroll="showEdit"
         @keydown.window.escape="showEdit=false"
         @click.self="showEdit=false"
         class="fixed inset-0 z-40 flex items-center justify-center bg-black/40">
        <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
            <div class="text-lg font-semibold text-gray-800">Editar solicitud</div>
            <form method="POST" :action="editAction" class="mt-4 space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Cliente</label>
                        <select name="cliente_id" x-model="edit.cliente_id"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500">
                            @foreach(\App\Models\ClientesAsignacion::orderBy('nombre_cliente')->get(['id','nombre_cliente']) as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre_cliente }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Estado</label>
                        <select name="estado" x-model="edit.estado"
                                class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500">
                            @foreach (['pendiente','en_proceso','finalizado'] as $estado)
                                <option value="{{ $estado }}">{{ ucfirst(str_replace('_',' ', $estado)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">No. serie</label>
                        <input name="no_serie" x-model="edit.no_serie"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Dispositivo</label>
                        <input name="dispositivo" x-model="edit.dispositivo"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Modelo</label>
                        <input name="modelo" x-model="edit.modelo"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Tipo de servicio</label>
                        <input name="tipo_servicio" x-model="edit.tipo_servicio"
                               class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"/>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600">Descripción</label>
                        <textarea name="descripcion" rows="3" x-model="edit.descripcion"
                                  class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="showEdit=false">Cancelar</button>
                    <button
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ASIGNAR (solo admin) --}}
    @role('admin')
    <div x-cloak x-show="showAssign" x-trap.noscroll="showAssign"
         @keydown.window.escape="showAssign=false"
         @click.self="showAssign=false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <div class="text-lg font-semibold text-gray-800">Asignar solicitud</div>
            <form method="POST" :action="assignAction" class="mt-4">
                @csrf
                <label class="block text-sm text-gray-600 mb-1">Asignar a</label>
                <select name="user_id"
                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:ring-2 focus:ring-indigo-500">
                    @foreach ($usuarios as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="showAssign=false">Cancelar</button>
                    <button
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endrole

    {{-- ================= Alpine ================= --}}
    <script>
        function solicitudesUI(init = { clienteId: null, clienteName: null }) {
            const defaultEdit = () => ({
                id: null,
                cliente_id: init.clienteId ?? '',
                no_serie: '',
                dispositivo: '',
                modelo: '',
                tipo_servicio: '',
                estado: 'pendiente',
                descripcion: '',
            });

            return {
                showCreate: false,
                showEdit: false,
                showAssign: false,

                edit: defaultEdit(),

                get editAction() {
                    return this.edit.id ? `{{ url('solicitudes') }}/${this.edit.id}` : '#';
                },

                assignId: null,
                get assignAction() {
                    return this.assignId ? `{{ url('solicitudes') }}/${this.assignId}/assign` : '#';
                },

                resetState() {
                    this.showCreate = false;
                    this.showEdit = false;
                    this.showAssign = false;
                    this.assignId = null;
                },

                openCreate() {
                    this.resetState();
                    this.edit = defaultEdit();
                    this.showCreate = true;
                    this.$nextTick(() => {
                        if (init.clienteId && this.$refs.createCliente) {
                            this.$refs.createCliente.value = init.clienteId;
                        }
                    });
                },
                openEdit(payload) {
                    this.resetState();
                    const sanitized = {
                        ...payload,
                        cliente_id: payload.cliente_id ?? '',
                        no_serie: payload.no_serie ?? '',
                        dispositivo: payload.dispositivo ?? '',
                        modelo: payload.modelo ?? '',
                        tipo_servicio: payload.tipo_servicio ?? '',
                        estado: payload.estado ?? 'pendiente',
                        descripcion: payload.descripcion ?? '',
                    };
                    this.edit = { ...defaultEdit(), ...sanitized };
                    this.showEdit = true;
                },
                openAssign({ id }) {
                    this.resetState();
                    this.assignId = id;
                    this.showAssign = true;
                },
            }
        }
    </script>
</x-app-layout>
