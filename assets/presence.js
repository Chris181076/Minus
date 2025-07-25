// BroadcastChannel global
const channel =
  typeof BroadcastChannel !== "undefined"
    ? new BroadcastChannel("presence_updates")
    : null;

// Flag pour éviter les appels concurrents
let syncInProgress = false;

// Fonction principale de synchronisation
function syncWithDatabase() {
  if (syncInProgress) return;
  syncInProgress = true;

  const today = new Date().toISOString().split("T")[0];

  fetch(`/child/presence/sync/${today}`, {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        localStorage.setItem(
          `presences_${today}`,
          JSON.stringify(data.presences)
        );
        resetAllRows();
        loadSavedPresences();

        // Notifier les autres onglets
        channel?.postMessage("sync");
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la synchronisation:", error);
    })
    .finally(() => {
      syncInProgress = false;
    });
}

function resetAllRows() {
  document
    .querySelectorAll("tr[data-child-id]")
    .forEach(resetRowToInitialState);
}

function resetRowToInitialState(row) {
  const childId = row.dataset.childId;

  row.querySelector(".status-cell").innerHTML = "🔴";
  row.querySelector(".presence-cell").innerHTML = "Non";

  const arrivalTimeCell = row.querySelector(".arrival-time");
  const departureTimeCell = row.querySelector(".departure-time");
  if (arrivalTimeCell) arrivalTimeCell.textContent = "";
  if (departureTimeCell) departureTimeCell.textContent = "";

  const departureAction = row.querySelector(".departure-action");
  if (departureAction) {
    departureAction.innerHTML = `
            <button class="presence mark-arrival-btn" data-id="${childId}">
                Arrivée
            </button>
        `;
  }

  const actionsCell = row.querySelector(".actions-cell");
  if (actionsCell) {
    const template = document.querySelector(`#delete-form-${childId}`);
    if (template) {
      actionsCell.innerHTML = template.innerHTML;
    }
  }
}

function loadSavedPresences() {
  const today = new Date().toISOString().split("T")[0];
  const savedPresences = JSON.parse(
    localStorage.getItem(`presences_${today}`) || "{}"
  );

  Object.entries(savedPresences).forEach(([childId, presenceData], index) => {
    setTimeout(() => {
      const row = document.querySelector(`tr[data-child-id="${childId}"]`);
      if (row && presenceData) {
        if (presenceData.arrivalTime && presenceData.presenceId) {
          updateUIWithPresence(row, presenceData);
          const arrivalCell = row.querySelector(".arrival-time");
          const button = document.createElement("button");
          button.className = "edit-hour";
          button.dataset.type = "arrival";
          button.dataset.id = presenceData.presenceId;
          button.textContent = "🕑";
          arrivalCell.appendChild(button);
        }

        if (presenceData.departureTime) {
          updateUIWithDeparture(row, presenceData);
        }
      }
    }, index * 5);
  });
}

function waitForRowsAndLoadPresencesOnce() {
  const tableBody = document.querySelector("tbody");
  if (!tableBody) return;

  const observer = new MutationObserver((mutations, obs) => {
    if (document.querySelector("tr[data-child-id]")) {
      loadSavedPresences();
      obs.disconnect();
    }
  });

  observer.observe(tableBody, {
    childList: true,
    subtree: false,
  });
}

function updateUIWithPresence(row, data) {
  row.querySelector(".status-cell").innerHTML = "🟢";
  row.querySelector(".presence-cell").innerHTML = "Oui";

  const arrivalTime = new Date(data.arrivalTime);
  const arrivalTimeCell = row.querySelector(".arrival-time");
  if (arrivalTimeCell) {
    arrivalTimeCell.textContent = `${arrivalTime
      .getUTCHours()
      .toString()
      .padStart(2, "0")}:${arrivalTime
      .getMinutes()
      .toString()
      .padStart(2, "0")}`;
  }


  const departureBtn = document.createElement("button");
  departureBtn.className = "depart mark-departure-btn";
  departureBtn.textContent = "Départ";
  departureBtn.dataset.id = data.presenceId;

    const actionsCell = row.querySelector(".actions-cell");
  if (actionsCell) {
    // Vider d'abord la cellule pour éviter les duplications
    actionsCell.innerHTML = "";
    
    // Créer le bouton de suppression
    const deleteBtn = document.createElement("button");
    deleteBtn.className = "depart delete-btn";
    deleteBtn.textContent = "Supprimer";
    deleteBtn.dataset.id = data.presenceId;
    
    actionsCell.appendChild(deleteBtn);
  }

  const departureAction = row.querySelector(".departure-action");
  if (departureAction) {
    departureAction.innerHTML = "";
    departureAction.appendChild(departureBtn);
  }
}

function updateUIWithDeparture(row, data) {
  const departureTime = new Date(data.departureTime);
  const departureTimeCell = row.querySelector(".departure-time");
  if (departureTimeCell) {
    departureTimeCell.textContent = `${departureTime
      .getHours()
      .toString()
      .padStart(2, "0")}:${departureTime
      .getMinutes()
      .toString()
      .padStart(2, "0")}`;
  }

  const departureAction = row.querySelector(".departure-action");
  if (departureAction) {
    departureAction.innerHTML = "✔️";
  }
}

