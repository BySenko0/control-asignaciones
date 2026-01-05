<x-app-layout>
    @push('styles')
    <style>[x-cloak]{display:none!important}</style>
    @endpush>

    <div x-data="clientesUI()" class="mx-auto max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Clientes</h1>
                <p class="text-sm text-gray-500">Gestiona a tus clientes y sus datos de contacto.</p>
            </div>
            <button @click="openCreate()"
                    class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">
                + Nuevo Cliente
            </button>
        </div>

        {{-- DataTables CSS --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

        {{-- Estilos UI (buscador, card rows, paginación) --}}
        <style>
          .search-wrap{position:relative;margin-top:.25rem}
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
          table.dataTable tbody tr td{
            padding:1rem 1rem; border-top:1px solid #F3F4F6; border-bottom:1px solid #F3F4F6;
          }
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

        {{-- Buscador (cliente) --}}
        <div class="search-wrap">
            <span class="icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <input id="searchClientes" type="text" placeholder="Buscar por RFC, nombre, empresa, correo o teléfono…"
                   class="w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            @if(!empty($q))
              <a class="clear" href="{{ route('clientes.index') }}">Limpiar</a>
            @endif
        </div>

        {{-- Cards móvil --}}
        <div class="md:hidden space-y-4">
            @forelse ($clientes as $c)
                @php
                    $url = $c->imagen
                        ? (filter_var($c->imagen, FILTER_VALIDATE_URL) ? $c->imagen : asset($c->imagen))
                        : null;
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        @if($url)
                            <img
                                src="{{ $url }}"
                                class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200"
                                onerror="this.replaceWith(Object.assign(document.createElement('div'),{
                                    className:'h-10 w-10 rounded-full ring-1 ring-gray-200 flex items-center justify-center text-[10px] text-gray-500 bg-gray-100',
                                    innerText:'Sin imagen'
                                }))"
                            />
                        @else
                            <div class="h-10 w-10 rounded-full ring-1 ring-gray-200 flex items-center justify-center text-[10px] text-gray-500 bg-gray-100">
                                Sin imagen
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 truncate">{{ $c->nombre_cliente }}</div>
                            <div class="text-sm text-gray-500 truncate">{{ $c->nombre_empresa }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $c->correo_empresa }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $c->telefono ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button @click='openEdit(@json($c))'
                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-100">
                            Editar
                        </button>
                        <form method="POST" action="{{ route('clientes.destroy', $c->id) }}"
                              onsubmit="return confirm('¿Eliminar este cliente?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="rounded-lg border border-red-300 text-red-700 px-3 py-1.5 text-sm hover:bg-red-50">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-500">
                    Sin registros.
                </div>
            @endforelse
        </div>

        {{-- Tabla desktop --}}
        <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table id="tablaClientes" class="min-w-full text-sm">
                <thead class="text-gray-600">
                    <tr>
                        <th class="text-left">Imagen</th>
                        <th class="text-left">Nombre</th>
                        <th class="text-left">Empresa</th>
                        <th class="text-left">Dirección</th>
                        <th class="text-left">Responsable</th>
                        <th class="text-left">Teléfono</th>
                        <th class="text-left">RFC</th>
                        <th class="text-left">Correo empresa</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                @forelse ($clientes as $c)
                    <tr>
                        <td class="align-middle">
                            @php
                                $url = $c->imagen
                                    ? (filter_var($c->imagen, FILTER_VALIDATE_URL) ? $c->imagen : asset($c->imagen))
                                    : null;
                            @endphp

                            @if($url)
                                <img
                                    src="{{ $url }}"
                                    class="h-9 w-9 rounded-full object-cover ring-1 ring-gray-200"
                                    onerror="this.replaceWith(Object.assign(document.createElement('div'),{
                                        className:'h-9 w-9 rounded-full ring-1 ring-gray-200 flex items-center justify-center text-[10px] text-gray-500 bg-gray-100',
                                        innerText:'Sin imagen'
                                    }))"
                                />
                            @else
                                <div class="h-9 w-9 rounded-full ring-1 ring-gray-200 flex items-center justify-center text-[10px] text-gray-500 bg-gray-100">
                                    Sin imagen
                                </div>
                            @endif
                        </td>
                        <td class="font-medium text-gray-900">{{ $c->nombre_cliente }}</td>
                        <td>{{ $c->nombre_empresa }}</td>
                        <td>{{ $c->direccion }}</td>
                        <td>{{ $c->responsable }}</td>
                        <td>{{ $c->telefono ?? '—' }}</td>
                        <td class="uppercase tracking-wide">{{ $c->rfc }}</td>
                        <td>{{ $c->correo_empresa }}</td>
                        <td>
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
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Sin registros.</td></tr>
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

        {{-- ******** MODAL (Crear / Editar) ******** --}}
        <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
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
                    <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>

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
                        <label class="text-sm text-gray-700">Teléfono</label>
                        <input name="telefono" x-model="form.telefono"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div>
                        <label class="text-sm text-gray-700">Correo de la empresa</label>
                        <input type="email" name="correo_empresa" x-model="form.correo_empresa"
                               class="mt-1 w-full rounded-xl border-gray-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm text-gray-700">Imagen (ruta/url)</label>
                        <input name="imagen" x-model="form.imagen" placeholder="storage/img_clientes/logo.png"
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
            form:{ id:null,nombre_cliente:'',nombre_empresa:'',direccion:'',responsable:'',telefono:'',rfc:'',imagen:'',correo_empresa:'' },
            openCreate(){ this.mode='create'; this.form={ id:null,nombre_cliente:'',nombre_empresa:'',direccion:'',responsable:'',telefono:'',rfc:'',imagen:'',correo_empresa:'' }; this.modalOpen=true; },
            openEdit(item){
                this.mode='edit';
                this.form={ id:item.id,nombre_cliente:item.nombre_cliente ?? '',nombre_empresa:item.nombre_empresa ?? '',direccion:item.direccion ?? '',responsable:item.responsable ?? '',telefono:item.telefono ?? '',rfc:item.rfc ?? '',imagen:item.imagen ?? '',correo_empresa:item.correo_empresa ?? '' };
                this.modalOpen=true;
            },
            close(){ this.modalOpen=false; },
            formAction(){
                if(this.mode==='create'){ return @json(route('clientes.store')); }
                const base = @json(route('clientes.update','__ID__')); return base.replace('__ID__', this.form.id ?? '');
            }
        }
    }
    </script>

    {{-- jQuery + DataTables --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
      $(function () {
        const table = $('#tablaClientes').DataTable({
          dom: 't<"flex justify-between items-center mt-3 px-4"<"length-menu"l><"pagination-wrapper"p>>',
          pagingType: 'simple_numbers',
          pageLength: 10,
          lengthMenu: [[10,25,50,-1],[10,25,50,'Todos']],
          order: [[1,'asc']], // orden por nombre
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
            { targets:[0,8], className:'align-middle' },
            { targets:[1,2,3,4,5,6,7], className:'align-middle' }
          ]
        });

        // Buscador externo controla DataTables
        $('#searchClientes').on('input', function(){ table.search(this.value).draw(); });

        // precargar con ?q=
        @if(!empty($q))
          $('#searchClientes').val(@json($q));
          table.search(@json($q)).draw();
        @endif
      });
    </script>
    @endpush
</x-app-layout>
