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
    initAdminUploadUi();
    initAdminCourseLessonUi();
    initAdminConfirmModalUi();
    initAdminCategoryFormUi();

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

function isAdminTheme() {
    return document.documentElement?.dataset?.theme === 'admin';
}

function formatBytes(bytes) {
    const value = Number(bytes || 0);
    if (!Number.isFinite(value) || value <= 0) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const index = Math.min(units.length - 1, Math.floor(Math.log(value) / Math.log(1024)));
    const unit = units[index];
    const number = value / Math.pow(1024, index);
    const rounded = index === 0 ? Math.round(number) : Math.round(number * 10) / 10;
    return `${rounded} ${unit}`;
}

function showUploadUiMessage({ type = 'danger', title = '', message = '' } = {}) {
    if (window.showToast) {
        window.showToast({ type, title, message });
        return;
    }

    alert(message || title || 'خطا');
}

function enhanceFileInput(input) {
    if (!input || input.dataset.uploadEnhanced === '1') {
        return;
    }

    input.dataset.uploadEnhanced = '1';

    const wrapper = document.createElement('div');
    wrapper.className = 'upload-dropzone';

    const hint = document.createElement('div');
    hint.className = 'upload-dropzone__hint';
    hint.textContent = 'فایل را بکشید و رها کنید یا کلیک کنید';

    const fileInfo = document.createElement('div');
    fileInfo.className = 'upload-dropzone__file';

    const parent = input.parentNode;
    parent.insertBefore(wrapper, input);
    wrapper.appendChild(hint);
    wrapper.appendChild(fileInfo);
    wrapper.appendChild(input);

    const accept = (input.getAttribute('accept') || '').toLowerCase();
    const maxVideoBytes = 1024 * 1024 * 1024;

    const syncState = () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) {
            fileInfo.textContent = '';
            return;
        }

        if (accept.includes('video') && file.size > maxVideoBytes) {
            input.value = '';
            fileInfo.textContent = '';
            showUploadUiMessage({
                type: 'danger',
                title: 'حجم فایل زیاد است',
                message: 'حداکثر حجم مجاز برای ویدیو ۱ گیگابایت است.',
            });
            return;
        }

        fileInfo.textContent = `${file.name} (${formatBytes(file.size)})`;
    };

    wrapper.addEventListener('dragover', (event) => {
        event.preventDefault();
        wrapper.classList.add('is-dragover');
    });

    wrapper.addEventListener('dragleave', () => {
        wrapper.classList.remove('is-dragover');
    });

    wrapper.addEventListener('drop', (event) => {
        event.preventDefault();
        wrapper.classList.remove('is-dragover');

        const dt = event.dataTransfer;
        const file = dt?.files && dt.files[0] ? dt.files[0] : null;
        if (!file) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        syncState();
    });

    input.addEventListener('change', syncState);
    syncState();
}

function ensureFormProgressUi(form) {
    const existing = form.querySelector('[data-upload-progress]');
    if (existing) {
        return existing;
    }

    const host = document.createElement('div');
    host.className = 'upload-progress';
    host.hidden = true;
    host.dataset.uploadProgress = '1';

    const top = document.createElement('div');
    top.className = 'upload-progress__top';

    const label = document.createElement('div');
    label.className = 'upload-progress__label';
    label.textContent = 'در حال آپلود...';

    const percent = document.createElement('div');
    percent.className = 'upload-progress__percent';
    percent.textContent = '0%';

    const bar = document.createElement('div');
    bar.className = 'upload-progress__bar';

    const fill = document.createElement('div');
    bar.appendChild(fill);

    top.appendChild(label);
    top.appendChild(percent);
    host.appendChild(top);
    host.appendChild(bar);

    form.prepend(host);
    return host;
}

