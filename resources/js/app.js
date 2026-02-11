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

    initLoginUi();
    initPasswordToggles();
    initOtpSenders();
    initAjaxAuthForms();

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

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta?.content) {
        return meta.content;
    }

    const tokenInput = document.querySelector('input[name="_token"]');
    if (tokenInput?.value) {
        return tokenInput.value;
    }

    return '';
}

function getOtpSendUrl() {
    const meta = document.querySelector('meta[name="otp-send-url"]');
    if (meta?.content) {
        return meta.content;
    }

    const config = parseAppConfig();
    if (config?.routes?.otp_send) {
        return config.routes.otp_send;
    }

    return '';
}

function setLoginMode(form, mode) {
    const actionInput = form.querySelector('[data-login-action]');
    const passwordSection = form.querySelector('[data-login-section="password"]');
    const otpSection = form.querySelector('[data-login-section="otp"]');
    const passwordBtn = form.querySelector('[data-login-mode="password"]');
    const otpBtn = form.querySelector('[data-login-mode="otp"]');
    const toggleBtn = form.querySelector('[data-login-mode-toggle]');

    if (mode === 'otp') {
        if (passwordSection) passwordSection.hidden = true;
        if (otpSection) otpSection.hidden = false;
        if (actionInput) actionInput.value = 'login_otp';

        if (passwordBtn) {
            passwordBtn.classList.remove('btn--primary');
            passwordBtn.classList.add('btn--ghost');
        }

        if (otpBtn) {
            otpBtn.classList.remove('btn--ghost');
            otpBtn.classList.add('btn--primary');
        }

        if (toggleBtn) {
            toggleBtn.textContent = 'رمز';
        }

        return;
    }

    if (passwordSection) passwordSection.hidden = false;
    if (otpSection) otpSection.hidden = true;
    if (actionInput) actionInput.value = 'login_password';

    if (passwordBtn) {
        passwordBtn.classList.remove('btn--ghost');
        passwordBtn.classList.add('btn--primary');
    }

    if (otpBtn) {
        otpBtn.classList.remove('btn--primary');
        otpBtn.classList.add('btn--ghost');
    }

    if (toggleBtn) {
        toggleBtn.textContent = 'کد';
    }
}

function initLoginUi() {
    const forms = document.querySelectorAll('form');
    forms.forEach((form) => {
        const actionInput = form.querySelector('[data-login-action]');
        if (!actionInput) {
            return;
        }

        const initialMode = (actionInput.value || '').includes('otp') ? 'otp' : 'password';
        setLoginMode(form, initialMode);

        form.querySelectorAll('[data-login-mode]').forEach((btn) => {
            if (btn.dataset.bound === '1') {
                return;
            }

            btn.dataset.bound = '1';
            btn.addEventListener('click', () => {
                const mode = btn.getAttribute('data-login-mode');
                setLoginMode(form, mode);
            });
        });

        form.querySelectorAll('[data-login-mode-toggle]').forEach((btn) => {
            if (btn.dataset.bound === '1') {
                return;
            }

            btn.dataset.bound = '1';
            btn.addEventListener('click', () => {
                const currentMode = (actionInput.value || '').includes('otp') ? 'otp' : 'password';
                const nextMode = currentMode === 'otp' ? 'password' : 'otp';
                setLoginMode(form, nextMode);
            });
        });
    });
}

function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        if (btn.dataset.bound === '1') {
            return;
        }

        btn.dataset.bound = '1';
        btn.addEventListener('click', () => {
            const group = btn.closest('.input-group') || btn.parentElement;
            const input = group?.querySelector('input');
            if (!input) {
                return;
            }

            const showIcon = btn.querySelector('[data-password-icon="show"]');
            const hideIcon = btn.querySelector('[data-password-icon="hide"]');
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            if (showIcon) showIcon.hidden = !isPassword;
            if (hideIcon) hideIcon.hidden = isPassword;
        });
    });
}

