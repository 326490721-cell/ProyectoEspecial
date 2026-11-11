// Load the sound relative to this script so it works from every page.
const buttonSound = new Audio(new URL("../audio/Botones.mp3", document.currentScript.src));
buttonSound.preload = "auto";

const playButtonSound = () => {
    const sound = buttonSound.paused ? buttonSound : buttonSound.cloneNode();
    sound.currentTime = 0;
    const playback = sound.play();

    playback.catch((error) => {
        console.error("No se pudo reproducir audio/Botones.mp3:", error);
    });
};

// Start the sound before a link navigates to another page.
document.addEventListener("pointerdown", (event) => {
    if (event.target instanceof Element && event.target.closest("button, a[href]")) {
        playButtonSound();
    }
}, true);

// Also play the sound when buttons or links are activated by keyboard.
document.addEventListener("keydown", (event) => {
    if (event.repeat || !(event.target instanceof Element)) {
        return;
    }

    const control = event.target.closest("button, a[href]");
    const activatesControl = control instanceof HTMLButtonElement
        ? event.key === "Enter" || event.key === " "
        : control instanceof HTMLAnchorElement && event.key === "Enter";

    if (activatesControl) {
        playButtonSound();
    }
}, true);
