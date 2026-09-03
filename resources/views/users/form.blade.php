@csrf

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="lg:col-span-2">

        <h2 class="text-xl font-bold border-b pb-2">

            Datos Personales

        </h2>

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Nombres <span class="text-red-600">*</span>

        </label>

        <x-input type="text" name="first_name" value="{{ old('first_name', $user->first_name ?? '') }}" required />

        @error('first_name')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Apellidos <span class="text-red-600">*</span>

        </label>

        <x-input type="text" name="last_name" value="{{ old('last_name', $user->last_name ?? '') }}" required />

        @error('last_name')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Cédula <span class="text-red-600">*</span>

        </label>

        <x-input id="identification" type="text" maxlength="10" name="identification"
            value="{{ old('identification', $user->identification ?? '') }}" required />

        <p id="identification-message" class="text-sm mt-1"></p>

        @error('identification')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Teléfono <span class="text-red-600">*</span>

        </label>

        <x-input id="phone" type="text" maxlength="10" name="phone"
            value="{{ old('phone', $user->phone ?? '') }}" required />

        <p id="phone-message" class="text-sm mt-1"></p>

        @error('phone')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div class="lg:col-span-2 mt-6">

        <h2 class="text-xl font-bold border-b pb-2">

            Acceso

        </h2>

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Rol <span class="text-red-600">*</span>

        </label>

        <select id="role_id" name="role_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">

            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '') == $role->id)>

                    {{ $role->name }}

                </option>
            @endforeach

        </select>

        @error('role_id')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Usuario <span class="text-red-600">*</span>

        </label>

        <x-input id="username" type="text" name="username" value="{{ old('username', $user->username ?? '') }}"
            required />

        @error('username')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Correo Electrónico

        </label>

        <x-input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" />

        @error('email')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Estado

        </label>

        <select name="is_active" class="w-full rounded-xl border border-gray-300 px-4 py-3">

            <option value="1" @selected(old('is_active', $user->is_active ?? 1) == 1)>

                Activo

            </option>

            <option value="0" @selected(old('is_active', $user->is_active ?? 1) == 0)>

                Inactivo

            </option>

        </select>

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Contraseña

            @if (isset($user))
                <span class="text-gray-500 text-sm">

                    (Dejar vacío para conservar la actual)

                </span>
            @endif

        </label>

        <x-input id="password" type="password" name="password" />

        @error('password')
            <p class="mt-1 text-sm text-red-600">

                {{ $message }}

            </p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">

            Confirmar Contraseña

        </label>

        <x-input id="password_confirmation" type="password" name="password_confirmation" />

        <p id="password-message" class="text-sm mt-1 font-semibold">

        </p>

    </div>
    {{-- ========================= --}}
    {{-- ORGANIZACIÓN --}}
    {{-- ========================= --}}

    <div id="organization-section" class="lg:col-span-2 mt-8">

        <h2 class="text-xl font-bold border-b pb-2 mb-6">

            Organización

        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div>

                <label class="block mb-2 font-semibold">

                    Almacén <span class="text-red-600">*</span>

                </label>

                <select name="warehouse_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">

                        Seleccione...

                    </option>

                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $user->warehouse_id ?? '') == $warehouse->id)>

                            {{ $warehouse->name }}

                        </option>
                    @endforeach

                </select>

                @error('warehouse_id')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            <div>

                <label class="block mb-2 font-semibold">

                    Zona <span class="text-red-600">*</span>

                </label>

                <select name="zone_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">

                        Seleccione...

                    </option>

                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @selected(old('zone_id', $user->zone_id ?? '') == $zone->id)>

                            {{ $zone->name }}

                        </option>
                    @endforeach

                </select>

                @error('zone_id')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            <div>

                <label class="block mb-2 font-semibold">

                    Sucursal <span class="text-red-600">*</span>

                </label>

                <select name="branch_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">

                        Seleccione...

                    </option>

                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id ?? '') == $branch->id)>

                            {{ $branch->name }}

                        </option>
                    @endforeach

                </select>

                @error('branch_id')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

        </div>

    </div>

    {{-- ========================= --}}
    {{-- DATOS BANCARIOS --}}
    {{-- ========================= --}}

    <div id="bank-section" class="lg:col-span-2 mt-8">

        <h2 class="text-xl font-bold border-b pb-2 mb-6">

            Datos Bancarios

        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div>

                <label class="block mb-2 font-semibold">

                    Banco

                </label>

                <select name="bank" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">Seleccione...</option>

                    @php
                        $banks = [
                            'Banco Pichincha',
                            'Produbanco',
                            'Banco Guayaquil',
                            'Banco del Pacífico',
                            'Banco Internacional',
                            'Banco Bolivariano',
                            'Banco del Austro',
                            'Cooperativa JEP',
                            'Otro',
                        ];
                    @endphp

                    @foreach ($banks as $bank)
                        <option value="{{ $bank }}" @selected(old('bank', $user->bank ?? '') == $bank)>

                            {{ $bank }}

                        </option>
                    @endforeach

                </select>

                @error('bank')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            <div>

                <label class="block mb-2 font-semibold">

                    Tipo de Cuenta

                </label>

                <select name="account_type" class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">

                        Seleccione...

                    </option>

                    <option value="Ahorros" @selected(old('account_type', $user->account_type ?? '') == 'Ahorros')>

                        Cuenta de Ahorros

                    </option>

                    <option value="Corriente" @selected(old('account_type', $user->account_type ?? '') == 'Corriente')>

                        Cuenta Corriente

                    </option>

                </select>

                @error('account_type')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

            <div>

                <label class="block mb-2 font-semibold">

                    Número de Cuenta

                </label>

                <x-input id="account_number" type="text" name="account_number" maxlength="20"
                    value="{{ old('account_number', $user->account_number ?? '') }}" />
                @error('account_number')
                    <p class="mt-1 text-sm text-red-600">

                        {{ $message }}

                    </p>
                @enderror

            </div>

        </div>

    </div>

    <div class="lg:col-span-2 mt-10 flex justify-end gap-4">

        <a href="{{ route('users.index') }}" class="border border-gray-300 px-6 py-3 rounded-xl hover:bg-gray-100">

            Cancelar

        </a>

        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-8 py-3 rounded-xl">

            Guardar

        </button>

    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const role = document.getElementById("role_id");

        const organization = document.getElementById("organization-section");

        const bank = document.getElementById("bank-section");

        function toggleSections() {

            const roleId = parseInt(role.value);

            const isPerchero = roleId === 2;
            const isGuia = roleId === 3;

            // Organización:
            // Perchero y Guía
            organization.style.display =
                (isPerchero || isGuia) ? "block" : "none";

            // Datos bancarios:
            // Solo Perchero
            bank.style.display =
                isPerchero ? "block" : "none";
        }

        role.addEventListener("change", toggleSections);

        toggleSections();

        document.getElementById("identification").addEventListener("input", function() {

            this.value = this.value.replace(/\D/g, '');

        });

        document.getElementById("phone").addEventListener("input", function() {

            this.value = this.value.replace(/\D/g, '');

            const msg = document.getElementById("phone-message");

            if (this.value.length > 0) {

                if (!this.value.startsWith("09")) {

                    msg.innerHTML = "❌ Debe iniciar con 09";

                    msg.className = "text-red-600 text-sm mt-1";

                } else if (this.value.length < 10) {

                    msg.innerHTML = "❌ Debe tener 10 dígitos.";

                    msg.className = "text-red-600 text-sm mt-1";

                } else {

                    msg.innerHTML = "✅ Número válido.";

                    msg.className = "text-green-600 text-sm mt-1";

                }

            } else {

                msg.innerHTML = "";

            }

        });

        document.getElementById("account_number").addEventListener("input", function() {

            this.value = this.value.replace(/\D/g, '');

        });

        const identification = document.getElementById("identification");

        const identificationMessage =
            document.getElementById("identification-message");

        identification.addEventListener("input", function() {

            this.value = this.value.replace(/\D/g, '');

            if (this.value.length === 0) {

                identificationMessage.innerHTML = "";

            } else if (this.value.length < 10) {

                identificationMessage.innerHTML =
                    "❌ La cédula debe tener 10 dígitos.";

                identificationMessage.className =
                    "text-red-600 text-sm mt-1";

            } else {

                identificationMessage.innerHTML =
                    "✅ Cédula válida.";

                identificationMessage.className =
                    "text-green-600 text-sm mt-1";

            }

        });

        const password = document.getElementById("password");

        const confirm =
            document.getElementById("password_confirmation");

        const message =
            document.getElementById("password-message");

        function validatePassword() {

            if (password.value === "" && confirm.value === "") {

                message.innerHTML = "";

                return;

            }

            if (password.value.length < 8) {

                message.innerHTML =
                    "❌ La contraseña debe tener mínimo 8 caracteres.";

                message.className =
                    "text-red-600 text-sm mt-2 font-semibold";

            } else if (password.value === confirm.value) {

                message.innerHTML =
                    "✅ Las contraseñas coinciden.";

                message.className =
                    "text-green-600 text-sm mt-2 font-semibold";

            } else {

                message.innerHTML =
                    "❌ Las contraseñas no coinciden.";

                message.className =
                    "text-red-600 text-sm mt-2 font-semibold";
            }
        }

        password.addEventListener("keyup", validatePassword);

        confirm.addEventListener("keyup", validatePassword);

    });
</script>
