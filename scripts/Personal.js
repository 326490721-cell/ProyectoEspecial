const personalForm = document.querySelector("#personal-form");
const personalMessage = document.querySelector("#personal-message");

if (personalForm instanceof HTMLFormElement
    && personalMessage instanceof HTMLElement) {
    personalForm.addEventListener("submit", (event) => {
        event.preventDefault();

        if (!personalForm.reportValidity()) {
            return;
        }

        personalMessage.textContent = "Los datos son válidos. No se guardaron ni se enviaron.";
        personalMessage.hidden = false;
    });

    personalForm.addEventListener("input", () => {
        personalMessage.hidden = true;
    });

    personalForm.addEventListener("reset", () => {
        personalMessage.hidden = true;
    });
}
