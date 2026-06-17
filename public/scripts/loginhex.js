const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

registerBtn.addEventListener("click", () => {
  container.classList.add("active");
  window.location.hash = "register";
});

loginBtn.addEventListener("click", () => {
  container.classList.remove("active");
  window.location.hash = "";
});

// Función para revisar el hash de la URL al cargar o cambiar
function checkHash() {
  if (window.location.hash === "#register") {
    container.classList.add("active");
  } else {
    container.classList.remove("active");
  }
}

// Ejecutar al inicio y al cambiar el hash
window.addEventListener("DOMContentLoaded", checkHash);
window.addEventListener("hashchange", checkHash);

// Manejar redirecciones de los formularios a la vista de dashboard
const signInForm = document.querySelector(".sign-in form");
const signUpForm = document.querySelector(".sign-up form");

if (signInForm) {
  signInForm.addEventListener("submit", (e) => {
    e.preventDefault();
    window.location.href = "dashboard-cliente.html";
  });
}

if (signUpForm) {
  signUpForm.addEventListener("submit", (e) => {
    e.preventDefault();
    window.location.href = "dashboard-cliente.html";
  });
}