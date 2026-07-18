import { loadCrops, loadProblems } from "./ajax";
import {
    addApplication,
    registerApplicationEvents
} from "./application";
import { registerProductEvents } from "./product";

/**
 * Obtener datos del protocolo en edición.
 */
function getProtocolData() {

    const script = document.getElementById("protocol-data");

    if (!script) {
        return null;
    }

    try {

        return JSON.parse(script.textContent);

    } catch (error) {

        console.error(error);

        return null;

    }

}

/**
 * Cargar protocolo existente.
 */
async function loadProtocol(protocol) {

    await loadCrops(protocol.crop_id);

    await loadProblems(
        protocol.crop_id,
        protocol.problem_id
    );

    if (
        protocol.applications &&
        protocol.applications.length
    ) {

        for (const application of protocol.applications) {

            await addApplication(application);

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
            document.getElementById("crop_id");

        if (!cropSelect) {
            return;
        }

        registerApplicationEvents();

        registerProductEvents();

        const protocol =
            getProtocolData();

        if (protocol) {

            await loadProtocol(protocol);

        } else {

            await loadCrops();

            cropSelect.addEventListener(
                "change",
                async () => {

                    await loadProblems(
                        cropSelect.value
                    );

                }
            );

            await addApplication();

        }


    }
);
