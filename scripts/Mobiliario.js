const mobiliarioForm = document.querySelector("#mobiliario-form");
const mobiliarioMessage = document.querySelector("#mobiliario-message");

if (mobiliarioForm instanceof HTMLFormElement
    && mobiliarioMessage instanceof HTMLElement) {
    mobiliarioForm.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!mobiliarioForm.reportValidity()) {
            return;
        }

        mobiliarioMessage.textContent = "Los datos son válidos. No se guardaron ni se enviaron.";
        mobiliarioMessage.hidden = false;
    });

    mobiliarioForm.addEventListener("input", () => {
        mobiliarioMessage.hidden = true;
    });

    mobiliarioForm.addEventListener("reset", () => {
        mobiliarioMessage.hidden = true;
    });
}
