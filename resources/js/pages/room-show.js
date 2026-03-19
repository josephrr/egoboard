import { initNoteWall } from '../rooms/note-wall';
import { initQuestionRoom } from '../rooms/question-room';

document.addEventListener('DOMContentLoaded', () => {
    initNoteWall();
    initQuestionRoom();
});
