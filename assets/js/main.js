document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.main-nav');

    if (menuToggle && navigation) {
        menuToggle.addEventListener('click', function () {
            const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', String(!isOpen));
            navigation.classList.toggle('is-open', !isOpen);
        });
    }

    document.querySelectorAll('.save-button').forEach(function (button) {
        button.addEventListener('click', function () {
            const saved = button.classList.toggle('is-saved');
            button.textContent = saved ? '♥' : '♡';
            button.setAttribute('aria-pressed', String(saved));
        });
    });
});
