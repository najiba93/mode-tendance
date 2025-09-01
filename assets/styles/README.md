# 📁 Organisation des Fichiers CSS

## 🎯 Structure des Fichiers

### 1. **`app.css`** - Styles Généraux
- **Styles de base** : body, main, footer
- **Images des produits** : `.img-produit`, `.produit-overlay`
- **Images du panier** : `.img-panier`
- **Composants Bootstrap** : `.card`, `.alert`, `.table`, `.btn`
- **Bannière de cookies** : `.cookie-banner`
- **Responsive mobile** : media queries pour les images

### 2. **`header.css`** - Styles du Header
- **Header principal** : structure et layout
- **Logo et navigation** : menu principal
- **Catégories** : menu des catégories de produits
- **Menus déroulants** : dropdown pour profil et actions
- **Icônes et actions** : panier, profil, contact
- **Responsive mobile** : menu hamburger et adaptations

### 3. **`home.css`** - Styles de la Page d'Accueil
- **Page d'accueil** : `.page-home`
- **Carrousel** : `#carouselAccueil`
- **Contenu principal** : `.accueil`
- **Bouton d'action** : styles du bouton principal

## 🚀 Optimisations Appliquées

### ✅ **Code Supprimé :**
- Classes CSS inutilisées
- Règles dupliquées
- Commentaires obsolètes
- Code mort

### ✅ **Code Organisé :**
- Sections clairement délimitées
- Commentaires détaillés
- Structure logique
- Responsive design optimisé

### ✅ **Performance :**
- CSS minifié et optimisé
- Règles spécifiques et ciblées
- Media queries organisées
- Transitions fluides

## 📱 Responsive Design

### **Breakpoints :**
- **Desktop** : > 992px
- **Tablette** : 768px - 992px  
- **Mobile** : < 768px
- **Petit mobile** : < 480px

### **Adaptations :**
- Images redimensionnées
- Effets hover réduits
- Menus adaptés
- Espacements optimisés

## 🎨 Classes Principales

### **Images :**
- `.img-produit` - Images des produits avec hover
- `.img-panier` - Images du panier
- `.produit-overlay` - Overlay des noms de produits

### **Layout :**
- `.header-container` - Conteneur principal du header
- `.page-home` - Page d'accueil
- `.ratio-1x1` - Images carrées

### **Navigation :**
- `.site-nav` - Navigation principale
- `.categories-container` - Menu des catégories
- `.dropdown` - Menus déroulants

## 🔧 Maintenance

### **Ajouter un style :**
1. Identifier le bon fichier selon la fonction
2. Ajouter dans la section appropriée
3. Commenter le code
4. Tester le responsive

### **Modifier un style :**
1. Localiser la classe dans le bon fichier
2. Modifier en gardant la structure
3. Vérifier la compatibilité mobile
4. Tester les changements

## 📋 Bonnes Pratiques

- ✅ **Commentaires détaillés** pour chaque section
- ✅ **Structure logique** et organisée
- ✅ **Responsive first** design
- ✅ **Performance optimisée** avec des règles ciblées
- ✅ **Maintenance facilitée** avec une organisation claire
