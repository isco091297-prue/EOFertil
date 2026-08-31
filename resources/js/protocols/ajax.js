/**
 * Obtener cultivos.
 */
export async function loadCrops(selected = null) {

    const select = document.getElementById("crop_id");

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un cultivo
        </option>
    `;

    try {

        const response = await fetch(
            "/protocols/crops/search"
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los cultivos."
            );
        }

        const crops = await response.json();

        crops.forEach(crop => {

            const option =
                document.createElement("option");

            option.value = crop.id;
            option.textContent = crop.text;

            if (
                selected !== null &&
                Number(selected) === Number(crop.id)
            ) {
                option.selected = true;
            }

            select.appendChild(option);

        });

    } catch (error) {

        console.error(error);

    }

}

/**
 * Obtener problemas.
 */
export async function loadProblems(
    cropId,
    selected = null
) {

    const select =
        document.getElementById("problem_id");

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un problema
        </option>
    `;

    if (!cropId) {
        return;
    }

    try {

        const response = await fetch(
            `/protocols/problems/search?crop_id=${encodeURIComponent(cropId)}`
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los problemas."
            );
        }

        const problems =
            await response.json();

        problems.forEach(problem => {

            const option =
                document.createElement("option");

            option.value = problem.id;
            option.textContent = problem.text;

            if (
                selected !== null &&
                Number(selected) === Number(problem.id)
            ) {
                option.selected = true;
            }

            select.appendChild(option);

        });

    } catch (error) {

        console.error(error);

    }

}

/**
 * Obtener productos EOFertil.
 */
export async function loadProducts(
    select,
    selected = null
) {

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un producto
        </option>
    `;

    try {

        const response = await fetch(
            "/protocols/products/search"
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los productos."
            );
        }

        const products =
            await response.json();

        products.forEach(product => {

            const option =
                document.createElement("option");

            option.value = product.id;
            option.textContent = product.text;

            if (
                selected !== null &&
                Number(selected) === Number(product.id)
            ) {
                option.selected = true;
            }

            select.appendChild(option);

        });

    } catch (error) {

        console.error(error);

    }

}

/**
 * Obtener ingredientes activos.
 */
export async function loadActiveIngredients(
    select,
    selected = null
) {

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un ingrediente activo
        </option>
    `;

    try {

        const response = await fetch(
            "/protocols/active-ingredients/search"
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los ingredientes activos."
            );
        }

        const activeIngredients =
            await response.json();

        activeIngredients.forEach(
            activeIngredient => {

                const option =
                    document.createElement("option");

                option.value =
                    activeIngredient.id;

                option.textContent =
                    activeIngredient.text;

                if (
                    selected !== null &&
                    Number(selected) ===
                        Number(activeIngredient.id)
                ) {
                    option.selected = true;
                }

                select.appendChild(option);

            }
        );

    } catch (error) {

        console.error(error);

    }

}

/**
 * Obtener productos vinculados
 * a un ingrediente activo.
 */
export async function loadActiveIngredientProducts(
    select,
    activeIngredientId,
    selected = null
) {

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un producto
        </option>
    `;

    if (!activeIngredientId) {
        return;
    }

    try {

        const response = await fetch(
            `/protocols/active-ingredients/${encodeURIComponent(activeIngredientId)}/products`
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los productos del ingrediente activo."
            );
        }

        const products =
            await response.json();

        products.forEach(product => {

            const option =
                document.createElement("option");

            option.value = product.id;
            option.textContent = product.text;

            if (
                selected !== null &&
                Number(selected) === Number(product.id)
            ) {
                option.selected = true;
            }

            select.appendChild(option);

        });

    } catch (error) {

        console.error(error);

    }

}
/**
 * Obtener combinaciones de ingredientes activos.
 */
export async function loadActiveIngredientCombinations(
    select,
    selected = null
) {

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione una combinación
        </option>
    `;

    try {

        const response = await fetch(
            "/protocols/active-ingredient-combinations/search"
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener las combinaciones de ingredientes activos."
            );
        }

        const combinations =
            await response.json();

        combinations.forEach(
            combination => {

                const option =
                    document.createElement("option");

                option.value =
                    combination.id;

                option.textContent =
                    combination.text;

                if (
                    selected !== null &&
                    Number(selected) ===
                        Number(combination.id)
                ) {
                    option.selected = true;
                }

                select.appendChild(option);

            }
        );

    } catch (error) {

        console.error(error);

    }

}

/**
 * Obtener productos vinculados
 * a una combinación de ingredientes activos.
 */
export async function loadActiveIngredientCombinationProducts(
    select,
    combinationId,
    selected = null
) {

    if (!select) {
        return;
    }

    select.innerHTML = `
        <option value="">
            Seleccione un producto
        </option>
    `;

    if (!combinationId) {
        return;
    }

    try {

        const response = await fetch(
            `/protocols/active-ingredient-combinations/${encodeURIComponent(combinationId)}/products`
        );

        if (!response.ok) {
            throw new Error(
                "No fue posible obtener los productos de la combinación."
            );
        }

        const products =
            await response.json();

        products.forEach(
            product => {

                const option =
                    document.createElement("option");

                option.value =
                    product.id;

                option.textContent =
                    product.text;

                if (
                    selected !== null &&
                    Number(selected) ===
                        Number(product.id)
                ) {
                    option.selected = true;
                }

                select.appendChild(option);

            }
        );

    } catch (error) {

        console.error(error);

    }

}
