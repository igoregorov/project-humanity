/**
 * public/scripts/avatar.js
 *
 * Автозагрузка аватара: клик по превью → диалог выбора файла → автоотправка формы.
 * Работает через addEventListener, чтобы соответствовать CSP (script-src 'self').
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('.avatar-upload-form');
        if (!form) return;

        var fileInput = form.querySelector('input[type="file"]');
        var avatarPreview = document.querySelector('.avatar-preview');

        if (!fileInput || !avatarPreview) return;

        // Делаем аватар доступным с клавиатуры
        if (!avatarPreview.hasAttribute('tabindex')) {
            avatarPreview.setAttribute('tabindex', '0');
        }
        if (!avatarPreview.hasAttribute('role')) {
            avatarPreview.setAttribute('role', 'button');
        }

        // Клик по аватару → открытие диалога выбора файла
        avatarPreview.addEventListener('click', function (e) {
            e.preventDefault();
            fileInput.click();
        });

        // Enter / Space на аватаре → то же самое
        avatarPreview.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fileInput.click();
            }
        });

        // Выбрали файл → автоотправка формы
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files.length > 0) {
                form.submit();
            }
        });
    });
})();