function initOtpSenders() {
    document.querySelectorAll('[data-otp-send]').forEach((btn) => {
        if (btn.dataset.bound === '1') {
            return;
        }

        btn.dataset.bound = '1';
        btn.addEventListener('click', async () => {
            const form = btn.closest('form') || document;
            const phoneInput = form.querySelector('input[name="phone"]');
            const phone = (phoneInput?.value || '').trim();
            const purpose = btn.dataset.otpPurpose || '';

            const errorNode =
                btn.closest('[data-login-section="otp"]')?.querySelector('[data-otp-error]') ||
                btn.closest('.field')?.querySelector('[data-otp-error]') ||
                null;

            if (errorNode) {
                errorNode.hidden = true;
                errorNode.textContent = '';
            }

            if (!phone) {
                if (errorNode) {
                    errorNode.textContent = 'لطفا شماره موبایل را وارد کنید.';
                    errorNode.hidden = false;
                    return;
                }

                alert('لطفا شماره موبایل را وارد کنید.');
                return;
            }

            const url = getOtpSendUrl();
            if (!url) {
                alert('آدرس ارسال کد تنظیم نشده است.');
                return;
            }

            const token = getCsrfToken();
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'در حال ارسال...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ phone, purpose }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.ok) {
                    const message = data.message || 'خطا در ارسال کد';
                    if (errorNode) {
                        errorNode.textContent = message;
                        errorNode.hidden = false;
                    } else if (window.showToast) {
                        window.showToast({ type: 'danger', title: 'خطا', message });
                    } else {
                        alert(message);
                    }

                    btn.disabled = false;
                    btn.textContent = originalText;
                    return;
                }

                if (data.toast && window.showToast) {
                    window.showToast(data.toast);
                }

                const cooldownSeconds = Math.max(1, Number(data.cooldown_seconds || 60));
                let remaining = cooldownSeconds;
                btn.textContent = `${remaining} ثانیه`;
                const timer = setInterval(() => {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(timer);
                        btn.disabled = false;
                        btn.textContent = originalText;
                        return;
                    }

                    btn.textContent = `${remaining} ثانیه`;
                }, 1000);
            } catch (error) {
                const message = error?.message || 'خطا در برقراری ارتباط';
                if (errorNode) {
                    errorNode.textContent = message;
                    errorNode.hidden = false;
                } else if (window.showToast) {
                    window.showToast({ type: 'danger', title: 'خطا', message });
                } else {
                    alert(message);
                }

                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    });
}

function clearAuthFormErrors(form) {
    form.querySelectorAll('[data-form-error], [data-field-error]').forEach((node) => {
        node.hidden = true;
        node.textContent = '';
    });
}

function showAuthFormError(form, message) {
    const node = form.querySelector('[data-form-error]');
    if (!node) {
        if (window.showToast) {
            window.showToast({ type: 'danger', title: 'خطا', message });
            return;
        }

        alert(message);
        return;
    }

    node.textContent = message;
    node.hidden = false;
}

function renderAuthValidationErrors(form, errors) {
    const entries = Object.entries(errors || {});
    if (entries.length === 0) {
        showAuthFormError(form, 'اطلاعات وارد شده صحیح نیست.');
        return;
    }

    entries.forEach(([field, messages]) => {
        const message = Array.isArray(messages) ? (messages[0] || '') : String(messages || '');
        const node = form.querySelector(`[data-field-error="${field}"]`);
        if (node) {
            node.textContent = message;
            node.hidden = false;
            return;
        }

        showAuthFormError(form, message);
    });
}

function initAjaxAuthForms() {
    document.querySelectorAll('form[data-auth-ajax="1"]').forEach((form) => {
        if (form.dataset.bound === '1') {
            return;
        }

        form.dataset.bound = '1';
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            clearAuthFormErrors(form);

            const submitBtn = form.querySelector('button[type="submit"]');
            const submitText = submitBtn ? submitBtn.textContent : '';
            const actionUrl = form.getAttribute('action') || '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'در حال ورود...';
            }

            try {
                const token = getCsrfToken();
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                    redirect: 'follow',
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok && data?.ok) {
                    if (typeof window.closeModal === 'function') {
                        window.closeModal('auth-modal');
                    }

                    const redirectTo = data.redirect_to || window.location.href;
                    window.location.href = redirectTo;
                    return;
                }

                if (response.status === 422) {
                    renderAuthValidationErrors(form, data?.errors || {});
                    return;
                }

                const message = data?.message || 'خطا در ورود.';
                showAuthFormError(form, message);
            } catch (error) {
                showAuthFormError(form, error?.message || 'خطا در برقراری ارتباط.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitText;
                }
            }
        });
    });
}

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
