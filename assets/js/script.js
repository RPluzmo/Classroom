class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.setupThemeToggle();
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
    }

    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
        
        fetch('/controllers/toggle_theme.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    }
})
        .then(response => response.json())
        .then(data => {
            console.log('Theme updated on server');
        })
        .catch(error => {
            console.error('Error updating theme:', error);
        });
    }

    setupThemeToggle() {
        const toggleBtn = document.querySelector('.theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggleTheme());
            
            this.updateThemeIcon(toggleBtn);
        }
    }

    updateThemeIcon(button) {
        if (this.currentTheme === 'dark') {
            button.innerHTML = '☀️';
            button.title = 'Giašs';
        } else {
            button.innerHTML = '🌙';
            button.title = 'Tumšs';
        }
    }
}

// Modal Management
class ModalManager {
    constructor() {
        this.modals = {};
        this.init();
    }

    init() {
        document.querySelectorAll('.modal').forEach(modal => {
            const id = modal.id;
            this.modals[id] = modal;
            
            // Close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.close(id);
                }
            });

            // Close modal with ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    this.close(id);
                }
            });
        });

        // Setup close buttons
        document.querySelectorAll('.modal-close').forEach(button => {
            button.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.close(modal.id);
                }
            });
        });
    }

    open(modalId) {
        if (this.modals[modalId]) {
            this.modals[modalId].classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    close(modalId) {
        if (this.modals[modalId]) {
            this.modals[modalId].classList.remove('active');
            document.body.style.overflow = '';
        }
    }
}

// File Upload Management
class FileUploadManager {
    constructor(inputId, dropZoneId = null) {
        this.input = document.getElementById(inputId);
        this.dropZone = dropZoneId ? document.getElementById(dropZoneId) : null;
        this.files = [];
        this.init();
    }

    init() {
        if (this.input) {
            this.input.addEventListener('change', (e) => {
                this.handleFiles(e.target.files);
            });
        }

        if (this.dropZone) {
            this.setupDragAndDrop();
        }
    }

    setupDragAndDrop() {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => {
                this.dropZone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            this.dropZone.addEventListener(eventName, () => {
                this.dropZone.classList.remove('dragover');
            });
        });

        this.dropZone.addEventListener('drop', (e) => {
            this.handleFiles(e.dataTransfer.files);
        });
    }

    handleFiles(fileList) {
        this.files = Array.from(fileList);
        this.displayFiles();
    }

    displayFiles() {
        const fileListElement = document.querySelector('.file-list');
        if (!fileListElement) return;

        fileListElement.innerHTML = '';

        this.files.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <span class="file-icon">📄</span>
                <span class="file-name">${file.name}</span>
                <span class="file-size">${this.formatFileSize(file.size)}</span>
                <button type="button" class="btn btn-icon btn-sm" onclick="fileUploadManager.removeFile(${index})">×</button>
            `;
            fileListElement.appendChild(fileItem);
        });
    }

    removeFile(index) {
        this.files.splice(index, 1);
        this.displayFiles();
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getFiles() {
        return this.files;
    }
}

// QR Code Generation
class QRCodeManager {
    static generateClassCodeModal(classCode, className) {
        const modal = document.getElementById('qrModal');
        if (!modal) return;

        const qrContainer = modal.querySelector('.qr-code-container');
        const classCodeElement = modal.querySelector('.class-code-display');
        const classNameElement = modal.querySelector('.class-name-display');

        if (classCodeElement) {
            classCodeElement.textContent = classCode;
        }

        if (classNameElement) {
            classNameElement.textContent = className;
        }

        if (qrContainer) {
            qrContainer.innerHTML = `
                <div style="text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 16px;">📱</div>
                    <p style="color: var(--text-secondary); margin-bottom: 16px;">
                        QR itkā sastāv no kursa koda...yeh idk
                    </p>
                    <div style="background: white; padding: 20px; border-radius: 8px; display: inline-block;">
                        <code style="font-size: 24px; font-weight: bold; color: var(--primary-color);">${classCode}</code>
                    </div>
                </div>
            `;
        }

        modalManager.open('qrModal');
    }
}

// Form Validation
class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.rules = {};
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                if (!this.validate()) {
                    e.preventDefault();
                }
            });
        }
    }

    addRule(fieldName, rules) {
        this.rules[fieldName] = rules;
    }

    validate() {
        let isValid = true;
        const errors = {};

        Object.keys(this.rules).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            const fieldRules = this.rules[fieldName];
            
            if (field) {
                fieldRules.forEach(rule => {
                    if (!this.validateField(field, rule)) {
                        isValid = false;
                        if (!errors[fieldName]) {
                            errors[fieldName] = [];
                        }
                        errors[fieldName].push(rule.message);
                    }
                });
            }
        });

        this.displayErrors(errors);
        return isValid;
    }

    validateField(field, rule) {
        const value = field.value.trim();

        switch (rule.type) {
            case 'required':
                return value !== '';
            case 'email':
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            case 'minLength':
                return value.length >= rule.value;
            case 'maxLength':
                return value.length <= rule.value;
            case 'pattern':
                return new RegExp(rule.value).test(value);
            default:
                return true;
        }
    }

    displayErrors(errors) {
        // Clear existing errors
        this.form.querySelectorAll('.error-message').forEach(el => el.remove());

        Object.keys(errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                errors[fieldName].forEach(errorMessage => {
                    const errorElement = document.createElement('div');
                    errorElement.className = 'error-message';
                    errorElement.style.color = 'var(--danger-color)';
                    errorElement.style.fontSize = '12px';
                    errorElement.style.marginTop = '4px';
                    errorElement.textContent = errorMessage;
                    field.parentNode.appendChild(errorElement);
                });
            }
        });
    }
}

// Utility Functions
const utils = {
    // Show notification
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.maxWidth = '300px';

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    },

    // Format date
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Tiko';
        if (minutes < 60) return `${minutes} pirms pāris minūtēm`;
        if (hours < 24) return `${hours} Pirms pāris stundām`;
        if (days < 7) return `${days} Pirs vairākām dienām`;
        
        return date.toLocaleDateString();
    },

    // Copy to clipboard
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showNotification('Kopēts', 'success');
        }).catch(() => {
            this.showNotification('', 'error');
        });
    },

    // Confirm action
    confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },

    // Loading states
    showLoading(element) {
        if (element) {
            element.disabled = true;
            element.innerHTML = '<span class="spinner"></span> Loading...';
        }
    },

    hideLoading(element, originalText) {
        if (element) {
            element.disabled = false;
            element.innerHTML = originalText;
        }
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize theme manager
    window.themeManager = new ThemeManager();
    
    // Initialize modal manager
    window.modalManager = new ModalManager();
    
    // Initialize file upload managers
    window.fileUploadManager = new FileUploadManager('fileInput', 'dropZone');

    // Setup AJAX for forms
    document.querySelectorAll('.ajax-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            handleAjaxForm(form);
        });
    });

    // Setup copy buttons
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const textToCopy = button.getAttribute('data-copy') || button.textContent;
            utils.copyToClipboard(textToCopy);
        });
    });

    // Setup delete confirmations
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            const message = button.getAttribute('data-confirm') || 'Vai tiešām vēlaties dzēst?';
            utils.confirmAction(message, () => {
                window.location.href = button.getAttribute('data-href');
            });
        });
    });
});

// Handle AJAX form submissions
function handleAjaxForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn?.innerHTML || 'Submit';

    utils.showLoading(submitBtn);

    const formData = new FormData(form);

    fetch(form.action, {
        method: form.method,
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        utils.hideLoading(submitBtn, originalText);

        if (data.success) {
            utils.showNotification(data.message || 'Ok!', 'success');
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            } else if (data.reload) {
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            utils.showNotification(data.message || 'Kautkas nesanāca', 'error');
        }
    })
    .catch(error => {
        utils.hideLoading(submitBtn, originalText);
        utils.showNotification('nē', 'error');
        console.error('Error:', error);
    });
}


// Export for global access
window.utils = utils;
window.QRCodeManager = QRCodeManager;
window.FormValidator = FormValidator;