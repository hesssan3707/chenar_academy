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
    initPasswordPolicyUi();
    initOtpSenders();
    initAjaxAuthForms();
    initAdminMobileSidebarUi();
    initAdminUploadUi();
    initAdminCourseLessonUi();
    initAdminConfirmModalUi();
    initAdminCategoryFormUi();
    initAdminSettingsTabsUi();
    initAdminWysiwygUi();
    initAdminBulkDiscountUi();
    initAdminCouponFormUi();
    initAdminProductInitialSelectsUi();
    initHomeRowsAutoHideUi();
    initCategoryTapPreviewUi();
    initHorizontalWheelScrollUi();
    initCatalogInstitutionWheelUi();
    initProductDetailTabsUi();
    initCheckoutCouponUi();

    const surveyModal = document.querySelector('[data-survey-modal]');
    if (surveyModal && !window.__surveyModalShown) {
        const optedOut = (() => {
            try {
                return window.localStorage && window.localStorage.getItem('survey_opt_out') === '1';
            } catch (e) {
                return false;
            }
        })();

        window.__surveyModalShown = true;
        if (optedOut) {
            surveyModal.hidden = true;
        } else {
            surveyModal.hidden = false;
        }

        const close = () => {
            surveyModal.hidden = true;
        };

        if (surveyModal.dataset.surveyBound !== '1') {
            surveyModal.dataset.surveyBound = '1';

            surveyModal.querySelectorAll('[data-survey-close]').forEach((node) => {
                node.addEventListener('click', close);
            });

            surveyModal.querySelectorAll('[data-survey-optout]').forEach((node) => {
                node.addEventListener('click', () => {
                    try {
                        if (window.localStorage) {
                            window.localStorage.setItem('survey_opt_out', '1');
                        }
                    } catch (e) {}
                    close();
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !surveyModal.hidden) {
                    close();
                }
            });
        }

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

function initAdminBulkDiscountUi() {
    if (!isAdminTheme()) {
        return;
    }

    document.querySelectorAll('form[data-discount-unit-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const typeSelect = form.querySelector('select[name="discount_type"]');
        const unitNode = form.querySelector('[data-discount-unit]');
        if (!(typeSelect instanceof HTMLSelectElement) || !(unitNode instanceof HTMLElement)) {
            return;
        }

        if (typeSelect.dataset.boundDiscountUnit === '1') {
            return;
        }
        typeSelect.dataset.boundDiscountUnit = '1';

        const currencyUnit = form.getAttribute('data-currency-unit') || '';

        const sync = () => {
            const typeValue = String(typeSelect.value || '');
            unitNode.textContent = typeValue === 'percent' ? '٪' : currencyUnit;
        };

        typeSelect.addEventListener('change', sync);
        sync();
    });
}

function initAdminCouponFormUi() {
    if (!isAdminTheme()) {
        return;
    }

    const form = document.querySelector('#coupon-form');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const codeInput = form.querySelector('[data-coupon-code-input]');
    if (codeInput instanceof HTMLInputElement) {
        const normalize = () => {
            const raw = String(codeInput.value || '');
            const upper = raw.toUpperCase();
            const cleaned = upper.replace(/[^A-Z0-9]/g, '').slice(0, 8);
            if (cleaned !== raw) {
                codeInput.value = cleaned;
            }
        };

        codeInput.addEventListener('input', normalize);
        normalize();
    }

    const generateButton = form.querySelector('[data-generate-coupon-code]');
    if (generateButton instanceof HTMLButtonElement && codeInput instanceof HTMLInputElement) {
        const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const numbers = '23456789';
        const all = letters + numbers;

        const randInt = (max) => {
            if (max <= 0) {
                return 0;
            }

            if (window.crypto && window.crypto.getRandomValues) {
                const buf = new Uint32Array(1);
                window.crypto.getRandomValues(buf);
                return Number(buf[0] % max);
            }

            return Math.floor(Math.random() * max);
        };

        const shuffle = (arr) => {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = randInt(i + 1);
                const t = arr[i];
                arr[i] = arr[j];
                arr[j] = t;
            }
            return arr;
        };

        const generate = () => {
            const length = 8;
            const chars = [letters[randInt(letters.length)], numbers[randInt(numbers.length)]];

            while (chars.length < length) {
                chars.push(all[randInt(all.length)]);
            }

            return shuffle(chars).join('');
        };

        generateButton.addEventListener('click', () => {
            codeInput.value = generate();
            codeInput.dispatchEvent(new Event('input', { bubbles: true }));
            codeInput.focus();
        });
    }

    const allProducts = form.querySelector('[data-coupon-all-products]');
    const productsSelect = form.querySelector('[data-coupon-products]');
    if (allProducts instanceof HTMLInputElement && productsSelect instanceof HTMLSelectElement) {
        const sync = () => {
            const isAll = !!allProducts.checked;
            productsSelect.disabled = isAll;
        };
        allProducts.addEventListener('change', sync);
        sync();
    }
}

