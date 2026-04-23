import * as fabric from 'fabric';

const STAGE_WIDTH = 1600;
const STAGE_HEIGHT = 1000;
const HISTORY_LIMIT = 50;

export const createCanvasEditor = ({ stageEl, wrapperEl, backgroundUrl = '', initialData = null, onDirty = null } = {}) => {
    if (!(stageEl instanceof HTMLCanvasElement) || !(wrapperEl instanceof HTMLElement)) {
        throw new Error('createCanvasEditor requiere stageEl y wrapperEl.');
    }

    const canvas = new fabric.Canvas(stageEl, {
        width: STAGE_WIDTH,
        height: STAGE_HEIGHT,
        backgroundColor: '#ffffff',
        preserveObjectStacking: true,
        enableRetinaScaling: true,
        stopContextMenu: true,
        fireRightClick: false,
    });

    let currentTool = 'pen';
    let currentColor = '#0f172a';
    let currentWidth = 4;
    let currentFontSize = 32;
    let isRestoring = false;
    let isSuspendHistory = false;
    let activeShape = null;
    let shapeStart = null;
    let dirty = false;

    const undoStack = [];
    const redoStack = [];

    const setDirty = (value) => {
        dirty = value;
        if (typeof onDirty === 'function') {
            onDirty(dirty);
        }
    };

    const snapshot = () => JSON.stringify(canvas.toJSON(['selectable', 'evented', 'erasable']));

    const pushHistory = () => {
        if (isRestoring || isSuspendHistory) {
            return;
        }
        undoStack.push(snapshot());
        if (undoStack.length > HISTORY_LIMIT) {
            undoStack.shift();
        }
        redoStack.length = 0;
        setDirty(true);
    };

    const applyState = async (json) => {
        isRestoring = true;
        try {
            await canvas.loadFromJSON(json);
            await applyBackgroundImage(backgroundUrl);
            canvas.renderAll();
        } finally {
            isRestoring = false;
        }
    };

    const applyBackgroundImage = (url) => new Promise((resolve) => {
        if (!url) {
            canvas.backgroundImage = null;
            canvas.backgroundColor = '#ffffff';
            canvas.renderAll();
            resolve();
            return;
        }
        fabric.FabricImage.fromURL(url, { crossOrigin: 'anonymous' })
            .then((img) => {
                if (!img) {
                    resolve();
                    return;
                }
                img.set({
                    selectable: false,
                    evented: false,
                    scaleX: STAGE_WIDTH / (img.width || STAGE_WIDTH),
                    scaleY: STAGE_HEIGHT / (img.height || STAGE_HEIGHT),
                    originX: 'left',
                    originY: 'top',
                });
                canvas.backgroundImage = img;
                canvas.renderAll();
                resolve();
            })
            .catch(() => resolve());
    });

    const resize = () => {
        const wrapperWidth = wrapperEl.clientWidth || STAGE_WIDTH;
        const scale = Math.min(1, wrapperWidth / STAGE_WIDTH);
        canvas.setDimensions(
            { width: STAGE_WIDTH * scale, height: STAGE_HEIGHT * scale },
            { cssOnly: false, backstoreOnly: false }
        );
        canvas.setZoom(scale);
        canvas.requestRenderAll();
    };

    const configureFreeDrawing = () => {
        if (!canvas.freeDrawingBrush || !(canvas.freeDrawingBrush instanceof fabric.PencilBrush)) {
            canvas.freeDrawingBrush = new fabric.PencilBrush(canvas);
        }
        canvas.freeDrawingBrush.color = currentColor;
        canvas.freeDrawingBrush.width = currentWidth;
    };

    const setTool = (tool) => {
        currentTool = tool;
        canvas.isDrawingMode = false;
        canvas.selection = tool === 'select';
        canvas.defaultCursor = 'default';
        canvas.hoverCursor = 'move';
        canvas.skipTargetFind = false;

        if (tool === 'pen') {
            canvas.isDrawingMode = true;
            configureFreeDrawing();
        } else if (tool === 'eraser') {
            canvas.defaultCursor = 'not-allowed';
            canvas.hoverCursor = 'not-allowed';
        } else if (tool === 'select') {
            canvas.selection = true;
        } else {
            canvas.skipTargetFind = true;
            canvas.defaultCursor = 'crosshair';
            canvas.hoverCursor = 'crosshair';
        }
    };

    const setColor = (color) => {
        currentColor = color;
        if (canvas.freeDrawingBrush) {
            canvas.freeDrawingBrush.color = color;
        }
        const active = canvas.getActiveObject();
        if (active) {
            if ('text' in active || active.type === 'i-text' || active.type === 'text') {
                active.set({ fill: color });
            } else if (active.stroke) {
                active.set({ stroke: color });
            } else {
                active.set({ fill: color });
            }
            canvas.requestRenderAll();
            pushHistory();
        }
    };

    const setWidth = (width) => {
        currentWidth = Math.max(1, Math.min(80, width));
        if (canvas.freeDrawingBrush) {
            canvas.freeDrawingBrush.width = currentWidth;
        }
        const active = canvas.getActiveObject();
        if (active && active.strokeWidth != null && !(active.type === 'i-text' || active.type === 'text')) {
            active.set({ strokeWidth: currentWidth });
            canvas.requestRenderAll();
            pushHistory();
        }
    };

    const setFontSize = (size) => {
        currentFontSize = Math.max(8, Math.min(400, Number.parseInt(size, 10) || 32));
        const active = canvas.getActiveObject();
        if (active && (active.type === 'i-text' || active.type === 'text')) {
            active.set({ fontSize: currentFontSize });
            canvas.requestRenderAll();
            pushHistory();
        }
    };

    const createShapeAt = (tool, pointer) => {
        const common = {
            stroke: currentColor,
            strokeWidth: currentWidth,
            fill: 'transparent',
            left: pointer.x,
            top: pointer.y,
            originX: 'left',
            originY: 'top',
        };
        if (tool === 'rect') {
            return new fabric.Rect({ ...common, width: 1, height: 1 });
        }
        if (tool === 'circle') {
            return new fabric.Ellipse({ ...common, rx: 1, ry: 1 });
        }
        if (tool === 'triangle') {
            return new fabric.Triangle({ ...common, width: 1, height: 1 });
        }
        if (tool === 'line') {
            return new fabric.Line([pointer.x, pointer.y, pointer.x, pointer.y], {
                stroke: currentColor,
                strokeWidth: currentWidth,
                originX: 'left',
                originY: 'top',
            });
        }
        if (tool === 'arrow') {
            const line = new fabric.Line([pointer.x, pointer.y, pointer.x, pointer.y], {
                stroke: currentColor,
                strokeWidth: currentWidth,
                originX: 'center',
                originY: 'center',
            });
            const head = new fabric.Triangle({
                width: Math.max(12, currentWidth * 3),
                height: Math.max(12, currentWidth * 3),
                fill: currentColor,
                left: pointer.x,
                top: pointer.y,
                originX: 'center',
                originY: 'center',
                angle: 0,
            });
            line.data = { arrowPart: 'body' };
            head.data = { arrowPart: 'head' };
            return { arrow: true, line, head };
        }
        return null;
    };

    const updateShape = (shape, tool, start, pointer) => {
        if (!shape) return;
        if (tool === 'rect' || tool === 'triangle') {
            const width = pointer.x - start.x;
            const height = pointer.y - start.y;
            shape.set({
                left: width < 0 ? pointer.x : start.x,
                top: height < 0 ? pointer.y : start.y,
                width: Math.max(1, Math.abs(width)),
                height: Math.max(1, Math.abs(height)),
            });
        } else if (tool === 'circle') {
            const rx = Math.max(1, Math.abs(pointer.x - start.x) / 2);
            const ry = Math.max(1, Math.abs(pointer.y - start.y) / 2);
            shape.set({
                left: Math.min(start.x, pointer.x),
                top: Math.min(start.y, pointer.y),
                rx,
                ry,
            });
        } else if (tool === 'line') {
            shape.set({ x2: pointer.x, y2: pointer.y });
        } else if (tool === 'arrow' && shape.arrow) {
            shape.line.set({ x2: pointer.x, y2: pointer.y });
            const dx = pointer.x - start.x;
            const dy = pointer.y - start.y;
            const angle = (Math.atan2(dy, dx) * 180) / Math.PI + 90;
            shape.head.set({ left: pointer.x, top: pointer.y, angle });
        }
        shape.setCoords?.();
    };

    const commitShape = (shape) => {
        if (!shape) return;
        isSuspendHistory = true;
        if (shape.arrow) {
            const group = new fabric.Group([shape.line, shape.head], {
                subTargetCheck: false,
            });
            canvas.remove(shape.line);
            canvas.remove(shape.head);
            canvas.add(group);
        }
        isSuspendHistory = false;
        canvas.requestRenderAll();
        pushHistory();
    };

    canvas.on('mouse:down', (event) => {
        if (currentTool === 'eraser') {
            const target = event.target;
            if (target) {
                canvas.remove(target);
                canvas.requestRenderAll();
                pushHistory();
            }
            return;
        }
        if (currentTool === 'text') {
            const pointer = canvas.getPointer(event.e);
            const text = new fabric.IText('Escribe aqui', {
                left: pointer.x,
                top: pointer.y,
                fill: currentColor,
                fontFamily: 'Sora, sans-serif',
                fontSize: currentFontSize,
                originX: 'left',
                originY: 'top',
            });
            canvas.add(text);
            canvas.setActiveObject(text);
            text.enterEditing?.();
            text.selectAll?.();
            canvas.requestRenderAll();
            setTool('select');
            return;
        }
        if (['rect', 'circle', 'triangle', 'line', 'arrow'].includes(currentTool)) {
            const pointer = canvas.getPointer(event.e);
            shapeStart = pointer;
            activeShape = createShapeAt(currentTool, pointer);
            if (activeShape) {
                isSuspendHistory = true;
                if (activeShape.arrow) {
                    canvas.add(activeShape.line);
                    canvas.add(activeShape.head);
                } else {
                    canvas.add(activeShape);
                }
                isSuspendHistory = false;
            }
        }
    });

    canvas.on('mouse:move', (event) => {
        if (!activeShape || !shapeStart) return;
        const pointer = canvas.getPointer(event.e);
        isSuspendHistory = true;
        updateShape(activeShape, currentTool, shapeStart, pointer);
        canvas.requestRenderAll();
        isSuspendHistory = false;
    });

    canvas.on('mouse:up', () => {
        if (activeShape) {
            commitShape(activeShape);
            activeShape = null;
            shapeStart = null;
        }
    });

    canvas.on('path:created', () => pushHistory());
    canvas.on('object:added', () => pushHistory());
    canvas.on('object:modified', () => pushHistory());
    canvas.on('object:removed', () => pushHistory());

    const undo = () => {
        if (undoStack.length <= 1) return;
        const last = undoStack.pop();
        redoStack.push(last);
        const prev = undoStack[undoStack.length - 1];
        if (prev) {
            applyState(prev);
        }
    };

    const redo = () => {
        if (redoStack.length === 0) return;
        const next = redoStack.pop();
        undoStack.push(next);
        applyState(next);
    };

    const clear = async () => {
        isSuspendHistory = true;
        canvas.getObjects().slice().forEach((obj) => canvas.remove(obj));
        isSuspendHistory = false;
        await applyBackgroundImage(backgroundUrl);
        pushHistory();
    };

    const loadJson = async (json) => {
        if (!json) {
            await applyBackgroundImage(backgroundUrl);
            undoStack.length = 0;
            redoStack.length = 0;
            pushHistory();
            setDirty(false);
            return;
        }
        await applyState(json);
        undoStack.length = 0;
        redoStack.length = 0;
        undoStack.push(snapshot());
        setDirty(false);
    };

    const toJson = () => snapshot();

    const exportPng = ({ multiplier = 1 } = {}) => {
        const previousZoom = canvas.getZoom();
        canvas.setZoom(1);
        canvas.setDimensions({ width: STAGE_WIDTH, height: STAGE_HEIGHT }, { backstoreOnly: false });
        const dataUrl = canvas.toDataURL({ format: 'png', multiplier, quality: 1 });
        canvas.setZoom(previousZoom);
        canvas.setDimensions(
            { width: STAGE_WIDTH * previousZoom, height: STAGE_HEIGHT * previousZoom },
            { backstoreOnly: false }
        );
        canvas.requestRenderAll();
        return dataUrl;
    };

    const markSaved = () => setDirty(false);

    const handleKeydown = (event) => {
        const target = event.target;
        if (target instanceof HTMLElement) {
            const tag = target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || target.isContentEditable) {
                return;
            }
        }
        const active = canvas.getActiveObject();
        if (active && (active.type === 'i-text' || active.type === 'text') && active.isEditing) {
            return;
        }

        const mod = event.ctrlKey || event.metaKey;
        if (!mod) return;

        const key = event.key.toLowerCase();
        if (key === 'z' && !event.shiftKey) {
            event.preventDefault();
            undo();
        } else if ((key === 'z' && event.shiftKey) || key === 'y') {
            event.preventDefault();
            redo();
        }
    };

    const destroy = () => {
        window.removeEventListener('resize', resize);
        window.removeEventListener('orientationchange', resize);
        window.removeEventListener('keydown', handleKeydown);
        canvas.dispose();
    };

    window.addEventListener('resize', resize);
    window.addEventListener('orientationchange', resize);
    window.addEventListener('keydown', handleKeydown);

    (async () => {
        await applyBackgroundImage(backgroundUrl);
        if (initialData) {
            await loadJson(initialData);
        } else {
            undoStack.push(snapshot());
            setDirty(false);
        }
        resize();
    })();

    setTool('pen');

    return {
        canvas,
        setTool,
        setColor,
        setWidth,
        setFontSize,
        undo,
        redo,
        clear,
        loadJson,
        toJson,
        exportPng,
        resize,
        markSaved,
        destroy,
        isDirty: () => dirty,
        getTool: () => currentTool,
    };
};

