// Find the menu elements; do not register handlers if they are missing.
const menuButton = document.querySelector(".menu-toggle");
const menu = document.querySelector("#main-menu");

if (menuButton && menu) {
    // Close the menu and restore the button's accessible state.
    const closeMenu = () => {
        menu.hidden = true;
        menuButton.setAttribute("aria-expanded", "false");
        menuButton.setAttribute("aria-label", "Open menu");
    };

    // Toggle visibility and update the button's accessible label.
    menuButton.addEventListener("click", () => {
        const isOpen = menuButton.getAttribute("aria-expanded") === "true";
        menu.hidden = isOpen;
        menuButton.setAttribute("aria-expanded", String(!isOpen));
        menuButton.setAttribute("aria-label", isOpen ? "Open menu" : "Close menu");
    });

    // Close the menu when one of its links is selected.
    menu.addEventListener("click", (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeMenu();
        }
    });

    // Close the menu when clicking outside it and its button.
    document.addEventListener("click", (event) => {
        if (!menu.hidden && event.target instanceof Node
            && !menu.contains(event.target) && !menuButton.contains(event.target)) {
            closeMenu();
        }
    });

    // Allow Escape to close the menu and return focus to its button.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !menu.hidden) {
            closeMenu();
            menuButton.focus();
        }
    });
}