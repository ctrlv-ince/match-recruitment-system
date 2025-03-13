// Example: Add interactivity to cards
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('click', () => {
        window.location.href = card.querySelector('a').href;
    });
});