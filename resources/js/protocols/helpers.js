/**
 * Renumera todas las aplicaciones.
 */
export function renumberApplications() {

    const applications = document.querySelectorAll(".application-card");

    applications.forEach((application, applicationIndex) => {

        application.dataset.index = applicationIndex;

        const number = application.querySelector(".application-number");

        if (number) {
            number.textContent = applicationIndex + 1;
        }

        const numberInput = application.querySelector(
            ".application-number-input"
        );

        if (numberInput) {

            numberInput.name =
                `applications[${applicationIndex}][application_number]`;

            numberInput.value =
                applicationIndex + 1;

        }

        const description = application.querySelector(
            ".application-description"
        );

        if (description) {

            description.name =
                `applications[${applicationIndex}][description]`;

        }

        renumberProducts(
            application,
            applicationIndex
        );

    });

}

/**
 * Renumera todos los productos de una aplicación.
 */
export function renumberProducts(
    application,
    applicationIndex
) {

    const rows =
        application.querySelectorAll(".product-row");

    rows.forEach((row, productIndex) => {

        const productSelect =
            row.querySelector(".product-select");

        if (productSelect) {

            productSelect.name =
                `applications[${applicationIndex}][products][${productIndex}][product_id]`;

        }

        const doseInput =
            row.querySelector(".dose-input");

        if (doseInput) {

            doseInput.name =
                `applications[${applicationIndex}][products][${productIndex}][dose]`;

        }

        const observationsInput =
            row.querySelector(".observations-input");

        if (observationsInput) {

            observationsInput.name =
                `applications[${applicationIndex}][products][${productIndex}][observations]`;

        }

    });

}
