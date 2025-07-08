<?php
require_once 'template.php';

$template = new Template('Gestion des Remboursements - Système de Prêts');

ob_start();
?>

<div class="header">
    <div>
        <h1>📊 Gestion des Remboursements</h1>
        <p>Gérez les remboursements et intérêts des prêts</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterRemboursements()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Statistiques des remboursements -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3 id="total-rembourse">0 €</h3>
            <p>Total Remboursé</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <h3 id="total-interets">0 €</h3>
            <p>Total Intérêts</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🛡️</div>
        <div class="stat-content">
            <h3 id="total-assurance">0 €</h3>
            <p>Total Assurance</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
            <h3 id="nb-remboursements">0</h3>
            <p>Nombre de Remboursements</p>
        </div>
    </div>
</div>

<!-- Formulaire de simulation -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">🧮 Simulation de Remboursement</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="id_pret_simulation" class="form-label">Prêt à simuler</label>
            <select id="id_pret_simulation" class="form-input">
                <option value="">Sélectionner un prêt</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-primary" onclick="simulerRemboursement()">
                🧮 Simuler
            </button>
        </div>
    </div>
</div>

<!-- Résultat de simulation -->
<div id="resultat-simulation" class="form-container" style="display: none;">
    <div class="card-header">
        <h2 class="card-title">📊 Résultat de la Simulation</h2>
    </div>
    <div id="simulation-content"></div>
</div>

<!-- Formulaire de validation -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">✅ Validation de Remboursement</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="id_pret_validation" class="form-label">Prêt à valider</label>
            <select id="id_pret_validation" class="form-input">
                <option value="">Sélectionner un prêt</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-success" onclick="validerRemboursement()">
                ✅ Valider
            </button>
        </div>
    </div>
</div>

<!-- Filtres pour la période -->
<div class="form-container" style="margin-bottom: 24px;">
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="mois_debut">Mois début</label>
            <input type="number" min="1" max="12" id="mois_debut" class="form-input" value="1">
        </div>
        <div class="form-group">
            <label class="form-label" for="annee_debut">Année début</label>
            <input type="number" min="2000" max="2100" id="annee_debut" class="form-input" value="2023">
        </div>
        <div class="form-group">
            <label class="form-label" for="mois_fin">Mois fin</label>
            <input type="number" min="1" max="12" id="mois_fin" class="form-input" value="12">
        </div>
        <div class="form-group">
            <label class="form-label" for="annee_fin">Année fin</label>
            <input type="number" min="2000" max="2100" id="annee_fin" class="form-input" value="2023">
        </div>
        <div class="form-group" style="align-self: end;">
            <button class="btn btn-primary" onclick="filtrerStatistiques()">Filtrer</button>
        </div>
    </div>
</div>

