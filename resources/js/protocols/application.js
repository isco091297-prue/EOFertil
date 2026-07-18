import { addProduct } from "./product";
import { renumberApplications } from "./helpers";

/**
 * Agrega una nueva aplicación.
 *
 * @param {Object|null} applicationData
 */
export async function addApplication(applicationData = null) {

    const template =
        document.getElementById("application-template");

    if (!template) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const application =
        clone.firstElementChild;

    document
        .getElementById("applications-container")
        .appendChild(application);

    const applicationCard =
        document.querySelector(
            "#applications-container .application-card:last-child"
        );

    /*
    |--------------------------------------------------------------------------
    | Descripción
    |--------------------------------------------------------------------------
    */

    if (applicationData) {

        const description =
            applicationCard.querySelector(
                ".application-description"
            );

        description.value =
            applicationData.description ?? "";

    }

    /*
    |--------------------------------------------------------------------------
    | Productos
    |--------------------------------------------------------------------------
    */

    if (
        applicationData &&
        Array.isArray(applicationData.products) &&
        applicationData.products.length
    ) {

        for (const product of applicationData.products) {

            await addProduct(
                applicationCard,
                product
            );

        }

    } else {

        await addProduct(applicationCard);

    }

    renumberApplications();

}

/**
 * Elimina una aplicación.
 */
export function removeApplication(button) {

    const application =
        button.closest(".application-card");

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
    |--------------------------------------------------------------------------
    | Agregar aplicación
    |--------------------------------------------------------------------------
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
    |--------------------------------------------------------------------------
    | Eliminar aplicación
    |--------------------------------------------------------------------------
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

            removeApplication(button);

        }
    );

}
