import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.querySelector('[data-nav-toggle]');
    const header = document.querySelector('.site-header');

    if (!toggleButton || !header) {
        return;
    }

    toggleButton.addEventListener('click', () => {
        header.classList.toggle('is-open');
    });
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
            const baseUrl = (appConfig?.base_url || '').toString().replace(/\/+$/, '');
            let otpSendUrl = appConfig?.routes?.otp_send || '/otp/send';
            otpSendUrl = otpSendUrl.toString();
            if (otpSendUrl && !otpSendUrl.startsWith('http') && !otpSendUrl.startsWith('/')) {
                otpSendUrl = `/${otpSendUrl}`;
            }
            if (baseUrl && otpSendUrl.startsWith('/')) {
                otpSendUrl = `${baseUrl}${otpSendUrl}`;
            }

            const response = await window.axios.post(otpSendUrl, { phone, purpose });
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

const attachPasswordToggle = (button) => {
    if (!button || button.__passwordToggleAttached) {
        return;
    }

    button.__passwordToggleAttached = true;

    const group = button.closest('.input-group');
    const input = group ? group.querySelector('input') : null;
    if (!input) {
        return;
    }

    const showIcon = button.querySelector('[data-password-icon="show"]');
    const hideIcon = button.querySelector('[data-password-icon="hide"]');

    const setState = (isVisible) => {
        input.type = isVisible ? 'text' : 'password';
        if (showIcon) {
            showIcon.hidden = isVisible;
        }
        if (hideIcon) {
            hideIcon.hidden = !isVisible;
        }
        button.setAttribute('aria-label', isVisible ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
    };

    setState(false);

    button.addEventListener('click', () => {
        setState(input.type === 'password');
    });
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

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        attachPasswordToggle(button);
    });

    attachLoginModeToggles();
});
