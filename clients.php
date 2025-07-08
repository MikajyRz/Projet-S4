<?php
require_once 'template.php';

$template = new Template('Gestion des Clients');

ob_start();
?>
<div class="header">
    <h1>👥 Gestion des Clients</h1>
    <p>Ajoutez, modifiez ou supprimez les clients de votre système de prêts.</p>
</div>

<div class="card">
    <div class="card-title">Ajouter un client</div>
    <form id="form-client" class="form-container">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input type="text" id="nom" name="nom" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="telephone">Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-input">
            </div>
        </div>
        <button type="submit" class="btn">Ajouter</button>
        <button type="reset" class="btn btn-secondary">Réinitialiser</button>
    </form>
</div>

<div class="card">
    <div class="card-title">Liste des clients</div>
    <div class="table-container">
        <table class="table" id="table-clients">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
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
        const tbody = document.querySelector("#table-clients tbody");
        tbody.innerHTML = "";
        data.forEach(client => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${client.id_client}</td>
                <td>${client.nom}</td>
                <td>${client.prenom}</td>
                <td>${client.email}</td>
                <td>${client.telephone || ''}</td>
                <td>
                    <button class="btn btn-secondary" onclick='remplirFormulaire(${JSON.stringify(client)})'>✏️</button>
                    <button class="btn btn-secondary" onclick='supprimerClient(${client.id_client})'>🗑️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function ajouterClient(e) {
    e.preventDefault();
    const form = e.target;
    const data = `nom=${encodeURIComponent(form.nom.value)}&prenom=${encodeURIComponent(form.prenom.value)}&email=${encodeURIComponent(form.email.value)}&telephone=${encodeURIComponent(form.telephone.value)}`;
    ajax("POST", "/clients", data, () => {
        chargerClients();
        form.reset();
    });
}

function remplirFormulaire(client) {
    document.getElementById('nom').value = client.nom;
    document.getElementById('prenom').value = client.prenom;
    document.getElementById('email').value = client.email;
    document.getElementById('telephone').value = client.telephone || '';
    // Pour la mise à jour, vous pouvez ajouter un champ caché id_client et adapter le submit
}

function supprimerClient(id) {
    if (confirm("Supprimer ce client ?")) {
        ajax("DELETE", `/clients/${id}`, null, () => chargerClients());
    }
}

document.getElementById('form-client').addEventListener('submit', ajouterClient);

// Chargement initial
document.addEventListener('DOMContentLoaded', chargerClients);
</script>
<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 