<?php
require_once 'template.php';

$template = new Template('Gestion des Étudiants - Système de Gestion');

ob_start();
?>

<div class="header">
    <div>
        <h1>👥 Gestion des Étudiants</h1>
        <p>Gérez tous les étudiants de votre établissement</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterDonnees()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire de recherche -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">🔍 Recherche et Filtres</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="recherche" class="form-label">Rechercher</label>
            <input type="text" id="recherche" class="form-input" placeholder="Nom, prénom ou email...">
        </div>
        <div class="form-group">
            <label for="age-min" class="form-label">Âge minimum</label>
            <input type="number" id="age-min" class="form-input" placeholder="18">
        </div>
        <div class="form-group">
            <label for="age-max" class="form-label">Âge maximum</label>
            <input type="number" id="age-max" class="form-input" placeholder="25">
        </div>
        <div class="form-group">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-secondary" onclick="filtrerEtudiants()">
                🔍 Filtrer
            </button>
        </div>
    </div>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier un Étudiant</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="nom" class="form-label">Nom *</label>
            <input type="text" id="nom" class="form-input" placeholder="Nom de l'étudiant" required>
        </div>
        <div class="form-group">
            <label for="prenom" class="form-label">Prénom *</label>
            <input type="text" id="prenom" class="form-input" placeholder="Prénom de l'étudiant" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" class="form-input" placeholder="email@exemple.com" required>
        </div>
        <div class="form-group">
            <label for="age" class="form-label">Âge *</label>
            <input type="number" id="age" class="form-input" placeholder="Âge" min="16" max="100" required>
        </div>
        <div class="form-group">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="tel" id="telephone" class="form-input" placeholder="+33 6 12 34 56 78">
        </div>
        <div class="form-group">
            <label for="adresse" class="form-label">Adresse</label>
            <input type="text" id="adresse" class="form-input" placeholder="Adresse complète">
        </div>
    </div>
    
    <input type="hidden" id="id">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifier()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetForm()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des étudiants -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Étudiants</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerEtudiants()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-etudiants">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Âge</th>
                <th>Téléphone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    let etudiantsData = [];

    function chargerEtudiants() {
        ajax("GET", "/etudiants", null, (data) => {
            etudiantsData = data;
            afficherEtudiants(data);
        });
    }

    function afficherEtudiants(data) {
        const tbody = document.querySelector("#table-etudiants tbody");
        tbody.innerHTML = "";
        
        data.forEach(e => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${e.id}</td>
                <td>${e.nom}</td>
                <td>${e.prenom}</td>
                <td>${e.email}</td>
                <td>${e.age}</td>
                <td>${e.telephone || '-'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn edit" onclick='remplirFormulaire(${JSON.stringify(e)})'>✏️</button>
                        <button class="icon-btn delete" onclick='supprimerEtudiant(${e.id})'>🗑️</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filtrerEtudiants() {
        const recherche = document.getElementById("recherche").value.toLowerCase();
        const ageMin = document.getElementById("age-min").value;
        const ageMax = document.getElementById("age-max").value;

        let filtres = etudiantsData.filter(e => {
            const matchRecherche = !recherche || 
                e.nom.toLowerCase().includes(recherche) ||
                e.prenom.toLowerCase().includes(recherche) ||
                e.email.toLowerCase().includes(recherche);
            
            const matchAgeMin = !ageMin || e.age >= parseInt(ageMin);
            const matchAgeMax = !ageMax || e.age <= parseInt(ageMax);

            return matchRecherche && matchAgeMin && matchAgeMax;
        });

        afficherEtudiants(filtres);
    }

    function ajouterOuModifier() {
        const id = document.getElementById("id").value;
        const nom = document.getElementById("nom").value;
        const prenom = document.getElementById("prenom").value;
        const email = document.getElementById("email").value;
        const age = document.getElementById("age").value;
        const telephone = document.getElementById("telephone").value;
        const adresse = document.getElementById("adresse").value;

        // Validation
        if (!nom || !prenom || !email || !age) {
            showNotification("Veuillez remplir tous les champs obligatoires", "error");
            return;
        }

        if (!validateEmail(email)) {
            showNotification("Format d'email invalide", "error");
            return;
        }

        const data = `nom=${encodeURIComponent(nom)}&prenom=${encodeURIComponent(prenom)}&email=${encodeURIComponent(email)}&age=${age}&telephone=${encodeURIComponent(telephone)}&adresse=${encodeURIComponent(adresse)}`;

        if (id) {
            ajax("PUT", `/etudiants/${id}`, data, () => {
                resetForm();
                chargerEtudiants();
                showNotification("Étudiant modifié avec succès");
            });
        } else {
            ajax("POST", "/etudiants", data, () => {
                resetForm();
                chargerEtudiants();
                showNotification("Étudiant ajouté avec succès");
            });
        }
    }

    function remplirFormulaire(e) {
        document.getElementById("id").value = e.id;
        document.getElementById("nom").value = e.nom;
        document.getElementById("prenom").value = e.prenom;
        document.getElementById("email").value = e.email;
        document.getElementById("age").value = e.age;
        document.getElementById("telephone").value = e.telephone || '';
        document.getElementById("adresse").value = e.adresse || '';
    }

    function supprimerEtudiant(id) {
        if (confirmAction("Êtes-vous sûr de vouloir supprimer cet étudiant ?")) {
            ajax("DELETE", `/etudiants/${id}`, null, () => {
                chargerEtudiants();
                showNotification("Étudiant supprimé avec succès");
            });
        }
    }

    function resetForm() {
        document.getElementById("id").value = "";
        document.getElementById("nom").value = "";
        document.getElementById("prenom").value = "";
        document.getElementById("email").value = "";
        document.getElementById("age").value = "";
        document.getElementById("telephone").value = "";
        document.getElementById("adresse").value = "";
    }

    function exporterDonnees() {
        const csvContent = "data:text/csv;charset=utf-8," 
            + "ID,Nom,Prénom,Email,Âge,Téléphone,Adresse\n"
            + etudiantsData.map(e => 
                `${e.id},${e.nom},${e.prenom},${e.email},${e.age},${e.telephone || ''},${e.adresse || ''}`
            ).join("\n");
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "etudiants.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification("Export CSV terminé");
    }

    // Chargement initial
    chargerEtudiants();

    // Recherche en temps réel
    document.getElementById("recherche").addEventListener("input", filtrerEtudiants);
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 