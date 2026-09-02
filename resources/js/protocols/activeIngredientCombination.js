import {
    loadActiveIngredientCombinations,
    loadActiveIngredientCombinationProducts,
} from "./ajax";

import { renumberApplications } from "./helpers";

/**
 * Agregar una combinación de ingredientes activos a una aplicación.
 */
export async function addActiveIngredientCombination(
    applicationCard,
    combinationData = null,
) {
    const template = document.getElementById(
        "active-ingredient-combination-template",
    );

    if (!template || !applicationCard) return;

    const container = applicationCard.querySelector(
        ".active-ingredient-combinations-container",
    );

    if (!container) return;

    const clone = template.content.cloneNode(true);
    const card = clone.querySelector(".active-ingredient-combination-card");

    if (!card) return;

    container.appendChild(card);
    const newCard = container.lastElementChild;
    const combinationSelect = newCard.querySelector(
        ".active-ingredient-combination-select",
    );

    await loadActiveIngredientCombinations(
        combinationSelect,
        combinationData?.active_ingredient_combination_id ?? null,
    );

    combinationSelect.addEventListener("change", async () => {
        await loadLinkedProducts(newCard, combinationSelect.value);
    });

    if (
        combinationData &&
        Array.isArray(combinationData.products) &&
        combinationData.products.length
    ) {
        for (const productData of combinationData.products) {
            await addActiveIngredientCombinationProduct(newCard, productData);
        }
    } else if (combinationSelect.value) {
        await loadLinkedProducts(newCard, combinationSelect.value);
    }

    renumberApplications();
}

/**
 * Crear una fila de producto dentro de una combinación.
 */
export async function addActiveIngredientCombinationProduct(
    combinationCard,
    productData = null,
) {
    const template = document.getElementById(
        "active-ingredient-combination-product-template",
    );

    if (!template || !combinationCard) return;

    const container = combinationCard.querySelector(
        ".active-ingredient-combination-products-container",
    );

    if (!container) return;

    const combinationSelect = combinationCard.querySelector(
        ".active-ingredient-combination-select",
    );

    const combinationId = combinationSelect?.value;
    if (!combinationId) return;

    const clone = template.content.cloneNode(true);
    const row = clone.querySelector(
        ".active-ingredient-combination-product-row",
    );

    if (!row) return;

    container.appendChild(row);
    const newRow = container.lastElementChild;
    const productSelect = newRow.querySelector(
        ".active-ingredient-combination-product-select",
    );

    await loadActiveIngredientCombinationProducts(
        productSelect,
        combinationId,
        productData?.product_id ?? null,
    );

    if (productData) {
        const dose = newRow.querySelector(
            ".active-ingredient-combination-product-dose",
        );
        const unit = newRow.querySelector(
            ".active-ingredient-combination-product-unit",
        );
        const applicationBase = newRow.querySelector(
            ".active-ingredient-combination-product-application-base",
        );

        if (dose) dose.value = productData.dose ?? "";
        if (unit) unit.value = productData.unit ?? "";
        if (applicationBase) {
            applicationBase.value = productData.application_base ?? "";
        }
    }

    lockCombinationProductSelect(productSelect);
    renumberApplications();
}

/**
 * Carga automáticamente todos los productos vinculados a la combinación.
 */
async function loadLinkedProducts(combinationCard, combinationId) {
    const container = combinationCard.querySelector(
        ".active-ingredient-combination-products-container",
    );

    if (!container) return;

    container.innerHTML = "";

    if (!combinationId) {
        renumberApplications();
        return;
    }

    const temporarySelect = document.createElement("select");
    await loadActiveIngredientCombinationProducts(
        temporarySelect,
        combinationId,
    );

    const productOptions = Array.from(temporarySelect.options).filter(
        (option) => option.value !== "",
    );

    for (const option of productOptions) {
        await createLinkedProductRow(
            combinationCard,
            option.value,
            option.textContent,
        );
    }

    renumberApplications();
}

/**
 * Crear una fila para un producto que pertenece a la combinación.
 */
async function createLinkedProductRow(combinationCard, productId, productText) {
    const template = document.getElementById(
        "active-ingredient-combination-product-template",
    );

    if (!template) return;

    const container = combinationCard.querySelector(
        ".active-ingredient-combination-products-container",
    );

    if (!container) return;

    const clone = template.content.cloneNode(true);
    const row = clone.querySelector(
        ".active-ingredient-combination-product-row",
    );

    if (!row) return;

    container.appendChild(row);
    const newRow = container.lastElementChild;
    const productSelect = newRow.querySelector(
        ".active-ingredient-combination-product-select",
    );

    if (productSelect) {
        productSelect.innerHTML = "";

        const option = document.createElement("option");
        option.value = productId;
        option.textContent = productText;
        option.selected = true;
        productSelect.appendChild(option);

        lockCombinationProductSelect(productSelect);
    }
}

function lockCombinationProductSelect(select) {
    if (!select) return;

    select.classList.add("bg-gray-100");
    select.style.pointerEvents = "none";
    select.tabIndex = -1;
}

/**
 * Registrar eventos de combinaciones.
 */
export function registerActiveIngredientCombinationEvents() {
    document.addEventListener("click", async (event) => {
        const addButton = event.target.closest(
            ".btn-add-active-ingredient-combination",
        );

        if (addButton) {
            const applicationCard = addButton.closest(".application-card");

            if (applicationCard) {
                await addActiveIngredientCombination(applicationCard);
            }

            return;
        }

        const removeCombinationButton = event.target.closest(
            ".btn-remove-active-ingredient-combination",
        );

        if (removeCombinationButton) {
            const card = removeCombinationButton.closest(
                ".active-ingredient-combination-card",
            );

            if (card) {
                card.remove();
                renumberApplications();
            }

            return;
        }

        const removeProductButton = event.target.closest(
            ".btn-remove-active-ingredient-combination-product",
        );

        if (removeProductButton) {
            const row = removeProductButton.closest(
                ".active-ingredient-combination-product-row",
            );

            if (row) {
                row.remove();
                renumberApplications();
            }
        }
    });
}
