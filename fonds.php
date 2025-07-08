<?php
require_once 'template.php';

$template = new Template('Gestion des Fonds');

ob_start();
?>
<div class="header">
    <h1>💰 Gestion des Fonds</h1>
    <p>Ajoutez, modifiez ou supprimez les fonds disponibles pour les prêts.</p>
</div>

<div class="card">
    <div class="card-title">Ajouter un fonds</div>
    <form id="form-fond" class="form-container">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="motif">Motif</label>
                <input type="text" id="motif" name="motif" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="montant">Montant (€)</label>
                <input type="number" id="montant" name="montant" class="form-input" required min="1" step="0.01">
            </div>
        </div>
        <button type="submit" class="btn">Ajouter</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Liste des fonds</div>
    <div class="table-container">
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

function chargerFonds() {
    ajax("GET", "/fonds", null, (data) => {
        const tbody = document.querySelector("#table-fonds tbody");
        tbody.innerHTML = "";
        data.forEach(fond => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${fond.id_fond}</td>
                <td>${fond.motif}</td>
                <td>${parseFloat(fond.montant).toFixed(2)} €</td>
                <td>
                    <button class="btn btn-secondary" onclick='remplirFormulaire(${JSON.stringify(fond)})'>✏️</button>
                    <button class="btn btn-secondary" onclick='supprimerFond(${fond.id_fond})'>🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function ajouterFond(e) {
    e.preventDefault();
    const form = e.target;
    const data = `motif=${encodeURIComponent(form.motif.value)}&montant=${encodeURIComponent(form.montant.value)}`;
    ajax("POST", "/fonds", data, () => {
        chargerFonds();
        form.reset();
    });
}

function remplirFormulaire(fond) {
    document.getElementById('motif').value = fond.motif;
    document.getElementById('montant').value = fond.montant;
    // Pour la mise à jour, vous pouvez ajouter un champ caché id_fond et adapter le submit
}

function supprimerFond(id) {
    if (confirm("Supprimer ce fonds ?")) {
        ajax("DELETE", `/fonds/${id}`, null, () => chargerFonds());
    }
}

document.getElementById('form-fond').addEventListener('submit', ajouterFond);
document.addEventListener('DOMContentLoaded', chargerFonds);
</script>
<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 