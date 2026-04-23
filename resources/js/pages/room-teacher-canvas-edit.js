import { bindCanvasToolbar, createCanvasEditor } from '../rooms/canvas-editor';

const readCsrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta instanceof HTMLMetaElement ? meta.content : '';
};

const downloadDataUrl = (dataUrl, filename) => {
    const anchor = document.createElement('a');
    anchor.href = dataUrl;
    anchor.download = filename;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
};

const fileNameFor = (author) => {
    const safe = (author || 'dibujo').toLowerCase().replace(/[^a-z0-9]+/gi, '-').replace(/^-+|-+$/g, '');
    return `${safe || 'dibujo'}.png`;
};

const init = async () => {
    const root = document.querySelector('[data-canvas-edit]');
    if (!root) return;

    const stageEl = root.querySelector('[data-canvas-edit-stage]');
    const wrapperEl = root.querySelector('[data-canvas-edit-wrapper]');
    const toolbarEl = root.querySelector('[data-canvas-toolbar]');
    const titleEl = root.querySelector('[data-canvas-edit-title]');
    const dirtyPill = root.querySelector('[data-canvas-edit-dirty]');
    const savedLabel = root.querySelector('[data-canvas-edit-saved]');
    const saveBtn = root.querySelector('[data-canvas-edit-save]');
    const downloadBtn = root.querySelector('[data-canvas-edit-download]');

    if (!(stageEl instanceof HTMLCanvasElement) || !(wrapperEl instanceof HTMLElement)) {
        return;
    }

    const fetchUrl = root.dataset.fetchUrl ?? '';
    const updateUrl = root.dataset.updateUrl ?? '';
    const backgroundUrl = root.dataset.backgroundUrl ?? '';

    let payload = null;
    try {
        const response = await fetch(fetchUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) {
            window.alert('No se pudo cargar el dibujo.');
            return;
        }
        payload = await response.json();
    } catch (error) {
        console.error('No se pudo cargar el dibujo.', error);
        window.alert('No se pudo cargar el dibujo.');
        return;
    }

    if (payload?.author_name && titleEl) {
        titleEl.textContent = payload.author_name;
    }

    const editor = createCanvasEditor({
        stageEl,
        wrapperEl,
        backgroundUrl,
        initialData: payload?.canvas_data ?? null,
        onDirty: (dirty) => {
            if (dirtyPill) {
                dirtyPill.classList.toggle('hidden', !dirty);
            }
        },
    });

    bindCanvasToolbar({ toolbarEl, editor });
    window.setTimeout(() => editor.resize(), 150);

    const save = async () => {
        if (!updateUrl || !(saveBtn instanceof HTMLButtonElement)) return;
        saveBtn.disabled = true;
        try {
            const response = await fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': readCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    canvas_data: editor.toJson(),
                    preview_png: editor.exportPng({ multiplier: 0.3 }),
                }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                window.alert(data.message ?? 'No se pudo guardar el dibujo.');
                return;
            }
            editor.markSaved();
            if (savedLabel && data.savedAt) {
                savedLabel.textContent = `Guardado hace un momento.`;
            }
        } catch (error) {
            console.error('No se pudo guardar el dibujo.', error);
            window.alert('No se pudo guardar el dibujo.');
        } finally {
            saveBtn.disabled = false;
        }
    };

    saveBtn?.addEventListener('click', save);

    downloadBtn?.addEventListener('click', () => {
        const author = payload?.author_name ?? titleEl?.textContent ?? 'dibujo';
        downloadDataUrl(editor.exportPng({ multiplier: 1 }), fileNameFor(author));
    });

    window.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            save();
        }
    });

    window.addEventListener('beforeunload', (event) => {
        if (editor.isDirty()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
};

document.addEventListener('DOMContentLoaded', init);
