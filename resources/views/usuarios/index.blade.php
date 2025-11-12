<x-app-layout>
  {{-- Estado Alpine + listener para abrir modal desde jQuery --}}
  <div
    class="mx-auto max-w-7xl space-y-6"
    x-data="{
      openCreate:false,
      openEdit:false,
      editing:{},
      init() {
        window.addEventListener('open-edit', (e) => {
          this.editing = e.detail
          this.openEdit = true
        })
      }
    }"
  >
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Usuarios</h1>
        <p class="text-sm text-gray-500">Gestiona los usuarios con acceso al sistema.</p>
      </div>

      <button @click="openCreate=true"
        class="inline-flex items-center gap-2 rounded-lg border border-indigo-600 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Agregar usuario
      </button>
    </div>

    {{-- DataTables + estilos --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
      /* Tabla tipo “card rows” */
      .card { border:1px solid #E5E7EB;border-radius:1rem;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04) }
      .search-wrap{position:relative}
      .search-wrap .icon{position:absolute;inset-inline-start:.9rem;inset-block:0;display:flex;align-items:center;color:#9CA3AF}
      .search-wrap input{height:2.75rem;padding:.625rem .75rem .625rem 2.5rem;border:1px solid #D1D5DB;border-radius:.75rem}
      .search-wrap a.clear{position:absolute;inset-inline-end:.5rem;inset-block:0;display:flex;align-items:center;padding-inline:.5rem;color:#6B7280}

      table.dataTable { border-collapse:separate; border-spacing:0 10px !important; background:transparent }
      table.dataTable thead th{
        position:sticky; top:0; z-index:10;
        background:#F9FAFB !important; color:#6B7280; font-weight:600; text-transform:uppercase; font-size:.72rem;
        padding:.9rem 1rem; border:none !important; box-shadow:inset 0 -1px 0 #E5E7EB;
      }
      table.dataTable tbody tr{ background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); border-radius:.75rem }
      table.dataTable tbody tr td{ padding:1rem 1rem; border-top:1px solid #F3F4F6; border-bottom:1px solid #F3F4F6; }
      table.dataTable tbody tr td:first-child{ border-left:1px solid #F3F4F6; border-top-left-radius:.75rem; border-bottom-left-radius:.75rem }
      table.dataTable tbody tr td:last-child{ border-right:1px solid #F3F4F6; border-top-right-radius:.75rem; border-bottom-right-radius:.75rem }
      table.dataTable tbody tr:hover{ background:#F9FAFB }

      .role-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.18rem .55rem;border-radius:9999px;font-size:.72rem;font-weight:600}
      .role-admin{ background:#EEF2FF; color:#4338CA }
      .role-virt{ background:#E0F2FE; color:#075985 }

      .length-menu .dataTables_length{display:flex;align-items:center;gap:.5rem}
      .length-menu select{
        appearance:none;-webkit-appearance:none;-moz-appearance:none;
        padding:.5rem 2.1rem .5rem .7rem;border:1px solid #D1D5DB;border-radius:.5rem;background:#fff;
        background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="%236B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
        background-repeat:no-repeat;background-position:right .5rem center
      }
      .pagination-wrapper{display:flex;align-items:center;gap:1rem}
      .dataTables_paginate .paginate_button{border:1px solid #E5E7EB;border-radius:.5rem;padding:.35rem .6rem;margin:0 .2rem;background:#fff;color:#374151}
      .dataTables_paginate .paginate_button.current{background:#111827;color:#fff;border-color:#111827}
      .dataTables_paginate .paginate_button:hover{background:#F3F4F6}
      .dataTables_info{display:none}

      /* Modal: altura segura + scroll interno */
      .modal-card{ max-height: calc(100vh - 6rem); overflow:auto; }
      [x-cloak]{display:none !important;}
    </style>

    {{-- Buscador --}}
    <div class="search-wrap">
      <span class="icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </span>
      <input id="searchUsuarios" type="text" placeholder="Buscar por nombre, correo o rol..." class="w-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
      @if(!empty($q))
        <a class="clear" href="{{ route('usuarios.index') }}">Limpiar</a>
      @endif
    </div>

    <div class="card">
      <table id="tablaUsuarios" class="min-w-full text-sm">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Roles</th>
            <th>Creado</th>
            <th class="text-right">Acciones</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          @forelse ($usuarios as $usuario)
            @php($roles = $usuario->roles->pluck('name'))
            <tr>
              <td class="font-medium text-gray-900">{{ $usuario->name }}</td>
              <td>{{ $usuario->email }}</td>
              <td>
                @if($roles->isEmpty())
                  <span class="role-badge" style="background:#F3F4F6;color:#374151">Sin rol</span>
                @else
                  <div class="flex flex-wrap gap-2">
                    @foreach ($roles as $rol)
                      @php($k = strtolower($rol))
                      <span class="role-badge {{ $k==='admin' ? 'role-admin' : ($k==='virtuality' ? 'role-virt' : '') }}">
                        {{ ucwords(str_replace(['_','-'],' ', $rol)) }}
                      </span>
                    @endforeach
                  </div>
                @endif
              </td>
              <td class="text-gray-500">{{ optional($usuario->created_at)->format('d/m/Y') }}</td>
              <td class="text-right">
                <button
                  type="button"
                  class="btn-edit inline-flex items-center gap-1 rounded-md border px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                  data-id="{{ $usuario->id }}"
                  data-name="{{ e($usuario->name) }}"
                  data-email="{{ e($usuario->email) }}"
                  data-roles='@json($usuario->roles->pluck("name"))'
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M4 21h4l11-11a2.828 2.828 0 10-4-4L4 17v4z" stroke="currentColor" stroke-width="1.5"/>
                  </svg>
                  Editar
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-10 text-center text-gray-500">No hay usuarios registrados.</td>
            </tr>
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

    {{-- ===== Modal CREAR ===== --}}
    <div x-show="openCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div @click.outside="openCreate=false" class="modal-card w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
        <h3 class="mb-4 text-lg font-semibold">Agregar usuario</h3>
        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-4">
          @csrf
          <div>
            <label class="text-sm font-medium text-gray-700">Nombre</label>
            <input name="name" required autocomplete="off"
              class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Correo</label>
            <input type="email" name="email" required autocomplete="off"
              class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Contraseña</label>
              <input type="password" name="password" autocomplete="new-password"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Confirmar</label>
              <input type="password" name="password_confirmation" autocomplete="new-password"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
          </div>

          {{-- Chips de roles (crear) --}}
          <div x-data="{ r_admin:false, r_virt:false }">
            <label class="text-sm font-medium text-gray-700 block mb-1">Rol(es)</label>

            <div class="flex flex-wrap gap-2">
              <label
                :class="[
                  'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium cursor-pointer select-none transition',
                  r_admin ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
              >
                <input type="checkbox" name="roles[]" value="admin" class="sr-only" x-model="r_admin">
                <span>admin</span>
              </label>

              <label
                :class="[
                  'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium cursor-pointer select-none transition',
                  r_virt ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
              >
                <input type="checkbox" name="roles[]" value="virtuality" class="sr-only" x-model="r_virt">
                <span>virtuality</span>
              </label>
            </div>

            <p class="mt-1 text-xs text-gray-500">Puedes seleccionar uno o ambos.</p>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="openCreate=false" class="rounded-md border px-4 py-2 text-sm">Cancelar</button>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Guardar</button>
          </div>
        </form>
      </div>
    </div>

    {{-- ===== Modal EDITAR ===== --}}
    <div x-show="openEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div @click.outside="openEdit=false" class="modal-card w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
        <h3 class="mb-4 text-lg font-semibold">Editar usuario</h3>
        <form method="POST" :action="`{{ url('usuarios') }}/${editing.id}`" class="space-y-4">
          @csrf @method('PUT')
          <div>
            <label class="text-sm font-medium text-gray-700">Nombre</label>
            <input name="name" x-model="editing.name" required autocomplete="off"
              class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Correo</label>
            <input type="email" name="email" x-model="editing.email" required autocomplete="off"
              class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Nueva contraseña (opcional)</label>
              <input type="password" name="password" autocomplete="new-password"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Confirmar</label>
              <input type="password" name="password_confirmation" autocomplete="new-password"
                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
          </div>

          {{-- Chips de roles (editar) --}}
          <div
            x-data="{
              r_admin:false, r_virt:false,
              sync(){
                this.r_admin = Array.isArray(editing.roles) && editing.roles.includes('admin');
                this.r_virt  = Array.isArray(editing.roles) && editing.roles.includes('virtuality');
              }
            }"
            x-init="sync()" x-effect="sync()"
          >
            <label class="text-sm font-medium text-gray-700 block mb-1">Rol(es)</label>

            <div class="flex flex-wrap gap-2">
              <label
                :class="[
                  'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium cursor-pointer select-none transition',
                  r_admin ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
              >
                <input type="checkbox" name="roles[]" value="admin" class="sr-only" x-model="r_admin">
                <span>admin</span>
              </label>

              <label
                :class="[
                  'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium cursor-pointer select-none transition',
                  r_virt ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
              >
                <input type="checkbox" name="roles[]" value="virtuality" class="sr-only" x-model="r_virt">
                <span>virtuality</span>
              </label>
            </div>

            <p class="mt-1 text-xs text-gray-500">Deja ambos sin seleccionar para conservar roles actuales.</p>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="openEdit=false" class="rounded-md border px-4 py-2 text-sm">Cancelar</button>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Actualizar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
      $(function () {
        const table = $('#tablaUsuarios').DataTable({
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
            { targets:[0,1,2,3,4], className:'align-middle' },
            { targets:[4], orderable:false, searchable:false } // Acciones
          ]
        });

        // Buscador externo
        $('#searchUsuarios').on('input', function(){ table.search(this.value).draw(); });

        // Delegación para botón Editar
        $(document).on('click', '.btn-edit', function () {
          const $b = $(this);
          const payload = {
            id:    Number($b.data('id')),
            name:  $b.data('name'),
            email: $b.data('email'),
            roles: JSON.parse($b.attr('data-roles') || '[]')
          };
          window.dispatchEvent(new CustomEvent('open-edit', { detail: payload }));
        });

        // Precargar búsqueda con ?q=
        @if(!empty($q))
          $('#searchUsuarios').val(@json($q));
          table.search(@json($q)).draw();
        @endif
      });
    </script>
  @endpush
</x-app-layout>
