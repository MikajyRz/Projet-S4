<?php
require_once 'template.php';

$template = new Template('Gestion des Fonds - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>💰 Gestion des Fonds</h1>
        <p>Gérez les fonds disponibles pour les prêts</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterFonds()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier un Fonds</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="motif" class="form-label">Motif</label>
            <input type="text" id="motif" class="form-input" placeholder="Motif du fonds">
        </div>
        <div class="form-group">
            <label for="montant" class="form-label">Montant *</label>
            <input type="number" id="montant" class="form-input" placeholder="Montant en euros" step="0.01" min="0" required>
        </div>
    </div>
    
    <input type="hidden" id="id_fond">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierFonds()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetForm()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des fonds -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Fonds</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerFonds()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-fonds">
        <thead>
            <tr>
                <th>ID</th>
                <th>Motif</th>
                <th>Montant</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    const apiBase = "http://localhost/Projet-S4/ws";

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

    function chargerFonds() {
        ajax("GET", "/fonds", null, (data) => {
            const tbody = document.querySelector("#table-fonds tbody");
            tbody.innerHTML = "";
            
            data.forEach(f => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${f.id_fond}</td>
                    <td>${f.motif || '-'}</td>
                    <td>${parseFloat(f.montant).toFixed(2)} €</td>
                    <td>
                        <div class="action-buttons">
                            <button class="icon-btn edit" onclick='remplirFormulaire(${JSON.stringify(f)})'>✏️</button>
                            <button class="icon-btn delete" onclick='supprimerFonds(${f.id_fond})'>🗑️</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
    }

    function ajouterOuModifierFonds() {
        const id = document.getElementById("id_fond").value;
        const motif = document.getElementById("motif").value;
        const montant = document.getElementById("montant").value;

        if (!montant) {
            alert("Veuillez remplir le montant");
            return;
        }

        const data = `motif=${encodeURIComponent(motif)}&montant=${montant}`;

        if (id) {
            ajax("PUT", `/fonds/${id}`, data, () => {
                resetForm();
                chargerFonds();
                alert("Fonds modifié avec succès");
            });
        } else {
            ajax("POST", "/fonds", data, () => {
                resetForm();
                chargerFonds();
                alert("Fonds ajouté avec succès");
            });
        }
    }

    function remplirFormulaire(f) {
        document.getElementById("id_fond").value = f.id_fond;
        document.getElementById("motif").value = f.motif || '';
        document.getElementById("montant").value = f.montant;
    }

    function supprimerFonds(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce fonds ?")) {
            ajax("DELETE", `/fonds/${id}`, null, () => {
                chargerFonds();
                alert("Fonds supprimé avec succès");
            });
        }
    }

    function resetForm() {
        document.getElementById("id_fond").value = "";
        document.getElementById("motif").value = "";
        document.getElementById("montant").value = "";
    }

    function exporterFonds() {
        alert("Fonctionnalité d'export à implémenter");
    }

    // Chargement initial
    chargerFonds();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 