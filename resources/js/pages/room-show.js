import { initNoteWall } from '../rooms/note-wall';
import { initQuestionRoom } from '../rooms/question-room';
import { initCanvasRoom } from '../rooms/canvas-room';

document.addEventListener('DOMContentLoaded', () => {
    initNoteWall();
    initQuestionRoom();
    initCanvasRoom();
});
