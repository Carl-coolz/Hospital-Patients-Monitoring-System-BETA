//Welcome Message
document.getElementById('understandBtn').addEventListener('click', function() {
            const overlay = document.getElementById('welcomeOverlay');
            overlay.style.transition = 'opacity 0.4s ease';
            overlay.style.opacity = '0';

            // Wait for fade animation, then remove from DOM
            setTimeout(() => {
                overlay.remove();
            }, 400);
        });
//Login Functions
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});

