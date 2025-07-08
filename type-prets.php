<?php
require_once 'template.php';

$template = new Template('Gestion des Types de Prêts');

ob_start();
?>
<div class="header">
    <h1>📑 Types de Prêts</h1>
    <p>Ajoutez, modifiez ou supprimez les types de prêts proposés.</p>
</div>

<div class="card">
    <div class="card-title">Ajouter un type de prêt</div>
    <form id="form-type" class="form-container">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input type="text" id="nom" name="nom" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="taux">Taux d'intérêt (%)</label>
                <input type="number" id="taux" name="taux" class="form-input" required min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label" for="assurance">Taux d'assurance (%)</label>
                <input type="number" id="assurance" name="assurance" class="form-input" required min="0" step="0.01">
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
    <div class="card-title">Liste des types de prêts</div>
    <div class="table-container">
        <table class="table" id="table-types">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Taux d'intérêt</th>
                    <th>Taux d'assurance</th>
                    <th>Durée</th>
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

function chargerTypes() {
    ajax("GET", "/type-pret", null, (data) => {
        const tbody = document.querySelector("#table-types tbody");
        tbody.innerHTML = "";
        data.forEach(type => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${type.id_type_pret}</td>
                <td>${type.nom}</td>
                <td>${parseFloat(type.taux).toFixed(2)}%</td>
                <td>${parseFloat(type.assurance).toFixed(2)}%</td>
                <td>${type.duree} mois</td>
                <td>
                    <button class="btn btn-secondary" onclick='remplirFormulaire(${JSON.stringify(type)})'>✏️</button>
                    <button class="btn btn-secondary" onclick='supprimerType(${type.id_type_pret})'>🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function ajouterType(e) {
    e.preventDefault();
    const form = e.target;
    const data = `nom=${encodeURIComponent(form.nom.value)}&taux=${encodeURIComponent(form.taux.value)}&assurance=${encodeURIComponent(form.assurance.value)}&duree=${encodeURIComponent(form.duree.value)}`;
    ajax("POST", "/type-pret", data, () => {
        chargerTypes();
        form.reset();
    });
}

function remplirFormulaire(type) {
    document.getElementById('nom').value = type.nom;
    document.getElementById('taux').value = type.taux;
    document.getElementById('assurance').value = type.assurance;
    document.getElementById('duree').value = type.duree;
    // Pour la mise à jour, vous pouvez ajouter un champ caché id_type_pret et adapter le submit
}

function supprimerType(id) {
    if (confirm("Supprimer ce type de prêt ?")) {
        ajax("DELETE", `/type-pret/${id}`, null, () => chargerTypes());
    }
}

document.getElementById('form-type').addEventListener('submit', ajouterType);
document.addEventListener('DOMContentLoaded', chargerTypes);
</script>
<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 