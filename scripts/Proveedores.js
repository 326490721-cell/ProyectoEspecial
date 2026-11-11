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
            ? "Enter a phone number."
            : !validCharacters
                ? "Use only numbers and separators in the phone number."
                : validDigitCount ? "" : "Enter a phone number with 10 to 15 digits.");
    };

    telefonoField.addEventListener("input", validatePhone);

    proveedoresForm.addEventListener("submit", (event) => {
        validatePhone();
        event.preventDefault();

        if (!proveedoresForm.reportValidity()) {
            return;
        }

        proveedoresMessage.textContent = "The information is valid. It has not been saved or submitted.";
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
