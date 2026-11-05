const menuButton = document.querySelector(".menu-toggle");
const menu = document.querySelector("#main-menu");

if (menuButton && menu) {
    const closeMenu = () => {
        menu.hidden = true;
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-label", "Abrir menú");
    };

    menuButton.addEventListener("click", () => {
        const isOpen = menuButton.getAttribute("aria-expanded") === "true";
        menu.hidden = isOpen;
        menuButton.setAttribute("aria-expanded", String(!isOpen));
        menuButton.setAttribute("aria-label", isOpen ? "Abrir menú" : "Cerrar menú");
    });

    menu.addEventListener("click", (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeMenu();
        }
    });

    document.addEventListener("click", (event) => {
        if (!menu.hidden && event.target instanceof Node
            && !menu.contains(event.target) && !menuButton.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !menu.hidden) {
            closeMenu();
            menuButton.focus();
        }
    });
}