function bindAdminUploadForm(form) {
    if (!form || form.dataset.uploadBound === '1') {
        return;
    }

    const hasFileInputs = form.querySelector('input[type="file"]') !== null;
    if (!hasFileInputs) {
        return;
    }

    form.dataset.uploadBound = '1';

    form.addEventListener('submit', (event) => {
        const fileInputs = Array.from(form.querySelectorAll('input[type="file"]'));
        const hasFiles = fileInputs.some((input) => (input.files || []).length > 0);
        if (!hasFiles) {
            return;
        }

        event.preventDefault();

        const progress = ensureFormProgressUi(form);
        const percentNode = progress.querySelector('.upload-progress__percent');
        const fill = progress.querySelector('.upload-progress__bar > div');

        const submitButtons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));
        submitButtons.forEach((btn) => {
            btn.disabled = true;
        });

        progress.hidden = false;
        if (percentNode) percentNode.textContent = '0%';
        if (fill) fill.style.width = '0%';

        const xhr = new XMLHttpRequest();
        xhr.open((form.getAttribute('method') || 'POST').toUpperCase(), form.getAttribute('action') || window.location.href);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.timeout = 0;

        xhr.upload.addEventListener('progress', (e) => {
            if (!e.lengthComputable) {
                return;
            }

            const pct = Math.min(100, Math.max(0, Math.round((e.loaded / e.total) * 100)));
            if (percentNode) percentNode.textContent = `${pct}%`;
            if (fill) fill.style.width = `${pct}%`;
        });

        xhr.addEventListener('load', () => {
            const url = xhr.responseURL || window.location.href;
            const contentType = (xhr.getResponseHeader('content-type') || '').toLowerCase();
            const html = xhr.responseText || '';

            if (contentType.includes('text/html') && html.trim() !== '') {
                history.replaceState(null, '', url);
                document.open();
                document.write(html);
                document.close();

                if (typeof window.initApp === 'function') {
                    window.initApp();
                }

                return;
            }

            window.location.href = url;
        });

        xhr.addEventListener('error', () => {
            submitButtons.forEach((btn) => {
                btn.disabled = false;
            });
            progress.hidden = true;

            showUploadUiMessage({
                type: 'danger',
                title: 'خطا',
                message: 'آپلود با خطا مواجه شد.',
            });
        });

        xhr.addEventListener('abort', () => {
            submitButtons.forEach((btn) => {
                btn.disabled = false;
            });
            progress.hidden = true;
        });

        xhr.send(new FormData(form));
    });
}

function initAdminUploadUi() {
    if (!isAdminTheme()) {
        return;
    }

    document.querySelectorAll('input[type="file"]').forEach((input) => {
        enhanceFileInput(input);
    });

    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach((form) => {
        bindAdminUploadForm(form);
    });
}

