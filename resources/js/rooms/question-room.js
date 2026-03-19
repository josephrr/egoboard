import { createNoteWallStorage } from './note-wall-storage';

export const initQuestionRoom = () => {
    const wall = document.querySelector('[data-question-room]');

    if (!wall) {
        return;
    }

    let authorInput = wall.querySelector('[data-author-name]');
    const statusBanner = wall.querySelector('[data-status-banner]');
    const roomSlug = wall.dataset.roomSlug;
    const stateUrl = wall.dataset.stateUrl;
    const boardUrl = wall.dataset.boardUrl;
    const nameModal = wall.querySelector('[data-name-modal]');
    const nameForm = wall.querySelector('[data-name-form]');
    const openNameButtons = wall.querySelectorAll('[data-open-name-modal]');
    const closeNameButtons = wall.querySelectorAll('[data-close-name-modal]');
    let currentBoardSignature = wall.dataset.boardSignature;
    let isRefreshingBoard = false;

    const storage = createNoteWallStorage(roomSlug);

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

    const openNameModal = () => {
        if (!nameModal) {
            return;
        }

        nameModal.classList.remove('hidden');
        nameModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        window.setTimeout(() => {
            if (authorInput instanceof HTMLInputElement) {
                authorInput.focus();
            }
        }, 0);
    };

    const closeNameModal = () => {
        if (!nameModal) {
            return;
        }

        nameModal.classList.add('hidden');
        nameModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    const refreshParticipantInputs = () => {
        wall.querySelectorAll('[data-participant-key]').forEach((input) => {
            input.value = storage.getParticipantKey();
        });

        wall.querySelectorAll('[data-hidden-author-name]').forEach((input) => {
            input.value = authorInput instanceof HTMLInputElement ? authorInput.value.trim() : storage.getAuthorName().trim();
        });
    };

    const bindAuthorInput = () => {
        authorInput = wall.querySelector('[data-author-name]');

        if (!(authorInput instanceof HTMLInputElement)) {
            return;
        }

        if (!authorInput.value && storage.getAuthorName()) {
            authorInput.value = storage.getAuthorName();
        }

        authorInput.addEventListener('input', () => {
            refreshParticipantInputs();
        });
    };

    const saveAuthorName = () => {
        if (!(authorInput instanceof HTMLInputElement)) {
            return false;
        }

        const name = authorInput.value.trim();

        if (!name) {
            showStatus('Escribe tu nombre completo para continuar.', 'error');
            openNameModal();
            return false;
        }

        storage.saveAuthorName(name);
        authorInput.value = name;
        refreshParticipantInputs();
        closeNameModal();

        return true;
    };

    const refreshBoard = async () => {
        if (!boardUrl || isRefreshingBoard) {
            return false;
        }

        isRefreshingBoard = true;

        try {
            const params = new URLSearchParams(window.location.search);
            params.set('participant_key', storage.getParticipantKey());

            const response = await fetch(`${boardUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return false;
            }

            const payload = await response.json();
            const boardRegion = wall.querySelector('[data-board-region]');

            if (!boardRegion || !payload.html) {
                return false;
            }

            const currentName = authorInput instanceof HTMLInputElement ? authorInput.value : storage.getAuthorName();

            boardRegion.innerHTML = payload.html;
            currentBoardSignature = payload.signature ?? currentBoardSignature;
            wall.dataset.boardSignature = currentBoardSignature;

            bindAuthorInput();

            if (authorInput instanceof HTMLInputElement) {
                authorInput.value = currentName;
            }

            refreshParticipantInputs();
            wall.querySelectorAll('[data-open-name-modal]').forEach((button) => {
                button.addEventListener('click', openNameModal);
            });

            return true;
        } finally {
            isRefreshingBoard = false;
        }
    };

    storage.ensureParticipantKey();
    bindAuthorInput();
    refreshParticipantInputs();

    openNameButtons.forEach((button) => button.addEventListener('click', openNameModal));
    closeNameButtons.forEach((button) => button.addEventListener('click', closeNameModal));

    if (nameForm instanceof HTMLFormElement) {
        nameForm.addEventListener('submit', (event) => {
            event.preventDefault();
            saveAuthorName();
        });
    }

    wall.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !form.matches('[data-question-answer-form]')) {
            return;
        }

        event.preventDefault();

        if (!saveAuthorName()) {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                showStatus(payload.message ?? 'No se pudo guardar la respuesta.', 'error');
                return;
            }

            await refreshBoard();
            showStatus(payload.message ?? 'Respuesta guardada correctamente.');
        } catch (error) {
            console.error('No se pudo guardar la respuesta.', error);
            showStatus('No se pudo guardar la respuesta.', 'error');
        } finally {
            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = false;
            }
        }
    });

    const pollState = async () => {
        if (!stateUrl || document.hidden) {
            return;
        }

        try {
            const response = await fetch(stateUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();

            if (!payload.signature || payload.signature === currentBoardSignature) {
                return;
            }

            await refreshBoard();
        } catch (error) {
            console.error('No se pudo actualizar la sala de preguntas.', error);
        }
    };

    if (!storage.getAuthorName().trim()) {
        openNameModal();
    }

    refreshBoard();
    pollState();
    window.setInterval(pollState, 15000);
};
