{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ sidebarOpen: true, osOpen:false, userOpen:false }" class="relative">

    {{-- TOPBAR --}}
    <div class="fixed top-0 inset-x-0 h-14 bg-[#161a1d] text-white z-40 flex items-center justify-between px-4 sm:px-6">
        {{-- Hamburguesa --}}
        <button x-on:click="sidebarOpen=!sidebarOpen" class="p-2 rounded hover:bg-white/10 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Usuario --}}
        <div class="relative">
            <button x-on:click="userOpen=!userOpen" class="p-2 rounded-full hover:bg-white/10 focus:outline-none">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12a5 5 0 100-10 5 5 0 000 10zM21 22a9 9 0 10-18 0h18z"/>
                </svg>
            </button>

            <div x-cloak x-show="userOpen" x-transition x-on:click.outside="userOpen=false"
                 class="absolute right-0 mt-2 w-56 bg-white text-gray-700 rounded-xl shadow-lg overflow-hidden z-50">
                <div class="px-4 py-3 border-b">
                    <div class="font-medium">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Salir</button>
                </form>
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <aside class="fixed z-30 top-14 inset-y-0 bg-[#1f262b] text-gray-200 transition-all"
           :class="sidebarOpen ? 'w-56' : 'w-16'">
        <div class="h-full flex flex-col">
            <nav class="flex-1 space-y-1 px-2 py-3 overflow-y-auto">
                @php
                    // Solo resaltamos Dashboard porque es la única ruta “real” ahora
                    $activeDash = request()->routeIs('dashboard')
                        ? 'bg-white/10 text-white'
                        : 'text-gray-300 hover:text-white hover:bg-white/10';
                    $linkClass = 'text-gray-300 hover:text-white hover:bg-white/10';
                @endphp

                {{-- Dashboard (ruta existente en Breeze) --}}
                <a href="{{ route('dashboard') }}"
                   class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $activeDash }}">
                    <svg class="w-5 h-5 opacity-70 group-hover:opacity-100" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="truncate">Inicio</span>
                </a>

                {{-- Órdenes de servicio (submenu – enlaces vacíos por ahora) --}}
                <div>
                    <button x-on:click="osOpen=!osOpen"
                            class="w-full flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                        <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5a2 2 0 00-2 2v3h18V5a2 2 0 00-2-2zM3 19a2 2 0 002 2h14a2 2 0 002-2v-8H3v8z"/>
                        </svg>
                        <span x-show="sidebarOpen" class="flex-1 text-left">Órdenes de servicio</span>
                        <svg x-show="sidebarOpen" :class="osOpen ? 'rotate-180' : ''"
                             class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5H7z"/>
                        </svg>
                    </button>
                    <div x-show="osOpen" x-collapse class="pl-8 pr-2">
                        <a href="#" class="block px-2 py-1.5 rounded {{ $linkClass }}">• Pendientes</a>
                        <a href="#" class="block px-2 py-1.5 rounded {{ $linkClass }}">• En proceso</a>
                        <a href="#" class="block px-2 py-1.5 rounded {{ $linkClass }}">• Resueltas</a>
                    </div>
                </div>

                {{-- Enlaces placeholder (sin rutas) --}}
                <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M7 3h10v2H7zM4 7h16v13H4z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Equipos del cliente</span>
                </a>

                <a href="{{ route('clientes.seleccion') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M5 3h14v18H5zM8 7h8v2H8zM8 11h8v2H8zM8 15h8v2H8z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Solicitudes de cliente</span>
                </a>

                <a href="{{ route('clientes.index') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05C16.67 13.84 18 14.79 18 16.5V19h6v-2.5c0-2.33-4.67-3.5-8-3.5z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Clientes</span>
                </a>

                @role('admin')
                <a href="{{ route('usuarios.index') }}"
                   class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('usuarios.*') ? 'bg-white/10 text-white' : $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm-3 5c-2.33 0-5 1.17-5 3.5V21h10v-1.5c0-2.33-2.67-3.5-5-3.5zm11 0c-.52 0-1.02.05-1.48.13 1.55.7 2.48 1.77 2.48 3.37V21h6v-1.5c0-2.33-3.67-3.5-7-3.5z"/>
                    </svg>
                    <span x-show="sidebarOpen" class="truncate">Usuarios</span>
                </a>
                @endrole

                <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5a2 2 0 00-2 2v14l4-4h12a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Checklist-plantillas</span>
                </a>

                <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12l-4 4-4-4 4-4 4 4zM2 12l4 4 4-4-4-4-4 4z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Mantenimientos preventivos</span>
                </a>

                <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12a5 5 0 100-10 5 5 0 000 10zM2 22a10 10 0 1120 0H2z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Empleados-TI</span>
                </a>

                <a href="#" class="group flex items-center gap-3 rounded-lg px-3 py-2 {{ $linkClass }}">
                    <svg class="w-5 h-5 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M4 4h16v16H4zM8 8h8v2H8zM8 12h8v2H8z"/></svg>
                    <span x-show="sidebarOpen" class="truncate">Folios</span>
                </a>
            </nav>
        </div>
    </aside>

    {{-- SEPARADOR para que el contenido no quede bajo el sidebar/topbar --}}
    <div class="pt-14" :style="sidebarOpen ? 'padding-left:14rem' : 'padding-left:4rem'"></div>
</nav>