function initAdminMobileSidebarUi() {
    if (!isAdminTheme()) {
        return;
    }

    if (window.__adminMobileSidebarBound) {
        return;
    }
    window.__adminMobileSidebarBound = true;

    const toggle = document.querySelector('#admin-nav-toggle');
    if (!(toggle instanceof HTMLInputElement)) {
        return;
    }

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const link = target.closest('.admin-sidebar .admin-menu__link');
        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        if (!toggle.checked) {
            return;
        }

        toggle.checked = false;
    });
}

function initCheckoutCouponUi() {
    const form = document.querySelector('[data-checkout-coupon-form]');
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.dataset.checkoutCouponBound === '1') {
        return;
    }
    form.dataset.checkoutCouponBound = '1';

    const panel = document.querySelector('[data-checkout-coupon-panel]');
    const submitBtn = form.querySelector('[data-checkout-coupon-submit]');
    const messageEl = form.querySelector('[data-checkout-coupon-message]');
    const input = form.querySelector('[data-checkout-coupon-input]');

    const formatNumber = (value) => {
        const n = Number(value || 0);
        if (!Number.isFinite(n)) {
            return '0';
        }
        return new Intl.NumberFormat('en-US').format(Math.trunc(n));
    };

    const setLoading = (isLoading) => {
        if (panel instanceof HTMLElement) {
            panel.classList.toggle('panel-main-is-loading', !!isLoading);
        }
        if (submitBtn instanceof HTMLButtonElement) {
            submitBtn.disabled = !!isLoading;
        }
    };

    const setMessage = (text, type) => {
        if (!(messageEl instanceof HTMLElement)) {
            return;
        }
        const t = String(text || '').trim();
        messageEl.hidden = t === '';
        messageEl.textContent = t;
        messageEl.classList.toggle('field__error', type === 'error');
        messageEl.classList.toggle('field__hint', type !== 'error');
    };

    const syncInvoice = (data) => {
        const discountEl = document.querySelector('[data-checkout-discount]');
        const payableEl = document.querySelector('[data-checkout-payable]');
        const taxEl = document.querySelector('[data-checkout-tax]');

        if (discountEl instanceof HTMLElement) {
            discountEl.textContent = formatNumber(data?.discountAmount);
        }
        if (payableEl instanceof HTMLElement) {
            payableEl.textContent = formatNumber(data?.payableAmount);
        }
        if (taxEl instanceof HTMLElement) {
            taxEl.textContent = formatNumber(data?.taxAmount);
        }
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setMessage('', 'info');
        setLoading(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: new FormData(form),
            });

            let data = null;
            try {
                data = await response.json();
            } catch (e) {}

            if (!response.ok) {
                const message = data?.message || 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.';
                syncInvoice(data);
                setMessage(message, 'error');
                return;
            }

            syncInvoice(data);
            if (input instanceof HTMLInputElement && typeof data?.couponCode === 'string') {
                input.value = data.couponCode;
            }
            setMessage(data?.message || '', data?.type === 'error' ? 'error' : 'success');
        } catch (e) {
            setMessage('خطا در ارتباط. دوباره تلاش کنید.', 'error');
        } finally {
            setLoading(false);
        }
    });
}

