import { loadProducts } from "./ajax";
import { renumberApplications } from "./helpers";

/**
 * Agrega un producto EOFertil
 * a una aplicación.
 *
 * @param {HTMLElement} applicationCard
 * @param {Object|null} productData
 */
export async function addProduct(
    applicationCard,
    productData = null
) {

    const template =
        document.getElementById(
            "product-template"
        );

    if (!template || !applicationCard) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const row =
        clone.querySelector(
            ".product-row"
        );

    const productsContainer =
        applicationCard.querySelector(
            ".products-container"
        );

    if (!row || !productsContainer) {
        return;
    }

    productsContainer.appendChild(row);

    const newRow =
        productsContainer.lastElementChild;

    const productSelect =
        newRow.querySelector(
            ".product-select"
        );

    /*
    |--------------------------------------------------------------
    | Cargar productos EOFertil
    |--------------------------------------------------------------
    */

    await loadProducts(
        productSelect,
        productData?.product_id ?? null
    );

    /*
    |--------------------------------------------------------------
    | Cargar información existente
    |--------------------------------------------------------------
    */

    if (productData) {

        const dose =
            newRow.querySelector(
                ".dose-input"
            );

        const unit =
            newRow.querySelector(
                ".unit-input"
            );

        const applicationBase =
            newRow.querySelector(
                ".application-base-input"
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
 * Eliminar producto EOFertil.
 */
export function removeProduct(button) {

    const row =
        button.closest(
            ".product-row"
        );

    if (!row) {
        return;
    }

    row.remove();

    renumberApplications();

}

/**
 * Registrar eventos de productos EOFertil.
 */
export function registerProductEvents() {

    document.addEventListener(
        "click",
        async event => {

            /*
            |----------------------------------------------------------
            | Agregar producto
            |----------------------------------------------------------
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

                if (!application) {
                    return;
                }

                await addProduct(
                    application
                );

                return;

            }

            /*
            |----------------------------------------------------------
            | Eliminar producto
            |----------------------------------------------------------
            */

            const removeButton =
                event.target.closest(
                    ".btn-remove-product"
                );

            if (removeButton) {

                removeProduct(
                    removeButton
                );

            }

        }
    );

}