function setupStorageSync() {
  window.addEventListener("storage", (event) => {
    if (event.key && event.key.startsWith("presences_")) {
      setTimeout(loadSavedPresences, 100);
    }
  });

  if (channel) {
    channel.onmessage = () => {
      setTimeout(loadSavedPresences, 100);
    };
  }
}

document.addEventListener("turbo:load", () => {
  const today = new Date().toISOString().split("T")[0];

  waitForRowsAndLoadPresencesOnce();
  setupStorageSync();

    if (document.querySelector("tr[data-child-id]")) {
    loadSavedPresences(); // ← Ajouter cette ligne
  }

  setTimeout(() => {
    if (document.querySelector("tr[data-child-id]")) {
      syncWithDatabase();
    }
  }, 500);

  if (document.querySelector('[data-page="presence"]')?.closest(".active")) {
    if (typeof initializePresences === "function") {
      initializePresences(today);
    }
  }

  document
    .querySelector('[data-page="presence"]')
    ?.addEventListener("click", () => {
      const currentState = localStorage.getItem(`presences_${today}`);
      if (currentState) {
        localStorage.setItem("lastPresenceState", currentState);
      }
    });

  document.body.addEventListener("click", (e) => {
    // ARRIVÉE
    if (e.target.classList.contains("mark-arrival-btn")) {
      const childId = e.target.dataset.id;
      const now = new Date();
      const localDateTime = now.toLocaleString("sv-SE");

      fetch(`/child/presence/mark-arrival/${childId}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ arrivalTime: localDateTime }), // <-- à l'extérieur du bloc headers
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const row = e.target.closest("tr");
            updateUIWithPresence(row, data);
            const actionsCell = row.querySelector(".actions-cell");

            if (actionsCell) {
              const template = document.querySelector(
                `#delete-form-${childId}`
              );
              if (template && template.content) {
                actionsCell.innerHTML = "";
                actionsCell.appendChild(template.content.cloneNode(true));
              }
            }
            const today = now.toLocaleString("sv-SE").split(" ")[0];
            const saved = JSON.parse(
              localStorage.getItem(`presences_${today}`) || "{}"
            );
            saved[childId] = data;
            localStorage.setItem(`presences_${today}`, JSON.stringify(saved));

            channel?.postMessage("update");
          } else {
            alert("Erreur: " + data.message);
          }
        })
        .catch((err) => {
          console.error("Erreur:", err);
          alert("Erreur de connexion");
        });
    }

    // DÉPART
    if (e.target.classList.contains("mark-departure-btn")) {
      const presenceId = e.target.dataset.id;

      fetch(`/child/presence/mark-departure/${presenceId}`, { method: "POST" })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            const row = e.target.closest("tr");
            updateUIWithDeparture(row, data);

            const today = new Date().toISOString().split("T")[0];
            const saved = JSON.parse(
              localStorage.getItem(`presences_${today}`) || "{}"
            );
            const childId = row.dataset.childId;

            if (saved[childId]) {
              saved[childId].departureTime = data.departureTime;
              localStorage.setItem(`presences_${today}`, JSON.stringify(saved));
              channel?.postMessage("update");
            }
          } else {
            alert("Erreur: " + data.message);
          }
        })
        .catch((err) => {
          console.error("Erreur:", err);
          alert("Erreur de connexion");
        });
    }

    SUPPRESSION
        if (e.target.dataset.Id('delete-btn')) {
            const presenceId = e.target.dataset.id;
            if (confirm('Êtes-vous sûr de vouloir supprimer cette présence ?')) {
                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
                                document.querySelector('input[name="_token"]')?.value || '';
    console.log('Token trouvé:', csrfToken);
    console.log('Presence ID:', presenceId);
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
                        syncWithDatabase();
                    } else {
                        alert(`Erreur HTTP: ${response.status}`);
                    }
                })
                .catch(error => {
                    alert('Erreur de suppression: ' + error.message);
                });
            }
        }
  });
});

document.body.addEventListener("click", function (e) {
  if (e.target.classList.contains("edit-hour")) {
    const btn = e.target;
    const cell = btn.closest("td");
    const presenceId = btn.dataset.id;
    const type = btn.dataset.type;
    const timeSpan = cell.querySelector(".time-value");
    const currentTime =
      timeSpan && !timeSpan.classList.contains("no-time")
        ? timeSpan.textContent
        : "08:00";

    // Crée l'input
    const input = document.createElement("input");
    input.type = "time";
    input.value = currentTime;
    input.classList.add("time-editor");

    // Gère la sauvegarde
    const saveTime = () => {
      const newTime = input.value;
      if (!newTime) return;

      fetch(`/child/presence/update-time/${presenceId}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({
          type: type,
          time: newTime,
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            cell.innerHTML = `
                            <span class="time-value">${newTime}</span>
                            <button class="edit-hour" data-type="${type}" data-id="${presenceId}" title="Modifier">✎</button>
                        `;
          } else {
            alert("Erreur : " + (data.message || "Erreur inconnue"));
                }
        })
        .catch(() => {
          alert("Erreur réseau");
        });
    };

    input.addEventListener("blur", saveTime);
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        saveTime();
     }
    });  
    cell.innerHTML = "";
    cell.appendChild(input);
    input.focus();
  }
});