function initHomeRowsAutoHideUi() {
    if (typeof window.__homeRowsAutoHideCleanup === 'function') {
        window.__homeRowsAutoHideCleanup();
        window.__homeRowsAutoHideCleanup = null;
    }

    const rows = document.querySelector('[data-home-rows]');
    if (!rows) {
        return;
    }

    const idleMs = 12_000;
    let idleTimer = null;
    const scrollers = Array.from(rows.querySelectorAll('.h-scroll-container')).filter((node) => node instanceof HTMLElement);
    const passiveOpts = { passive: true };

    const hide = () => {
        rows.classList.add('is-hidden');
    };

    const show = () => {
        rows.classList.remove('is-hidden');
    };

    const reset = () => {
        show();
        if (idleTimer) {
            clearTimeout(idleTimer);
        }
        idleTimer = setTimeout(hide, idleMs);
    };

    const onActivity = () => {
        reset();
    };

    const windowEvents = ['mousemove', 'pointermove', 'touchstart', 'touchmove', 'keydown', 'scroll', 'wheel', 'mousedown', 'click'];
    windowEvents.forEach((eventName) => {
        window.addEventListener(eventName, onActivity, passiveOpts);
    });

    const scrollerEvents = ['scroll', 'touchstart', 'touchmove', 'pointerdown', 'pointermove', 'wheel'];
    scrollers.forEach((scroller) => {
        scrollerEvents.forEach((eventName) => {
            scroller.addEventListener(eventName, onActivity, passiveOpts);
        });
    });

    reset();

    window.__homeRowsAutoHideCleanup = () => {
        if (idleTimer) {
            clearTimeout(idleTimer);
        }
        windowEvents.forEach((eventName) => {
            window.removeEventListener(eventName, onActivity, passiveOpts);
        });
        scrollers.forEach((scroller) => {
            scrollerEvents.forEach((eventName) => {
                scroller.removeEventListener(eventName, onActivity, passiveOpts);
            });
        });
    };
}

function initCategoryTapPreviewUi() {
    if (typeof window.__categoryTapPreviewCleanup === 'function') {
        window.__categoryTapPreviewCleanup();
        window.__categoryTapPreviewCleanup = null;
    }

    const supportsHover = (() => {
        try {
            return window.matchMedia && window.matchMedia('(hover: hover)').matches;
        } catch (e) {
            return true;
        }
    })();

    if (supportsHover) {
        return;
    }

    const clearPeek = () => {
        document.querySelectorAll('a.card-category.is-peek').forEach((node) => {
            node.classList.remove('is-peek');
        });
    };

    const onClickCapture = (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const link = target.closest('a.card-category');
        if (!(link instanceof HTMLAnchorElement)) {
            clearPeek();
            return;
        }

        if (!link.querySelector('.card-category__overlay') && !link.querySelector('.info')) {
            return;
        }

        if (link.classList.contains('is-peek')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        clearPeek();
        link.classList.add('is-peek');
    };

    document.addEventListener('click', onClickCapture, true);

    window.__categoryTapPreviewCleanup = () => {
        clearPeek();
        document.removeEventListener('click', onClickCapture, true);
    };
}

function initHorizontalWheelScrollUi() {
    const containers = Array.from(document.querySelectorAll('.h-scroll-container')).filter((node) => node instanceof HTMLElement);
    if (containers.length === 0) {
        return;
    }

    containers.forEach((container) => {
        if (container.dataset.hWheelBound === '1') {
            return;
        }
        container.dataset.hWheelBound = '1';

        container.addEventListener('wheel', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (container.scrollWidth <= container.clientWidth + 1) {
                return;
            }

            const absX = Math.abs(event.deltaX);
            const absY = Math.abs(event.deltaY);
            if (absY === 0 || absY <= absX) {
                return;
            }

            let delta = event.deltaY;
            if (event.deltaMode === 1) {
                delta *= 16;
            } else if (event.deltaMode === 2) {
                delta *= window.innerHeight;
            }

            event.preventDefault();
            container.scrollBy({ left: delta, top: 0, behavior: 'auto' });
        }, { passive: false });
    });
}

