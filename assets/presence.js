function loadSavedPresences() {
    const today = new Date().toISOString().split('T')[0];
    const savedPresences = JSON.parse(localStorage.getItem(`presences_${today}`) || '{}');

    Object.entries(savedPresences).forEach(([childId, presenceData]) => {
        const row = document.querySelector(`tr[data-child-id="${childId}"]`);
        if (row) {
            if (presenceData.arrivalTime) {
                updateUIWithPresence(row, presenceData);
            }
            if (presenceData.departureTime) {
                updateUIWithDeparture(row, presenceData);
            }
        }
    });
}

// Nouvelle fonction pour synchroniser avec la base de données
function syncWithDatabase() {
    const today = new Date().toISOString().split('T')[0];
    
    fetch(`/child/presence/sync/${today}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour le localStorage avec les données de la BDD
            localStorage.setItem(`presences_${today}`, JSON.stringify(data.presences));
            
            // Recharger l'interface
            resetAllRows();
            loadSavedPresences();
            
            // Notifier les autres onglets
            if (typeof BroadcastChannel !== 'undefined') {
                new BroadcastChannel('presence_updates').postMessage('sync');
            }
        }
    })
    .catch(error => {
        console.error('Erreur lors de la synchronisation:', error);
    });
}

// Fonction pour remettre toutes les lignes à l'état initial
function resetAllRows() {
    document.querySelectorAll('tr[data-child-id]').forEach(row => {
        resetRowToInitialState(row);
    });
}

// Fonction pour remettre une ligne à l'état initial
function resetRowToInitialState(row) {
    const childId = row.dataset.childId;
    
    // Remettre le statut à l'état initial
    row.querySelector('.status-cell').innerHTML = '🔴';
    row.querySelector('.presence-cell').innerHTML = 'Non';
    
    // Vider les heures
    const arrivalTimeCell = row.querySelector('.arrival-time');
    const departureTimeCell = row.querySelector('.departure-time');
    if (arrivalTimeCell) arrivalTimeCell.textContent = '';
    if (departureTimeCell) departureTimeCell.textContent = '';
    
    // Remettre le bouton d'arrivée
    const departureAction = row.querySelector('.departure-action');
    if (departureAction) {
        departureAction.innerHTML = `
            <button class="btn btn-sm btn-success mark-arrival-btn" data-id="${childId}">
                Arrivée
            </button>
        `;
    }
    
    // Vider les actions
    const actionsCell = row.querySelector('.actions-cell');
    if (actionsCell) {
        actionsCell.innerHTML = '';
    }
}

function waitForRowsAndLoadPresencesOnce() {
    const observer = new MutationObserver((mutations, obs) => {
        if (document.querySelector('tr[data-child-id]')) {
            loadSavedPresences();
            obs.disconnect(); // Stop observing
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

function updateUIWithPresence(row, data) {
    row.querySelector('.status-cell').innerHTML = '🟢';
    row.querySelector('.presence-cell').innerHTML = 'Oui';
    
    const arrivalTime = new Date(data.arrivalTime);
    const arrivalTimeCell = row.querySelector('.arrival-time');
    if (arrivalTimeCell) {
        arrivalTimeCell.textContent = 
            arrivalTime.getHours().toString().padStart(2, '0') + ':' + 
            arrivalTime.getMinutes().toString().padStart(2, '0');
    }
    
    const departureBtn = document.createElement('button');
    departureBtn.className = 'btn btn-sm btn-warning mark-departure-btn';
    departureBtn.textContent = 'Départ';
    departureBtn.dataset.id = data.presenceId;
    
    const departureAction = row.querySelector('.departure-action');
    if (departureAction) {
        departureAction.innerHTML = '';
        departureAction.appendChild(departureBtn);
    }
    
    const actionsCell = row.querySelector('.actions-cell');
    if (actionsCell) {
        actionsCell.innerHTML = `
            <a href="/child/presence/${data.presenceId}">voir</a>
            <a href="/child/presence/${data.presenceId}/edit">modifier</a>
            <button class="btn btn-sm btn-danger delete-presence-btn" data-id="${data.presenceId}" style="margin-left: 5px;">
                Supprimer
            </button>
        `;
    }
}

function updateUIWithDeparture(row, data) {
    const departureTime = new Date(data.departureTime);
    const departureTimeCell = row.querySelector('.departure-time');
    if (departureTimeCell) {
        departureTimeCell.textContent = 
            departureTime.getHours().toString().padStart(2, '0') + ':' + 
            departureTime.getMinutes().toString().padStart(2, '0');
    }
    const departureAction = row.querySelector('.departure-action');
    if (departureAction) {
        departureAction.innerHTML = '✔️';
    }
}

function setupStorageSync() {
    // Écoute des changements de localStorage (autres onglets)
    window.addEventListener('storage', (event) => {
        if (event.key && event.key.startsWith('presences_')) {
            setTimeout(loadSavedPresences, 100);
        }
    });

    // Écoute du BroadcastChannel (pour l'onglet courant)
    if (typeof BroadcastChannel !== 'undefined') {
        const channel = new BroadcastChannel('presence_updates');
        channel.onmessage = (event) => {
            if (event.data === 'sync') {
                setTimeout(loadSavedPresences, 100);
            } else {
                setTimeout(loadSavedPresences, 100);
            }
        };
    }
}

// Initialisation au chargement de la page
document.addEventListener('turbo:load', function() {
    const today = new Date().toISOString().split('T')[0];
    waitForRowsAndLoadPresencesOnce();
    setupStorageSync();
    
    // Synchroniser avec la BDD au chargement après que les lignes soient présentes
    setTimeout(() => {
        if (document.querySelector('tr[data-child-id]')) {
            syncWithDatabase();
        }
    }, 500);
    
    if (document.querySelector('[data-page="presence"]')?.closest('.active')) {
        if (typeof initializePresences === 'function') {
            initializePresences(today);
        }
    }
    
    document.querySelector('[data-page="presence"]')?.addEventListener('click', function(e) {
        // Store current state before navigation
        const currentState = localStorage.getItem(`presences_${today}`);
        if (currentState) {
            localStorage.setItem('lastPresenceState', currentState);
        }
    });
    
    // Gestion des clics
    document.body.addEventListener('click', function(e) {
        // Marquer l'arrivée
        if (e.target.classList.contains('mark-arrival-btn')) {
            const childId = e.target.dataset.id;
            
            fetch(`/child/presence/mark-arrival/${childId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = e.target.closest('tr');
                    updateUIWithPresence(row, data);
                    
                    data.childId = childId;
                    // Sauvegarder dans localStorage
                    const today = new Date().toISOString().split('T')[0];
                    const savedPresences = JSON.parse(localStorage.getItem(`presences_${today}`) || '{}');
                    savedPresences[childId] = data;
                    localStorage.setItem(`presences_${today}`, JSON.stringify(savedPresences));

                    // Notifier tous les onglets
                    if (typeof BroadcastChannel !== 'undefined') {
                        new BroadcastChannel('presence_updates').postMessage('update');
                    }
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors du marquage d\'arrivée:', error);
                alert('Erreur de connexion');
            });
        }
        
        // Marquer le départ
        if (e.target.classList.contains('mark-departure-btn')) {
            const presenceId = e.target.dataset.id;
            
            fetch(`/child/presence/mark-departure/${presenceId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = e.target.closest('tr');
                    updateUIWithDeparture(row, data);
                    
                    // Mettre à jour localStorage
                    const today = new Date().toISOString().split('T')[0];
                    const savedPresences = JSON.parse(localStorage.getItem(`presences_${today}`) || '{}');
                    const childId = row.dataset.childId;
                    
                    if (savedPresences[childId]) {
                        savedPresences[childId].departureTime = data.departureTime;
                        localStorage.setItem(`presences_${today}`, JSON.stringify(savedPresences));

                        // Notifier tous les onglets
                        if (typeof BroadcastChannel !== 'undefined') {
                            new BroadcastChannel('presence_updates').postMessage('update');
                        }
                    }
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors du marquage de départ:', error);
                alert('Erreur de connexion');
            });
        }
        
        // Supprimer la présence
        if (e.target.classList.contains('delete-presence-btn')) {
            const presenceId = e.target.dataset.id;
            
            if (confirm('Êtes-vous sûr de vouloir supprimer cette présence ?')) {
                // Récupérer le token CSRF - essayer plusieurs méthodes
                let csrfToken = '';
                
                // Méthode 1: depuis un meta tag
                const metaToken = document.querySelector('meta[name="csrf-token"]');
                if (metaToken) {
                    csrfToken = metaToken.getAttribute('content');
                }
                
                // Méthode 2: depuis un input hidden (si pas de meta)
                if (!csrfToken) {
                    const hiddenToken = document.querySelector('input[name="_token"]');
                    if (hiddenToken) {
                        csrfToken = hiddenToken.value;
                    }
                }
                
                // Utiliser POST avec _method=DELETE comme Symfony l'attend
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('_method', 'DELETE');
                
                fetch(`/child/presence/${presenceId}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (response.ok) {
                        // Synchroniser avec la BDD après suppression
                        syncWithDatabase();
                    } else {
                        console.error('Erreur HTTP:', response.status);
                        alert('Erreur lors de la suppression (Code: ' + response.status + ')');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression: ' + error.message);
                });
            }
        }
    });
});

// Bouton de rafraîchissement amélioré
document.getElementById('refreshPresences')?.addEventListener('click', () => {
    syncWithDatabase();
});

// Synchronisation périodique (optionnel - désactivé par défaut)
// Décommentez si vous voulez une synchronisation automatique
/*
setInterval(() => {
    if (document.querySelector('tr[data-child-id]')) {
        syncWithDatabase();
    }
}, 30000); // Synchronise toutes les 30 secondes
*/