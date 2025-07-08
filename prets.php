<?php
require_once 'template.php';

$template = new Template('Gestion des Prêts - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>💼 Gestion des Prêts</h1>
        <p>Gérez tous les prêts de votre système</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterPrets()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Nouveau Prêt</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="montant" class="form-label">Montant *</label>
            <input type="number" id="montant" class="form-input" placeholder="Montant en euros" step="0.01" min="0" required>
        </div>
        <div class="form-group">
            <label for="id_client" class="form-label">Client *</label>
            <select id="id_client" class="form-input" required>
                <option value="">Sélectionner un client</option>
            </select>
        </div>
        <div class="form-group">
            <label for="id_type_pret" class="form-label">Type de Prêt *</label>
            <select id="id_type_pret" class="form-input" required>
                <option value="">Sélectionner un type</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_pret" class="form-label">Date du Prêt</label>
            <input type="date" id="date_pret" class="form-input" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="form-group">
            <label for="statuts" class="form-label">Statut</label>
            <select id="statuts" class="form-input">
                <option value="en attente">En attente</option>
                <option value="valide">Validé</option>
                <option value="refuse">Refusé</option>
            </select>
        </div>
    </div>
    
    <input type="hidden" id="id_pret">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierPret()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetForm()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des prêts -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Prêts</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerPrets()">
                🔄 Actualiser
            </button>
            <button class="btn btn-success" onclick="chargerDemandes()">
                📋 Voir Demandes
            </button>
        </div>
    </div>
    <table class="table" id="table-prets">
        <thead>
            <tr>
                <th>ID</th>
                <th>Montant</th>
                <th>Client</th>
                <th>Type de Prêt</th>
                <th>Date</th>
                <th>Statut</th>
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

    function chargerClients() {
        ajax("GET", "/clients", null, (data) => {
            const select = document.getElementById("id_client");
            select.innerHTML = '<option value="">Sélectionner un client</option>';
            
            data.forEach(c => {
                const option = document.createElement("option");
                option.value = c.id_client;
                option.textContent = c.nom;
                select.appendChild(option);
            });
        });
    }

    function chargerTypes() {
        ajax("GET", "/type-pret", null, (data) => {
            const select = document.getElementById("id_type_pret");
            select.innerHTML = '<option value="">Sélectionner un type</option>';
            
            data.forEach(t => {
                const option = document.createElement("option");
                option.value = t.id_type_pret;
                option.textContent = `${t.nom} (${t.taux}%)`;
                select.appendChild(option);
            });
        });
    }

    function chargerPrets() {
        ajax("GET", "/prets", null, (data) => {
            afficherPrets(data);
        });
    }

    function chargerDemandes() {
        ajax("GET", "/pretsDemande", null, (data) => {
            afficherPrets(data);
        });
    }

    function afficherPrets(data) {
        const tbody = document.querySelector("#table-prets tbody");
        tbody.innerHTML = "";
        
        data.forEach(p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${p.id_pret}</td>
                <td>${parseFloat(p.montant).toFixed(2)} €</td>
                <td>${p.client_nom}</td>
                <td>${p.type_pret_nom} (${p.taux}%${p.assurance ? ', Ass: ' + p.assurance + '%' : ''})</td>
                <td>${p.date_pret}</td>
                <td><span class="badge badge-${getBadgeClass(p.statuts)}">${p.statuts}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn edit" onclick='remplirFormulaire(${JSON.stringify(p)})'>✏️</button>
                        <button class="icon-btn delete" onclick='supprimerPret(${p.id_pret})'>🗑️</button>
                        ${p.statuts === 'en attente' ? `<button class="icon-btn validate" onclick='validerPret(${p.id_pret})'>✅</button>` : ''}
                        ${p.statuts === 'valide' ? `<button class="icon-btn view" onclick='genererPDF(${p.id_pret})'>📄</button>` : ''}
                        ${p.statuts === 'valide' ? `<button class="icon-btn view" onclick='exporterPretFpdf(${p.id_pret})'>📝 PDF FPDF</button>` : ''}
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function getBadgeClass(statut) {
        switch(statut) {
            case 'valide': return 'success';
            case 'en attente': return 'warning';
            case 'refuse': return 'danger';
            default: return 'secondary';
        }
    }

    function ajouterOuModifierPret() {
        const id = document.getElementById("id_pret").value;
        const montant = document.getElementById("montant").value;
        const id_client = document.getElementById("id_client").value;
        const id_type_pret = document.getElementById("id_type_pret").value;
        const date_pret = document.getElementById("date_pret").value;
        const statuts = document.getElementById("statuts").value;

        if (!montant || !id_client || !id_type_pret) {
            alert("Veuillez remplir tous les champs obligatoires");
            return;
        }

        const data = `montant=${montant}&id_client=${id_client}&id_type_pret=${id_type_pret}&date_pret=${date_pret}&statuts=${encodeURIComponent(statuts)}`;

        if (id) {
            // Note: Pas de route PUT pour prets dans l'API existante
            alert("Modification non disponible pour les prêts");
            return;
        } else {
            ajax("POST", "/prets", data, () => {
                resetForm();
                chargerPrets();
                alert("Prêt ajouté avec succès");
            });
        }
    }

    function validerPret(id) {
        if (confirm("Êtes-vous sûr de vouloir valider ce prêt ?")) {
            ajax("POST", `/prets/valider/${id}`, null, () => {
                chargerPrets();
                alert("Prêt validé avec succès");
            });
        }
    }

    function remplirFormulaire(p) {
        document.getElementById("id_pret").value = p.id_pret;
        document.getElementById("montant").value = p.montant;
        document.getElementById("id_client").value = p.id_client;
        document.getElementById("id_type_pret").value = p.id_type_pret;
        document.getElementById("date_pret").value = p.date_pret;
        document.getElementById("statuts").value = p.statuts;
    }

    function supprimerPret(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce prêt ?")) {
            ajax("DELETE", `/prets/${id}`, null, () => {
                chargerPrets();
                alert("Prêt supprimé avec succès");
            });
        }
    }

    function resetForm() {
        document.getElementById("id_pret").value = "";
        document.getElementById("montant").value = "";
        document.getElementById("id_client").value = "";
        document.getElementById("id_type_pret").value = "";
        document.getElementById("date_pret").value = "<?php echo date('Y-m-d'); ?>";
        document.getElementById("statuts").value = "en attente";
    }

    function exporterPrets() {
        alert("Fonctionnalité d'export à implémenter");
    }

    function genererPDF(id_pret) {
        // Ouvrir le PDF dans une nouvelle fenêtre
        window.open(`${apiBase}/pdf/pret/${id_pret}`, '_blank');
    }

    function exporterPretFpdf(id_pret) {
        window.open(`${apiBase}/pdf-fpdf/pret/${id_pret}`, '_blank');
    }

    // Chargement initial
    chargerClients();
    chargerTypes();
    chargerPrets();
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

.icon-btn.validate {
    background: #28a745;
    color: white;
}
</style>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 