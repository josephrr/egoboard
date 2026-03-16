import { createNoteWallStorage } from './note-wall-storage';
import { createNoteWallUi } from './note-wall-ui';

export const initNoteWall = () => {
    const wall = document.querySelector('[data-note-wall]');

    if (!wall) {
        return;
    }

    const roomSlug = wall.dataset.roomSlug;
    const modal = wall.querySelector('[data-note-modal]');
    const openButtons = wall.querySelectorAll('[data-open-note-modal]');
    const closeButtons = wall.querySelectorAll('[data-close-note-modal]');
    const authorInput = wall.querySelector('[data-author-name]');
    const anonymousToggle = wall.querySelector('[data-anonymous-toggle]');
    const statusBanner = wall.querySelector('[data-status-banner]');
    const stateUrl = wall.dataset.stateUrl;
    const boardUrl = wall.dataset.boardUrl;
    let currentBoardSignature = wall.dataset.boardSignature;
    let isRefreshingBoard = false;

    const storage = createNoteWallStorage(roomSlug);
    const ui = createNoteWallUi({
        modal,
        statusBanner,
        authorInput,
        anonymousToggle,
    });

    const refreshParticipantInputs = () => {
        wall.querySelectorAll('[data-participant-key]').forEach((input) => {
            input.value = storage.getParticipantKey();
        });
    };

    const refreshBoard = async () => {
        if (!boardUrl || isRefreshingBoard) {
            return false;
        }

        isRefreshingBoard = true;

        try {
            const params = new URLSearchParams(window.location.search);
            params.set('participant_key', storage.getParticipantKey());

            const boardResponse = await fetch(`${boardUrl}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!boardResponse.ok) {
                return false;
            }

            const boardPayload = await boardResponse.json();
            const boardRegion = wall.querySelector('[data-board-region]');

            if (!boardRegion || !boardPayload.html) {
                return false;
            }

            boardRegion.innerHTML = boardPayload.html;
            currentBoardSignature = boardPayload.signature ?? currentBoardSignature;
            wall.dataset.boardSignature = currentBoardSignature;
            refreshParticipantInputs();

            return true;
        } finally {
            isRefreshingBoard = false;
        }
    };

    if (authorInput) {
        if (!authorInput.value && storage.getAuthorName()) {
            authorInput.value = storage.getAuthorName();
        }

        authorInput.addEventListener('input', () => {
            storage.saveAuthorName(authorInput.value);
        });
    }

    storage.ensureParticipantKey();
    refreshParticipantInputs();

    if (anonymousToggle) {
        anonymousToggle.addEventListener('change', ui.syncAnonymousState);
        ui.syncAnonymousState();
    }

    openButtons.forEach((button) => button.addEventListener('click', ui.openModal));
    closeButtons.forEach((button) => button.addEventListener('click', ui.closeModal));

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            ui.closeModal();
        }
    });

    wall.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const isReaction = form.matches('[data-reaction-form]');
        const isNoteForm = form.matches('[data-note-form]');

        if (!isReaction && !isNoteForm) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton instanceof HTMLButtonElement) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                ui.showStatus(payload.message ?? 'No se pudo completar la accion.', 'error');
                return;
            }

            await refreshBoard();

            if (isReaction) {
                return;
            }

            ui.showStatus(payload.message ?? 'Nota publicada correctamente.');
            ui.closeModal();
            ui.resetNoteForm(form);
        } catch (error) {
            console.error('No se pudo completar la accion.', error);
            ui.showStatus('No se pudo completar la accion.', 'error');
        } finally {
            if (submitButton instanceof HTMLButtonElement) {
                submitButton.disabled = false;
            }
        }
    });

    if (wall.dataset.openOnLoad === 'true') {
        ui.openModal();
    }

    const pollState = async () => {
        if (!stateUrl || document.hidden || modal?.classList.contains('flex')) {
            return;
        }

        try {
            const response = await fetch(stateUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
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
            console.error('No se pudo actualizar el estado del muro.', error);
        }
    };

    refreshBoard();
    pollState();
    window.setInterval(pollState, 15000);
};
