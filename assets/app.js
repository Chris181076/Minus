import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
document.querySelectorAll('.mark-arrival-btn').forEach(button => {
    button.addEventListener('click', async () => {
        const id = button.dataset.id;
        const response = await fetch(`/child/presence/mark-arrival/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            document.querySelector(`.arrival-time[data-id="${id}"]`).textContent = data.arrivalTime;
            button.replaceWith('Oui');
            // Mettre à jour l'état visuel
            button.closest('tr').querySelector('td:nth-child(2)').textContent = '🟢';
        }
    });
});

document.querySelectorAll('.mark-departure-btn').forEach(button => {
    button.addEventListener('click', async () => {
        const id = button.dataset.id;
        const response = await fetch(`/child/presence/mark-departure/${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            document.querySelector(`.departure-time[data-id="${id}"]`).textContent = data.departureTime;
            button.replaceWith('✔️');
        }
    });
});