<?php
require_once 'template.php';

$template = new Template('Gestion des Prêts');

ob_start();
?>
<div class="header">
    <h1>💼 Gestion des Prêts</h1>
    <p>Créez, validez ou supprimez les prêts accordés aux clients.</p>
</div>

<div class="card">
    <div class="card-title">Ajouter un prêt</div>
    <form id="form-pret" class="form-container">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="client">Client</label>
                <select id="client" name="client" class="form-input" required></select>
            </div>
            <div class="form-group">
                <label class="form-label" for="type_pret">Type de Prêt</label>
                <select id="type_pret" name="type_pret" class="form-input" required></select>
            </div>
            <div class="form-group">
                <label class="form-label" for="montant">Montant (€)</label>
                <input type="number" id="montant" name="montant" class="form-input" required min="1" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label" for="duree">Durée (mois)</label>
                <input type="number" id="duree" name="duree" class="form-input" required min="1">
            </div>
        </div>
        <button type="submit" class="btn">Ajouter</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Liste des prêts</div>
    <div class="table-container">
        <table class="table" id="table-prets">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Durée</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
const apiBase = "http://localhost/T/Examen_S4/ws";

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
        const select = document.getElementById("client");
        select.innerHTML = '<option value="">Sélectionner un client</option>';
        data.forEach(c => {
            const option = document.createElement("option");
            option.value = c.id_client;
            option.textContent = `${c.nom} ${c.prenom}`;
            select.appendChild(option);
        });
    });
}
function chargerTypes() {
    ajax("GET", "/type-pret", null, (data) => {
        const select = document.getElementById("type_pret");
        select.innerHTML = '<option value="">Sélectionner un type</option>';
        data.forEach(t => {
            const option = document.createElement("option");
            option.value = t.id_type_pret;
            option.textContent = t.nom;
            select.appendChild(option);
        });
    });
}
function chargerPrets() {
    ajax("GET", "/prets", null, (data) => {
        const tbody = document.querySelector("#table-prets tbody");
        tbody.innerHTML = "";
        data.forEach(p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${p.id_pret}</td>
                <td>${p.client_nom}</td>
                <td>${p.type_pret_nom}</td>
                <td>${parseFloat(p.montant).toFixed(2)} €</td>
                <td>${p.duree} mois</td>
                <td>${p.statuts}</td>
                <td>
                    <button class="btn btn-secondary" onclick='remplirFormulaire(${JSON.stringify(p)})'>✏️</button>
                    <button class="btn btn-secondary" onclick='supprimerPret(${p.id_pret})'>🗑️</button>
                    ${p.statuts === 'en attente' ? `<button class="btn" onclick='validerPret(${p.id_pret})'>Valider</button>` : ''}
                    ${p.statuts === 'valide' ? `<button class="btn" onclick='genererPDF(${p.id_pret})'>PDF</button>` : ''}
                    ${p.statuts === 'valide' ? `<button class="btn" onclick='exporterPretFpdf(${p.id_pret})'>FPDF</button>` : ''}
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}
function ajouterPret(e) {
    e.preventDefault();
    const form = e.target;
    const data = `id_client=${encodeURIComponent(form.client.value)}&id_type_pret=${encodeURIComponent(form.type_pret.value)}&montant=${encodeURIComponent(form.montant.value)}&duree=${encodeURIComponent(form.duree.value)}`;
    ajax("POST", "/prets", data, () => {
        chargerPrets();
        form.reset();
    });
}
function remplirFormulaire(p) {
    document.getElementById('client').value = p.id_client;
    document.getElementById('type_pret').value = p.id_type_pret;
    document.getElementById('montant').value = p.montant;
    document.getElementById('duree').value = p.duree;
}
function supprimerPret(id) {
    if (confirm("Supprimer ce prêt ?")) {
        ajax("DELETE", `/prets/${id}`, null, () => chargerPrets());
    }
}
function validerPret(id) {
    if (confirm("Valider ce prêt ?")) {
        ajax("PUT", `/prets/${id}/valider`, null, () => chargerPrets());
    }
}
function genererPDF(id) {
    window.open(`${apiBase}/pdf/pret/${id}`, '_blank');
}
function exporterPretFpdf(id) {
    window.open(`${apiBase}/pdf-fpdf/pret/${id}`, '_blank');
}
document.getElementById('form-pret').addEventListener('submit', ajouterPret);
document.addEventListener('DOMContentLoaded', () => {
    chargerClients();
    chargerTypes();
    chargerPrets();
});
</script>
<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 