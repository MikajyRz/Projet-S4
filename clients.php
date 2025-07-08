<?php
require_once 'template.php';

$template = new Template('Gestion des Clients - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>👥 Gestion des Clients</h1>
        <p>Gérez tous les clients de votre système de prêts</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterClients()">
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
            <input type="text" id="recherche" class="form-input" placeholder="Nom ou email...">
        </div>
        <div class="form-group">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-secondary" onclick="filtrerClients()">
                🔍 Filtrer
            </button>
        </div>
    </div>
</div>

<!-- Formulaire d'ajout/modification -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier un Client</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="nom" class="form-label">Nom *</label>
            <input type="text" id="nom" class="form-input" placeholder="Nom du client" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" class="form-input" placeholder="email@exemple.com" required>
        </div>
    </div>
    
    <input type="hidden" id="id_client">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierClient()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetForm()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des clients -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Clients</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerClients()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-clients">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    const apiBase = "http://localhost/Projet-S4/ws";
    let clientsData = [];

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
            clientsData = data;
            afficherClients(data);
        });
    }

    function afficherClients(data) {
        const tbody = document.querySelector("#table-clients tbody");
        tbody.innerHTML = "";
        
        data.forEach(c => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${c.id_client}</td>
                <td>${c.nom}</td>
                <td>${c.email}</td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn edit" onclick='remplirFormulaire(${JSON.stringify(c)})'>✏️</button>
                        <button class="icon-btn delete" onclick='supprimerClient(${c.id_client})'>🗑️</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function filtrerClients() {
        const recherche = document.getElementById("recherche").value.toLowerCase();

        let filtres = clientsData.filter(c => {
            return !recherche || 
                c.nom.toLowerCase().includes(recherche) ||
                c.email.toLowerCase().includes(recherche);
        });

        afficherClients(filtres);
    }

    function ajouterOuModifierClient() {
        const id = document.getElementById("id_client").value;
        const nom = document.getElementById("nom").value;
        const email = document.getElementById("email").value;

        if (!nom || !email) {
            alert("Veuillez remplir tous les champs obligatoires");
            return;
        }

        const data = `nom=${encodeURIComponent(nom)}&email=${encodeURIComponent(email)}`;

        if (id) {
            ajax("PUT", `/clients/${id}`, data, () => {
                resetForm();
                chargerClients();
                alert("Client modifié avec succès");
            });
        } else {
            ajax("POST", "/clients", data, () => {
                resetForm();
                chargerClients();
                alert("Client ajouté avec succès");
            });
        }
    }

    function remplirFormulaire(c) {
        document.getElementById("id_client").value = c.id_client;
        document.getElementById("nom").value = c.nom;
        document.getElementById("email").value = c.email;
    }

    function supprimerClient(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce client ?")) {
            ajax("DELETE", `/clients/${id}`, null, () => {
                chargerClients();
                alert("Client supprimé avec succès");
            });
        }
    }

    function resetForm() {
        document.getElementById("id_client").value = "";
        document.getElementById("nom").value = "";
        document.getElementById("email").value = "";
    }

    function exporterClients() {
        const csvContent = "data:text/csv;charset=utf-8," 
            + "ID,Nom,Email\n"
            + clientsData.map(c => 
                `${c.id_client},${c.nom},${c.email}`
            ).join("\n");
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "clients.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        alert("Export CSV terminé");
    }

    // Chargement initial
    chargerClients();

    // Recherche en temps réel
    document.getElementById("recherche").addEventListener("input", filtrerClients);
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 