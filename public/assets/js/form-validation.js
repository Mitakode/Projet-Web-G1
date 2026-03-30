(function () {
    'use strict';

    var patterns = {
        // Recherche libre courte (accueil/header).
        query: /^[\p{L}\p{N}\s\-'".,()@]{0,100}$/u,
        // Recherche entreprises.
        search: /^[\p{L}\p{N}\s\-'".,()@]{0,100}$/u,
        // Nom utilisateur/entreprise.
        nom: /^[\p{L}\p{N}\s\-'".&()]{1,150}$/u,
        // Prénom personne.
        prenom: /^[\p{L}\s\-']{1,100}$/u,
        // Intitulé de formation.
        formation: /^[\p{L}\p{N}\s\-'".,()\/]{1,150}$/u,
        // Description texte contrôlée.
        description: /^[\p{L}\p{N}\s\-'".,()!?@:\/]{0,3000}$/u,
        // Email standard.
        email: /^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/,
        // Email de contact entreprise.
        email_contact: /^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/,
        // Numéro de téléphone FR/intl simple.
        telephone: /^\+?[0-9\s().-]{6,20}$/,
        // Date ISO (vérifiée ensuite avec un vrai objet Date).
        date_naissance: /^\d{4}-\d{2}-\d{2}$/,
        // Date ISO pour l'offre.
        date_offre: /^\d{4}-\d{2}-\d{2}$/,
        // Montant avec 0 à 2 décimales.
        remuneration: /^\d{1,7}(?:[.,]\d{1,2})?$/,
        // Durée en mois (entier positif).
        duree_mois: /^\d{1,12}$/,
        // Nombre de places (entier positif).
        nombre_place: /^\d{1,20}$/,
        // Identifiant numérique générique.
        id: /^\d+$/,
        // ID offre.
        id_offre: /^\d+$/,
        // ID entreprise.
        id_entreprise: /^\d+$/,
        // Pilote gestionnaire optionnel.
        est_gere_par: /^\d*$/,
        // Rôle utilisateur (0, 1 ou 2).
        role: /^[012]$/,
        // Note entreprise de 1 à 5.
        note: /^[1-5]$/,
        // Action CRUD autorisée.
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
            field.style.borderColor = '';
        });
    }

    function markFieldInvalid(field) {
        field.style.borderColor = '#c62828';
    }

    function isValidDate(value) {
        // Vérifie d'abord la structure YYYY-MM-DD.
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
        // Extension alphanumérique courte en fin de nom de fichier.
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
            for (var i = 0; i < field.files.length; i += 1) {
                if (!isFileExtensionValid(field.files[i].name)) {
                    return false;
                }
            }
            return true;
        }

        if (field.name === 'Password' || field.name === 'password') {
            return value.length >= 8;
        }

        if (field.name === 'date_naissance' || field.name === 'date_offre') {
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
                if (!validateForm(form)) {
                    event.preventDefault();
                    var errorNode = getOrCreateErrorNode(form);
                    errorNode.textContent = 'Incorrect';
                }
            });
        });
    });
})();
