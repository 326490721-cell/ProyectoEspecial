// Reproduce el sonido guardado en la carpeta de audio al activar un botón.
const buttonSound = new Audio("audio/Botones.mp3");
buttonSound.preload = "auto";

document.addEventListener("click", (event) => {
    if (event.target instanceof Element && event.target.closest("button")) {
        buttonSound.currentTime = 0;
        const playback = buttonSound.play();
        playback.catch((error) => {
            console.error("No se pudo reproducir audio/Botones.mp3:", error);
        });
    }
});
