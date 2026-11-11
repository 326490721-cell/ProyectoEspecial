// Validate the credentials before sending them to the server.
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
                    : "The username must contain 3 to 20 letters or numbers."
            );
            const passwordCharacterCount = Array.from(passwordInput.value).length;
            const passwordByteCount = new TextEncoder().encode(passwordInput.value).length;
            passwordInput.setCustomValidity(
                passwordCharacterCount >= 12 && passwordByteCount <= 72
                    ? ""
                    : "The password must contain at least 12 characters and no more than 72 bytes."
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
        throttled: "There have been too many failed attempts. Wait 15 minutes before trying again."
    };
    const error = query.get("error");

    if (error && messages[error]) {
        loginMessage.textContent = messages[error];
        loginMessage.hidden = false;
    } else if (query.has("logout")) {
        loginMessage.textContent = "You have been logged out successfully.";
        loginMessage.hidden = false;
    }
}