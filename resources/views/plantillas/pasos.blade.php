<x-app-layout>
  @push('styles')
  <style>[x-cloak]{display:none!important}</style>
  @endpush

  <div x-data="{
        editModal:false,
        editing:{ id:null, titulo:'' },
        openEdit(p){ this.editing={ id:p.id, titulo:p.titulo }; this.editModal=true; }
      }"
      class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-800">{{ $plantilla->nombre }}</h1>
        <p class="text-sm text-gray-500">Máximo 15 pasos por plantilla.</p>
      </div>
      <a href="{{ route('plantillas.index') }}"
         class="rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
        ← Volver
      </a>
    </div>

    {{-- agregar paso --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4">
      <form method="POST" action="{{ route('plantillas.pasos.store',$plantilla) }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        @csrf
        <input name="titulo" placeholder="Nuevo paso (ej. Verificar si hay material disponible)"
               class="flex-1 rounded-xl border border-gray-300 px-3 py-2" required>
        <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700"
                @if($plantilla->pasos->count()>=15) disabled @endif>
          Agregar
        </button>
      </form>
      @error('titulo') <div class="mt-2 rounded-xl bg-red-50 text-red-700 px-4 py-2">{{ $message }}</div> @enderror
      @if($plantilla->pasos->count()>=15)
        <div class="mt-2 text-sm text-red-600">Has llegado al límite de 15 pasos.</div>
      @endif
    </div>

    {{-- listado de pasos --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="rounded-t-2xl bg-[#1f262b] px-4 py-2 text-center text-sm font-medium text-gray-100">
        Pasos ({{ $plantilla->pasos->count() }})
      </div>

      {{-- Tabla desktop --}}
      <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr>
              <th class="px-4 py-3 text-left font-semibold">Orden</th>
              <th class="px-4 py-3 text-left font-semibold">Paso</th>
              <th class="px-4 py-3 text-right font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($plantilla->pasos as $paso)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                  <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">
                    {{ $paso->numero }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-800">{{ $paso->titulo }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-end gap-2">
                    <form method="POST" action="{{ route('plantillas.pasos.mover', [$plantilla,$paso]) }}">
                      @csrf
                      <input type="hidden" name="dir" value="up">
                      <button class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-100" {{ $paso->numero==1 ? 'disabled' : '' }}>↑</button>
                    </form>
                    <form method="POST" action="{{ route('plantillas.pasos.mover', [$plantilla,$paso]) }}">
                      @csrf
                      <input type="hidden" name="dir" value="down">
                      <button class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-100"
                              {{ $paso->numero==$plantilla->pasos->max('numero') ? 'disabled' : '' }}>↓</button>
                    </form>
                    <button @click="openEdit(@js(['id'=>$paso->id,'titulo'=>$paso->titulo]))"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-100">Editar</button>
                    <form method="POST" action="{{ route('plantillas.pasos.destroy', [$plantilla,$paso]) }}"
                          onsubmit="return confirm('¿Eliminar este paso?')">
                      @csrf @method('DELETE')
                      <button class="rounded-lg border border-red-300 text-red-700 px-3 py-1.5 text-sm hover:bg-red-50">Borrar</button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Aún no hay pasos para esta plantilla.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Cards móvil --}}
      <div class="md:hidden p-4 space-y-3">
        @forelse($plantilla->pasos as $paso)
          <div class="rounded-xl bg-gray-50 px-4 py-3 ring-1 ring-gray-200">
            <div class="flex items-start gap-3">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 text-white text-sm font-semibold">
                {{ $paso->numero }}
              </span>
              <div class="flex-1 text-gray-800">{{ $paso->titulo }}</div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
              <form method="POST" action="{{ route('plantillas.pasos.mover', [$plantilla,$paso]) }}">
                @csrf
                <input type="hidden" name="dir" value="up">
                <button class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-100" {{ $paso->numero==1 ? 'disabled' : '' }}>↑</button>
              </form>
              <form method="POST" action="{{ route('plantillas.pasos.mover', [$plantilla,$paso]) }}">
                @csrf
                <input type="hidden" name="dir" value="down">
                <button class="rounded-lg border px-2 py-1 text-sm hover:bg-gray-100"
                        {{ $paso->numero==$plantilla->pasos->max('numero') ? 'disabled' : '' }}>↓</button>
              </form>
              <button @click="openEdit(@js(['id'=>$paso->id,'titulo'=>$paso->titulo]))"
                      class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-100">Editar</button>
              <form method="POST" action="{{ route('plantillas.pasos.destroy', [$plantilla,$paso]) }}"
                    onsubmit="return confirm('¿Eliminar este paso?')">
                @csrf @method('DELETE')
                <button class="rounded-lg border border-red-300 text-red-700 px-3 py-1.5 text-sm hover:bg-red-50">Borrar</button>
              </form>
            </div>
          </div>
        @empty
          <div class="p-6 text-center text-gray-500">Aún no hay pasos para esta plantilla.</div>
        @endforelse
      </div>
    </div>

    @if(session('ok')) <div class="rounded-xl bg-green-50 text-green-800 px-4 py-2">{{ session('ok') }}</div> @endif

    {{-- modal editar paso --}}
    <div x-cloak x-show="editModal" class="fixed inset-0 z-50 grid place-items-center">
      <div class="absolute inset-0 bg-black/40" @click="editModal=false"></div>
      <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Editar paso</h2>
          <button class="p-2 rounded hover:bg-gray-100" @click="editModal=false">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <form :action="@js(route('plantillas.pasos.update',[$plantilla,'__ID__'])).replace('__ID__', editing.id)"
              method="POST" class="space-y-3">
          @csrf @method('PUT')
          <div>
            <label class="text-sm text-gray-700">Título del paso</label>
            <input name="titulo" x-model="editing.titulo" class="mt-1 w-full rounded-xl border-gray-300" required>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="editModal=false" class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-100">Cancelar</button>
            <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Guardar</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</x-app-layout>
