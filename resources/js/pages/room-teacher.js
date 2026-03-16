import { bindCopyButtons } from '../utils/copy-to-clipboard';
import { bindConfirmForms } from '../utils/confirm-forms';

const initTeacherPanel = () => {
    const panel = document.querySelector('[data-teacher-panel]');

    if (!panel) {
        return;
    }

    bindCopyButtons(panel);
    bindConfirmForms(panel);
};

document.addEventListener('DOMContentLoaded', initTeacherPanel);
