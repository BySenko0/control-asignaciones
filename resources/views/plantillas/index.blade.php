<x-app-layout>
  @push('styles')
  <style>[x-cloak]{display:none!important}</style>
  @endpush

  <div x-data="{ modal:false, mode:'create', form:{id:null,nombre:'',descripcion:''} }"
       class="mx-auto max-w-7xl space-y-6">

    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-800">Plantillas de servicios</h1>
        <p class="text-sm text-gray-500">Crea plantillas (ej. “Cambio de memoria”) y gestiona sus pasos.</p>
      </div>
      <button @click="mode='create';form={id:null,nombre:'',descripcion:''};modal=true"
              class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">+ Nueva plantilla</button>
    </div>

    {{-- buscador --}}
    <form method="GET" action="{{ route('plantillas.index') }}">
      <div class="relative">
        <input name="q" value="{{ $q ?? '' }}" placeholder="Buscar por nombre o descripción…"
               class="w-full rounded-xl border border-gray-300 bg-white pl-3 pr-3 py-2.5 focus:ring-2 focus:ring-indigo-500" />
      </div>
    </form>

    {{-- lista --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="rounded-t-2xl bg-[#1f262b] px-4 py-2 text-center text-sm font-medium text-gray-100">Plantillas</div>
      <div class="p-4 space-y-3">
        @forelse($plantillas as $p)
          <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 ring-1 ring-gray-200">
            <div class="flex-1">
              <div class="font-medium text-gray-900">{{ $p->nombre }}</div>
              <div class="text-sm text-gray-500">{{ $p->descripcion }}</div>
            </div>
            <a href="{{ route('plantillas.pasos', $p) }}"
               class="rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-100">Ir</a>
            <button @click="mode='edit';form={id:{{ $p->id }},nombre:@js($p->nombre),descripcion:@js($p->descripcion)};modal=true"
                    class="rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-sm hover:bg-gray-100">Editar</button>
            <form method="POST" action="{{ route('plantillas.destroy',$p) }}"
                  onsubmit="return confirm('¿Eliminar esta plantilla y sus pasos?')">
              @csrf @method('DELETE')
              <button class="rounded-xl border border-red-300 text-red-700 px-3 py-1.5 text-sm hover:bg-red-50">Borrar</button>
            </form>
          </div>
        @empty
          <div class="p-6 text-center text-gray-500">No hay plantillas aún.</div>
        @endforelse
      </div>
    </div>

    @if(session('ok'))
      <div class="rounded-xl bg-green-50 text-green-800 px-4 py-2">{{ session('ok') }}</div>
    @endif
    @error('nombre') <div class="rounded-xl bg-red-50 text-red-700 px-4 py-2">{{ $message }}</div> @enderror

    {{-- modal crear/editar --}}
    <div x-cloak x-show="modal" class="fixed inset-0 z-50 grid place-items-center">
      <div class="absolute inset-0 bg-black/40" @click="modal=false"></div>
      <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold" x-text="mode==='create'?'Nueva plantilla':'Editar plantilla'"></h2>
          <button class="p-2 rounded hover:bg-gray-100" @click="modal=false">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <form :action="mode==='create'? @js(route('plantillas.store')) : @js(route('plantillas.update','__ID__')).replace('__ID__', form.id)"
              method="POST" class="space-y-3">
          @csrf
          <template x-if="mode==='edit'"><input type="hidden" name="_method" value="PUT"></template>
          <div>
            <label class="text-sm text-gray-700">Nombre</label>
            <input name="nombre" x-model="form.nombre" class="mt-1 w-full rounded-xl border-gray-300" required>
          </div>
          <div>
            <label class="text-sm text-gray-700">Descripción</label>
            <input name="descripcion" x-model="form.descripcion" class="mt-1 w-full rounded-xl border-gray-300">
          </div>
          <div class="flex justify-end gap-2 pt-1">
            <button type="button" @click="modal=false" class="rounded-xl border border-gray-300 px-4 py-2 hover:bg-gray-100">Cancelar</button>
            <button class="rounded-xl bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-700">Guardar</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</x-app-layout>