<!-- Tableau des statistiques d'intérêts -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Statistiques des Intérêts</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerStatistiques()">
                🔄 Actualiser
            </button>
            <button class="btn btn-primary" onclick="exporterInteretsPdf()">
                📄 Exporter PDF
            </button>
        </div>
    </div>
    <table class="table" id="table-statistiques">
        <thead>
            <tr>
                <th>Mois</th>
                <th>Année</th>
                <th>Total Intérêts</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Graphique des intérêts par mois -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📊 Graphique des Intérêts par Mois</h2>
    </div>
    <div style="padding: 24px;">
        <canvas id="chart-interets" height="80"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    function chargerPrets() {
        ajax("GET", "/prets", null, (data) => {
            const selectSimulation = document.getElementById("id_pret_simulation");
            const selectValidation = document.getElementById("id_pret_validation");
            
            selectSimulation.innerHTML = '<option value="">Sélectionner un prêt</option>';
            selectValidation.innerHTML = '<option value="">Sélectionner un prêt</option>';
            
            data.forEach(p => {
                const optionSim = document.createElement("option");
                optionSim.value = p.id_pret;
                optionSim.textContent = `Prêt #${p.id_pret} - ${p.client_nom} (${p.montant}€)`;
                selectSimulation.appendChild(optionSim);
                
                const optionVal = document.createElement("option");
                optionVal.value = p.id_pret;
                optionVal.textContent = `Prêt #${p.id_pret} - ${p.client_nom} (${p.montant}€)`;
                selectValidation.appendChild(optionVal);
            });
        });
    }

    function simulerRemboursement() {
        const id_pret = document.getElementById("id_pret_simulation").value;
        
        if (!id_pret) {
            alert("Veuillez sélectionner un prêt");
            return;
        }

        // Récupérer les détails du prêt pour la simulation
        ajax("GET", `/prets/${id_pret}`, null, (pret) => {
            const data = `montant=${pret.montant}&taux=${pret.taux}&duree=${pret.duree}&assurance=${pret.assurance || 0}&date_pret=${pret.date_pret}`;
            
            ajax("POST", "/prets/simuler", data, (simulation) => {
                const resultatDiv = document.getElementById("resultat-simulation");
                const contentDiv = document.getElementById("simulation-content");
                
                let html = `<div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h3>Simulation pour le prêt #${id_pret}</h3>
                    <p><strong>Montant:</strong> ${pret.montant}€ | <strong>Taux:</strong> ${pret.taux}% | <strong>Durée:</strong> ${pret.duree} mois | <strong>Assurance:</strong> ${pret.assurance || 0}%</p>
                    <table class="table" style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th>Année</th>
                                <th>Capital</th>
                                <th>Intérêts</th>
                                <th>Assurance</th>
                                <th>Total</th>
                                <th>Capital Restant</th>
                            </tr>
                        </thead>
                        <tbody>`;
                
                simulation.forEach((remb, index) => {
                    html += `<tr>
                        <td>${remb.mois}</td>
                        <td>${remb.annee}</td>
                        <td>${remb.capital.toFixed(2)}€</td>
                        <td>${remb.interet.toFixed(2)}€</td>
                        <td>${remb.assurance.toFixed(2)}€</td>
                        <td><strong>${remb.montant_total.toFixed(2)}€</strong></td>
                        <td>${remb.capital_restant.toFixed(2)}€</td>
                    </tr>`;
                });
                
                html += `</tbody></table></div>`;
                
                contentDiv.innerHTML = html;
                resultatDiv.style.display = "block";
            });
        });
    }

    function validerRemboursement() {
        const id_pret = document.getElementById("id_pret_validation").value;
        
        if (!id_pret) {
            alert("Veuillez sélectionner un prêt");
            return;
        }

        if (confirm("Êtes-vous sûr de vouloir valider ce prêt et générer les remboursements ?")) {
            ajax("POST", `/prets/valider/${id_pret}`, null, (data) => {
                alert("Prêt validé et remboursements générés avec succès");
                chargerStatistiques();
            });
        }
    }

    let statsParMois = [];

    function chargerStatistiques() {
        // Charger les statistiques globales
        ajax("GET", "/remboursements/stats", null, (stats) => {
            document.getElementById("total-rembourse").textContent = parseFloat(stats.total_remboursements || 0).toFixed(2) + " €";
            document.getElementById("total-interets").textContent = parseFloat(stats.total_interets || 0).toFixed(2) + " €";
            document.getElementById("total-assurance").textContent = parseFloat(stats.total_assurance || 0).toFixed(2) + " €";
            document.getElementById("nb-remboursements").textContent = stats.nombre_remboursements || 0;
        });

        // Charger les statistiques par mois/année
        ajax("GET", "/remboursements/par-mois", null, (data) => {
            statsParMois = data;
            afficherStatsFiltrees();
        });
    }

    function exporterInteretsPdf() {
        const md = document.getElementById('mois_debut').value;
        const ad = document.getElementById('annee_debut').value;
        const mf = document.getElementById('mois_fin').value;
        const af = document.getElementById('annee_fin').value;
        window.open(`${apiBase}/pdf-fpdf/interets-par-mois?mois_debut=${md}&annee_debut=${ad}&mois_fin=${mf}&annee_fin=${af}`, '_blank');
    }

    let chartInterets = null;
    function afficherStatsFiltrees() {
        const moisDebut = parseInt(document.getElementById("mois_debut").value);
        const anneeDebut = parseInt(document.getElementById("annee_debut").value);
        const moisFin = parseInt(document.getElementById("mois_fin").value);
        const anneeFin = parseInt(document.getElementById("annee_fin").value);

        // Filtrage par période
        const statsFiltrees = statsParMois.filter(stat => {
            const dateStat = stat.annee * 100 + stat.mois;
            const dateDebut = anneeDebut * 100 + moisDebut;
            const dateFin = anneeFin * 100 + moisFin;
            return dateStat >= dateDebut && dateStat <= dateFin;
        });

        // Tableau
        const tbody = document.querySelector("#table-statistiques tbody");
        tbody.innerHTML = "";
        statsFiltrees.forEach(stat => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${stat.mois}</td>
                <td>${stat.annee}</td>
                <td>${parseFloat(stat.total_interets || 0).toFixed(2)} €</td>
                <td>
                    <button class="icon-btn view" onclick='voirDetails(${stat.mois}, ${stat.annee})'>👁️</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Graphique
        const ctx = document.getElementById('chart-interets').getContext('2d');
        const labels = statsFiltrees.map(stat => `${stat.mois}/${stat.annee}`);
        const data = statsFiltrees.map(stat => parseFloat(stat.total_interets || 0));
        if (chartInterets) chartInterets.destroy();
        chartInterets = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Intérêts gagnés (€)',
                    data: data,
                    backgroundColor: 'rgba(102, 126, 234, 0.7)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    function filtrerStatistiques() {
        afficherStatsFiltrees();
    }

    function voirDetails(mois, annee) {
        ajax("GET", "/remboursements", null, (data) => {
            const remboursements = data.filter(r => r.mois == mois && r.annee == annee);
            if (remboursements.length > 0) {
                let details = `Détails pour ${mois}/${annee}:\n\n`;
                remboursements.forEach(r => {
                    details += `Prêt #${r.id_pret} - ${r.client_nom}\n`;
                    details += `Capital: ${r.capital}€ | Intérêts: ${r.interet}€ | Assurance: ${r.assurance}€ | Total: ${r.montant_total}€\n\n`;
                });
                alert(details);
            } else {
                alert(`Aucun remboursement trouvé pour ${mois}/${annee}`);
            }
        });
    }

    function exporterRemboursements() {
        alert("Fonctionnalité d'export à implémenter");
    }

    // Chargement initial
    chargerPrets();
    chargerStatistiques();
</script>

<style>
.icon-btn.view {
    background: #17a2b8;
    color: white;
}
</style>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 