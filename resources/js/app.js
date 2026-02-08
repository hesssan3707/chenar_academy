import './bootstrap';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css';
import '@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js';

document.addEventListener('DOMContentLoaded', () => {
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
});

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

const normalizePhone = (value) => (value || '').toString().replace(/\D+/g, '');

const getCooldownKey = (purpose, phone) => `otpCooldown:${purpose}:${normalizePhone(phone)}`;

const setCooldown = (purpose, phone, milliseconds) => {
    const key = getCooldownKey(purpose, phone);
    if (key.endsWith(':')) {
        return;
    }

    localStorage.setItem(key, (Date.now() + milliseconds).toString());
};

const getCooldownUntil = (purpose, phone) => {
    const key = getCooldownKey(purpose, phone);
    if (key.endsWith(':')) {
        return null;
    }

    const raw = localStorage.getItem(key);
    if (!raw) {
        return null;
    }

    const value = Number.parseInt(raw, 10);
    if (!Number.isFinite(value)) {
        return null;
    }

    return value;
};

const formatCountdown = (seconds) => {
    const mm = Math.floor(seconds / 60)
        .toString()
        .padStart(2, '0');
    const ss = Math.floor(seconds % 60)
        .toString()
        .padStart(2, '0');
    return `${mm}:${ss}`;
};

const attachOtpCooldown = (button) => {
    if (!button || button.__otpCooldownAttached) {
        return;
    }

    button.__otpCooldownAttached = true;

    const purpose = button.getAttribute('data-otp-purpose') || '';
    const form = button.closest('form');
    if (!purpose || !form) {
        return;
    }

    const phoneInput = form.querySelector('[name="phone"]');
    const baseText = button.textContent || '';

    const tick = () => {
        const phone = phoneInput ? phoneInput.value : '';
        const until = getCooldownUntil(purpose, phone);
        if (!until) {
            button.disabled = false;
            button.textContent = baseText;
            return;
        }

        const secondsLeft = Math.ceil((until - Date.now()) / 1000);
        if (secondsLeft <= 0) {
            button.disabled = false;
            button.textContent = baseText;
            return;
        }

        button.disabled = true;
        button.textContent = `${baseText} (${formatCountdown(secondsLeft)})`;
    };

    tick();
    setInterval(tick, 1000);

    if (phoneInput) {
        phoneInput.addEventListener('input', tick);
    }
};

const setOtpError = (form, message) => {
    const node = form ? form.querySelector('[data-otp-error]') : null;
    if (!node) {
        return;
    }

    node.textContent = message || '';
    node.hidden = !message;
};

const attachOtpAjaxSend = (button) => {
    if (!button || button.__otpAjaxAttached) {
        return;
    }

    button.__otpAjaxAttached = true;

    const purpose = button.getAttribute('data-otp-purpose') || '';
    const form = button.closest('form');
    if (!purpose || !form) {
        return;
    }

    button.addEventListener('click', async () => {
        const phoneInput = form.querySelector('[name="phone"]');
        const phone = phoneInput ? phoneInput.value : '';

        setOtpError(form, '');

        if (!phone) {
            showToast({ type: 'danger', message: 'شماره موبایل را وارد کنید.' });
            return;
        }

        try {
            const appConfig = parseAppConfig();
            const baseUrl = (appConfig?.base_url || window.location.origin).toString().replace(/\/+$/, '');
            const otpSendUrlRaw = (appConfig?.routes?.otp_send || '').toString().trim();

            let finalOtpSendUrl = '';
            if (otpSendUrlRaw) {
                try {
                    finalOtpSendUrl = new URL(otpSendUrlRaw, `${baseUrl}/`).toString();
                } catch {
                    finalOtpSendUrl = `${baseUrl}/${otpSendUrlRaw.replace(/^\/+/, '')}`;
                }
            } else {
                finalOtpSendUrl = `${baseUrl}/otp/send`;
            }

            const response = await window.axios.post(finalOtpSendUrl, { phone, purpose });
            if (response?.data?.toast) {
                showToast(response.data.toast);
            }
            setCooldown(purpose, phone, (response?.data?.cooldown_seconds || 120) * 1000);
        } catch (error) {
            const status = error?.response?.status;
            const errors = error?.response?.data?.errors;
            if (status === 422 && errors) {
                const message =
                    (errors.otp_code && errors.otp_code[0]) ||
                    (errors.phone && errors.phone[0]) ||
                    'خطا در ارسال کد.';
                setOtpError(form, message);
                return;
            }

            if (status === 404) {
                showToast({ type: 'danger', message: 'مسیر ارسال کد پیدا نشد.' });
                return;
            }

            showToast({ type: 'danger', message: 'خطا در ارسال کد.' });
        }
    });
};