function initAdminCourseLessonUi() {
    if (!isAdminTheme()) {
        return;
    }

    const addBtn = document.querySelector('[data-course-add-lesson]');
    const tbody = document.querySelector('[data-course-new-lessons]');
    const template = document.querySelector('template[data-course-lesson-row-template]');
    const courseForm = document.querySelector('form#course-form');

    if (!addBtn || !tbody || !template || !(courseForm instanceof HTMLFormElement)) {
        return;
    }

    if (addBtn.dataset.bound === '1') {
        return;
    }

    addBtn.dataset.bound = '1';

    const lessonLists = Array.from(courseForm.querySelectorAll('tbody[data-course-lessons-list]'));

    const syncSortOrders = () => {
        lessonLists.forEach((list) => {
            const rows = Array.from(list.querySelectorAll('tr'));
            rows.forEach((row, index) => {
                row.querySelectorAll('input[data-sort-order]').forEach((input) => {
                    input.value = String(index);
                });
            });
        });
    };

    const getDragAfterRow = (list, clientY) => {
        const rows = Array.from(list.querySelectorAll('tr')).filter((row) => !row.classList.contains('is-dragging'));

        let closestRow = null;
        let closestOffset = Number.NEGATIVE_INFINITY;

        rows.forEach((row) => {
            const box = row.getBoundingClientRect();
            const offset = clientY - box.top - box.height / 2;
            if (offset < 0 && offset > closestOffset) {
                closestOffset = offset;
                closestRow = row;
            }
        });

        return closestRow;
    };

    const bindDragAndDrop = (list) => {
        if (!(list instanceof HTMLElement) || list.dataset.dndBound === '1') {
            return;
        }

        list.dataset.dndBound = '1';

        let draggingRow = null;
        let armedRow = null;

        list.addEventListener('pointerdown', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const handle = target.closest('[data-drag-handle]');
            if (!(handle instanceof HTMLElement)) {
                return;
            }

            const row = handle.closest('tr');
            if (!(row instanceof HTMLTableRowElement)) {
                return;
            }

            row.setAttribute('draggable', 'true');
            armedRow = row;
        });

        list.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const handle = target.closest('[data-drag-handle]');
            if (!(handle instanceof HTMLElement)) {
                return;
            }

            const row = handle.closest('tr');
            if (!(row instanceof HTMLTableRowElement)) {
                return;
            }

            if (!draggingRow) {
                row.setAttribute('draggable', 'false');
            }
        });

        list.addEventListener('dragstart', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            const handle = target.closest('[data-drag-handle]');
            if (!(handle instanceof HTMLElement)) {
                return;
            }

            const row = handle.closest('tr');
            if (!(row instanceof HTMLTableRowElement)) {
                return;
            }

            if (row.getAttribute('draggable') !== 'true') {
                return;
            }

            draggingRow = row;
            armedRow = null;
            row.classList.add('is-dragging');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.dropEffect = 'move';
                event.dataTransfer.setData('text/plain', '1');
            }
        });

        list.addEventListener('dragend', () => {
            if (draggingRow) {
                draggingRow.classList.remove('is-dragging');
                draggingRow.setAttribute('draggable', 'false');
            }
            draggingRow = null;
            if (armedRow) {
                armedRow.setAttribute('draggable', 'false');
            }
            armedRow = null;
            syncSortOrders();
        });

        list.addEventListener('dragover', (event) => {
            if (!draggingRow) {
                return;
            }

            event.preventDefault();

            const afterRow = getDragAfterRow(list, event.clientY);
            if (!afterRow) {
                list.appendChild(draggingRow);
                return;
            }

            if (afterRow !== draggingRow) {
                list.insertBefore(draggingRow, afterRow);
            }
        });

        list.addEventListener('drop', (event) => {
            if (!draggingRow) {
                return;
            }

            event.preventDefault();
            syncSortOrders();
        });
    };

    lessonLists.forEach(bindDragAndDrop);
    syncSortOrders();

    tbody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const removeBtn = target.closest('[data-course-remove-lesson]');
        if (!(removeBtn instanceof HTMLElement)) {
            return;
        }

        const row = removeBtn.closest('tr');
        if (!(row instanceof HTMLElement)) {
            return;
        }

        const remove = () => {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            if (rows.length <= 1) {
                row.querySelectorAll('input').forEach((input) => {
                    const type = (input.getAttribute('type') || '').toLowerCase();
                    if (type === 'hidden') {
                        return;
                    }
                    if (type === 'checkbox') {
                        input.checked = false;
                        return;
                    }
                    if (type === 'file') {
                        input.value = '';
                        return;
                    }

                    input.value = '';
                });

                syncSortOrders();
                return;
            }

            row.remove();
            syncSortOrders();
        };

        if (window.adminConfirm) {
            window.adminConfirm({
                title: 'حذف ویدیو',
                message: 'آیا از حذف این ردیف مطمئن هستید؟',
                onConfirm: remove,
            });
            return;
        }

        if (confirm('آیا از حذف این ردیف مطمئن هستید؟')) {
            remove();
        }
    });

    addBtn.addEventListener('click', () => {
        const nextIndex = Math.max(0, parseInt(tbody.dataset.nextIndex || '1', 10) || 0);
        const key = `new_${nextIndex}`;
        const html = (template.innerHTML || '')
            .replaceAll('__KEY__', key)
            .replaceAll('__INDEX__', String(nextIndex));

        const host = document.createElement('tbody');
        host.innerHTML = html.trim();
        const row = host.firstElementChild;
        if (!row) {
            return;
        }

        tbody.appendChild(row);
        tbody.dataset.nextIndex = String(nextIndex + 1);

        const fileInput = row.querySelector('input[type="file"]');
        enhanceFileInput(fileInput);

        const titleInput = row.querySelector('input[name*="[title]"]');
        if (titleInput) {
            titleInput.focus();
        }

        syncSortOrders();
    });
}

