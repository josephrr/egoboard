export const bindCopyButtons = (root = document) => {
    root.querySelectorAll('[data-copy-target]').forEach((button) => {
        if (button.dataset.copyBound === 'true') {
            return;
        }

        button.dataset.copyBound = 'true';

        button.addEventListener('click', async () => {
            const selector = button.getAttribute('data-copy-target');
            const target = selector ? root.querySelector(selector) ?? document.querySelector(selector) : null;

            if (!(target instanceof HTMLInputElement) && !(target instanceof HTMLTextAreaElement)) {
                return;
            }

            await navigator.clipboard.writeText(target.value);
        });
    });
};