function initCatalogInstitutionWheelUi() {
    const widgets = Array.from(document.querySelectorAll('[data-uni-wheel]')).filter((node) => node instanceof HTMLElement);
    if (widgets.length === 0) {
        return;
    }

    widgets.forEach((widget) => {
        if (widget.dataset.uniWheelBound === '1') {
            return;
        }
        widget.dataset.uniWheelBound = '1';

        const viewport = widget.querySelector('[data-uni-wheel-viewport]');
        const track = widget.querySelector('[data-uni-wheel-track]');
        const slides = Array.from(widget.querySelectorAll('[data-uni-wheel-slide]')).filter((node) => node instanceof HTMLElement);
        if (!(viewport instanceof HTMLElement) || !(track instanceof HTMLElement) || slides.length === 0) {
            return;
        }

        const prevButton = widget.querySelector('[data-uni-wheel-prev]');
        const nextButton = widget.querySelector('[data-uni-wheel-next]');
        const prevLabel = widget.querySelector('[data-uni-wheel-prev-label]');
        const nextLabel = widget.querySelector('[data-uni-wheel-next-label]');

        if (slides.length <= 1) {
            if (prevButton instanceof HTMLElement) {
                prevButton.style.display = 'none';
            }
            if (nextButton instanceof HTMLElement) {
                nextButton.style.display = 'none';
            }
            return;
        }

        widget.style.position = widget.style.position || 'relative';
        viewport.style.overflow = 'hidden';
        viewport.style.position = 'relative';
        track.style.willChange = 'transform';
        track.style.transition = 'transform 480ms cubic-bezier(0.22, 1, 0.36, 1)';

        let slideHeight = 0;
        let activeIndex = 0;
        let wheelLockUntil = 0;
        const viewportPadding = 12;

        const clampIndex = (index) => {
            const count = slides.length;
            if (count <= 0) {
                return 0;
            }
            let i = Number(index || 0);
            if (!Number.isFinite(i)) {
                i = 0;
            }
            return Math.max(0, Math.min(count - 1, i));
        };

        const measure = () => {
            const heights = slides.map((s) => Math.ceil(s.getBoundingClientRect().height));
            slideHeight = Math.max(1, ...heights, 1);
            viewport.style.paddingTop = `${viewportPadding}px`;
            viewport.style.paddingBottom = `${viewportPadding}px`;
            viewport.style.height = `${slideHeight + viewportPadding * 2}px`;

            slides.forEach((slide) => {
                slide.style.height = `${slideHeight}px`;
                slide.style.boxSizing = 'border-box';
                slide.style.display = 'flex';
                slide.style.flexDirection = 'column';
                slide.style.justifyContent = 'flex-start';
                slide.style.transition = 'opacity 480ms cubic-bezier(0.22, 1, 0.36, 1), transform 480ms cubic-bezier(0.22, 1, 0.36, 1)';
            });
        };

        const render = () => {
            activeIndex = clampIndex(activeIndex);
            const offset = viewportPadding - activeIndex * slideHeight;
            track.style.transform = `translate3d(0, ${offset}px, 0)`;

            slides.forEach((slide, index) => {
                const distance = Math.min(2, Math.abs(index - activeIndex));
                const isActive = index === activeIndex;
                slide.style.opacity = isActive ? '1' : distance === 1 ? '0.65' : '0.45';
                slide.style.transform = isActive ? 'scale(1)' : distance === 1 ? 'scale(0.985)' : 'scale(0.97)';
                slide.style.pointerEvents = isActive ? 'auto' : 'none';
            });

            const hasPrev = activeIndex > 0;
            const hasNext = activeIndex < slides.length - 1;
            const prevIndex = activeIndex - 1;
            const nextIndex = activeIndex + 1;
            const prevTitle = hasPrev ? String(slides[prevIndex]?.dataset?.uniWheelTitle || '').trim() : '';
            const nextTitle = hasNext ? String(slides[nextIndex]?.dataset?.uniWheelTitle || '').trim() : '';

            if (prevButton instanceof HTMLElement) {
                prevButton.hidden = !hasPrev;
            }
            if (nextButton instanceof HTMLElement) {
                nextButton.hidden = !hasNext;
            }

            if (prevLabel instanceof HTMLElement) {
                prevLabel.textContent = prevTitle !== '' ? prevTitle : '';
            }
            if (nextLabel instanceof HTMLElement) {
                nextLabel.textContent = nextTitle !== '' ? nextTitle : '';
            }
        };

        const go = (direction) => {
            const nextIndex = clampIndex(activeIndex + direction);
            if (nextIndex === activeIndex) {
                return;
            }
            activeIndex = nextIndex;
            render();
        };

        const onWheel = (event) => {
            const now = Date.now();
            if (now < wheelLockUntil) {
                return;
            }

            const target = event.target;
            if (target instanceof Element && target.closest('.h-scroll-container')) {
                return;
            }

            const absX = Math.abs(event.deltaX);
            const absY = Math.abs(event.deltaY);
            if (absY === 0 || absY <= absX) {
                return;
            }

            const direction = event.deltaY > 0 ? 1 : -1;
            if ((direction < 0 && activeIndex <= 0) || (direction > 0 && activeIndex >= slides.length - 1)) {
                return;
            }

            event.preventDefault();
            wheelLockUntil = now + 480;
            go(direction);
        };

        viewport.addEventListener('wheel', onWheel, { passive: false });

        if (prevButton instanceof HTMLButtonElement) {
            prevButton.addEventListener('click', () => go(-1));
        }
        if (nextButton instanceof HTMLButtonElement) {
            nextButton.addEventListener('click', () => go(1));
        }

        const onResize = () => {
            const prevHeight = slideHeight;
            measure();
            if (slideHeight !== prevHeight) {
                render();
            }
        };

        window.addEventListener('resize', onResize, { passive: true });
        measure();
        render();

        if (typeof ResizeObserver === 'function') {
            const observer = new ResizeObserver(() => onResize());
            observer.observe(viewport);
            slides.forEach((slide) => observer.observe(slide));
        } else {
            setTimeout(onResize, 0);
            setTimeout(onResize, 350);
            window.addEventListener('load', onResize, { once: true, passive: true });
        }
    });
}

