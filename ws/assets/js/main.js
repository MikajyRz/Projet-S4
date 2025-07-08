function ajax(method, url, data, callback) {
    const xhr = new XMLHttpRequest();
    const apiBase = "/ws"; // Utilisez un chemin relatif
    xhr.open(method, apiBase + url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = xhr.responseText ? JSON.parse(xhr.responseText) : {};
                    callback(response);
                } catch (e) {
                    console.error("Erreur parsing JSON:", e);
                    showNotification("Erreur dans la réponse du serveur", 'error');
                }
            } else {
                const errorMsg = `Erreur ${xhr.status}: ${xhr.statusText}`;
                console.error(errorMsg);
                showNotification(errorMsg, 'error');
            }
        }
    };
    
    xhr.onerror = function() {
        showNotification("Erreur de connexion au serveur", 'error');
    };
    
    xhr.send(data);
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Fonction pour confirmer les actions
function confirmAction(message) {
    return confirm(message);
}

// Fonction pour réinitialiser un formulaire
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

// Fonction pour valider les emails
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Fonction pour formater les dates
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('Application de gestion des étudiants chargée');
}); 