function initAdminConfirmModalUi() {
    if (!isAdminTheme()) {
        return;
    }

    const modal = document.querySelector('[data-confirm-modal]');
    if (!(modal instanceof HTMLElement)) {
        return;
    }

    const titleNode = modal.querySelector('[data-confirm-title]');
    const messageNode = modal.querySelector('[data-confirm-message]');
    const confirmBtn = modal.querySelector('[data-confirm-confirm]');
    const cancelNodes = modal.querySelectorAll('[data-confirm-cancel]');

    let activeForm = null;
    let activeOnConfirm = null;

    const close = () => {
        modal.hidden = true;
        document.body.classList.remove('has-modal');
        activeForm = null;
        activeOnConfirm = null;
    };

    const open = ({ form = null, title = 'حذف', message = 'آیا مطمئن هستید؟', onConfirm = null } = {}) => {
        activeForm = form;
        activeOnConfirm = typeof onConfirm === 'function' ? onConfirm : null;

        if (form) {
            title = form.dataset.confirmTitle || title;
            message = form.dataset.confirmMessage || message;
        }

        if (titleNode) {
            titleNode.textContent = title;
        }
        if (messageNode) {
            messageNode.textContent = message;
        }

        modal.hidden = false;
        document.body.classList.add('has-modal');

        if (confirmBtn instanceof HTMLElement) {
            confirmBtn.focus();
        }
    };

    window.adminConfirm = ({ title = 'حذف', message = 'آیا مطمئن هستید؟', onConfirm } = {}) => {
        open({ title, message, onConfirm });
    };

    cancelNodes.forEach((node) => {
        node.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            close();
        }
    });

    modal.addEventListener('click', (event) => {
        const target = event.target;
        if (target === modal) {
            close();
        }
    });

    if (confirmBtn instanceof HTMLElement) {
        confirmBtn.addEventListener('click', () => {
            if (activeOnConfirm) {
                const callback = activeOnConfirm;
                close();
                callback();
                return;
            }

            if (!activeForm) {
                close();
                return;
            }

            activeForm.dataset.confirmBypass = '1';
            activeForm.submit();
        });
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.confirm !== '1') {
            return;
        }

        if (form.dataset.confirmBypass === '1') {
            delete form.dataset.confirmBypass;
            return;
        }

        event.preventDefault();
        open({ form });
    });
}

function initAdminCategoryFormUi() {
    if (!isAdminTheme()) {
        return;
    }

    const typeSelect = document.querySelector('select[data-category-type]');
    const parentSelect = document.querySelector('select[data-category-parent]');
    if (!(typeSelect instanceof HTMLSelectElement) || !(parentSelect instanceof HTMLSelectElement)) {
        return;
    }

    const optionTemplates = Array.from(parentSelect.querySelectorAll('option')).map((option) => option.cloneNode(true));

    const sync = () => {
        const typeValue = String(typeSelect.value || '');
        const currentValue = String(parentSelect.value || '');

        parentSelect.innerHTML = '';

        optionTemplates.forEach((template) => {
            if (!(template instanceof HTMLOptionElement)) {
                return;
            }

            const optionType = template.dataset.type;
            const isMatch = typeValue === '' || !optionType || optionType === typeValue;
            if (!isMatch) {
                return;
            }

            parentSelect.appendChild(template.cloneNode(true));
        });

        const stillExists = Array.from(parentSelect.options).some((option) => option.value === currentValue);
        parentSelect.value = stillExists ? currentValue : '';
    };

    typeSelect.addEventListener('change', sync);
    sync();
}

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
