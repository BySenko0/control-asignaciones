<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    <div x-data="clientesUI()" class="mx-auto max-w-6xl space-y-5">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Clientes</h1>
            <button @click="openCreate()"
                    class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                + Nuevo Cliente
            </button>
        </div>

        {{-- Buscador --}}
        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="relative">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                       placeholder="Buscar por RFC, nombre, empresa o correo…"
                       class="w-full rounded-xl border border-gray-300 bg-white pl-11 pr-4 py-2.5
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                
            </div>
        </form>

        {{-- Tabla --}}
        <div class="overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Imagen</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-left">Empresa</th>
                        <th class="px-4 py-3 text-left">Dirección</th>
                        <th class="px-4 py-3 text-left">Responsable</th>
                        <th class="px-4 py-3 text-left">RFC</th>
                        <th class="px-4 py-3 text-left">Correo empresa</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($clientes as $c)
                    <tr>
                        <td class="px-4 py-3">
                            <img src="{{ $c->imagen ? asset($c->imagen) : asset('img/no-image.png') }}"
                                 class="h-8 w-8 rounded-full object-cover"
                                 onerror="this.src='{{ asset('img/no-image.png') }}';" />
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $c->nombre_cliente }}</td>
                        <td class="px-4 py-3">{{ $c->nombre_empresa }}</td>
                        <td class="px-4 py-3">{{ $c->direccion }}</td>
                        <td class="px-4 py-3">{{ $c->responsable }}</td>
                        <td class="px-4 py-3">{{ $c->rfc }}</td>
                        <td class="px-4 py-3">{{ $c->correo_empresa }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button @click='openEdit(@json($c))'
                                   class="rounded-lg border border-gray-300 px-3 py-1.5 hover:bg-gray-100">
                                   Editar
                                </button>

                                <form method="POST" action="{{ route('clientes.destroy', $c->id) }}"
                                      onsubmit="return confirm('¿Eliminar este cliente?')">
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
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Sin registros.</td></tr>
                @endforelse
                </tbody>
            </table>

            <div class="px-4 py-3">
                {{ $clientes->links() }}
            </div>
        </div>

        @if(session('ok'))
            <div class="rounded-xl bg-green-50 text-green-800 px-4 py-2">{{ session('ok') }}</div>
        @endif

        {{-- ******** MODAL (Crear / Editar) ******** --}}
        <div x-cloak x-show="modalOpen"
             class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" @click="close()"></div>

            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold" x-text="mode==='create' ? 'Nuevo cliente' : 'Editar cliente'"></h2>
                    <button class="p-2 rounded hover:bg-gray-100" @click="close()">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="formAction()" method="POST" class="grid gap-4 sm:grid-cols-2">
                    @csrf
                    <template x-if="mode==='edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="_edit_id" x-model="form.id">

                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-700">Nombre del cliente</label>
                        <input name="nombre_cliente" x-model="form.nombre_cliente"
                               class="mt-1 w-full rounded-xl border-gray-300" required>
                    </div>

                    <div>
                        <label class="text-sm text-gray-700">Nombre de la empresa</label>
                        <input name="nombre_empresa" x-model="form.nombre_empresa"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm text-gray-700">RFC</label>
                        <input name="rfc" x-model="form.rfc"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-700">Dirección</label>
                        <input name="direccion" x-model="form.direccion"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm text-gray-700">Responsable</label>
                        <input name="responsable" x-model="form.responsable"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div>
                        <label class="text-sm text-gray-700">Correo de la empresa</label>
                        <input type="email" name="correo_empresa" x-model="form.correo_empresa"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-700">Imagen (ruta/url)</label>
                        <input name="imagen" x-model="form.imagen"
                               placeholder="storage/img_clientes/logo.png"
                               class="mt-1 w-full rounded-xl border-gray-300">
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
        {{-- ******** FIN MODAL ******** --}}
    </div>

    @push('scripts')
    <script>
    function clientesUI(){
        return {
            modalOpen:false,
            mode:'create',
            form: {
                id:null,
                nombre_cliente:'',
                nombre_empresa:'',
                direccion:'',
                responsable:'',
                rfc:'',
                imagen:'',
                correo_empresa:''
            },
            openCreate(){
                this.mode='create';
                this.form = {id:null,nombre_cliente:'',nombre_empresa:'',direccion:'',responsable:'',rfc:'',imagen:'',correo_empresa:''};
                this.modalOpen=true;
            },
            openEdit(item){
                this.mode='edit';
                this.form = {
                    id:item.id,
                    nombre_cliente:item.nombre_cliente ?? '',
                    nombre_empresa:item.nombre_empresa ?? '',
                    direccion:item.direccion ?? '',
                    responsable:item.responsable ?? '',
                    rfc:item.rfc ?? '',
                    imagen:item.imagen ?? '',
                    correo_empresa:item.correo_empresa ?? ''
                };
                this.modalOpen=true;
            },
            close(){ this.modalOpen=false; },
            formAction(){
                if(this.mode==='create'){
                    return @json(route('clientes.store'));
                }else{
                    const base = @json(route('clientes.update','__ID__'));
                    return base.replace('__ID__', this.form.id ?? '');
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
