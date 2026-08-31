import {
    loadCrops,
    loadProblems
} from "./ajax";

import {
    addApplication,
    registerApplicationEvents
} from "./application";

import {
    registerProductEvents
} from "./product";

import {
    registerActiveIngredientEvents
} from "./activeIngredient";
import {
    registerActiveIngredientCombinationEvents
} from "./activeIngredientCombination";
/**
 * Obtener datos del receta en edición.
 */
function getProtocolData() {

    const script =
        document.getElementById(
            "protocol-data"
        );

    if (!script) {
        return null;
    }

    try {

        return JSON.parse(
            script.textContent
        );

    } catch (error) {

        console.error(
            "No fue posible leer los datos del receta.",
            error
        );

        return null;

    }

}

/**
 * Cargar receta existente.
 */
async function loadProtocol(protocol) {

    /*
    |----------------------------------------------------------------------
    | Cultivo
    |----------------------------------------------------------------------
    */

    await loadCrops(
        protocol.crop_id
    );

    /*
    |----------------------------------------------------------------------
    | Problema
    |----------------------------------------------------------------------
    */

    await loadProblems(
        protocol.crop_id,
        protocol.problem_id
    );

    /*
    |----------------------------------------------------------------------
    | Aplicaciones
    |----------------------------------------------------------------------
    */

    if (
        Array.isArray(protocol.applications) &&
        protocol.applications.length
    ) {

        for (
            const application
            of protocol.applications
        ) {

            await addApplication(
                application
            );

        }

    } else {

        await addApplication();

    }

}

/**
 * Inicializar formulario.
 */
document.addEventListener(
    "DOMContentLoaded",
    async () => {

        const cropSelect =
            document.getElementById(
                "crop_id"
            );

        if (!cropSelect) {
            return;
        }

        /*
        |------------------------------------------------------------------
        | Registrar eventos
        |------------------------------------------------------------------
        */

        registerApplicationEvents();

registerProductEvents();

registerActiveIngredientEvents();

registerActiveIngredientCombinationEvents();

        /*
        |------------------------------------------------------------------
        | Cambio de cultivo
        |------------------------------------------------------------------
        |
        | Esto debe funcionar tanto al crear como al editar.
        |
        */

        cropSelect.addEventListener(
            "change",
            async () => {

                await loadProblems(
                    cropSelect.value
                );

            }
        );

        /*
        |------------------------------------------------------------------
        | Comprobar edición
        |------------------------------------------------------------------
        */

        const protocol =
            getProtocolData();

        if (protocol) {

            await loadProtocol(
                protocol
            );

            return;

        }

        /*
        |------------------------------------------------------------------
        | Nuevo reeta
        |------------------------------------------------------------------
        */

        await loadCrops();

        await addApplication();

    }
);
