const comprasForm = document.querySelector("#compras-form");
const comprasMessage = document.querySelector("#compras-message");

if (comprasForm instanceof HTMLFormElement
    && comprasMessage instanceof HTMLElement) {
    comprasForm.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!comprasForm.reportValidity()) {
            return;
        }

        comprasMessage.textContent = "Los datos son válidos. No se guardaron ni se enviaron.";
        comprasMessage.hidden = false;
    });

    comprasForm.addEventListener("input", () => {
        comprasMessage.hidden = true;
    });

    comprasForm.addEventListener("reset", () => {
        comprasMessage.hidden = true;
    });
}
