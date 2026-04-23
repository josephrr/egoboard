import { createNoteWallStorage } from './note-wall-storage';
import { bindCanvasToolbar, createCanvasEditor } from './canvas-editor';

const readCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta instanceof HTMLMetaElement ? meta.content : '';
};

const readInitialData = (scope) => {
    const script = scope.querySelector('[data-canvas-initial-data]');
    if (!(script instanceof HTMLScriptElement)) {
        return null;
    }
    const raw = script.textContent?.trim() ?? '';
    if (!raw || raw === 'null') {
        return null;
    }
    return raw;
};

const downloadDataUrl = (dataUrl, filename) => {
    const anchor = document.createElement('a');
    anchor.href = dataUrl;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
};

export const initCanvasRoom = () => {
    const room = document.querySelector('[data-canvas-room]');
    if (!room) return;

    const board = room.querySelector('[data-canvas-board]');
    if (!board) return;

    const stageEl = board.querySelector('[data-canvas-stage]');
    const wrapperEl = board.querySelector('[data-canvas-wrapper]');
    const toolbarEl = board.querySelector('[data-canvas-toolbar]');
    const saveBtn = board.querySelector('[data-canvas-save]');
    const downloadBtn = board.querySelector('[data-canvas-download-png]');
    const dirtyIndicator = board.querySelector('[data-canvas-dirty-indicator]');
    const savedIndicator = board.querySelector('[data-canvas-saved-indicator]');
    const statusBanner = room.querySelector('[data-status-banner]');
    const nameModal = room.querySelector('[data-name-modal]');
    const nameForm = room.querySelector('[data-name-form]');
    const openNameButtons = room.querySelectorAll('[data-open-name-modal]');
    const closeNameButtons = room.querySelectorAll('[data-close-name-modal]');

    if (!(stageEl instanceof HTMLCanvasElement) || !(wrapperEl instanceof HTMLElement)) {
        return;
    }

    const roomSlug = room.dataset.roomSlug ?? '';
    const saveUrl = board.dataset.saveUrl ?? '';
    const mineUrl = board.dataset.mineUrl ?? '';
    const backgroundUrl = board.dataset.backgroundUrl ?? '';
    const isClosed = board.dataset.roomClosed === 'true';
    const storage = createNoteWallStorage(roomSlug);
    storage.ensureParticipantKey();

    const showStatus = (message, tone = 'success') => {
        if (!statusBanner) return;
        const tones = {
            success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
            error: 'border-rose-200 bg-rose-50 text-rose-800',
        };
        statusBanner.className = `rounded-3xl border px-5 py-4 text-sm font-medium ${tones[tone] ?? tones.success}`;
        statusBanner.textContent = message;
        statusBanner.classList.remove('hidden');
    };

    const openNameModal = () => {
        if (!nameModal) return;
        nameModal.classList.remove('hidden');
        nameModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        const input = nameModal.querySelector('[data-author-name]');
        if (input instanceof HTMLInputElement) {
            input.value = storage.getAuthorName();
            window.setTimeout(() => input.focus(), 0);
        }
    };

    const closeNameModal = () => {
        if (!nameModal) return;
        nameModal.classList.add('hidden');
        nameModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    const editor = createCanvasEditor({
        stageEl,
        wrapperEl,
        backgroundUrl,
        initialData: readInitialData(board),
        onDirty: (dirty) => {
            if (dirtyIndicator) {
                dirtyIndicator.classList.toggle('hidden', !dirty);
            }
        },
    });

    bindCanvasToolbar({ toolbarEl, editor });

    const loadMyDrawing = async () => {
        if (!mineUrl) return;
        try {
            const participantKey = storage.getParticipantKey();
            if (!participantKey) return;
            const url = `${mineUrl}?participant_key=${encodeURIComponent(participantKey)}`;
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (payload.canvas_data) {
                await editor.loadJson(payload.canvas_data);
                if (savedIndicator && payload.updated_at) {
                    const fecha = new Date(payload.updated_at);
                    savedIndicator.textContent = `Ultimo guardado: ${fecha.toLocaleString()}`;
                }
            }
        } catch (error) {
            console.error('No se pudo cargar tu dibujo guardado.', error);
        }
    };

    loadMyDrawing();

    const requireName = () => {
        const name = storage.getAuthorName().trim();
        if (name) return name;
        openNameModal();
        showStatus('Escribe tu nombre completo para guardar tu dibujo.', 'error');
        return '';
    };

    const saveDrawing = async () => {
        if (isClosed) {
            showStatus('Esta sala esta cerrada y ya no recibe dibujos.', 'error');
            return;
        }
        const name = requireName();
        if (!name) return;

        if (saveBtn instanceof HTMLButtonElement) {
            saveBtn.disabled = true;
        }

        try {
            const canvasData = editor.toJson();
            const previewPng = editor.exportPng({ multiplier: 0.3 });

            const response = await fetch(saveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': readCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    author_name: name,
                    participant_key: storage.getParticipantKey(),
                    canvas_data: canvasData,
                    preview_png: previewPng,
                }),
            });

            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                showStatus(payload.message ?? 'No se pudo guardar el dibujo.', 'error');
                return;
            }

            editor.markSaved();
            if (savedIndicator) {
                savedIndicator.textContent = 'Guardado hace un momento.';
            }
            showStatus(payload.message ?? 'Dibujo guardado correctamente.');
        } catch (error) {
            console.error('No se pudo guardar el dibujo.', error);
            showStatus('No se pudo guardar el dibujo.', 'error');
        } finally {
            if (saveBtn instanceof HTMLButtonElement) {
                saveBtn.disabled = false;
            }
        }
    };

    saveBtn?.addEventListener('click', saveDrawing);
    downloadBtn?.addEventListener('click', () => {
        const dataUrl = editor.exportPng({ multiplier: 1 });
        const safeSlug = roomSlug || 'dibujo';
        downloadDataUrl(dataUrl, `${safeSlug}.png`);
    });

    openNameButtons.forEach((btn) => btn.addEventListener('click', openNameModal));
    closeNameButtons.forEach((btn) => btn.addEventListener('click', closeNameModal));

    if (nameForm instanceof HTMLFormElement) {
        nameForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = nameForm.querySelector('[data-author-name]');
            if (!(input instanceof HTMLInputElement)) return;
            const value = input.value.trim();
            if (!value) {
                showStatus('Escribe tu nombre completo para continuar.', 'error');
                return;
            }
            storage.saveAuthorName(value);
            closeNameModal();
            showStatus('Nombre guardado. Ya puedes dibujar y guardar tu trabajo.');
        });
    }

    window.addEventListener('beforeunload', (event) => {
        if (editor.isDirty()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    if (!storage.getAuthorName().trim()) {
        openNameModal();
    }
};
