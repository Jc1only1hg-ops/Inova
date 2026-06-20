document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("container");
    const registerBtn = document.getElementById("register");
    const loginBtn = document.getElementById("login");

    if (container) {
        // Evaluamos si la URL termina con "#register"
        if (window.location.hash === '#register') {
            container.classList.add("active");
        }
    }

    // Eventos internos dinámicos (se quedan igual)
    if (registerBtn) {
        registerBtn.addEventListener("click", () => {
            container.classList.add("active");
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener("click", () => {
            container.classList.remove("active");
            // Opcional: Limpia el hash de la URL si el usuario vuelve a "Iniciar Sesión"
            if (window.location.hash) {
                history.replaceState(null, null, window.location.pathname);
            }
        });
    }
});