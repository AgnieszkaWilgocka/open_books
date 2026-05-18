const hamburgerIcon = document.querySelector('.hamburger-icon');
const menu = document.querySelector('.mobile-menu');

hamburgerIcon.addEventListener('click', () => {
    hamburgerIcon.classList.toggle('active');
    menu.classList.toggle('visible');
});