function initProductDetailTabsUi() {
    const containers = Array.from(document.querySelectorAll('[data-detail-tabs]')).filter((node) => node instanceof HTMLElement);
    if (containers.length === 0) {
        return;
    }

    const mobileQuery = (() => {
        try {
            return window.matchMedia('(max-width: 900px)');
        } catch (e) {
            return null;
        }
    })();

    containers.forEach((container) => {
        if (container.dataset.detailTabsBound === '1') {
            return;
        }
        container.dataset.detailTabsBound = '1';

        const root = container.parentElement;
        if (!root) {
            return;
        }

        const tabs = Array.from(container.querySelectorAll('[data-detail-tab]')).filter((node) => node instanceof HTMLButtonElement);
        const panels = Array.from(root.querySelectorAll('[data-detail-panel]')).filter((node) => node instanceof HTMLElement);
        if (tabs.length === 0 || panels.length === 0) {
            return;
        }

        const firstValue = String(tabs[0].dataset.detailTab || '');
        const validValues = new Set(tabs.map((tab) => String(tab.dataset.detailTab || '')).filter((value) => value !== ''));

        const setActive = (value) => {
            const targetValue = validValues.has(String(value)) ? String(value) : firstValue;

            tabs.forEach((tab) => {
                const tabValue = String(tab.dataset.detailTab || '');
                const isActive = tabValue === targetValue;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                tab.toggleAttribute('tabindex', !isActive);
                if (!isActive) {
                    tab.setAttribute('tabindex', '-1');
                }
            });

            const shouldHide = mobileQuery ? mobileQuery.matches : window.innerWidth <= 900;

            panels.forEach((panel) => {
                const panelValue = String(panel.dataset.detailPanel || '');
                const isPanelActive = panelValue === targetValue;
                if (shouldHide) {
                    panel.hidden = !isPanelActive;
                } else {
                    panel.hidden = false;
                }
            });
        };

        const onClick = (event) => {
            const target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            const button = target.closest('[data-detail-tab]');
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            setActive(button.dataset.detailTab || firstValue);
        };

        container.addEventListener('click', onClick);

        const onLayoutChange = () => {
            const active = tabs.find((tab) => tab.classList.contains('is-active'));
            setActive(active?.dataset.detailTab || firstValue);
        };

        if (mobileQuery) {
            if (typeof mobileQuery.addEventListener === 'function') {
                mobileQuery.addEventListener('change', onLayoutChange);
            } else if (typeof mobileQuery.addListener === 'function') {
                mobileQuery.addListener(onLayoutChange);
            }
        } else {
            window.addEventListener('resize', onLayoutChange, { passive: true });
        }

        setActive(firstValue);
    });
}

