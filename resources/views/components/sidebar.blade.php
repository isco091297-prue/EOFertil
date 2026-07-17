<aside class="w-48 bg-green-800 text-white min-h-screen flex flex-col">
    <div class="p-4 border-b border-green-700">
        <x-logo />

    </div>

    <nav class="flex-1 mt-4 px-3 space-y-1">

        <x-menu-item route="dashboard" icon="🏠" label="Dashboard" />

        <div class="pt-5 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">

            Organización

        </div>

        <x-menu-item route="warehouses.index" icon="🏢" label="Almacenes" />

        <x-menu-item route="zones.index" icon="🗺️" label="Zonas" />

        <x-menu-item route="branches.index" icon="🏪" label="Sucursales" />

        <x-menu-item route="users.index" icon="👥" label="Usuarios" />

        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">

            Catálogo Técnico

        </div>

        <x-menu-item route="crops.index" icon="🌱" label="Cultivos" />

        <x-menu-item route="#" icon="📂" label="Categorías" />

        <x-menu-item route="#" icon="⚠️" label="Problemas" />

        <x-menu-item route="#" icon="🧪" label="Aplicaciones" />

        <x-menu-item route="#" icon="🧴" label="Productos" />

        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">

            Comercial

        </div>

        <x-menu-item route="#" icon="💰" label="Ventas" />

        <x-menu-item route="#" icon="🎁" label="Campañas" />

        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">

            Sistema

        </div>

        <x-menu-item route="#" icon="⚙️" label="Configuración" />

    </nav>

</aside>
