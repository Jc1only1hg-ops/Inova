const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

// Evento para mostrar el panel de registro
registerBtn.addEventListener("click", () => {
  container.classList.add("active");
  window.location.hash = "register";
});

// Evento para mostrar el panel de inicio de sesión
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

// Manejar redirecciones de los formularios
const signInForm = document.getElementById("signInForm");
const signUpForm = document.getElementById("signUpForm");

// Función para revisar el hash de la URL al cargar o cambiar
function checkHash() {
  if (window.location.hash === "#register") {
    container.classList.add("active");
  } else {
    container.classList.remove("active");
  }
}

// REDIRECCIÓN PARA INICIO DE SESIÓN
if (signInForm) {
  signInForm.addEventListener("submit", (e) => {
    e.preventDefault();
    
    const email = document.getElementById("signInEmail").value.trim();
    const password = document.getElementById("signInPassword").value.trim();
    
    if (email && password) {
      // Validación simple - aquí puedes agregar más validaciones
      if (email.includes('@') && password.length >= 6) {
        // Guardar sesión en localStorage (opcional)
        localStorage.setItem('userEmail', email);
        localStorage.setItem('isLoggedIn', 'true');
        
        // Redirige al dashboard para usuarios logueados
        window.location.href = "dashboard-cliente.html";
      } else {
        alert("Por favor, ingresa un correo válido y una contraseña de al menos 6 caracteres.");
      }
    } else {
      alert("Por favor, completa todos los campos.");
    }
  });
}

// REDIRECCIÓN PARA REGISTRO
if (signUpForm) {
  signUpForm.addEventListener("submit", (e) => {
    e.preventDefault();
    
    const name = document.getElementById("signUpName").value.trim();
    const email = document.getElementById("signUpEmail").value.trim();
    const password = document.getElementById("signUpPassword").value.trim();
    
    if (name && email && password) {
      // Validación básica
      if (name.length >= 2 && email.includes('@') && password.length >= 6) {
        // Guardar datos de registro en localStorage (opcional)
        localStorage.setItem('userName', name);
        localStorage.setItem('userEmail', email);
        localStorage.setItem('isLoggedIn', 'true');
        
        // Redirige a página de confirmación de registro
        window.location.href = "registro-exitoso.html";
      } else {
        alert("Por favor, asegúrate de que todos los campos sean válidos. La contraseña debe tener al menos 6 caracteres.");
      }
    } else {
      alert("Por favor, completa todos los campos.");
    }
  });
}