function initAdminProductInitialSelectsUi() {
    if (!isAdminTheme()) {
        return;
    }

    document.querySelectorAll('form[data-product-initial-selects]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.dataset.productInitialSelectsBound === '1') {
            return;
        }
        form.dataset.productInitialSelectsBound = '1';

        form.setAttribute('autocomplete', 'off');

        const selects = Array.from(form.querySelectorAll('select[data-initial-value]')).filter((node) => node instanceof HTMLSelectElement);
        const sync = () => {
            selects.forEach((select) => {
                const initialValue = String(select.dataset.initialValue || '');
                if (String(select.value || '') !== initialValue) {
                    select.value = initialValue;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        };

        sync();
        setTimeout(sync, 0);
        setTimeout(sync, 180);
    });

    if (!window.__adminProductInitialSelectsPageshowBound) {
        window.__adminProductInitialSelectsPageshowBound = true;
        window.addEventListener('pageshow', () => {
            document.querySelectorAll('form[data-product-initial-selects]').forEach((form) => {
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const selects = Array.from(form.querySelectorAll('select[data-initial-value]')).filter((node) => node instanceof HTMLSelectElement);
                selects.forEach((select) => {
                    const initialValue = String(select.dataset.initialValue || '');
                    if (String(select.value || '') !== initialValue) {
                        select.value = initialValue;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });
        });
    }
}

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

function ensureTinyMceLoaded({ maxAttempts = 30, intervalMs = 250 } = {}) {
    return new Promise((resolve, reject) => {
        let attempts = 0;

        const tick = () => {
            attempts++;
            if (window.tinymce) {
                resolve(window.tinymce);
                return;
            }

            if (attempts >= maxAttempts) {
                reject(new Error('TinyMCE not available'));
                return;
            }

            window.setTimeout(tick, intervalMs);
        };

        tick();
    });
}

function initAdminWysiwygUi() {
    if (!isAdminTheme()) {
        return;
    }

    const targets = Array.from(document.querySelectorAll('textarea[data-wysiwyg="1"]'));
    if (targets.length === 0) {
        return;
    }

    ensureTinyMceLoaded().then((tinymce) => {
        targets.forEach((textarea) => {
            if (!(textarea instanceof HTMLTextAreaElement)) {
                return;
            }

            if (textarea.dataset.wysiwygBound === '1') {
                return;
            }

            const uploadUrlBase = textarea.dataset.wysiwygUploadUrl || '';
            const csrfToken = getCsrfToken();
            textarea.dataset.wysiwygBound = '1';

            try {
                tinymce.init({
                    target: textarea,
                    height: 420,
                    directionality: 'rtl',
                    language: 'fa',
                    language_url: 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/langs/fa.min.js',
                    menubar: false,
                    plugins: 'image link lists table code directionality',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | rtl ltr | bullist numlist | link image | code',
                    images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', uploadUrlBase);
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                        xhr.upload.onprogress = (e) => {
                            progress(e.loaded / e.total * 100);
                        };

                        xhr.onload = () => {
                            if (xhr.status === 403) {
                                reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                                return;
                            }

                            if (xhr.status < 200 || xhr.status >= 300) {
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }

                            const json = JSON.parse(xhr.responseText);

                            if (!json || typeof json.url !== 'string') {
                                reject('Invalid JSON: ' + xhr.responseText);
                                return;
                            }

                            resolve(json.url);
                        };

                        xhr.onerror = () => {
                            reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                        };

                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());

                        xhr.send(formData);
                    })
                });
            } catch {
                textarea.dataset.wysiwygBound = '0';
            }
        });
    }).catch(() => {});
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

            const row = target.closest('tr');
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

    const typeSelects = Array.from(document.querySelectorAll('select[data-category-type]'));
    typeSelects.forEach((typeSelect) => {
        if (!(typeSelect instanceof HTMLSelectElement)) {
            return;
        }

        const host = typeSelect.closest('form') || document;
        const parentSelect = host.querySelector('select[data-category-parent]');
        if (!(parentSelect instanceof HTMLSelectElement)) {
            return;
        }

        if (typeSelect.dataset.bound === '1') {
            return;
        }
        typeSelect.dataset.bound = '1';

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
    });
}

function initAdminSettingsTabsUi() {
    if (!isAdminTheme()) {
        return;
    }

    const tabsRoot = document.querySelector('[data-settings-tabs]');
    if (!(tabsRoot instanceof HTMLElement)) {
        return;
    }

    if (tabsRoot.dataset.bound === '1') {
        return;
    }
    tabsRoot.dataset.bound = '1';

    const tabs = Array.from(tabsRoot.querySelectorAll('[data-settings-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
    if (tabs.length === 0 || panels.length === 0) {
        return;
    }

    const activate = (key) => {
        tabs.forEach((tab) => {
            const tabKey = tab.getAttribute('data-settings-tab');
            const isActive = tabKey === key;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        panels.forEach((panel) => {
            const panelKey = panel.getAttribute('data-settings-panel');
            panel.hidden = panelKey !== key;
        });
    };

    tabs.forEach((tab) => {
        if (tab instanceof HTMLButtonElement && tab.dataset.bound === '1') {
            return;
        }

        if (tab instanceof HTMLButtonElement) {
            tab.dataset.bound = '1';
        }

        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-settings-tab');
            if (!key) return;
            activate(key);
        });
    });

    const firstErrorPanel = panels.find((panel) => panel.querySelector('.field__error'));
    if (firstErrorPanel) {
        const key = firstErrorPanel.getAttribute('data-settings-panel');
        if (key) {
            activate(key);
            return;
        }
    }

    const firstKey = tabs[0]?.getAttribute('data-settings-tab');
    if (firstKey) {
        activate(firstKey);
    }
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
            toggleBtn.textContent = 'ورود با رمز';
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
        toggleBtn.textContent = 'ورود با کد';
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

function initPasswordPolicyUi() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.passwordPolicyBound === '1') {
            return;
        }

        const passwordInput = form.querySelector('input[name="password"]');
        if (!passwordInput) {
            return;
        }

        form.dataset.passwordPolicyBound = '1';

        const confirmationInput = form.querySelector('input[name="password_confirmation"]');
        const currentPasswordInput = form.querySelector('input[name="current_password"]');
        const loginActionInput = form.querySelector('[data-login-action]');

        const update = () => {
            const passwordVisible = isInputVisible(passwordInput);
            const confirmationVisible = confirmationInput ? isInputVisible(confirmationInput) : false;
            const currentPasswordVisible = currentPasswordInput ? isInputVisible(currentPasswordInput) : false;

            const loginRequiresPassword = loginActionInput && String(loginActionInput.value || '').includes('login_password');
            const policyRequired = passwordVisible && (passwordInput.required || loginRequiresPassword || (passwordInput.value || '').trim() !== '');
            const confirmationRequired = confirmationInput && confirmationVisible && (confirmationInput.required || (confirmationInput.value || '').trim() !== '');

            const passwordState = policyRequired ? validatePasswordPolicy(passwordInput.value) : { valid: true, message: '' };
            const confirmState = confirmationRequired
                ? validatePasswordConfirmation(passwordInput.value, confirmationInput.value)
                : { valid: true, message: '' };

            const passwordTouched = passwordInput.dataset.passwordTouched === '1';
            const confirmationTouched = confirmationInput ? confirmationInput.dataset.passwordTouched === '1' : false;

            const passwordShouldRender = passwordTouched && passwordVisible && (passwordInput.value || '').trim() !== '';
            const confirmationShouldRender = confirmationInput && confirmationTouched && confirmationVisible && (confirmationInput.value || '').trim() !== '';

            renderPasswordFeedback(passwordInput, passwordShouldRender ? passwordState : { valid: true, message: '' }, 'password');
            if (confirmationInput) {
                renderPasswordFeedback(
                    confirmationInput,
                    confirmationShouldRender ? confirmState : { valid: true, message: '' },
                    'password_confirmation'
                );
            }

            const currentPasswordOk =
                !currentPasswordInput ||
                !currentPasswordVisible ||
                !currentPasswordInput.required ||
                (currentPasswordInput.value || '').trim() !== '';

            const shouldDisable = passwordVisible && (!passwordState.valid || !confirmState.valid || !currentPasswordOk);
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((btn) => {
                if (btn instanceof HTMLButtonElement || btn instanceof HTMLInputElement) {
                    btn.disabled = shouldDisable;
                }
            });
        };

        passwordInput.addEventListener('input', update);
        passwordInput.addEventListener('blur', () => {
            if ((passwordInput.value || '').trim() !== '') {
                passwordInput.dataset.passwordTouched = '1';
            }
            update();
        });

        if (confirmationInput) {
            confirmationInput.addEventListener('input', update);
            confirmationInput.addEventListener('blur', () => {
                if ((confirmationInput.value || '').trim() !== '') {
                    confirmationInput.dataset.passwordTouched = '1';
                }
                update();
            });
        }

        if (currentPasswordInput) {
            currentPasswordInput.addEventListener('input', update);
        }

        form.querySelectorAll('[data-login-mode], [data-login-mode-toggle]').forEach((btn) => {
            if (btn.dataset.passwordPolicyBound === '1') {
                return;
            }
            btn.dataset.passwordPolicyBound = '1';
            btn.addEventListener('click', () => setTimeout(update, 0));
        });

        update();
    });
}

