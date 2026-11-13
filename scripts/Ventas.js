const ventasForm = document.querySelector("#ventas-form");
const ventasMessage = document.querySelector("#ventas-message");

if (ventasForm instanceof HTMLFormElement
    && ventasMessage instanceof HTMLElement) {
    ventasForm.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!ventasForm.reportValidity()) {
            return;
        }

        ventasMessage.textContent = "Los datos son válidos. No se guardaron ni se enviaron.";
        ventasMessage.hidden = false;
    });

    ventasForm.addEventListener("input", () => {
        ventasMessage.hidden = true;
    });

    ventasForm.addEventListener("reset", () => {
        ventasMessage.hidden = true;
    });
}
