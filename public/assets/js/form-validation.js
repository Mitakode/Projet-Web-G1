(function () {
    'use strict';

    // Client-side form validation.
    // This script validates common inputs (search, CRUD forms, candidature upload)
    // before submit to provide fast feedback and reduce invalid requests.
    var patterns = {
        // Short free-text search (home/header).
        query: /^[\p{L}\p{N}\s\-'".,()@]{0,100}$/u,
        // Company search.
        search: /^[\p{L}\p{N}\s\-'".,()@]{0,100}$/u,
        // User/company name. Kept in sync with backend validators.
        nom: /^[\p{L}\p{N}\s\-'".&()]+$/u,
        // Person first name.
        prenom: /^[\p{L}\s\-']{1,100}$/u,
        // Formation label.
        formation: /^[\p{L}\p{N}\s\-'".,()\/]{1,150}$/u,
        // Free description, restricted. Kept in sync with backend.
        description: /^[\p{L}\p{N}\s\-'".,()!?@:\/]*$/u,
        // Standard email.
        email: /^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/,
        // Company contact email.
        email_contact: /^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/,
        // Simple FR/intl phone. Must be empty OR match the format (10 digits with separators).
        telephone: /^$|^\+?[0-9\s().-]{10}$/,
        // ISO date (validated further with a real Date object).
        date_naissance: /^\d{4}-\d{2}-\d{2}$/,
        // ISO offer date.
        date_offre: /^\d{4}-\d{2}-\d{2}$/,
        // Amount with 0..2 decimals.
        remuneration: /^\d{1,7}(?:[.,]\d{1,2})?$/,
        // Duration in months (positive integer).
        duree_mois: /^\d{1,12}$/,
        // Number of spots (positive integer).
        nombre_place: /^\d{1,20}$/,
        // Generic numeric identifier.
        id: /^\d+$/,
        // Offer id.
        id_offre: /^\d+$/,
        // Company id.
        id_entreprise: /^\d+$/,
        // Optional manager (pilot) id.
        est_gere_par: /^\d*$/,
        // User role (0, 1 or 2).
        role: /^[012]$/,
        // Company rating (1..5).
        note: /^[1-5]$/,
        // Allowed CRUD action.
        action: /^(add|edit|delete)$/
    };

    function getOrCreateErrorNode(form) {
        var node = form.querySelector('.js-form-error');
        if (!node) {
            node = document.createElement('p');
            node.className = 'js-form-error';
            node.setAttribute('role', 'alert');
            node.setAttribute('aria-live', 'polite');
            node.style.color = '#c62828';
            node.style.fontWeight = '700';
            node.style.margin = '0 0 10px 0';
            form.insertBefore(node, form.firstChild);
        }

        return node;
    }

    function resetFormErrors(form) {
        var node = form.querySelector('.js-form-error');
        if (node) {
            node.textContent = '';
        }

        var fields = form.querySelectorAll('input, textarea, select');
        fields.forEach(function (field) {
            field.classList.remove('invalid');
            // Remove the field-level error message (if any).
            var errorMsg = field.nextElementSibling;
            if (errorMsg && errorMsg.classList.contains('field-error-message')) {
                errorMsg.remove();
            }
        });
    }

    function markFieldInvalid(field) {
        field.classList.add('invalid');
        
        // If an error message already exists, do not duplicate it.
        if (field.nextElementSibling && field.nextElementSibling.classList && field.nextElementSibling.classList.contains('field-error-message')) {
            return; // Le message existe déjà
        }
        
        // Add an error message right under the field.
        var errorMsg = document.createElement('span');
        errorMsg.className = 'field-error-message';
        errorMsg.textContent = 'Ce champ est invalide';
        errorMsg.style.display = 'block';
        errorMsg.style.color = '#c62828';
        errorMsg.style.fontSize = '12px';
        errorMsg.style.fontWeight = '600';
        errorMsg.style.marginTop = '5px';
        errorMsg.style.marginBottom = '5px';
        
        // Insert the message after the field.
        if (field.nextSibling) {
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
        } else {
            field.parentNode.appendChild(errorMsg);
        }
    }

    function isValidDate(value) {
        // First check YYYY-MM-DD shape.
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return false;
        }

        var parts = value.split('-');
        var year = Number(parts[0]);
        var month = Number(parts[1]);
        var day = Number(parts[2]);
        var d = new Date(year, month - 1, day);

        return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
    }

    function isFileExtensionValid(fileName) {
        // Short alphanumeric extension at the end of the filename.
        var match = fileName.toLowerCase().match(/\.([a-z0-9]{2,5})$/);
        if (!match) {
            return false;
        }

        return ['pdf', 'doc', 'docx'].indexOf(match[1]) !== -1;
    }

    function validateField(field) {
        if (field.disabled || !field.name) {
            return true;
        }

        if (field.type === 'hidden') {
            return true;
        }

        var value = (field.value || '').trim();

        if (field.required && value === '') {
            return false;
        }

        if (value === '') {
            return true;
        }

        if (field.type === 'file' && field.files && field.files.length > 0) {
            // Validate each selected file (extension-based).
            for (var i = 0; i < field.files.length; i += 1) {
                if (!isFileExtensionValid(field.files[i].name)) {
                    return false;
                }
            }
            return true;
        }

        if (field.name === 'Password' || field.name === 'password') {
            // Minimal password rule on the client.
            return value.length >= 8;
        }

        if (field.name === 'date_naissance' || field.name === 'date_offre') {
            // For dates we validate semantics (calendar date), not just regex.
            return isValidDate(value);
        }

        var pattern = patterns[field.name];
        if (!pattern) {
            return true;
        }

        return pattern.test(value);
    }

    function validateForm(form) {
        resetFormErrors(form);

        var fields = form.querySelectorAll('input, textarea, select');
        for (var i = 0; i < fields.length; i += 1) {
            if (!validateField(fields[i])) {
                markFieldInvalid(fields[i]);
                return false;
            }
        }

        return true;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var forms = document.querySelectorAll('form');

        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                // Cancel submit when at least one field is invalid.
                if (!validateForm(form)) {
                    event.preventDefault();
                    var errorNode = getOrCreateErrorNode(form);
                    errorNode.textContent = 'Veuillez corriger les erreurs dans le formulaire.';
                }
            });
        });
    });
})();
