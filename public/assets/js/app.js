document.addEventListener('DOMContentLoaded', () => {
    const confirmAction = (message) => window.confirm(message || '¿Confirmas esta acción?');

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm;
            if (!confirmAction(message)) {
                event.preventDefault();
            }
        });
    });

    document.body.addEventListener('click', (event) => {
        const target = event.target.closest('[data-confirm-click]');
        if (!target) {
            return;
        }
        const message = target.dataset.confirmClick || target.dataset.confirm || '';
        if (!confirmAction(message)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, { capture: true });
});
