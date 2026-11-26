document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('.nav-links a');
    links.forEach((link) => {
        if (link.getAttribute('href') && link.getAttribute('href').includes('/sobre')) {
            link.classList.add('active');
        }
    });
});
