<x-app-layout>
  <div class="mx-auto max-w-7xl space-y-6">
    <div>
      <h1 class="text-2xl font-semibold text-gray-800">Usuarios</h1>
      <p class="text-sm text-gray-500">Gestiona los usuarios con acceso al sistema.</p>
    </div>

    {{-- DataTables + estilos --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
      /* --- wrapper --- */
      .card { border:1px solid #E5E7EB;border-radius:1rem;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04) }

      /* --- buscador --- */
      .search-wrap{position:relative}
      .search-wrap .icon{position:absolute;inset-inline-start:.9rem;inset-block:0;display:flex;align-items:center;color:#9CA3AF}
      .search-wrap input{height:2.75rem;padding:.625rem .75rem .625rem 2.5rem;border:1px solid #D1D5DB;border-radius:.75rem}
      .search-wrap a.clear{position:absolute;inset-inline-end:.5rem;inset-block:0;display:flex;align-items:center;padding-inline:.5rem;color:#6B7280}

      /* --- tabla “card rows” --- */
      table.dataTable { border-collapse:separate; border-spacing:0 10px !important; background:transparent }
      table.dataTable thead th{
        position:sticky; top:0; z-index:10;
        background:#F9FAFB !important; color:#6B7280; font-weight:600; text-transform:uppercase; font-size:.72rem;
        padding:.9rem 1rem; border:none !important; box-shadow:inset 0 -1px 0 #E5E7EB;
      }
      table.dataTable tbody tr{ background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.06); border-radius:.75rem }
      table.dataTable tbody tr td{
        padding:1rem 1rem; border-top:1px solid #F3F4F6; border-bottom:1px solid #F3F4F6;
      }
      table.dataTable tbody tr td:first-child{ border-left:1px solid #F3F4F6; border-top-left-radius:.75rem; border-bottom-left-radius:.75rem }
      table.dataTable tbody tr td:last-child{ border-right:1px solid #F3F4F6; border-top-right-radius:.75rem; border-bottom-right-radius:.75rem }
      table.dataTable tbody tr:hover{ background:#F9FAFB }

      /* --- badges rol --- */
      .role-badge{
        display:inline-flex;align-items:center;gap:.4rem;
        padding:.18rem .55rem;border-radius:9999px;font-size:.72rem;font-weight:600
      }
      .role-admin{ background:#EEF2FF; color:#4338CA }
      .role-virt{ background:#E0F2FE; color:#075985 }

      /* --- length + paginación --- */
      .length-menu .dataTables_length{display:flex;align-items:center;gap:.5rem}
      .length-menu select{
        appearance:none;-webkit-appearance:none;-moz-appearance:none;
        padding:.5rem 2.1rem .5rem .7rem;border:1px solid #D1D5DB;border-radius:.5rem;background:#fff;
        background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="%236B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
        background-repeat:no-repeat;background-position:right .5rem center
      }
      .pagination-wrapper{display:flex;align-items:center;gap:1rem}
      .dataTables_paginate .paginate_button{
        border:1px solid #E5E7EB;border-radius:.5rem;padding:.35rem .6rem;margin:0 .2rem;background:#fff;color:#374151
      }
      .dataTables_paginate .paginate_button.current{background:#111827;color:#fff;border-color:#111827}
      .dataTables_paginate .paginate_button:hover{background:#F3F4F6}
      .dataTables_info{display:none}
    </style>

    {{-- Buscador --}}
    <div class="search-wrap">
      <span class="icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
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
                        {{ \Illuminate\Support\Str::headline($rol) }}
                      </span>
                    @endforeach
                  </div>
                @endif
              </td>
              <td class="text-gray-500">{{ optional($usuario->created_at)->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-10 text-center text-gray-500">No hay usuarios registrados.</td>
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
  </div>

  @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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
            { targets:[0,1,2,3], className:'align-middle' }
          ]
        });

        // search externo
        $('#searchUsuarios').on('input', function(){ table.search(this.value).draw(); });

        // precargar con ?q=
        @if(!empty($q))
          $('#searchUsuarios').val(@json($q));
          table.search(@json($q)).draw();
        @endif
      });
    </script>
  @endpush
</x-app-layout>
