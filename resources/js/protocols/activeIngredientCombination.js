import {
    loadActiveIngredientCombinations,
    loadActiveIngredientCombinationProducts
} from "./ajax";

import {
    renumberApplications
} from "./helpers";

/**
 * Agregar una combinación de ingredientes activos
 * a una aplicación.
 *
 * @param {HTMLElement} applicationCard
 * @param {Object|null} combinationData
 */
export async function addActiveIngredientCombination(
    applicationCard,
    combinationData = null
) {

    const template =
        document.getElementById(
            "active-ingredient-combination-template"
        );

    if (!template || !applicationCard) {
        return;
    }

    const container =
        applicationCard.querySelector(
            ".active-ingredient-combinations-container"
        );

    if (!container) {
        return;
    }

    const clone =
        template.content.cloneNode(true);

    const card =
        clone.querySelector(
            ".active-ingredient-combination-card"
        );

    if (!card) {
        return;
    }

    container.appendChild(card);

    const newCard =
        container.lastElementChild;

    const combinationSelect =
        newCard.querySelector(
            ".active-ingredient-combination-select"
        );

    if (!combinationSelect) {
        return;
    }

    /*
    |----------------------------------------------------------------------
    | Cargar combinaciones
    |----------------------------------------------------------------------
    */

    await loadActiveIngredientCombinations(
        combinationSelect,
        combinationData?.active_ingredient_combination_id ?? null
    );

    /*
    |----------------------------------------------------------------------
    | Cambio de combinación
    |----------------------------------------------------------------------
    */

    combinationSelect.addEventListener(
        "change",
        async () => {

            const combinationId =
                combinationSelect.value;

            if (!combinationId) {
                return;
            }

            await loadCombinationProducts(
                newCard,
                combinationId
            );

        }
    );

    /*
    |----------------------------------------------------------------------
    | Cargar datos existentes
    |----------------------------------------------------------------------
    */

    if (combinationData) {

        const dose =
            newCard.querySelector(
                ".active-ingredient-combination-dose"
            );

        const unit =
            newCard.querySelector(
                ".active-ingredient-combination-unit"
            );

        const applicationBase =
            newCard.querySelector(
                ".active-ingredient-combination-application-base"
            );

        if (dose) {
            dose.value =
                combinationData.dose ?? "";
        }

        if (unit) {
            unit.value =
                combinationData.unit ?? "";
        }

        if (applicationBase) {
            applicationBase.value =
                combinationData.application_base ?? "";
        }

        /*
        |------------------------------------------------------------------
        | Cargar productos de la combinación existente
        |------------------------------------------------------------------
        */

        if (
            combinationData.active_ingredient_combination_id
        ) {

            await loadCombinationProducts(
                newCard,
                combinationData.active_ingredient_combination_id
            );

        }

    }

    renumberApplications();

}

/**
 * Cargar productos vinculados
 * a una combinación.
 *
 * @param {HTMLElement} card
 * @param {number|string} combinationId
 */
async function loadCombinationProducts(
    card,
    combinationId
) {

    /*
    |----------------------------------------------------------------------
    | Buscar selects de productos dentro de la combinación.
    |----------------------------------------------------------------------
    */

    const productSelects =
        card.querySelectorAll(
            ".active-ingredient-combination-product-select"
        );

    if (!productSelects.length) {
        return;
    }

    /*
    |----------------------------------------------------------------------
    | Cargar productos en cada select.
    |----------------------------------------------------------------------
    */

    for (
        const select
        of productSelects
    ) {

        await loadActiveIngredientCombinationProducts(
            select,
            combinationId
        );

    }

}

/**
 * Registrar eventos de combinaciones.
 */
export function registerActiveIngredientCombinationEvents() {

    document.addEventListener(
        "click",
        async event => {

            /*
            |------------------------------------------------------------------
            | Agregar combinación
            |------------------------------------------------------------------
            */

            const addButton =
                event.target.closest(
                    ".btn-add-active-ingredient-combination"
                );

            if (addButton) {

                const applicationCard =
                    addButton.closest(
                        ".application-card"
                    );

                if (!applicationCard) {
                    return;
                }

                await addActiveIngredientCombination(
                    applicationCard
                );

                return;
            }

            /*
            |------------------------------------------------------------------
            | Eliminar combinación
            |------------------------------------------------------------------
            */

            const removeButton =
                event.target.closest(
                    ".btn-remove-active-ingredient-combination"
                );

            if (removeButton) {

                const card =
                    removeButton.closest(
                        ".active-ingredient-combination-card"
                    );

                if (card) {

                    card.remove();

                    renumberApplications();

                }

            }

        }
    );

}
