<?php
require_once 'template.php';

$template = new Template('Gestion des Inscriptions - Système de Gestion');

ob_start();
?>

<div class="header">
    <div>
        <h1>📝 Gestion des Inscriptions</h1>
        <p>Gérez les inscriptions des étudiants aux cours</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterInscriptions()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire d'inscription -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Nouvelle Inscription</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="etudiant_id" class="form-label">Étudiant *</label>
            <select id="etudiant_id" class="form-input" required>
                <option value="">Sélectionner un étudiant</option>
            </select>
        </div>
        <div class="form-group">
            <label for="cours_id" class="form-label">Cours *</label>
            <select id="cours_id" class="form-input" required>
                <option value="">Sélectionner un cours</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_inscription" class="form-label">Date d'inscription</label>
            <input type="date" id="date_inscription" class="form-input" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label for="statut" class="form-label">Statut</label>
            <select id="statut" class="form-input">
                <option value="En cours">En cours</option>
                <option value="Validé">Validé</option>
                <option value="Annulé">Annulé</option>
            </select>
        </div>
    </div>
    
    <input type="hidden" id="id_inscription">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierInscription()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetFormInscription()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des inscriptions -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Inscriptions</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerInscriptions()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-inscriptions">
        <thead>
            <tr>
                <th>ID</th>
                <th>Étudiant</th>
                <th>Cours</th>
                <th>Date d'inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    function chargerEtudiants() {
        ajax("GET", "/etudiants", null, (data) => {
            const select = document.getElementById("etudiant_id");
            select.innerHTML = '<option value="">Sélectionner un étudiant</option>';
            
            data.forEach(e => {
                const option = document.createElement("option");
                option.value = e.id;
                option.textContent = `${e.nom} ${e.prenom}`;
                select.appendChild(option);
            });
        });
    }

    function chargerCours() {
        ajax("GET", "/cours", null, (data) => {
            const select = document.getElementById("cours_id");
            select.innerHTML = '<option value="">Sélectionner un cours</option>';
            
            data.forEach(c => {
                const option = document.createElement("option");
                option.value = c.id_cours;
                option.textContent = c.nom_cours;
                select.appendChild(option);
            });
        });
    }

    function chargerInscriptions() {
        ajax("GET", "/inscriptions", null, (data) => {
            const tbody = document.querySelector("#table-inscriptions tbody");
            tbody.innerHTML = "";
            
            data.forEach(i => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${i.id_inscription}</td>
                    <td>${i.nom_etudiant} ${i.prenom_etudiant}</td>
                    <td>${i.nom_cours}</td>
                    <td>${formatDate(i.date_inscription)}</td>
                    <td><span class="badge badge-${getBadgeClass(i.statut)}">${i.statut}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="icon-btn edit" onclick='remplirFormulaireInscription(${JSON.stringify(i)})'>✏️</button>
                            <button class="icon-btn delete" onclick='supprimerInscription(${i.id_inscription})'>🗑️</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
    }

    function getBadgeClass(statut) {
        switch(statut) {
            case 'Validé': return 'success';
            case 'En cours': return 'warning';
            case 'Annulé': return 'danger';
            default: return 'secondary';
        }
    }

    function ajouterOuModifierInscription() {
        const id = document.getElementById("id_inscription").value;
        const etudiant_id = document.getElementById("etudiant_id").value;
        const cours_id = document.getElementById("cours_id").value;
        const date_inscription = document.getElementById("date_inscription").value;
        const statut = document.getElementById("statut").value;

        if (!etudiant_id || !cours_id) {
            showNotification("Veuillez sélectionner un étudiant et un cours", "error");
            return;
        }

        const data = `etudiant_id=${etudiant_id}&cours_id=${cours_id}&date_inscription=${date_inscription}&statut=${encodeURIComponent(statut)}`;

        if (id) {
            ajax("PUT", `/inscriptions/${id}`, data, () => {
                resetFormInscription();
                chargerInscriptions();
                showNotification("Inscription modifiée avec succès");
            });
        } else {
            ajax("POST", "/inscriptions", data, () => {
                resetFormInscription();
                chargerInscriptions();
                showNotification("Inscription ajoutée avec succès");
            });
        }
    }

    function remplirFormulaireInscription(i) {
        document.getElementById("id_inscription").value = i.id_inscription;
        document.getElementById("etudiant_id").value = i.etudiant_id;
        document.getElementById("cours_id").value = i.cours_id;
        document.getElementById("date_inscription").value = i.date_inscription;
        document.getElementById("statut").value = i.statut;
    }

    function supprimerInscription(id) {
        if (confirmAction("Êtes-vous sûr de vouloir supprimer cette inscription ?")) {
            ajax("DELETE", `/inscriptions/${id}`, null, () => {
                chargerInscriptions();
                showNotification("Inscription supprimée avec succès");
            });
        }
    }

    function resetFormInscription() {
        document.getElementById("id_inscription").value = "";
        document.getElementById("etudiant_id").value = "";
        document.getElementById("cours_id").value = "";
        document.getElementById("date_inscription").value = "<?php echo date('Y-m-d'); ?>";
        document.getElementById("statut").value = "En cours";
    }

    function exporterInscriptions() {
        showNotification("Fonctionnalité d'export à implémenter");
    }

    // Chargement initial
    chargerEtudiants();
    chargerCours();
    chargerInscriptions();
</script>

<style>
.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-secondary {
    background: #e2e3e5;
    color: #383d41;
}
</style>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 