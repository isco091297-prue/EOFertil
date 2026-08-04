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

        <x-menu-item route="categories.index" icon="📂" label="Categorías" />

        <x-menu-item route="brands.index" icon="🏷️" label="Marcas" />

        <x-menu-item route="products.index" icon="🧴" label="Productos" />

        <x-menu-item route="active-ingredients.index" icon="🧬" label="Ingredientes Activos" />

        <x-menu-item route="problems.index" icon="⚠️" label="Problemas" />

        <x-menu-item route="protocols.index" icon="🧪" label="Recetas" />

        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">
            Incentivos
        </div>

        <x-menu-item route="cashback-campaigns.index" icon="💵" label="Campañas Cashback" />

        <x-menu-item route="cashback-campaigns.index" icon="🎁" label="Campañas Cashback" />
        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">
            Comercial
        </div>

        <x-menu-item route="#" icon="💰" label="Ventas" />

        <div class="pt-6 pb-2 text-xs uppercase tracking-widest text-green-300 font-bold">
            Sistema
        </div>

        <x-menu-item route="#" icon="⚙️" label="Configuración" />

    </nav>

</aside>
