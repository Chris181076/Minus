import './bootstrap.js';
import './presence.js';

const aujourdHui = new Date().toISOString().split("T")[0]; // YYYY-MM-DD
const derniereNettoyage = localStorage.getItem("last_clear");

if (derniereNettoyage !== aujourdHui) {
  localStorage.clear();
  localStorage.setItem("last_clear", aujourdHui);
  console.log("Local storage nettoyé !");
}


import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
