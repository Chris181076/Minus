// BroadcastChannel global
const channel =
  typeof BroadcastChannel !== "undefined"
    ? new BroadcastChannel("presence_updates")
    : null;

// Flag pour éviter les appels concurrents
let syncInProgress = false;
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

  Object.entries(savedPresences).forEach(([childId, presenceData]) => {
    const row = document.querySelector(`tr[data-child-id="${childId}"]`);
    if (row && presenceData) {
      if (presenceData.arrivalTime && presenceData.presenceId) {
        updateUIWithPresence(row, presenceData);

        const arrivalCell = row.querySelector(".arrival-time");

        const existingButton = arrivalCell.querySelector(".edit-hour[data-type='arrival']");
        if (!existingButton) {
          const button = document.createElement("button");
          button.className = "edit-hour";
          button.dataset.type = "arrival";
          button.dataset.id = presenceData.presenceId;
          button.textContent = "🕑";
          arrivalCell.appendChild(button);
        }
      }
      if (presenceData.departureTime && presenceData.presenceId) {
        updateUIWithDeparture(row, presenceData);

        const departureCell = row.querySelector(".departure-time");
        if (departureCell) {
          const existingButton = departureCell.querySelector(".edit-hour[data-type='departure']");
          if (!existingButton) {
            const button = document.createElement("button");
            button.className = "edit-hour";
            button.dataset.type = "departure";
            button.dataset.id = presenceData.presenceId;
            const departureDate = new Date(presenceData.departureTime);
            const hour = departureDate.getHours();
            button.textContent = `🕑 ${formatToUtcTime(presenceData.departureTime, false)}`;
            button.classList.add(hour < 12 ? "morning" : "afternoon");
            departureCell.appendChild(button);
          }
        }
      }
    }
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
    arrivalTimeCell.textContent = formatToUtcTime(data.arrivalTime, false);
  }
  const departureBtn = document.createElement("button");
  departureBtn.className = "depart mark-departure-btn";
  departureBtn.textContent = "Départ";
  departureBtn.dataset.id = data.presenceId;

    const actionsCell = row.querySelector(".actions-cell");
   
      if (actionsCell && window.csrfToken) {
        actionsCell.innerHTML = "";

        const form = document.createElement("form");
        form.method = "post";
        form.action = `/child/presence/${data.presenceId}/delete`;
        form.style.display = "inline";

        const tokenInput = document.createElement("input");
        tokenInput.type = "hidden";
        tokenInput.name = "_token";
        tokenInput.value = window.csrfToken; 

        const deleteBtn = document.createElement("button");
        deleteBtn.className = "depart delete-btn";
        deleteBtn.textContent = "Supprimer";
        deleteBtn.dataset.id = data.presenceId;
        deleteBtn.dataset.csrf = window.csrfToken; 
        
       
        deleteBtn.onclick = (e) => {
            e.preventDefault();
            return confirm("Es-tu sûr de vouloir supprimer cette présence ?");
        };

        form.appendChild(tokenInput);
        form.appendChild(deleteBtn);
        actionsCell.appendChild(form);
        
        console.log("Bouton de suppression créé avec token :", window.csrfToken);
    } else {
        console.error("Token CSRF non disponible ou cellule d'actions introuvable");
    }


  const departureAction = row.querySelector(".departure-action");
  if (departureAction) {
    departureAction.innerHTML = "";
    departureAction.appendChild(departureBtn);
  }
}

