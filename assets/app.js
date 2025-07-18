import './bootstrap.js';
import './presence.js';

/*document.getElementById('create-user-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    const responseDiv = document.getElementById('response');

    try {
        const response = await fetch('/api/create-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (response.ok) {
            responseDiv.innerHTML = '<p style="color: green;">Utilisateur créé avec succès !</p>';
        } else {
            responseDiv.innerHTML = `<p style="color: red;">Erreur : ${result.error}</p>`;
        }
    } catch (err) {
        responseDiv.innerHTML = `<p style="color: red;">Erreur de connexion à l’API</p>`;
    }
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */


import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
