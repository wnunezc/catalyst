(function () {
    'use strict';

    var toggles = document.querySelectorAll('[data-account-sidebar-toggle]');
    if (toggles.length === 0) {
        return;
    }

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            document.body.classList.toggle('account-sidebar-open');
        });
    });

    document.addEventListener('click', function (event) {
        if (!document.body.classList.contains('account-sidebar-open')) {
            return;
        }

        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        if (target.closest('.account-sidenav') || target.closest('[data-account-sidebar-toggle]')) {
            return;
        }

        document.body.classList.remove('account-sidebar-open');
    });
})();
