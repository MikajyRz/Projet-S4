<?php
require_once 'template.php';

$template = new Template('Tableau de Bord - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>📊 Tableau de Bord</h1>
        <p>Vue d'ensemble de votre système de prêts</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="actualiserDashboard()">
            🔄 Actualiser
        </button>
    </div>
</div>

<!-- Statistiques principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3 id="total-fonds">0 €</h3>
            <p>Total des Fonds</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💳</div>
        <div class="stat-content">
            <h3 id="fonds-disponibles">0 €</h3>
            <p>Fonds Disponibles</p>
        </div>
    </div>
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
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <h3 id="montant-pretes">0 €</h3>
            <p>Montant Prêté</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <h3 id="taux-utilisation">0%</h3>
            <p>Taux d'Utilisation</p>
        </div>
    </div>
</div>

<!-- Graphiques et analyses -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Répartition des fonds -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">💰 Répartition des Fonds</h2>
        </div>
        <div id="chart-fonds" style="height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
            Graphique en cours de chargement...
        </div>
    </div>

    <!-- État des prêts -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📊 État des Prêts</h2>
        </div>
        <div id="chart-prets" style="height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
            Graphique en cours de chargement...
        </div>
    </div>
</div>

<!-- Derniers prêts -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🕒 Derniers Prêts</h2>
    </div>
    <div class="table-container">
        <table class="table" id="table-derniers-prets">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Derniers fonds ajoutés -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">💵 Derniers Fonds Ajoutés</h2>
    </div>
    <div class="table-container">
        <table class="table" id="table-derniers-fonds">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Motif</th>
                    <th>Montant</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    const apiBase = "http://localhost/Projet-S4/ws";
    let dashboardData = {};

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

    function actualiserDashboard() {
        chargerToutesLesDonnees();
        alert("Tableau de bord actualisé");
    }

    function chargerToutesLesDonnees() {
        // Charger les fonds
        ajax("GET", "/fonds/total", null, (data) => {
            dashboardData.totalFonds = data.total;
            
            // Charger les fonds disponibles
            ajax("GET", "/fonds/disponibles", null, (data) => {
                dashboardData.fondsDisponibles = data.disponibles;
                
                // Charger les clients
                ajax("GET", "/clients", null, (clients) => {
                    dashboardData.clients = clients;
                    
                    // Charger les prêts
                    ajax("GET", "/prets", null, (prets) => {
                        dashboardData.prets = prets;
                        
                        // Charger tous les fonds pour le tableau
                        ajax("GET", "/fonds", null, (fonds) => {
                            dashboardData.fonds = fonds;
                            calculerStatistiques();
                        });
                    });
                });
            });
        });
    }

    function calculerStatistiques() {
        // Statistiques principales
        document.getElementById("total-fonds").textContent = formatMoney(dashboardData.totalFonds);
        document.getElementById("fonds-disponibles").textContent = formatMoney(dashboardData.fondsDisponibles);
        document.getElementById("total-clients").textContent = dashboardData.clients.length;
        document.getElementById("total-prets").textContent = dashboardData.prets.length;
        
        const montantPretes = dashboardData.prets.reduce((sum, p) => sum + parseFloat(p.montant), 0);
        document.getElementById("montant-pretes").textContent = formatMoney(montantPretes);
        
        const tauxUtilisation = dashboardData.totalFonds > 0 ? (montantPretes / dashboardData.totalFonds * 100).toFixed(1) : 0;
        document.getElementById("taux-utilisation").textContent = tauxUtilisation + "%";

        // Graphiques
        afficherRepartitionFonds();
        afficherEtatPrets();
        afficherDerniersPrets();
        afficherDerniersFonds();
    }

    function afficherRepartitionFonds() {
        const montantPretes = dashboardData.prets.reduce((sum, p) => sum + parseFloat(p.montant), 0);
        const fondsDisponibles = dashboardData.fondsDisponibles;
        
        const chartDiv = document.getElementById("chart-fonds");
        chartDiv.innerHTML = `
            <div style="width: 100%; height: 100%;">
                <div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                    <span><strong>💰 Fonds Prêtés</strong></span>
                    <span style="color: #667eea; font-weight: 600;">${formatMoney(montantPretes)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                    <span><strong>💳 Fonds Disponibles</strong></span>
                    <span style="color: #48bb78; font-weight: 600;">${formatMoney(fondsDisponibles)}</span>
                </div>
                <div style="margin-top: 20px; padding: 15px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px; text-align: center;">
                    <h4>Total des Fonds</h4>
                    <h2>${formatMoney(dashboardData.totalFonds)}</h2>
                </div>
            </div>
        `;
    }

    function afficherEtatPrets() {
        const repartition = {
            'Validé': 0,
            'En attente': 0,
            'Refusé': 0
        };

        dashboardData.prets.forEach(p => {
            switch(p.statuts) {
                case 'valide': repartition['Validé']++; break;
                case 'en attente': repartition['En attente']++; break;
                case 'refuse': repartition['Refusé']++; break;
            }
        });

        const chartDiv = document.getElementById("chart-prets");
        chartDiv.innerHTML = `
            <div style="width: 100%; height: 100%;">
                ${Object.entries(repartition).map(([statut, count]) => `
                    <div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                        <span><strong>${statut}</strong></span>
                        <span style="color: #667eea; font-weight: 600;">${count} prêt(s)</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function afficherDerniersPrets() {
        const derniersPrets = dashboardData.prets.slice(0, 5);
        const tbody = document.querySelector("#table-derniers-prets tbody");
        tbody.innerHTML = "";
        
        derniersPrets.forEach(p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>#${p.id_pret}</td>
                <td>${p.client_nom}</td>
                <td>${formatMoney(p.montant)}</td>
                <td>${p.type_pret_nom}</td>
                <td>${formatDate(p.date_pret)}</td>
                <td><span class="badge badge-${getBadgeClass(p.statuts)}">${p.statuts}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function afficherDerniersFonds() {
        const derniersFonds = dashboardData.fonds.slice(0, 5);
        const tbody = document.querySelector("#table-derniers-fonds tbody");
        tbody.innerHTML = "";
        
        derniersFonds.forEach(f => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>#${f.id_fond}</td>
                <td>${f.motif || '-'}</td>
                <td>${formatMoney(f.montant)}</td>
                <td>${formatDate(new Date())}</td>
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

    function formatMoney(amount) {
        return parseFloat(amount).toFixed(2) + " €";
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR');
    }

    // Chargement initial
    chargerToutesLesDonnees();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 