export const bindCanvasToolbar = ({ toolbarEl, editor, onSave = null, onDownload = null }) => {
    if (!toolbarEl) return () => {};

    const toolButtons = Array.from(toolbarEl.querySelectorAll('[data-canvas-tool]'));
    const actionButtons = Array.from(toolbarEl.querySelectorAll('[data-canvas-action]'));
    const colorButtons = Array.from(toolbarEl.querySelectorAll('[data-canvas-color]'));
    const widthInput = toolbarEl.querySelector('[data-canvas-width]');
    const fontSizeInput = toolbarEl.querySelector('[data-canvas-font-size]');

    const updateToolActive = (tool) => {
        toolButtons.forEach((btn) => {
            btn.classList.toggle('canvas-tool-btn-active', btn.dataset.canvasTool === tool);
        });
    };

    toolButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            editor.setTool(btn.dataset.canvasTool);
            updateToolActive(btn.dataset.canvasTool);
        });
    });

    actionButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.canvasAction;
            if (action === 'undo') editor.undo();
            else if (action === 'redo') editor.redo();
            else if (action === 'clear') {
                if (window.confirm('Esto eliminara todo lo dibujado. Continuar?')) {
                    editor.clear();
                }
            }
            else if (action === 'save' && typeof onSave === 'function') onSave();
            else if (action === 'download' && typeof onDownload === 'function') onDownload();
        });
    });

    colorButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            editor.setColor(btn.dataset.canvasColor);
        });
    });

    if (widthInput instanceof HTMLInputElement) {
        widthInput.addEventListener('input', () => {
            editor.setWidth(Number.parseInt(widthInput.value, 10) || 4);
        });
    }

    if (fontSizeInput instanceof HTMLSelectElement || fontSizeInput instanceof HTMLInputElement) {
        fontSizeInput.addEventListener('change', () => {
            editor.setFontSize(fontSizeInput.value);
        });
    }

    const syncFontSizeFromSelection = () => {
        if (!fontSizeInput) return;
        const active = editor.canvas.getActiveObject();
        if (active && (active.type === 'i-text' || active.type === 'text') && active.fontSize) {
            fontSizeInput.value = String(active.fontSize);
        }
    };
    editor.canvas.on('selection:created', syncFontSizeFromSelection);
    editor.canvas.on('selection:updated', syncFontSizeFromSelection);

    updateToolActive(editor.getTool());

    return () => {
        // nothing — Fabric is disposed by editor.destroy()
    };
};