function updateUIWithDeparture(row, data) {
 
  const departureAction = row.querySelector(".departure-action");
  if (departureAction) {
    departureAction.innerHTML = "✔️";
  }
}
function formatToUtcTime(isoString, withSeconds = false) {
  const date = new Date(isoString);
  const options = {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'UTC'
  };

  if (withSeconds) {
    options.second = '2-digit';
  }

  return date.toLocaleTimeString([], options);
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
    loadSavedPresences();
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

  document.querySelector('[data-page="presence"]')
    ?.addEventListener("click", () => {
      const currentState = localStorage.getItem(`presences_${today}`);
      if (currentState) {
        localStorage.setItem("lastPresenceState", currentState);
      }
    });

 document.body.addEventListener("click", async (e) => {

  // ARRIVÉE
      
  if (e.target.classList.contains("mark-arrival-btn")) {
    const childId = e.target.dataset.id;
    const time = new Date();
    time.setHours(time.getHours() + 2);
    const arrivalTime = time.toISOString();

    fetch(`/child/presence/mark-arrival/${childId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ arrivalTime: arrivalTime}),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const row = e.target.closest("tr");
          updateUIWithPresence(row, data);

          const actionsCell = row.querySelector(".actions-cell");
          if (actionsCell) {
            const template = document.querySelector(`#delete-form-${childId}`);
            if (template && template.content) {
              actionsCell.innerHTML = "";
              actionsCell.appendChild(template.content.cloneNode(true));
            }
          }

          const today = new Date().toISOString().split("T")[0];
          const saved = JSON.parse(localStorage.getItem(`presences_${today}`) || "{}");
          saved[childId] = data;
          localStorage.setItem(`presences_${today}`, JSON.stringify(saved));
          loadSavedPresences();
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
  else if (e.target.classList.contains("mark-departure-btn")) {
    const presenceId = e.target.dataset.id;

    fetch(`/child/presence/mark-departure/${presenceId}`, { method: "POST" })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const row = e.target.closest("tr");
          updateUIWithDeparture(row, data);
         
          const today = new Date().toISOString().split("T")[0];
          const saved = JSON.parse(localStorage.getItem(`presences_${today}`) || "{}");
          localStorage.setItem(`presences_${today}`, JSON.stringify(saved));
          const departureCell = row.querySelector(".departure-time");

if (departureCell) {
  const existingButton = departureCell.querySelector(".edit-hour[data-type='departure']");
  if (!existingButton) {
    const button = document.createElement("button");
    button.className = "edit-hour";
    button.dataset.type = "departure";
    button.dataset.id = data.presenceId;
    const departureDate = new Date(data.departureTime);
    const hour = departureDate.getHours(); 

    button.textContent = `🕑 ${formatToUtcTime(data.departureTime, false)}`;

    button.classList.add(hour < 12 ? "morning" : "afternoon");


    departureCell.appendChild(button);
  }
}
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
  // SUPPRESSION

  else if (!e.target.classList.contains("delete-btn")) return;

  e.preventDefault();
  e.stopPropagation();

  const button = e.target.closest(".delete-btn");
  const presenceId = button.dataset.id;
  const csrfToken = button.dataset.csrf || window.csrfToken;
 

  console.log("Tentative de suppression - ID:", presenceId, "Token:", csrfToken);
 
  if (!confirm("Supprimer cette présence ?")) return;

  try {
    const response = await fetch(`/child/presence/${presenceId}/delete`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: new URLSearchParams({ _token: csrfToken })
    });

    const data = await response.json();

    if (data.success) {
      const row = button.closest("tr");
      resetRowToInitialState(row);
      button.remove();

      const today = new Date().toISOString().split("T")[0];
      const key = `presences_${today}_${presenceId}`;
      localStorage.removeItem(key);

      showBanner(data.message || "Suppression réussie 🎉");
      clearActionsCell(row);
      console.log("Suppression réussie pour l'ID:", presenceId);

    } else {
      alert(data.message || "Suppression échouée");
      console.error("Erreur de suppression:", data.message);
    }
  } catch (err) {
    alert("Erreur de requête : " + err.message);
    console.error("Erreur de requête:", err);
  }
});
function safeLoadPresences() {
  if (document.readyState === "complete") {
    loadSavedPresences();
  } else {
    window.addEventListener("load", () => loadSavedPresences());
  }
}

function clearActionsCell(row) {
    const actionsCell = row.querySelector(".actions-cell");
    if (actionsCell) {
        actionsCell.innerHTML = "";
        console.log("Cellule d'actions nettoyée");
    }
}


function showBanner(message) {
  const banner = document.createElement("div");
  banner.textContent = message;
  Object.assign(banner.style, {
    position: "fixed",
    top: "1em",
    right: "1em",
    background: "#4caf50",
    color: "#fff",
    padding: "10px",
    borderRadius: "5px",
    zIndex: "1000"
  });

  document.body.appendChild(banner);
  setTimeout(() => banner.remove(), 3000);
}
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



