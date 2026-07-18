import { loadProducts } from "./ajax";
import { renumberApplications } from "./helpers";

/**
 * Agrega un producto a una aplicación.
 *
 * @param {HTMLElement} applicationCard
 * @param {Object|null} productData
 */
export async function addProduct(
    applicationCard,
    productData = null
) {

    const template =
        document.getElementById("product-template");

    if (!template || !applicationCard) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const row =
        clone.querySelector(".product-row");

    const productsContainer =
        applicationCard.querySelector(".products-container");

    productsContainer.appendChild(row);

    const newRow =
        productsContainer.lastElementChild;

    const productSelect =
        newRow.querySelector(".product-select");

    /*
    |--------------------------------------------------------------------------
    | Cargar productos
    |--------------------------------------------------------------------------
    */

    await loadProducts(productSelect);

    /*
    |--------------------------------------------------------------------------
    | Editar
    |--------------------------------------------------------------------------
    */

    if (productData) {

        productSelect.value =
            productData.product_id ?? "";

        const dose =
            newRow.querySelector(".dose-input");

        dose.value =
            productData.dose ?? "";

        const observations =
            newRow.querySelector(".observations-input");

        observations.value =
            productData.observations ?? "";

    }

    renumberApplications();

}

/**
 * Eliminar producto.
 */
export function removeProduct(button) {

    const row =
        button.closest(".product-row");

    if (!row) {
        return;
    }

    row.remove();

    renumberApplications();

}

/**
 * Registrar eventos.
 */
export function registerProductEvents() {

    document.addEventListener(
        "click",
        async event => {

            /*
            |--------------------------------------------------------------------------
            | Agregar producto
            |--------------------------------------------------------------------------
            */

            const addButton =
                event.target.closest(
                    ".btn-add-product"
                );

            if (addButton) {

                const application =
                    addButton.closest(
                        ".application-card"
                    );

                await addProduct(application);

                return;

            }

            /*
            |--------------------------------------------------------------------------
            | Eliminar producto
            |--------------------------------------------------------------------------
            */

            const removeButton =
                event.target.closest(
                    ".btn-remove-product"
                );

            if (removeButton) {

                removeProduct(removeButton);

            }

        }
    );

}
