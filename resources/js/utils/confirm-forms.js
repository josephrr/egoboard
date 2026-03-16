export const bindConfirmForms = (root = document) => {
    root.querySelectorAll('form[data-confirm-message]').forEach((form) => {
        if (form.dataset.confirmBound === 'true') {
            return;
        }

        form.dataset.confirmBound = 'true';

        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm-message') ?? 'Confirmar accion';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
};
