const initPrintPage = () => {
    const page = document.querySelector('[data-print-page]');

    if (!page) {
        return;
    }

    page.querySelectorAll('[data-print-button]').forEach((button) => {
        button.addEventListener('click', () => {
            window.print();
        });
    });
};

document.addEventListener('DOMContentLoaded', initPrintPage);
