export const createNoteWallUi = ({ modal, statusBanner, authorInput, anonymousToggle }) => {
    const showStatus = (message, tone = 'success') => {
        if (!statusBanner) {
            return;
        }

        const tones = {
            success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            error: 'border-rose-200 bg-rose-50 text-rose-800',
        };

        statusBanner.className = `rounded-3xl border px-5 py-4 text-sm font-medium ${tones[tone] ?? tones.success}`;
        statusBanner.textContent = message;
        statusBanner.classList.remove('hidden');
    };

    const syncAnonymousState = () => {
        if (!authorInput || !anonymousToggle) {
            return;
        }

        authorInput.disabled = anonymousToggle.checked;

        if (anonymousToggle.checked) {
            authorInput.removeAttribute('required');
        } else {
            authorInput.setAttribute('required', 'required');
        }
    };

    const openModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    const resetNoteForm = (form) => {
        const messageField = form.querySelector('textarea[name="message"]');
        if (messageField instanceof HTMLTextAreaElement) {
            messageField.value = '';
        }

        const categoryField = form.querySelector('select[name="category"]');
        if (categoryField instanceof HTMLSelectElement) {
            categoryField.value = 'idea';
        }

        if (anonymousToggle instanceof HTMLInputElement) {
            anonymousToggle.checked = false;
            syncAnonymousState();
        }
    };

    return {
        showStatus,
        syncAnonymousState,
        openModal,
        closeModal,
        resetNoteForm,
    };
};
