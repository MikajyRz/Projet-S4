<?php
require_once 'template.php';

$template = new Template('Gestion des Types de Prêts - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>🏦 Gestion des Types de Prêts</h1>
        <p>Configurez les différents types de prêts disponibles</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterTypes()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier un Type de Prêt</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="nom" class="form-label">Nom *</label>
            <input type="text" id="nom" class="form-input" placeholder="Nom du type de prêt" required>
        </div>
        <div class="form-group">
            <label for="taux" class="form-label">Taux d'intérêt (%) *</label>
            <input type="number" id="taux" class="form-input" placeholder="Taux annuel" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="duree" class="form-label">Durée (mois) *</label>
            <input type="number" id="duree" class="form-input" placeholder="Durée en mois" min="1" required>
        </div>
        <div class="form-group">
            <label for="assurance" class="form-label">Assurance (%)</label>
            <input type="number" id="assurance" class="form-input" placeholder="Taux d'assurance annuel" step="0.01" min="0" value="0">
        </div>
    </div>
    
    <input type="hidden" id="id_type_pret">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierType()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetForm()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des types de prêts -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Types de Prêts</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerTypes()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-types">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Taux (%)</th>
                <th>Durée (mois)</th>
                <th>Assurance (%)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    const apiBase = "http://localhost/Projet-S4/ws";
    let typesData = [];

    function ajax(method, url, data, callback) {
        const xhr = new XMLHttpRequest();
        xhr.open(method, apiBase + url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                callback(JSON.parse(xhr.responseText));
            }
        };
        xhr.send(data);
    }

    function chargerTypes() {
        ajax("GET", "/type-pret", null, (data) => {
            typesData = data;
            afficherTypes(data);
        });
    }

    function afficherTypes(data) {
        const tbody = document.querySelector("#table-types tbody");
        tbody.innerHTML = "";
        
        data.forEach(t => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${t.id_type_pret}</td>
                <td>${t.nom}</td>
                <td>${parseFloat(t.taux).toFixed(2)}%</td>
                <td>${t.duree}</td>
                <td>${parseFloat(t.assurance || 0).toFixed(2)}%</td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn edit" onclick='remplirFormulaire(${JSON.stringify(t)})'>✏️</button>
                        <button class="icon-btn delete" onclick='supprimerType(${t.id_type_pret})'>🗑️</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function ajouterOuModifierType() {
        const id = document.getElementById("id_type_pret").value;
        const nom = document.getElementById("nom").value;
        const taux = document.getElementById("taux").value;
        const duree = document.getElementById("duree").value;
        const assurance = document.getElementById("assurance").value || 0;

        if (!nom || !taux || !duree) {
            alert("Veuillez remplir tous les champs obligatoires");
            return;
        }

        const data = `nom=${encodeURIComponent(nom)}&taux=${taux}&duree=${duree}&assurance=${assurance}`;

        if (id) {
            ajax("PUT", `/type-pret/${id}`, data, () => {
                resetForm();
                chargerTypes();
                alert("Type de prêt modifié avec succès");
            });
        } else {
            ajax("POST", "/type-pret", data, () => {
                resetForm();
                chargerTypes();
                alert("Type de prêt ajouté avec succès");
            });
        }
    }

    function remplirFormulaire(t) {
        document.getElementById("id_type_pret").value = t.id_type_pret;
        document.getElementById("nom").value = t.nom;
        document.getElementById("taux").value = t.taux;
        document.getElementById("duree").value = t.duree;
        document.getElementById("assurance").value = t.assurance || 0;
    }

    function supprimerType(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce type de prêt ?")) {
            ajax("DELETE", `/type-pret/${id}`, null, () => {
                chargerTypes();
                alert("Type de prêt supprimé avec succès");
            });
        }
    }

    function resetForm() {
        document.getElementById("id_type_pret").value = "";
        document.getElementById("nom").value = "";
        document.getElementById("taux").value = "";
        document.getElementById("duree").value = "";
        document.getElementById("assurance").value = "0";
    }

    function exporterTypes() {
        const csvContent = "data:text/csv;charset=utf-8," 
            + "ID,Nom,Taux (%),Durée (mois),Assurance (%)\n"
            + typesData.map(t => 
                `${t.id_type_pret},${t.nom},${t.taux},${t.duree},${t.assurance || 0}`
            ).join("\n");
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "types_pret.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        alert("Export CSV terminé");
    }

    // Chargement initial
    chargerTypes();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 