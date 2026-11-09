// Carga el sonido junto al script para que también pueda empezar antes de navegar.
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

// Inicia el sonido antes de que un enlace navegue a otra página.
document.addEventListener("pointerdown", (event) => {
    if (event.target instanceof Element && event.target.closest("button, a[href]")) {
        playButtonSound();
    }
}, true);

// También reproduce el sonido al activar botones o enlaces con teclado.
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
