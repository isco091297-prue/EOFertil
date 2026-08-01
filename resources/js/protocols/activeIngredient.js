import {
    loadActiveIngredients,
    loadActiveIngredientProducts
} from "./ajax";

import {
    renumberApplications
} from "./helpers";

/**
 * Agregar ingrediente activo
 * a una aplicación.
 *
 * @param {HTMLElement} applicationCard
 * @param {Object|null} activeIngredientData
 */
export async function addActiveIngredient(
    applicationCard,
    activeIngredientData = null
) {

    const template =
        document.getElementById(
            "active-ingredient-template"
        );

    if (!template || !applicationCard) {
        return;
    }

    const container =
        applicationCard.querySelector(
            ".active-ingredients-container"
        );

    if (!container) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const card =
        clone.querySelector(
            ".active-ingredient-card"
        );

    if (!card) {
        return;
    }

    container.appendChild(card);

    const newCard =
        container.lastElementChild;

    const activeIngredientSelect =
        newCard.querySelector(
            ".active-ingredient-select"
        );

    /*
    |--------------------------------------------------------------------------
    | Cargar ingredientes activos
    |--------------------------------------------------------------------------
    */

    await loadActiveIngredients(
        activeIngredientSelect,
        activeIngredientData?.active_ingredient_id ?? null
    );

    /*
    |--------------------------------------------------------------------------
    | Modo edición
    |--------------------------------------------------------------------------
    |
    | Si estamos editando un protocolo, cargamos exactamente
    | los productos que ya estaban guardados con sus dosis.
    |
    */

    if (
        activeIngredientData &&
        Array.isArray(activeIngredientData.products) &&
        activeIngredientData.products.length
    ) {

        for (const productData of activeIngredientData.products) {

            await addActiveIngredientProduct(
                newCard,
                productData
            );

        }

    }

    renumberApplications();

}

/**
 * Agregar una fila de producto recomendado.
 *
 * Esta función se utiliza principalmente al editar
 * un protocolo existente.
 *
 * @param {HTMLElement} activeIngredientCard
 * @param {Object|null} productData
 */
export async function addActiveIngredientProduct(
    activeIngredientCard,
    productData = null
) {

    const template =
        document.getElementById(
            "active-ingredient-product-template"
        );

    if (!template || !activeIngredientCard) {
        return;
    }

    const container =
        activeIngredientCard.querySelector(
            ".active-ingredient-products-container"
        );

    if (!container) {
        return;
    }

    const activeIngredientSelect =
        activeIngredientCard.querySelector(
            ".active-ingredient-select"
        );

    const activeIngredientId =
        activeIngredientSelect?.value;

    if (!activeIngredientId) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const row =
        clone.querySelector(
            ".active-ingredient-product-row"
        );

    if (!row) {
        return;
    }

    container.appendChild(row);

    const newRow =
        container.lastElementChild;

    const productSelect =
        newRow.querySelector(
            ".active-ingredient-product-select"
        );

    /*
    |--------------------------------------------------------------------------
    | Cargar productos vinculados al ingrediente
    |--------------------------------------------------------------------------
    */

    await loadActiveIngredientProducts(
        productSelect,
        activeIngredientId,
        productData?.product_id ?? null
    );

    /*
    |--------------------------------------------------------------------------
    | Datos existentes al editar
    |--------------------------------------------------------------------------
    */

    if (productData) {

        const dose =
            newRow.querySelector(
                ".active-ingredient-product-dose"
            );

        const unit =
            newRow.querySelector(
                ".active-ingredient-product-unit"
            );

        const applicationBase =
            newRow.querySelector(
                ".active-ingredient-product-application-base"
            );

        if (dose) {
            dose.value =
                productData.dose ?? "";
        }

        if (unit) {
            unit.value =
                productData.unit ?? "";
        }

        if (applicationBase) {
            applicationBase.value =
                productData.application_base ?? "";
        }

    }

    renumberApplications();

}

/**
 * Cargar AUTOMÁTICAMENTE todos los productos
 * vinculados a un ingrediente activo.
 *
 * @param {HTMLElement} activeIngredientCard
 */
