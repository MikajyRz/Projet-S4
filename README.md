# 🏦 Système de Gestion de Prêts - Version 2.0

Un système complet de gestion de prêts avec interface web moderne, génération de PDF et tableau de bord avancé.

## ✨ Nouvelles Fonctionnalités

- **📊 Tableau de Bord Interactif** : Vue d'ensemble avec statistiques en temps réel
- **📄 Génération de PDF** : Contrats de prêts professionnels
- **💰 Suivi des Fonds** : Total des fonds et disponibilité
- **🎨 Design Moderne** : Interface utilisateur améliorée avec animations
- **📱 Responsive Design** : Compatible mobile et tablette
- **🔔 Notifications** : Système de notifications en temps réel

## 🚀 Installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-projet]
   cd Examen_S4
   ```

2. **Configurer la base de données**
   - Créer une base de données MySQL
   - Importer le fichier `sql/pret_schema.sql`
   - Configurer les paramètres de connexion dans `ws/db.php`

3. **Configurer le serveur web**
   - Placer le projet dans le répertoire web
   - Ajuster l'URL de base dans `assets/js/main.js` (CONFIG.apiBase)

4. **Installer les dépendances**
   ```bash
   cd ws
   composer install
   ```

## 📊 Structure de la Base de Données

### Tables principales :
- `pret_login` : Utilisateurs du système
- `pret_client` : Informations des clients
- `pret_fond` : Fonds disponibles pour les prêts
- `pret_type_pret` : Types de prêts configurables
- `pret_pret` : Prêts accordés
- `pret_remboursement` : Échéances de remboursement

## 🔌 API Endpoints

### Clients
- `GET /clients` - Liste des clients
- `POST /clients` - Créer un client
- `PUT /clients/{id}` - Modifier un client
- `DELETE /clients/{id}` - Supprimer un client

### Fonds
- `GET /fonds` - Liste des fonds
- `POST /fonds` - Ajouter des fonds
- `PUT /fonds/{id}` - Modifier un fonds
- `DELETE /fonds/{id}` - Supprimer un fonds
- `GET /fonds/total` - **Total des fonds** ⭐
- `GET /fonds/disponibles` - **Fonds disponibles** ⭐

### Prêts
- `GET /prets` - Liste des prêts
- `POST /prets` - Créer un prêt
- `PUT /prets/{id}` - Modifier un prêt
- `DELETE /prets/{id}` - Supprimer un prêt
- `PUT /prets/{id}/valider` - Valider un prêt

### PDF
- `GET /pdf/pret/{id_pret}` - **Générer le PDF du prêt** ⭐

### Remboursements
- `GET /remboursements` - Liste des remboursements
- `GET /remboursements/pret/{id_pret}` - Remboursements d'un prêt
- `GET /remboursements/stats` - Statistiques des remboursements

## 💰 Fonctionnalités des Fonds

### Total des Fonds
Le système calcule automatiquement :
- **Total des fonds déposés** : Somme de tous les fonds ajoutés
- **Fonds disponibles** : Total - Montant des prêts validés
- **Taux d'utilisation** : Pourcentage des fonds utilisés

### Accès aux données
```javascript
// Obtenir le total des fonds
ajax("GET", "/fonds/total", null, (data) => {
    console.log("Total des fonds:", data.total);
});

// Obtenir les fonds disponibles
ajax("GET", "/fonds/disponibles", null, (data) => {
    console.log("Fonds disponibles:", data.disponibles);
});
```

## 📄 Génération de PDF

### Fonctionnalités du PDF
- **Contrat professionnel** avec en-tête et pied de page
- **Informations complètes** du prêt et du client
- **Échéancier détaillé** avec capital, intérêts et assurance
- **Calculs automatiques** des totaux et pourcentages
- **Zones de signature** pour validation

### Utilisation
```javascript
// Générer le PDF d'un prêt
function genererPDF(id_pret) {
    window.open(`${apiBase}/pdf/pret/${id_pret}`, '_blank');
}
```

## 🎨 Interface Utilisateur

### Design Moderne
- **Gradients et ombres** pour un look professionnel
- **Animations fluides** et transitions
- **Icônes emoji** pour une meilleure UX
- **Responsive design** pour tous les appareils

### Composants
- **Cartes statistiques** avec animations
- **Tableaux interactifs** avec tri et filtrage
- **Formulaires validés** avec messages d'erreur
- **Notifications toast** pour les actions

## 📱 Utilisation

1. **Tableau de Bord** : Commencer par `dashboard.php` pour une vue d'ensemble
2. **Gérer les fonds** : Ajouter des fonds via "Fonds"
3. **Créer des clients** : Enregistrer les clients dans "Clients"
4. **Configurer les types** : Définir les types de prêts
5. **Accorder des prêts** : Créer et valider les prêts
6. **Générer les PDF** : Cliquer sur l'icône 📄 pour les prêts validés
7. **Suivre les remboursements** : Consulter les échéances

## 🛠️ Technologies Utilisées

- **Backend** : PHP 7.4+, Flight Framework
- **Frontend** : HTML5, CSS3, JavaScript (ES6+)
- **Base de données** : MySQL 5.7+
- **Design** : CSS Grid, Flexbox, Animations CSS
- **PDF** : HTML/CSS pour génération de documents

## 📈 Fonctionnalités Avancées

### Tableau de Bord
- **Statistiques en temps réel**
- **Graphiques de répartition**
- **Derniers prêts et fonds**
- **Taux d'utilisation des fonds**

### Notifications
- **Succès** : Actions réussies
- **Erreurs** : Problèmes détectés
- **Avertissements** : Actions importantes
- **Informations** : Messages généraux

### Validation
- **Formulaires** : Validation côté client et serveur
- **Montants** : Vérification des valeurs numériques
- **Emails** : Validation du format email
- **Champs requis** : Vérification de la complétude

## 🔧 Configuration

### Variables globales
```javascript
const CONFIG = {
    apiBase: "http://localhost/T/Examen_S4/ws",
    currency: "€",
    dateFormat: "fr-FR"
};
```

### Personnalisation CSS
Les variables CSS permettent de personnaliser facilement l'apparence :
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #48bb78;
    --danger-color: #f56565;
    /* ... */
}
```

## 🆘 Support

Pour toute question ou problème :
1. Consultez la documentation
2. Vérifiez les logs d'erreur
3. Contactez l'équipe de développement

---

**Version 2.0** - Système de Gestion de Prêts avec PDF et Dashboard Avancé 