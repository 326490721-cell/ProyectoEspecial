// Obtiene los elementos del menú; si no existen, no registra eventos.
const menuButton = document.querySelector(".menu-toggle");
const menu = document.querySelector("#main-menu");

if (menuButton && menu) {
    // Cierra el menú y restablece el estado accesible del botón.
    const closeMenu = () => {
        menu.hidden = true;
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-label", "Abrir menú");
    };

    // Alterna la visibilidad y actualiza la etiqueta accesible del botón.
    menuButton.addEventListener("click", () => {
        const isOpen = menuButton.getAttribute("aria-expanded") === "true";
        menu.hidden = isOpen;
        menuButton.setAttribute("aria-expanded", String(!isOpen));
        menuButton.setAttribute("aria-label", isOpen ? "Abrir menú" : "Cerrar menú");
    });

    // Cierra el menú cuando se selecciona uno de sus enlaces.
    menu.addEventListener("click", (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeMenu();
        }
    });

    // Cierra el menú al hacer clic fuera de él y de su botón.
    document.addEventListener("click", (event) => {
        if (!menu.hidden && event.target instanceof Node
            && !menu.contains(event.target) && !menuButton.contains(event.target)) {
            closeMenu();
        }
    });

    // Permite cerrar con Escape y devuelve el foco al botón.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !menu.hidden) {
            closeMenu();
            menuButton.focus();
        }
    });
}