const setLoginMode = (form, mode) => {
    const actionInput = form.querySelector('[data-login-action]');
    const passwordSection = form.querySelector('[data-login-section="password"]');
    const otpSection = form.querySelector('[data-login-section="otp"]');
    const passwordInput = form.querySelector('[name="password"]');
    const otpInput = form.querySelector('[name="otp_code"]');

    const passwordModeButton = form.querySelector('[data-login-mode="password"]');
    const otpModeButton = form.querySelector('[data-login-mode="otp"]');

    const isOtp = mode === 'otp';

    if (actionInput) {
        actionInput.value = isOtp ? 'login_otp' : 'login_password';
    }

    if (passwordSection) {
        passwordSection.hidden = isOtp;
    }

    if (otpSection) {
        otpSection.hidden = !isOtp;
    }

    if (passwordInput) {
        passwordInput.required = !isOtp;
        passwordInput.disabled = isOtp;
    }

    if (otpInput) {
        otpInput.required = isOtp;
        otpInput.disabled = !isOtp;
    }

    if (passwordModeButton) {
        passwordModeButton.classList.toggle('btn--primary', !isOtp);
        passwordModeButton.classList.toggle('btn--ghost', isOtp);
    }

    if (otpModeButton) {
        otpModeButton.classList.toggle('btn--primary', isOtp);
        otpModeButton.classList.toggle('btn--ghost', !isOtp);
    }
};

const attachLoginModeToggles = () => {
    const actionInput = document.querySelector('[data-login-action]');
    if (!actionInput) {
        return;
    }

    const form = actionInput.closest('form');
    if (!form) {
        return;
    }

    const passwordModeButton = form.querySelector('[data-login-mode="password"]');
    const otpModeButton = form.querySelector('[data-login-mode="otp"]');

    const initialAction = actionInput.value || 'login_password';
    setLoginMode(form, initialAction === 'login_otp' ? 'otp' : 'password');

    if (passwordModeButton) {
        passwordModeButton.addEventListener('click', () => setLoginMode(form, 'password'));
    }

    if (otpModeButton) {
        otpModeButton.addEventListener('click', () => setLoginMode(form, 'otp'));
    }
};

const attachPasswordToggles = () => {
    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        const button = target ? target.closest('[data-password-toggle]') : null;
        if (!button) {
            return;
        }

        event.preventDefault();

        const group = button.closest('.input-group');
        const input = group ? group.querySelector('input') : null;
        if (!input) {
            return;
        }

        const showIcon = button.querySelector('[data-password-icon="show"]');
        const hideIcon = button.querySelector('[data-password-icon="hide"]');

        const isVisible = input.type === 'password';
        input.type = isVisible ? 'text' : 'password';

        if (showIcon) {
            showIcon.hidden = isVisible;
        }
        if (hideIcon) {
            hideIcon.hidden = !isVisible;
        }

        button.setAttribute('aria-label', isVisible ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
    });
};

const formatBytes = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB'];
    let value = bytes;
    let unitIndex = 0;

    while (value >= 1024 && unitIndex < units.length - 1) {
        value /= 1024;
        unitIndex += 1;
    }

    const digits = unitIndex === 0 ? 0 : value < 10 ? 1 : 0;
    return `${value.toFixed(digits)} ${units[unitIndex]}`;
};

const setUploadProgress = (input, percent, state = 'uploading') => {
    const ui = input ? input.__uploadUi : null;
    if (!ui) {
        return;
    }

    const clamped = Math.max(0, Math.min(100, Math.round(percent)));
    ui.progress.hidden = state === 'idle';
    ui.progressText.textContent = `${clamped}%`;
    ui.progressFill.style.width = `${clamped}%`;
    ui.progress.dataset.state = state;
};

