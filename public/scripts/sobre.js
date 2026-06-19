document.addEventListener('DOMContentLoaded', function () {
    const currentLink = document.querySelector('.nav-links a[href="sobre.html"]');
    if (currentLink) {
        currentLink.classList.add('active');
    }
});
