<?php
require_once 'template.php';

$template = new Template('Accueil - Gestion des Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>🏠 Tableau de Bord</h1>
        <p>Bienvenue dans votre système de gestion des prêts</p>
    </div>
    <div class="header-actions">
        <a href="prets.php" class="sync-btn">
            📊 Voir tous les prêts
        </a>
    </div>
</div>

<!-- Statistiques -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
            <h3 id="total-clients">0</h3>
            <p>Total Clients</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💼</div>
        <div class="stat-content">
            <h3 id="total-prets">0</h3>
            <p>Prêts Actifs</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3 id="total-fonds">0</h3>
            <p>Fonds Disponibles</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <h3 id="montant-total">0 €</h3>
            <p>Montant Total Prêté</p>
        </div>
    </div>
</div>

<!-- Formulaire d'ajout rapide de client -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">🚀 Ajout Rapide de Client</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" class="form-input" placeholder="Nom du client">
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" class="form-input" placeholder="email@exemple.com">
        </div>
    </div>
    
    <input type="hidden" id="id_client">
    <button class="btn btn-primary" onclick="ajouterOuModifierClient()">
        💾 Ajouter / Modifier
    </button>
</div>

<!-- Tableau des clients récents -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Derniers Clients Ajoutés</h2>
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
            
            // Mise à jour du compteur
            document.getElementById("total-clients").textContent = data.length;
            
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
        });
    }

    function ajouterOuModifierClient() {
        const id = document.getElementById("id_client").value;
        const nom = document.getElementById("nom").value;
        const email = document.getElementById("email").value;

        // Validation
        if (!nom || !email) {
            alert("Veuillez remplir tous les champs");
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

    // Chargement initial
    chargerClients();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?>
