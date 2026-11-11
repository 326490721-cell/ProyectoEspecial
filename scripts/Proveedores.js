const proveedoresForm = document.querySelector("#proveedores-form");
const proveedoresMessage = document.querySelector("#proveedores-message");
const telefonoField = document.querySelector("#telefono");

if (proveedoresForm instanceof HTMLFormElement
    && proveedoresMessage instanceof HTMLElement
    && telefonoField instanceof HTMLInputElement) {
    const validatePhone = () => {
        const hasPhone = telefonoField.value.trim().length > 0;
        const digitCount = telefonoField.value.replace(/\D/g, "").length;
        const validDigitCount = digitCount >= 10 && digitCount <= 15;
        const validCharacters = /^[0-9+().\s-]+$/.test(telefonoField.value.trim());

        telefonoField.setCustomValidity(!hasPhone
            ? "Completa el teléfono."
            : !validCharacters
                ? "Usa solo números y separadores en el teléfono."
                : validDigitCount ? "" : "Ingresa un teléfono con entre 10 y 15 dígitos.");
    };

    telefonoField.addEventListener("input", validatePhone);

    proveedoresForm.addEventListener("submit", (event) => {
        validatePhone();
        event.preventDefault();

        if (!proveedoresForm.reportValidity()) {
            return;
        }

        proveedoresMessage.textContent = "Los datos son válidos. No se guardaron ni se enviaron.";
        proveedoresMessage.hidden = false;
    });

    proveedoresForm.addEventListener("input", () => {
        proveedoresMessage.hidden = true;
    });

    proveedoresForm.addEventListener("reset", () => {
        telefonoField.setCustomValidity("");
        proveedoresMessage.hidden = true;
    });
}
