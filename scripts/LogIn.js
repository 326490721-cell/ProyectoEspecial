// Valida el usuario antes de enviar al PHP; el navegador valida la longitud de la contraseña.
const loginForm = document.querySelector(".login-form");
const loginMessage = document.querySelector("#login-message");

if (loginForm instanceof HTMLFormElement) {
    const usernameInput = loginForm.querySelector("#username");
    const passwordInput = loginForm.querySelector("#password");
    const usernamePattern = /^[A-Za-z0-9]{3,20}$/;

    if (usernameInput instanceof HTMLInputElement
        && passwordInput instanceof HTMLInputElement) {
        const updateValidation = () => {
            usernameInput.setCustomValidity(
                usernamePattern.test(usernameInput.value)
                    ? ""
                    : "El usuario debe tener de 3 a 20 letras o números."
            );
            const passwordCharacterCount = Array.from(passwordInput.value).length;
            const passwordByteCount = new TextEncoder().encode(passwordInput.value).length;
            passwordInput.setCustomValidity(
                passwordCharacterCount >= 12 && passwordByteCount <= 72
                    ? ""
                    : "La contraseña debe tener al menos 12 caracteres y un máximo de 72 bytes."
            );
        };

        loginForm.addEventListener("input", updateValidation);
        loginForm.addEventListener("submit", (event) => {
            updateValidation();

            if (!loginForm.checkValidity()) {
                event.preventDefault();
                loginForm.reportValidity();
            }
        });
    }
}

if (loginMessage instanceof HTMLElement) {
    const query = new URLSearchParams(window.location.search);
    const messages = {
        credentials: "The username or password is incorrect.",
        server: "The login service is unavailable. Please try again later.",
        throttled: "Too many failed attempts. Please wait 15 minutes before trying again."
    };
    const error = query.get("error");

    if (error && messages[error]) {
        loginMessage.textContent = messages[error];
        loginMessage.hidden = false;
    } else if (query.has("logout")) {
        loginMessage.textContent = "You have been logged out.";
        loginMessage.hidden = false;
    }
}