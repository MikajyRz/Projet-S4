<?php
require_once 'template.php';

$template = new Template('Gestion des Cours - Système de Gestion');

ob_start();
?>

<div class="header">
    <div>
        <h1>📚 Gestion des Cours</h1>
        <p>Gérez les cours et matières de votre établissement</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterCours()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire d'ajout/modification de cours -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier un Cours</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="nom_cours" class="form-label">Nom du Cours *</label>
            <input type="text" id="nom_cours" class="form-input" placeholder="Ex: Mathématiques" required>
        </div>
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" class="form-input" placeholder="Description du cours">
        </div>
        <div class="form-group">
            <label for="professeur" class="form-label">Professeur</label>
            <input type="text" id="professeur" class="form-input" placeholder="Nom du professeur">
        </div>
        <div class="form-group">
            <label for="credits" class="form-label">Crédits</label>
            <input type="number" id="credits" class="form-input" placeholder="Nombre de crédits" min="1" max="10">
        </div>
        <div class="form-group">
            <label for="duree" class="form-label">Durée (heures)</label>
            <input type="number" id="duree" class="form-input" placeholder="Durée en heures" min="1">
        </div>
        <div class="form-group">
            <label for="niveau" class="form-label">Niveau</label>
            <select id="niveau" class="form-input">
                <option value="">Sélectionner un niveau</option>
                <option value="Débutant">Débutant</option>
                <option value="Intermédiaire">Intermédiaire</option>
                <option value="Avancé">Avancé</option>
            </select>
        </div>
    </div>
    
    <input type="hidden" id="id_cours">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierCours()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetFormCours()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des cours -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Cours</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerCours()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-cours">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Cours</th>
                <th>Description</th>
                <th>Professeur</th>
                <th>Crédits</th>
                <th>Durée</th>
                <th>Niveau</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    function chargerCours() {
        ajax("GET", "/cours", null, (data) => {
            const tbody = document.querySelector("#table-cours tbody");
            tbody.innerHTML = "";
            
            data.forEach(c => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${c.id_cours}</td>
                    <td>${c.nom_cours}</td>
                    <td>${c.description || '-'}</td>
                    <td>${c.professeur || '-'}</td>
                    <td>${c.credits || '-'}</td>
                    <td>${c.duree || '-'}h</td>
                    <td>${c.niveau || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="icon-btn edit" onclick='remplirFormulaireCours(${JSON.stringify(c)})'>✏️</button>
                            <button class="icon-btn delete" onclick='supprimerCours(${c.id_cours})'>🗑️</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
    }

    function ajouterOuModifierCours() {
        const id = document.getElementById("id_cours").value;
        const nom_cours = document.getElementById("nom_cours").value;
        const description = document.getElementById("description").value;
        const professeur = document.getElementById("professeur").value;
        const credits = document.getElementById("credits").value;
        const duree = document.getElementById("duree").value;
        const niveau = document.getElementById("niveau").value;

        if (!nom_cours) {
            showNotification("Le nom du cours est obligatoire", "error");
            return;
        }

        const data = `nom_cours=${encodeURIComponent(nom_cours)}&description=${encodeURIComponent(description)}&professeur=${encodeURIComponent(professeur)}&credits=${credits}&duree=${duree}&niveau=${encodeURIComponent(niveau)}`;

        if (id) {
            ajax("PUT", `/cours/${id}`, data, () => {
                resetFormCours();
                chargerCours();
                showNotification("Cours modifié avec succès");
            });
        } else {
            ajax("POST", "/cours", data, () => {
                resetFormCours();
                chargerCours();
                showNotification("Cours ajouté avec succès");
            });
        }
    }

    function remplirFormulaireCours(c) {
        document.getElementById("id_cours").value = c.id_cours;
        document.getElementById("nom_cours").value = c.nom_cours;
        document.getElementById("description").value = c.description || '';
        document.getElementById("professeur").value = c.professeur || '';
        document.getElementById("credits").value = c.credits || '';
        document.getElementById("duree").value = c.duree || '';
        document.getElementById("niveau").value = c.niveau || '';
    }

    function supprimerCours(id) {
        if (confirmAction("Êtes-vous sûr de vouloir supprimer ce cours ?")) {
            ajax("DELETE", `/cours/${id}`, null, () => {
                chargerCours();
                showNotification("Cours supprimé avec succès");
            });
        }
    }

    function resetFormCours() {
        document.getElementById("id_cours").value = "";
        document.getElementById("nom_cours").value = "";
        document.getElementById("description").value = "";
        document.getElementById("professeur").value = "";
        document.getElementById("credits").value = "";
        document.getElementById("duree").value = "";
        document.getElementById("niveau").value = "";
    }

    function exporterCours() {
        showNotification("Fonctionnalité d'export à implémenter");
    }

    // Chargement initial
    chargerCours();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 