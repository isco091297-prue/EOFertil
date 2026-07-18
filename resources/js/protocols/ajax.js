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

        const response = await fetch("/protocols/crops/search");

        if (!response.ok) {
            throw new Error("No fue posible obtener los cultivos.");
        }

        const crops = await response.json();

        crops.forEach(crop => {

            const option = document.createElement("option");

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

    const select = document.getElementById("problem_id");

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
            `/protocols/problems/search?crop_id=${cropId}`
        );

        if (!response.ok) {
            throw new Error("No fue posible obtener los problemas.");
        }

        const problems = await response.json();

        problems.forEach(problem => {

            const option = document.createElement("option");

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
 * Obtener productos.
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
            throw new Error("No fue posible obtener los productos.");
        }

        const products = await response.json();

        products.forEach(product => {

            const option = document.createElement("option");

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
