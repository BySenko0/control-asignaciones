<x-app-layout>
    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Usuarios</h1>
            <p class="text-sm text-gray-500">Gestiona los usuarios con acceso al sistema.</p>
        </div>

        <form method="GET" action="{{ route('usuarios.index') }}">
            <div class="relative">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                       placeholder="Buscar por nombre, correo o rol..."
                       class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-4 pr-12 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500" />
                @if(!empty($q))
                    <a href="{{ route('usuarios.index') }}"
                       class="absolute inset-y-0 right-0 flex items-center px-3 text-sm text-gray-500 hover:text-gray-700">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3">Creado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse ($usuarios as $usuario)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $usuario->name }}</td>
                            <td class="px-4 py-3">{{ $usuario->email }}</td>
                            <td class="px-4 py-3">
                                @php($roles = $usuario->roles->pluck('name')->map(fn($name) => \Illuminate\Support\Str::headline($name)))
                                @if($roles->isEmpty())
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">Sin rol</span>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($roles as $rol)
                                            <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{{ $rol }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ optional($usuario->created_at)->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-gray-500">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-4 py-3">
                {{ $usuarios->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