function isInputVisible(input) {
    if (!input) return false;
    if (input.closest('[hidden]')) return false;
    const style = window.getComputedStyle(input);
    if (style.display === 'none' || style.visibility === 'hidden') return false;
    return true;
}

function validatePasswordPolicy(rawValue) {
    const value = String(rawValue || '');
    if (value.length < 6) {
        return {
            valid: false,
            message: 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
        };
    }

    if (!/^[A-Za-z0-9]+$/.test(value)) {
        return {
            valid: false,
            message: 'رمز عبور فقط باید شامل حروف انگلیسی و اعداد باشد.',
        };
    }

    if (!/[A-Za-z]/.test(value) || !/\d/.test(value)) {
        return {
            valid: false,
            message: 'رمز عبور باید ترکیبی از حروف انگلیسی و اعداد باشد.',
        };
    }

    return { valid: true, message: '' };
}

function validatePasswordConfirmation(passwordRaw, confirmationRaw) {
    const password = String(passwordRaw || '');
    const confirmation = String(confirmationRaw || '');
    if (confirmation === '') {
        return { valid: false, message: 'تکرار رمز عبور را وارد کنید.' };
    }
    if (password !== confirmation) {
        return { valid: false, message: 'رمز عبور و تکرار آن یکسان نیست.' };
    }
    return { valid: true, message: '' };
}

function renderPasswordFeedback(input, state, key) {
    const field = input.closest('.field');
    if (!field) {
        return;
    }

    const nodeKey = `passwordPolicyNode_${key}`;
    let node = field.querySelector(`[data-${nodeKey}]`);
    if (!node) {
        node = document.createElement('div');
        node.setAttribute(`data-${nodeKey}`, '1');
        field.appendChild(node);
    }

    if (!state.message) {
        node.textContent = '';
        node.hidden = true;
        input.classList.remove('is-invalid');
        return;
    }

    node.hidden = false;
    node.textContent = state.message;
    node.className = 'field__error';
    input.classList.toggle('is-invalid', !state.valid);
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
