<?php
require_once 'template.php';

$template = new Template('Statistiques - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>📈 Tableau de Bord Statistiques</h1>
        <p>Vue d'ensemble complète de votre système de prêts</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="actualiserStatistiques()">
            🔄 Actualiser
        </button>
    </div>
</div>

<!-- Statistiques générales -->
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
            <h3 id="montant-total-prets">0 €</h3>
            <p>Montant Total Prêté</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <h3 id="taux-validation">0%</h3>
            <p>Taux de Validation</p>
        </div>
    </div>
</div>

<!-- Graphiques et analyses -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Répartition par statut -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📊 Répartition par Statut</h2>
        </div>
        <div id="chart-statuts" style="height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
            Graphique en cours de chargement...
        </div>
    </div>

    <!-- Répartition par type de prêt -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📈 Répartition par Type de Prêt</h2>
        </div>
        <div id="chart-types" style="height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
            Graphique en cours de chargement...
        </div>
    </div>
</div>

<!-- Top des clients -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🏆 Top des Clients les Plus Actifs</h2>
    </div>
    <div class="table-container">
        <table class="table" id="table-top-clients">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Client</th>
                    <th>Nombre de prêts</th>
                    <th>Montant total</th>
                    <th>Moyenne par prêt</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Top des types de prêts -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🥇 Types de Prêts les Plus Populaires</h2>
    </div>
    <div class="table-container">
        <table class="table" id="table-top-types">
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Type de Prêt</th>
                    <th>Nombre de prêts</th>
                    <th>Montant total</th>
                    <th>Taux d'intérêt</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Évolution mensuelle -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📅 Évolution Mensuelle des Prêts</h2>
    </div>
    <div id="chart-evolution" style="height: 300px; display: flex; align-items: center; justify-content: center; color: #666;">
        Graphique en cours de chargement...
    </div>
</div>

<script>
    const apiBase = "http://localhost/T/Examen_S4/ws";
    let statsData = {};

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

    function actualiserStatistiques() {
        chargerToutesLesDonnees();
        alert("Statistiques actualisées");
    }

    function chargerToutesLesDonnees() {
        // Charger les données des clients
        ajax("GET", "/clients", null, (clients) => {
            statsData.clients = clients;
            
            // Charger les données des prêts
            ajax("GET", "/prets", null, (prets) => {
                statsData.prets = prets;
                
                // Charger les types de prêts
                ajax("GET", "/type-pret", null, (types) => {
                    statsData.types = types;
                    
                    // Charger les fonds
                    ajax("GET", "/fonds", null, (fonds) => {
                        statsData.fonds = fonds;
                        calculerToutesLesStatistiques();
                    });
                });
            });
        });
    }

    function calculerToutesLesStatistiques() {
        calculerStatistiquesGenerales();
        afficherRepartitionStatuts();
        afficherRepartitionTypes();
        afficherTopClients();
        afficherTopTypes();
        afficherEvolutionMensuelle();
    }

    function calculerStatistiquesGenerales() {
        const totalClients = statsData.clients.length;
        const totalPrets = statsData.prets.length;
        
        let montantTotal = 0;
        let pretsValides = 0;
        
        statsData.prets.forEach(p => {
            montantTotal += parseFloat(p.montant);
            if (p.statuts === 'valide') pretsValides++;
        });
        
        const tauxValidation = totalPrets > 0 ? (pretsValides / totalPrets * 100).toFixed(1) : 0;

        document.getElementById("total-clients").textContent = totalClients;
        document.getElementById("total-prets").textContent = totalPrets;
        document.getElementById("montant-total-prets").textContent = montantTotal.toFixed(2) + " €";
        document.getElementById("taux-validation").textContent = tauxValidation + "%";
    }

    function afficherRepartitionStatuts() {
        const repartition = {
            'En attente': 0,
            'Validé': 0,
            'Refusé': 0
        };

        statsData.prets.forEach(p => {
            switch(p.statuts) {
                case 'en attente': repartition['En attente']++; break;
                case 'valide': repartition['Validé']++; break;
                case 'refuse': repartition['Refusé']++; break;
            }
        });

        const chartDiv = document.getElementById("chart-statuts");
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

    function afficherRepartitionTypes() {
        const repartition = {};
        
        statsData.prets.forEach(p => {
            if (!repartition[p.type_pret_nom]) {
                repartition[p.type_pret_nom] = 0;
            }
            repartition[p.type_pret_nom]++;
        });

        const chartDiv = document.getElementById("chart-types");
        chartDiv.innerHTML = `
            <div style="width: 100%; height: 100%;">
                ${Object.entries(repartition).map(([type, count]) => `
                    <div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                        <span><strong>${type}</strong></span>
                        <span style="color: #667eea; font-weight: 600;">${count} prêt(s)</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function afficherTopClients() {
        const clientsStats = {};
        
        statsData.prets.forEach(p => {
            if (!clientsStats[p.client_nom]) {
                clientsStats[p.client_nom] = {
                    nom: p.client_nom,
                    prets: 0,
                    montant: 0
                };
            }
            clientsStats[p.client_nom].prets++;
            clientsStats[p.client_nom].montant += parseFloat(p.montant);
        });

        const topClients = Object.entries(clientsStats)
            .map(([nom, stats]) => ({
                nom: stats.nom,
                prets: stats.prets,
                montant: stats.montant,
                moyenne: stats.montant / stats.prets
            }))
            .sort((a, b) => b.prets - a.prets)
            .slice(0, 10);

        const tbody = document.querySelector("#table-top-clients tbody");
        tbody.innerHTML = "";
        
        topClients.forEach((client, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${client.nom}</td>
                <td>${client.prets}</td>
                <td>${client.montant.toFixed(2)} €</td>
                <td>${client.moyenne.toFixed(2)} €</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function afficherTopTypes() {
        const typesStats = {};
        
        // Initialiser les statistiques des types
        statsData.types.forEach(t => {
            typesStats[t.nom] = {
                nom: t.nom,
                prets: 0,
                montant: 0,
                taux: t.taux
            };
        });

        // Ajouter les prêts
        statsData.prets.forEach(p => {
            if (typesStats[p.type_pret_nom]) {
                typesStats[p.type_pret_nom].prets++;
                typesStats[p.type_pret_nom].montant += parseFloat(p.montant);
            }
        });

        const topTypes = Object.entries(typesStats)
            .map(([nom, stats]) => ({
                nom: stats.nom,
                prets: stats.prets,
                montant: stats.montant,
                taux: stats.taux
            }))
            .sort((a, b) => b.prets - a.prets)
            .slice(0, 10);

        const tbody = document.querySelector("#table-top-types tbody");
        tbody.innerHTML = "";
        
        topTypes.forEach((type, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${type.nom}</td>
                <td>${type.prets}</td>
                <td>${type.montant.toFixed(2)} €</td>
                <td>${type.taux}%</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function afficherEvolutionMensuelle() {
        const chartDiv = document.getElementById("chart-evolution");
        chartDiv.innerHTML = `
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #666;">
                <div style="text-align: center;">
                    <h3>📊 Évolution des Prêts</h3>
                    <p>Fonctionnalité de graphique avancé à implémenter</p>
                    <p>Total prêts: ${statsData.prets.length}</p>
                </div>
            </div>
        `;
    }

    // Chargement initial
    chargerToutesLesDonnees();
</script>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 