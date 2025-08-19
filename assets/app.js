

//  Importation des fichiers CSS globaux
import './styles/app.css';      // Fichier de styles principaux du site
import './styles/header.css';   // Styles spécifiques à l’en-tête du site

//  Sélection du bloc principal de la page HTML
const main = document.querySelector('main');

//  Vérifie si <main> contient la classe "page-home"
if (main?.classList.contains('page-home')) {
  //  Si oui, charge dynamiquement les styles spécifiques à la page d’accueil
  import('./styles/home.css');
}





//  Attendre que le DOM soit complètement chargé avant de manipuler les éléments HTML
document.addEventListener('DOMContentLoaded', function () {

  //  Récupère la case à cocher "Afficher le mot de passe" via son ID
  const toggle = document.getElementById('togglePassword');

  //  Sélectionne le champ du mot de passe dans le formulaire d’inscription
  // Utilise un sélecteur qui cible les IDs commençant par "registration_form_plainPassword"
  const passwordInput = document.querySelector('input[id^="registration_form_plainPassword"]');

  //  Vérifie que les deux éléments existent sur la page avant d’agir
  if (toggle && passwordInput) {

    //  Lorsque l’utilisateur coche ou décoche la case
    toggle.addEventListener('change', function () {

      //  Si la case est cochée, affiche le mot de passe en clair
      //  Sinon, masque le mot de passe avec des •••
      passwordInput.type = toggle.checked ? 'text' : 'password';
    });
  }

  //  Toggle du menu mobile
  const navToggleButton = document.querySelector('.nav-toggle');
  const siteNav = document.getElementById('siteNav');
  if (navToggleButton && siteNav) {
    navToggleButton.addEventListener('click', () => {
      const isOpen = siteNav.classList.toggle('is-open');
      navToggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }
});




 

