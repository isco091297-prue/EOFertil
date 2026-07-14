<aside class="w-72 bg-green-800 text-white min-h-screen">

    <div class="p-6 border-b border-green-700">
        <x-logo />
    </div>

    <nav class="mt-6 px-4 space-y-2">

        <x-menu-item route="dashboard" icon="🏠" label="Dashboard" />

        <div class="pt-4 text-xs uppercase text-green-300 font-bold">
            Organización
        </div>

        <x-menu-item route="warehouses.index" icon="🏢" label="Almacenes" />

        <x-menu-item route="zones.index" icon="🗺️" label="Zonas" />

        <x-menu-item route="branches.index" icon="🏪" label="Sucursales" />
        <x-menu-item route="users.index" icon="👥" label="Usuarios" />
        <div class="pt-4 text-xs uppercase text-green-300 font-bold">
            Catálogo Técnico
        </div>

        <x-menu-item route="#" icon="🌱" label="Cultivos" />

        <x-menu-item route="#" icon="📂" label="Categorías" />

        <x-menu-item route="#" icon="⚠️" label="Problemas" />

        <x-menu-item route="#" icon="🧪" label="Aplicaciones" />

        <x-menu-item route="#" icon="🧴" label="Productos" />

        <div class="pt-4 text-xs uppercase text-green-300 font-bold">
            Comercial
        </div>

        <x-menu-item route="#" icon="💰" label="Ventas" />

        <x-menu-item route="#" icon="🎁" label="Campañas" />

        <div class="pt-4 text-xs uppercase text-green-300 font-bold">
            Sistema
        </div>

        <x-menu-item route="#" icon="⚙️" label="Configuración" />

    </nav>

</aside>