const clearUploadUi = (input) => {
    const ui = input ? input.__uploadUi : null;
    if (!ui) {
        return;
    }

    ui.preview.hidden = true;
    ui.previewImage.hidden = true;
    ui.previewVideo.hidden = true;
    ui.previewVideo.pause();
    ui.previewVideo.removeAttribute('src');
    ui.previewVideo.load();
    ui.previewFile.textContent = '';
    ui.previewFile.hidden = true;

    if (ui.objectUrl) {
        URL.revokeObjectURL(ui.objectUrl);
        ui.objectUrl = '';
    }

    setUploadProgress(input, 0, 'idle');
};

const updateUploadPreview = (input) => {
    const ui = input ? input.__uploadUi : null;
    if (!ui) {
        return;
    }

    const file = input.files && input.files[0] ? input.files[0] : null;
    if (!file) {
        clearUploadUi(input);
        return;
    }

    if (ui.objectUrl) {
        URL.revokeObjectURL(ui.objectUrl);
        ui.objectUrl = '';
    }

    ui.preview.hidden = false;
    ui.previewImage.hidden = true;
    ui.previewVideo.hidden = true;
    ui.previewFile.hidden = true;

    const mime = (file.type || '').toLowerCase();
    const objectUrl = URL.createObjectURL(file);
    ui.objectUrl = objectUrl;

    if (mime.startsWith('image/')) {
        ui.previewImage.src = objectUrl;
        ui.previewImage.hidden = false;
        return;
    }

    if (mime.startsWith('video/')) {
        ui.previewVideo.src = objectUrl;
        ui.previewVideo.hidden = false;
        return;
    }

    ui.previewFile.textContent = `${file.name} (${formatBytes(file.size)})`;
    ui.previewFile.hidden = false;
};

