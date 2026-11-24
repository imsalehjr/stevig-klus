(function () {
    'use strict';

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('.bar span[data-w]').forEach(function (bar) {
        bar.style.setProperty('--w', Math.max(0, Math.min(100, parseFloat(bar.getAttribute('data-w')) || 0)) + '%');
    });

    document.querySelectorAll('select[data-autosubmit]').forEach(function (select) {
        select.addEventListener('change', function () {
            select.form.submit();
        });
    });
})();
