// Valida el formato del usuario y la contraseña antes de enviar al PHP.
const loginForm = document.querySelector(".login-form");

if (loginForm instanceof HTMLFormElement) {
    const usernameInput = loginForm.querySelector("#username");
    const passwordInput = loginForm.querySelector("#password");
    const usernamePattern = /^[A-Za-z0-9]{3,20}$/;
    const passwordPattern = /^[0-9]{4}-[0-9]{4}$/;

    if (usernameInput instanceof HTMLInputElement
        && passwordInput instanceof HTMLInputElement) {
        const updateValidation = () => {
            usernameInput.setCustomValidity(
                usernamePattern.test(usernameInput.value)
                    ? ""
                    : "El usuario debe tener de 3 a 20 letras o números."
            );
            passwordInput.setCustomValidity(
                passwordPattern.test(passwordInput.value)
                    ? ""
                    : "La contraseña debe tener el formato 1234-5678."
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