async function loadLinkedProducts(
    activeIngredientCard
) {

    if (!activeIngredientCard) {
        return;
    }

    const activeIngredientSelect =
        activeIngredientCard.querySelector(
            ".active-ingredient-select"
        );

    const container =
        activeIngredientCard.querySelector(
            ".active-ingredient-products-container"
        );

    if (
        !activeIngredientSelect ||
        !container
    ) {
        return;
    }

    const activeIngredientId =
        activeIngredientSelect.value;

    /*
    |--------------------------------------------------------------------------
    | Limpiar productos anteriores
    |--------------------------------------------------------------------------
    */

    container.innerHTML = "";

    if (!activeIngredientId) {

        renumberApplications();

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar productos vinculados
    |--------------------------------------------------------------------------
    */

    const temporarySelect =
        document.createElement("select");

    await loadActiveIngredientProducts(
        temporarySelect,
        activeIngredientId
    );

    /*
    |--------------------------------------------------------------------------
    | Obtener productos devueltos
    |--------------------------------------------------------------------------
    |
    | loadActiveIngredientProducts llena un select con los productos.
    | Reutilizamos esos resultados para generar automáticamente
    | una fila por producto.
    |
    */

    const productOptions =
        Array.from(
            temporarySelect.options
        ).filter(
            option => option.value !== ""
        );

    /*
    |--------------------------------------------------------------------------
    | Crear una fila por cada producto vinculado
    |--------------------------------------------------------------------------
    */

    for (const option of productOptions) {

        await createLinkedProductRow(
            activeIngredientCard,
            option.value,
            option.textContent
        );

    }

    renumberApplications();

}

/**
 * Crear una fila para un producto vinculado.
 *
 * @param {HTMLElement} activeIngredientCard
 * @param {string|number} productId
 * @param {string} productText
 */
async function createLinkedProductRow(
    activeIngredientCard,
    productId,
    productText
) {

    const template =
        document.getElementById(
            "active-ingredient-product-template"
        );

    if (!template) {
        return;
    }

    const container =
        activeIngredientCard.querySelector(
            ".active-ingredient-products-container"
        );

    if (!container) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const row =
        clone.querySelector(
            ".active-ingredient-product-row"
        );

    if (!row) {
        return;
    }

    container.appendChild(row);

    const newRow =
        container.lastElementChild;

    const productSelect =
        newRow.querySelector(
            ".active-ingredient-product-select"
        );

    /*
    |--------------------------------------------------------------------------
    | El producto ya viene definido por el ingrediente activo
    |--------------------------------------------------------------------------
    */

    if (productSelect) {

        productSelect.innerHTML = "";

        const option =
            document.createElement("option");

        option.value =
            productId;

        option.textContent =
            productText;

        option.selected =
            true;

        productSelect.appendChild(option);

        /*
        |--------------------------------------------------------------------------
        | No permitir cambiar manualmente el producto
        |--------------------------------------------------------------------------
        */

        productSelect.classList.add(
            "bg-gray-100"
        );

        productSelect.style.pointerEvents =
            "none";

        productSelect.tabIndex =
            -1;

    }

}

/**
 * Registrar eventos de ingredientes activos.
 */
export function registerActiveIngredientEvents() {

    /*
    |--------------------------------------------------------------------------
    | Clicks
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        "click",
        async event => {

            /*
            |--------------------------------------------------------------------------
            | Agregar ingrediente activo
            |--------------------------------------------------------------------------
            */

            const addIngredientButton =
                event.target.closest(
                    ".btn-add-active-ingredient"
                );

            if (addIngredientButton) {

                const applicationCard =
                    addIngredientButton.closest(
                        ".application-card"
                    );

                await addActiveIngredient(
                    applicationCard
                );

                return;

            }

            /*
            |--------------------------------------------------------------------------
            | Eliminar ingrediente activo
            |--------------------------------------------------------------------------
            */

            const removeIngredientButton =
                event.target.closest(
                    ".btn-remove-active-ingredient"
                );

            if (removeIngredientButton) {

                const card =
                    removeIngredientButton.closest(
                        ".active-ingredient-card"
                    );

                if (card) {

                    card.remove();

                    renumberApplications();

                }

                return;

            }

            /*
            |--------------------------------------------------------------------------
            | Eliminar producto recomendado
            |--------------------------------------------------------------------------
            */

            const removeProductButton =
                event.target.closest(
                    ".btn-remove-active-ingredient-product"
                );

            if (removeProductButton) {

                const row =
                    removeProductButton.closest(
                        ".active-ingredient-product-row"
                    );

                if (row) {

                    row.remove();

                    renumberApplications();

                }

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Cambio de ingrediente activo
    |--------------------------------------------------------------------------
    |
    | Al seleccionar DIMETOMORFOS, por ejemplo,
    | cargamos AUTOMÁTICAMENTE todos sus productos vinculados.
    |
    */

    document.addEventListener(
        "change",
        async event => {

            const select =
                event.target.closest(
                    ".active-ingredient-select"
                );

            if (!select) {
                return;
            }

            const card =
                select.closest(
                    ".active-ingredient-card"
                );

            if (!card) {
                return;
            }

            await loadLinkedProducts(
                card
            );

        }
    );

}
