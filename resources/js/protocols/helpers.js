/**
 * Renumera todas las aplicaciones.
 */
export function renumberApplications() {

    const applications =
        document.querySelectorAll(
            ".application-card"
        );

    applications.forEach(
        (application, applicationIndex) => {

            application.dataset.index =
                applicationIndex;

            /*
            |--------------------------------------------------------------
            | Número de aplicación
            |--------------------------------------------------------------
            */

            const number =
                application.querySelector(
                    ".application-number"
                );

            if (number) {
                number.textContent =
                    applicationIndex + 1;
            }

            const numberInput =
                application.querySelector(
                    ".application-number-input"
                );

            if (numberInput) {

                numberInput.name =
                    `applications[${applicationIndex}][application_number]`;

                numberInput.value =
                    applicationIndex + 1;

            }

            /*
            |--------------------------------------------------------------
            | Tipo de aplicación
            |--------------------------------------------------------------
            */

            const applicationType =
                application.querySelector(
                    ".application-type"
                );

            if (applicationType) {

                applicationType.name =
                    `applications[${applicationIndex}][application_type]`;

            }

            /*
            |--------------------------------------------------------------
            | Descripción
            |--------------------------------------------------------------
            */

            const description =
                application.querySelector(
                    ".application-description"
                );

            if (description) {

                description.name =
                    `applications[${applicationIndex}][description]`;

            }

            /*
            |--------------------------------------------------------------
            | Productos EOFertil
            |--------------------------------------------------------------
            */

            renumberProducts(
                application,
                applicationIndex
            );

            /*
            |--------------------------------------------------------------
            | Ingredientes activos
            |--------------------------------------------------------------
            */

            renumberActiveIngredients(
                application,
                applicationIndex
            );
            renumberActiveIngredientCombinations(
    application,
    applicationIndex
);

        }
    );

}

/**
 * Renumera los productos EOFertil
 * de una aplicación.
 */
export function renumberProducts(
    application,
    applicationIndex
) {

    const rows =
        application.querySelectorAll(
            ".products-container .product-row"
        );

    rows.forEach(
        (row, productIndex) => {

            /*
            |----------------------------------------------------------
            | Producto
            |----------------------------------------------------------
            */

            const productSelect =
                row.querySelector(
                    ".product-select"
                );

            if (productSelect) {

                productSelect.name =
                    `applications[${applicationIndex}][products][${productIndex}][product_id]`;

            }

            /*
            |----------------------------------------------------------
            | Dosis
            |----------------------------------------------------------
            */

            const doseInput =
                row.querySelector(
                    ".dose-input"
                );

            if (doseInput) {

                doseInput.name =
                    `applications[${applicationIndex}][products][${productIndex}][dose]`;

            }

            /*
            |----------------------------------------------------------
            | Unidad
            |----------------------------------------------------------
            */

            const unitInput =
                row.querySelector(
                    ".unit-input"
                );

            if (unitInput) {

                unitInput.name =
                    `applications[${applicationIndex}][products][${productIndex}][unit]`;

            }

            /*
            |----------------------------------------------------------
            | Base de aplicación
            |----------------------------------------------------------
            */

            const applicationBaseInput =
                row.querySelector(
                    ".application-base-input"
                );

            if (applicationBaseInput) {

                applicationBaseInput.name =
                    `applications[${applicationIndex}][products][${productIndex}][application_base]`;

            }

        }
    );

}

/**
 * Renumera los ingredientes activos
 * de una aplicación.
 */
export function renumberActiveIngredients(
    application,
    applicationIndex
) {

    const activeIngredientCards =
        application.querySelectorAll(
            ".active-ingredients-container .active-ingredient-card"
        );

    activeIngredientCards.forEach(
        (
            activeIngredientCard,
            activeIngredientIndex
        ) => {

            activeIngredientCard.dataset.index =
                activeIngredientIndex;

            /*
            |----------------------------------------------------------
            | Ingrediente activo
            |----------------------------------------------------------
            */

            const activeIngredientSelect =
                activeIngredientCard.querySelector(
                    ".active-ingredient-select"
                );

            if (activeIngredientSelect) {

                activeIngredientSelect.name =
                    `applications[${applicationIndex}][active_ingredients][${activeIngredientIndex}][active_ingredient_id]`;

            }

            /*
            |----------------------------------------------------------
            | Productos recomendados del ingrediente activo
            |----------------------------------------------------------
            */

            renumberActiveIngredientProducts(
                activeIngredientCard,
                applicationIndex,
                activeIngredientIndex
            );

        }
    );

}

