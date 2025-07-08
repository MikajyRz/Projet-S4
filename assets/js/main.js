// Configuration globale
const CONFIG = {
    apiBase: "http://localhost/Projet-S4/ws",
    currency: "€",
    dateFormat: "fr-FR"
};

// Fonction AJAX commune
function ajax(method, url, data, callback, errorCallback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, CONFIG.apiBase + url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    callback(response);
                } catch (e) {
                    console.error("Erreur de parsing JSON:", e);
                    if (errorCallback) errorCallback("Erreur de format de réponse");
                }
            } else {
                console.error("Erreur HTTP:", xhr.status, xhr.statusText);
                if (errorCallback) errorCallback(`Erreur ${xhr.status}: ${xhr.statusText}`);
            }
        }
    };
    
    xhr.onerror = () => {
        console.error("Erreur réseau");
        if (errorCallback) errorCallback("Erreur de connexion réseau");
    };
    
    xhr.send(data);
}

// Fonctions utilitaires
const Utils = {
    // Formatage des montants
    formatMoney: (amount) => {
        return parseFloat(amount || 0).toFixed(2) + " " + CONFIG.currency;
    },
    
    // Formatage des dates
    formatDate: (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString(CONFIG.dateFormat);
    },
    
    // Formatage des pourcentages
    formatPercent: (value) => {
        return parseFloat(value || 0).toFixed(2) + "%";
    },
    
    // Validation des emails
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Validation des montants
    isValidAmount: (amount) => {
        return !isNaN(amount) && parseFloat(amount) > 0;
    },
    
    // Génération d'ID unique
    generateId: () => {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },
    
    // Debounce pour les recherches
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Notifications
const Notifications = {
    show: (message, type = 'info', duration = 3000) => {
        // Créer la notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-icon">${this.getIcon(type)}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        // Ajouter les styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 16px;
            z-index: 10000;
            min-width: 300px;
            border-left: 4px solid ${this.getColor(type)};
            animation: slideIn 0.3s ease-out;
        `;
        
        // Ajouter au DOM
        document.body.appendChild(notification);
        
        // Auto-suppression
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, duration);
    },
    
    getIcon: (type) => {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    },
    
    getColor: (type) => {
        const colors = {
            success: '#48bb78',
            error: '#f56565',
            warning: '#ed8936',
            info: '#4299e1'
        };
        return colors[type] || colors.info;
    }
};

// Gestionnaire de formulaires
const FormHandler = {
    // Validation de formulaire
    validate: (formData, rules) => {
        const errors = {};
        
        for (const [field, rule] of Object.entries(rules)) {
            const value = formData[field];
            
            if (rule.required && (!value || value.trim() === '')) {
                errors[field] = `${rule.label} est requis`;
                continue;
            }
            
            if (rule.email && !Utils.isValidEmail(value)) {
                errors[field] = `${rule.label} doit être un email valide`;
                continue;
            }
            
            if (rule.minLength && value.length < rule.minLength) {
                errors[field] = `${rule.label} doit contenir au moins ${rule.minLength} caractères`;
                continue;
            }
            
            if (rule.amount && !Utils.isValidAmount(value)) {
                errors[field] = `${rule.label} doit être un montant valide`;
                continue;
            }
        }
        
        return errors;
    },
    
    // Affichage des erreurs
    showErrors: (errors) => {
        // Nettoyer les erreurs précédentes
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.form-input.error').forEach(el => el.classList.remove('error'));
        
        // Afficher les nouvelles erreurs
        for (const [field, message] of Object.entries(errors)) {
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                errorDiv.style.cssText = 'color: #f56565; font-size: 12px; margin-top: 4px;';
                input.parentNode.appendChild(errorDiv);
            }
        }
    },
    
    // Nettoyage des erreurs
    clearErrors: () => {
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.form-input.error').forEach(el => el.classList.remove('error'));
    }
};

// Gestionnaire de tableaux
const TableHandler = {
    // Tri de tableau
    sort: (table, columnIndex, dataType = 'string') => {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();
            
            if (dataType === 'number') {
                return parseFloat(aValue) - parseFloat(bValue);
            } else if (dataType === 'date') {
                return new Date(aValue) - new Date(bValue);
            } else {
                return aValue.localeCompare(bValue);
            }
        });
        
        // Réorganiser les lignes
        rows.forEach(row => tbody.appendChild(row));
    },
    
    // Filtrage de tableau
    filter: (table, searchTerm, columnIndex = null) => {
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let shouldShow = false;
            
            if (columnIndex !== null) {
                // Filtrage sur une colonne spécifique
                const cellText = cells[columnIndex].textContent.toLowerCase();
                shouldShow = cellText.includes(searchTerm.toLowerCase());
            } else {
                // Filtrage sur toutes les colonnes
                shouldShow = Array.from(cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }
            
            row.style.display = shouldShow ? '' : 'none';
        });
    }
};

// Styles CSS pour les notifications
const notificationStyles = `
<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.notification-icon {
    font-size: 18px;
}

.notification-message {
    flex: 1;
    font-weight: 500;
}

.notification-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.notification-close:hover {
    background-color: rgba(0,0,0,0.1);
}

.form-input.error {
    border-color: #f56565;
    box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1);
}

.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #667eea;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
`;

// Ajouter les styles au head
document.head.insertAdjacentHTML('beforeend', notificationStyles);

// Exposer les fonctions globalement
window.Utils = Utils;
window.Notifications = Notifications;
window.FormHandler = FormHandler;
window.TableHandler = TableHandler; 