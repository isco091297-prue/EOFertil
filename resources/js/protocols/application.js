import { addProduct } from "./product";
import { addActiveIngredient } from "./activeIngredient";
import { renumberApplications } from "./helpers";

/**
 * Agrega una nueva aplicación.
 *
 * @param {Object|null} applicationData
 */
export async function addApplication(
    applicationData = null
) {

    const template =
        document.getElementById(
            "application-template"
        );

    if (!template) {
        return;
    }

    const container =
        document.getElementById(
            "applications-container"
        );

    if (!container) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const application =
        clone.querySelector(
            ".application-card"
        );

    if (!application) {
        return;
    }

    container.appendChild(application);

    const applicationCard =
        container.lastElementChild;

    /*
    |----------------------------------------------------------------------
    | Tipo de aplicación y descripción
    |----------------------------------------------------------------------
    */

    if (applicationData) {

        const type =
            applicationCard.querySelector(
                ".application-type"
            );

        const description =
            applicationCard.querySelector(
                ".application-description"
            );

        if (type) {

            type.value =
                applicationData.application_type ?? "";

        }

        if (description) {

            description.value =
                applicationData.description ?? "";

        }

    }

    /*
    |----------------------------------------------------------------------
    | Productos EOFertil
    |----------------------------------------------------------------------
    */

    if (
        applicationData &&
        Array.isArray(applicationData.products) &&
        applicationData.products.length
    ) {

        for (
            const product
            of applicationData.products
        ) {

            await addProduct(
                applicationCard,
                product
            );

        }

    } else {

        /*
        | Una aplicación nueva comienza con
        | una fila para producto EOFertil.
        */

        await addProduct(
            applicationCard
        );

    }

    /*
    |----------------------------------------------------------------------
    | Ingredientes activos
    |----------------------------------------------------------------------
    */

    if (
        applicationData &&
        Array.isArray(
            applicationData.active_ingredients
        ) &&
        applicationData.active_ingredients.length
    ) {

        for (
            const activeIngredient
            of applicationData.active_ingredients
        ) {

            await addActiveIngredient(
                applicationCard,
                activeIngredient
            );

        }

    }

    /*
    |----------------------------------------------------------------------
    | Renumerar
    |----------------------------------------------------------------------
    */

    renumberApplications();

}

/**
 * Elimina una aplicación.
 */
export function removeApplication(button) {

    const application =
        button.closest(
            ".application-card"
        );

    if (!application) {
        return;
    }

    application.remove();

    renumberApplications();

}

/**
 * Registrar eventos.
 */
export function registerApplicationEvents() {

    /*
    |----------------------------------------------------------------------
    | Agregar aplicación
    |----------------------------------------------------------------------
    */

    const addButton =
        document.getElementById(
            "btn-add-application"
        );

    if (addButton) {

        addButton.addEventListener(
            "click",
            async () => {

                await addApplication();

            }
        );

    }

    /*
    |----------------------------------------------------------------------
    | Eliminar aplicación
    |----------------------------------------------------------------------
    */

    document.addEventListener(
        "click",
        event => {

            const button =
                event.target.closest(
                    ".btn-remove-application"
                );

            if (!button) {
                return;
            }

            removeApplication(
                button
            );

        }
    );

}