/**
 * Renumera los productos recomendados
 * pertenecientes a un ingrediente activo.
 */
export function renumberActiveIngredientProducts(
    activeIngredientCard,
    applicationIndex,
    activeIngredientIndex
) {

    const rows =
        activeIngredientCard.querySelectorAll(
            ".active-ingredient-products-container .active-ingredient-product-row"
        );

    rows.forEach(
        (row, productIndex) => {

            /*
            |----------------------------------------------------------
            | Producto
            |----------------------------------------------------------
            */

            const productSelect =
                row.querySelector(
                    ".active-ingredient-product-select"
                );

            if (productSelect) {

                productSelect.name =
                    `applications[${applicationIndex}][active_ingredients][${activeIngredientIndex}][products][${productIndex}][product_id]`;

            }

            /*
            |----------------------------------------------------------
            | Dosis
            |----------------------------------------------------------
            */

            const doseInput =
                row.querySelector(
                    ".active-ingredient-product-dose"
                );

            if (doseInput) {

                doseInput.name =
                    `applications[${applicationIndex}][active_ingredients][${activeIngredientIndex}][products][${productIndex}][dose]`;

            }

            /*
            |----------------------------------------------------------
            | Unidad
            |----------------------------------------------------------
            */

            const unitInput =
                row.querySelector(
                    ".active-ingredient-product-unit"
                );

            if (unitInput) {

                unitInput.name =
                    `applications[${applicationIndex}][active_ingredients][${activeIngredientIndex}][products][${productIndex}][unit]`;

            }

            /*
            |----------------------------------------------------------
            | Base de aplicación
            |----------------------------------------------------------
            */

            const applicationBaseInput =
                row.querySelector(
                    ".active-ingredient-product-application-base"
                );

            if (applicationBaseInput) {

                applicationBaseInput.name =
                    `applications[${applicationIndex}][active_ingredients][${activeIngredientIndex}][products][${productIndex}][application_base]`;

            }

        }
    );

}
/**
 * Renumera las combinaciones de ingredientes activos
 * de una aplicación.
 */
export function renumberActiveIngredientCombinations(
    application,
    applicationIndex
) {

    const combinationCards =
        application.querySelectorAll(
            ".active-ingredient-combinations-container .active-ingredient-combination-card"
        );

    combinationCards.forEach(
        (
            combinationCard,
            combinationIndex
        ) => {

            combinationCard.dataset.index =
                combinationIndex;

            const combinationSelect =
                combinationCard.querySelector(
                    ".active-ingredient-combination-select"
                );

            if (combinationSelect) {

                combinationSelect.name =
                    `applications[${applicationIndex}][active_ingredient_combinations][${combinationIndex}][active_ingredient_combination_id]`;

            }

            const productRows =
                combinationCard.querySelectorAll(
                    ".active-ingredient-combination-products-container .active-ingredient-combination-product-row"
                );

            productRows.forEach(
                (row, productIndex) => {

                    const productSelect =
                        row.querySelector(
                            ".active-ingredient-combination-product-select"
                        );

                    if (productSelect) {
                        productSelect.name =
                            `applications[${applicationIndex}][active_ingredient_combinations][${combinationIndex}][products][${productIndex}][product_id]`;
                    }

                    const doseInput =
                        row.querySelector(
                            ".active-ingredient-combination-product-dose"
                        );

                    if (doseInput) {
                        doseInput.name =
                            `applications[${applicationIndex}][active_ingredient_combinations][${combinationIndex}][products][${productIndex}][dose]`;
                    }

                    const unitInput =
                        row.querySelector(
                            ".active-ingredient-combination-product-unit"
                        );

                    if (unitInput) {
                        unitInput.name =
                            `applications[${applicationIndex}][active_ingredient_combinations][${combinationIndex}][products][${productIndex}][unit]`;
                    }

                    const applicationBaseInput =
                        row.querySelector(
                            ".active-ingredient-combination-product-application-base"
                        );

                    if (applicationBaseInput) {
                        applicationBaseInput.name =
                            `applications[${applicationIndex}][active_ingredient_combinations][${combinationIndex}][products][${productIndex}][application_base]`;
                    }
                }
            );

        }
    );

}
