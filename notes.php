<?php
require_once 'template.php';

$template = new Template('Gestion des Notes - Système de Gestion');

ob_start();
?>

<div class="header">
    <div>
        <h1>📊 Gestion des Notes</h1>
        <p>Gérez les notes et évaluations des étudiants</p>
    </div>
    <div class="header-actions">
        <button class="sync-btn" onclick="exporterNotes()">
            📥 Exporter
        </button>
    </div>
</div>

<!-- Statistiques des notes -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📈</div>
        <div class="stat-content">
            <h3 id="moyenne-generale">0</h3>
            <p>Moyenne Générale</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🏆</div>
        <div class="stat-content">
            <h3 id="meilleure-note">0</h3>
            <p>Meilleure Note</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-content">
            <h3 id="total-evaluations">0</h3>
            <p>Total Évaluations</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <h3 id="taux-reussite">0%</h3>
            <p>Taux de Réussite</p>
        </div>
    </div>
</div>

<!-- Formulaire d'ajout de note -->
<div class="form-container">
    <div class="card-header">
        <h2 class="card-title">➕ Ajouter / Modifier une Note</h2>
    </div>
    
    <div class="form-grid">
        <div class="form-group">
            <label for="etudiant_id" class="form-label">Étudiant *</label>
            <select id="etudiant_id" class="form-input" required>
                <option value="">Sélectionner un étudiant</option>
            </select>
        </div>
        <div class="form-group">
            <label for="cours_id" class="form-label">Cours *</label>
            <select id="cours_id" class="form-input" required>
                <option value="">Sélectionner un cours</option>
            </select>
        </div>
        <div class="form-group">
            <label for="note" class="form-label">Note *</label>
            <input type="number" id="note" class="form-input" placeholder="Note sur 20" min="0" max="20" step="0.5" required>
        </div>
        <div class="form-group">
            <label for="coefficient" class="form-label">Coefficient</label>
            <input type="number" id="coefficient" class="form-input" placeholder="Coefficient" min="0.1" max="5" step="0.1" value="1">
        </div>
        <div class="form-group">
            <label for="type_evaluation" class="form-label">Type d'évaluation</label>
            <select id="type_evaluation" class="form-input">
                <option value="Contrôle">Contrôle</option>
                <option value="Examen">Examen</option>
                <option value="TP">TP</option>
                <option value="Projet">Projet</option>
                <option value="Oral">Oral</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_evaluation" class="form-label">Date d'évaluation</label>
            <input type="date" id="date_evaluation" class="form-input" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
    
    <input type="hidden" id="id_note">
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-primary" onclick="ajouterOuModifierNote()">
            💾 Enregistrer
        </button>
        <button class="btn btn-secondary" onclick="resetFormNote()">
            🔄 Réinitialiser
        </button>
    </div>
</div>