const enhanceFileInput = (input) => {
    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || input.__uploadEnhanced) {
        return;
    }

    input.__uploadEnhanced = true;

    const wrapper = document.createElement('div');
    wrapper.className = 'upload';

    const drop = document.createElement('button');
    drop.type = 'button';
    drop.className = 'upload__drop';
    drop.innerHTML =
        '<div class="upload__title">فایل را اینجا رها کنید</div><div class="upload__meta">یا برای انتخاب کلیک کنید</div>';

    const preview = document.createElement('div');
    preview.className = 'upload__preview';
    preview.hidden = true;

    const previewMedia = document.createElement('div');
    previewMedia.className = 'upload__preview-media';

    const previewImage = document.createElement('img');
    previewImage.className = 'upload__preview-image';
    previewImage.alt = '';
    previewImage.hidden = true;

    const previewVideo = document.createElement('video');
    previewVideo.className = 'upload__preview-video';
    previewVideo.controls = true;
    previewVideo.preload = 'metadata';
    previewVideo.hidden = true;

    const previewFile = document.createElement('div');
    previewFile.className = 'upload__preview-file';
    previewFile.hidden = true;

    previewMedia.appendChild(previewImage);
    previewMedia.appendChild(previewVideo);
    previewMedia.appendChild(previewFile);

    const previewActions = document.createElement('div');
    previewActions.className = 'upload__preview-actions';

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'btn btn--sm btn--ghost upload__remove';
    removeButton.textContent = 'حذف فایل';

    previewActions.appendChild(removeButton);

    preview.appendChild(previewMedia);
    preview.appendChild(previewActions);

    const progress = document.createElement('div');
    progress.className = 'upload__progress';
    progress.hidden = true;
    progress.dataset.state = 'idle';

    const progressBar = document.createElement('div');
    progressBar.className = 'upload__progress-bar';

    const progressFill = document.createElement('div');
    progressFill.className = 'upload__progress-fill';
    progressBar.appendChild(progressFill);

    const progressText = document.createElement('div');
    progressText.className = 'upload__progress-text';
    progressText.textContent = '0%';

    progress.appendChild(progressBar);
    progress.appendChild(progressText);

    const parent = input.parentNode;
    if (!parent) {
        return;
    }

    parent.insertBefore(wrapper, input);
    wrapper.appendChild(drop);
    wrapper.appendChild(preview);
    wrapper.appendChild(progress);
    wrapper.appendChild(input);

    input.classList.add('upload__input');

    input.__uploadUi = {
        wrapper,
        drop,
        preview,
        previewImage,
        previewVideo,
        previewFile,
        progress,
        progressFill,
        progressText,
        objectUrl: '',
    };

    drop.addEventListener('click', () => input.click());

    const setDragging = (value) => wrapper.classList.toggle('is-dragover', value);

    drop.addEventListener('dragenter', (event) => {
        event.preventDefault();
        setDragging(true);
    });

    drop.addEventListener('dragover', (event) => {
        event.preventDefault();
        setDragging(true);
    });

    drop.addEventListener('dragleave', () => setDragging(false));
    drop.addEventListener('drop', (event) => {
        event.preventDefault();
        setDragging(false);

        const files = event.dataTransfer ? event.dataTransfer.files : null;
        if (!files || files.length === 0) {
            return;
        }

        const dt = new DataTransfer();
        Array.from(files).forEach((file) => dt.items.add(file));
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    input.addEventListener('change', () => updateUploadPreview(input));

    removeButton.addEventListener('click', () => {
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
};

const enhanceUploadForm = (form) => {
    if (!(form instanceof HTMLFormElement) || form.__uploadEnhanced) {
        return;
    }

    const inputs = Array.from(form.querySelectorAll('input[type="file"]'));
    if (inputs.length === 0) {
        return;
    }

    form.__uploadEnhanced = true;

    form.addEventListener('submit', (event) => {
        const fileInputs = inputs.filter((input) => input.files && input.files.length > 0);
        if (fileInputs.length === 0 || form.dataset.uploading === '1') {
            return;
        }

        event.preventDefault();

        form.dataset.uploading = '1';

        const controls = Array.from(form.querySelectorAll('button, input, select, textarea'));
        controls.forEach((node) => {
            if (node instanceof HTMLButtonElement || node instanceof HTMLInputElement || node instanceof HTMLSelectElement || node instanceof HTMLTextAreaElement) {
                node.disabled = true;
            }
        });

        fileInputs.forEach((input) => setUploadProgress(input, 0, 'uploading'));

        const action = (form.getAttribute('action') || window.location.href).toString();
        const method = (form.getAttribute('method') || 'post').toUpperCase();
        const formData = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open(method, action, true);

        xhr.upload.addEventListener('progress', (e) => {
            if (!e.lengthComputable) {
                return;
            }
            const percent = (e.loaded / e.total) * 100;
            fileInputs.forEach((input) => setUploadProgress(input, percent, 'uploading'));
        });

        xhr.addEventListener('load', () => {
            fileInputs.forEach((input) => setUploadProgress(input, 100, 'done'));
            window.location.assign(xhr.responseURL || action);
        });

        const handleError = () => {
            fileInputs.forEach((input) => setUploadProgress(input, 0, 'error'));
            form.dataset.uploading = '0';
            controls.forEach((node) => {
                if (node instanceof HTMLButtonElement || node instanceof HTMLInputElement || node instanceof HTMLSelectElement || node instanceof HTMLTextAreaElement) {
                    node.disabled = false;
                }
            });
        };

        xhr.addEventListener('error', handleError);
        xhr.addEventListener('abort', handleError);

        xhr.send(formData);
    });
};

const initEnhancedFileInputs = () => {
    document.querySelectorAll('input[type="file"]').forEach((input) => enhanceFileInput(input));
    document.querySelectorAll('form').forEach((form) => enhanceUploadForm(form));
};

document.addEventListener('DOMContentLoaded', () => {
    const flashes = parseFlashes();

    if (flashes?.toast) {
        if (typeof flashes.toast === 'string') {
            showToast({ type: 'success', message: flashes.toast });
        } else {
            showToast(flashes.toast);
        }
    }

    document.querySelectorAll('[data-otp-send]').forEach((button) => {
        attachOtpCooldown(button);
        attachOtpAjaxSend(button);
    });

    attachLoginModeToggles();
    attachPasswordToggles();
    initEnhancedFileInputs();
});
