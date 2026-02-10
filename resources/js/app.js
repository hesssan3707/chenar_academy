import './bootstrap';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';
import './spa.js';

document.addEventListener('DOMContentLoaded', () => {
    initApp();
});

// Expose initApp so SPA can call it
window.initApp = function() {
    const toggleButton = document.querySelector('[data-nav-toggle]');
    const header = document.querySelector('.site-header');

    if (toggleButton && header) {
        toggleButton.addEventListener('click', () => {
            header.classList.toggle('is-open');
        });
    }

    const surveyModal = document.querySelector('[data-survey-modal]');
    if (surveyModal) {
        surveyModal.hidden = false;

        const close = () => {
            surveyModal.hidden = true;
        };

        surveyModal.querySelectorAll('[data-survey-close]').forEach((node) => {
            node.addEventListener('click', close);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !surveyModal.hidden) {
                close();
            }
        });
    }

    if (window.jalaliDatepicker) {
        window.jalaliDatepicker.startWatch({
            time: true,
            hasSecond: false,
            persianDigits: true,
            separatorChars: { date: '/', between: ' ', time: ':' },
        });
    }
};

const parseFlashes = () => {
    const node = document.querySelector('[data-flashes]');
    if (!node) {
        return { toast: null, otp_sent: null };
    }

    try {
        return JSON.parse(node.textContent || '{}');
    } catch {
        return { toast: null, otp_sent: null };
    }
};

const parseAppConfig = () => {
    const node = document.querySelector('[data-app-config]');
    if (!node) {
        return { routes: {} };
    }

    try {
        return JSON.parse(node.textContent || '{}');
    } catch {
        return { routes: {} };
    }
};

const showToast = ({ type = 'success', title = '', message = '' } = {}) => {
    const host = document.querySelector('[data-toast-host]');
    if (!host) {
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;

    if (title) {
        const titleNode = document.createElement('div');
        titleNode.className = 'toast__title';
        titleNode.textContent = title;
        toast.appendChild(titleNode);
    }

    if (message) {
        const messageNode = document.createElement('div');
        messageNode.className = 'toast__message';
        messageNode.textContent = message;
        toast.appendChild(messageNode);
    }

    host.appendChild(toast);

    const remove = () => {
        toast.remove();
    };

    setTimeout(remove, 5000);
    toast.addEventListener('click', remove);
};

// Handle flashes
document.addEventListener('DOMContentLoaded', () => {
    const flashes = parseFlashes();
    if (flashes.toast) {
        showToast(flashes.toast);
    }
});

// Expose toast to window so it can be used elsewhere
window.showToast = showToast;
