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

const initCanvasTeacher = () => {
    const gallery = document.querySelector('[data-canvas-gallery]');
    if (!gallery) return;

    const stateUrl = gallery.dataset.stateUrl ?? '';
    let currentSignature = gallery.dataset.boardSignature ?? '';

    const deleteCard = async (card) => {
        const deleteUrl = card.dataset.drawingDeleteUrl;
        if (!deleteUrl) return;
        if (!window.confirm('Esto eliminara el dibujo de este estudiante. Continuar?')) {
            return;
        }
        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': readCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
            });
            if (!response.ok) {
                window.alert('No se pudo eliminar el dibujo.');
                return;
            }
            card.remove();
        } catch (error) {
            console.error('No se pudo eliminar el dibujo.', error);
        }
    };

    const downloadCard = async (card) => {
        const fetchUrl = card.dataset.drawingFetchUrl;
        if (!fetchUrl) return;
        try {
            const response = await fetch(fetchUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (payload.preview_png) {
                downloadDataUrl(payload.preview_png, fileNameFor(payload.author_name));
            }
        } catch (error) {
            console.error('No se pudo descargar el dibujo.', error);
        }
    };

    gallery.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;
        const card = target.closest('[data-canvas-card]');
        if (!card) return;
        if (target.closest('[data-canvas-delete]')) {
            deleteCard(card);
        } else if (target.closest('[data-canvas-download]')) {
            downloadCard(card);
        }
    });

    const pollState = async () => {
        if (!stateUrl || document.hidden) {
            return;
        }
        try {
            const response = await fetch(stateUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) return;
            const payload = await response.json();
            if (!payload.signature || payload.signature === currentSignature) {
                return;
            }
            currentSignature = payload.signature;
            window.location.reload();
        } catch (error) {
            console.error('No se pudo actualizar la galeria.', error);
        }
    };

    window.setInterval(pollState, 15000);
};

document.addEventListener('DOMContentLoaded', initCanvasTeacher);
