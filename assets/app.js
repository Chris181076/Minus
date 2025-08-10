import './bootstrap.js';
import './presence.js';

const aujourdHui = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
const derniereNettoyage = localStorage.getItem("last_clear");

if (derniereNettoyage !== aujourdHui) {
  localStorage.clear();
  localStorage.setItem("last_clear", aujourdHui);
  console.log("Local storage nettoyé !");
}

function updateClock() {
  const now = new Date();
  const hours = String(now.getHours()).padStart(2, '0');
  const minutes = String(now.getMinutes()).padStart(2, '0');
  const seconds = String(now.getSeconds()).padStart(2, '0');
  document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
}

setInterval(updateClock, 1000);
updateClock();

import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