<!-- Tableau des notes -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">📋 Liste des Notes</h2>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-secondary" onclick="chargerNotes()">
                🔄 Actualiser
            </button>
        </div>
    </div>
    <table class="table" id="table-notes">
        <thead>
            <tr>
                <th>ID</th>
                <th>Étudiant</th>
                <th>Cours</th>
                <th>Note</th>
                <th>Coefficient</th>
                <th>Note Pondérée</th>
                <th>Type</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    let notesData = [];

    function chargerEtudiants() {
        ajax("GET", "/etudiants", null, (data) => {
            const select = document.getElementById("etudiant_id");
            select.innerHTML = '<option value="">Sélectionner un étudiant</option>';
            
            data.forEach(e => {
                const option = document.createElement("option");
                option.value = e.id;
                option.textContent = `${e.nom} ${e.prenom}`;
                select.appendChild(option);
            });
        });
    }

    function chargerCours() {
        ajax("GET", "/cours", null, (data) => {
            const select = document.getElementById("cours_id");
            select.innerHTML = '<option value="">Sélectionner un cours</option>';
            
            data.forEach(c => {
                const option = document.createElement("option");
                option.value = c.id_cours;
                option.textContent = c.nom_cours;
                select.appendChild(option);
            });
        });
    }

    function chargerNotes() {
        ajax("GET", "/notes", null, (data) => {
            notesData = data;
            afficherNotes(data);
            calculerStatistiques(data);
        });
    }

    function afficherNotes(data) {
        const tbody = document.querySelector("#table-notes tbody");
        tbody.innerHTML = "";
        
        data.forEach(n => {
            const notePonderee = (n.note * n.coefficient).toFixed(2);
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${n.id_note}</td>
                <td>${n.nom_etudiant} ${n.prenom_etudiant}</td>
                <td>${n.nom_cours}</td>
                <td><span class="note-value ${getNoteClass(n.note)}">${n.note}/20</span></td>
                <td>${n.coefficient}</td>
                <td>${notePonderee}</td>
                <td>${n.type_evaluation}</td>
                <td>${formatDate(n.date_evaluation)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="icon-btn edit" onclick='remplirFormulaireNote(${JSON.stringify(n)})'>✏️</button>
                        <button class="icon-btn delete" onclick='supprimerNote(${n.id_note})'>🗑️</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function getNoteClass(note) {
        if (note >= 16) return 'excellent';
        if (note >= 14) return 'tres-bien';
        if (note >= 12) return 'bien';
        if (note >= 10) return 'moyen';
        return 'insuffisant';
    }

    function calculerStatistiques(data) {
        if (data.length === 0) return;

        const notes = data.map(n => n.note);
        const moyenne = notes.reduce((a, b) => a + b, 0) / notes.length;
        const meilleure = Math.max(...notes);
        const reussites = notes.filter(n => n >= 10).length;
        const tauxReussite = (reussites / notes.length * 100).toFixed(1);

        document.getElementById("moyenne-generale").textContent = moyenne.toFixed(2);
        document.getElementById("meilleure-note").textContent = meilleure;
        document.getElementById("total-evaluations").textContent = data.length;
        document.getElementById("taux-reussite").textContent = tauxReussite + '%';
    }

    function ajouterOuModifierNote() {
        const id = document.getElementById("id_note").value;
        const etudiant_id = document.getElementById("etudiant_id").value;
        const cours_id = document.getElementById("cours_id").value;
        const note = document.getElementById("note").value;
        const coefficient = document.getElementById("coefficient").value;
        const type_evaluation = document.getElementById("type_evaluation").value;
        const date_evaluation = document.getElementById("date_evaluation").value;

        if (!etudiant_id || !cours_id || !note) {
            showNotification("Veuillez remplir tous les champs obligatoires", "error");
            return;
        }

        if (note < 0 || note > 20) {
            showNotification("La note doit être comprise entre 0 et 20", "error");
            return;
        }

        const data = `etudiant_id=${etudiant_id}&cours_id=${cours_id}&note=${note}&coefficient=${coefficient}&type_evaluation=${encodeURIComponent(type_evaluation)}&date_evaluation=${date_evaluation}`;

        if (id) {
            ajax("PUT", `/notes/${id}`, data, () => {
                resetFormNote();
                chargerNotes();
                showNotification("Note modifiée avec succès");
            });
        } else {
            ajax("POST", "/notes", data, () => {
                resetFormNote();
                chargerNotes();
                showNotification("Note ajoutée avec succès");
            });
        }
    }

    function remplirFormulaireNote(n) {
        document.getElementById("id_note").value = n.id_note;
        document.getElementById("etudiant_id").value = n.etudiant_id;
        document.getElementById("cours_id").value = n.cours_id;
        document.getElementById("note").value = n.note;
        document.getElementById("coefficient").value = n.coefficient;
        document.getElementById("type_evaluation").value = n.type_evaluation;
        document.getElementById("date_evaluation").value = n.date_evaluation;
    }

    function supprimerNote(id) {
        if (confirmAction("Êtes-vous sûr de vouloir supprimer cette note ?")) {
            ajax("DELETE", `/notes/${id}`, null, () => {
                chargerNotes();
                showNotification("Note supprimée avec succès");
            });
        }
    }

    function resetFormNote() {
        document.getElementById("id_note").value = "";
        document.getElementById("etudiant_id").value = "";
        document.getElementById("cours_id").value = "";
        document.getElementById("note").value = "";
        document.getElementById("coefficient").value = "1";
        document.getElementById("type_evaluation").value = "Contrôle";
        document.getElementById("date_evaluation").value = "<?php echo date('Y-m-d'); ?>";
    }

    function exporterNotes() {
        showNotification("Fonctionnalité d'export à implémenter");
    }

    // Chargement initial
    chargerEtudiants();
    chargerCours();
    chargerNotes();
</script>

<style>
.note-value {
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 14px;
}

.note-value.excellent {
    background: #d4edda;
    color: #155724;
}

.note-value.tres-bien {
    background: #cce5ff;
    color: #004085;
}

.note-value.bien {
    background: #fff3cd;
    color: #856404;
}

.note-value.moyen {
    background: #f8d7da;
    color: #721c24;
}

.note-value.insuffisant {
    background: #f8d7da;
    color: #721c24;
}
</style>

<?php
$content = ob_get_clean();
$template->setContent($content);
$template->render();
?> 