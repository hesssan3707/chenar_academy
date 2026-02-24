document.addEventListener('DOMContentLoaded', () => {
    initSiteLoader();
    initSPA();
    initAuthModal();
    initPasswordSetupModal();
    if (typeof window.initCart === 'function') {
        window.initCart();
    }
});

let siteLoaderTokenCounter = 0;
let siteLoaderHideTimer = null;

function nowMs() {
    if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
        return performance.now();
    }
    return Date.now();
}

function getSiteLoaderEl() {
    const el = document.getElementById('site-loader');
    return el instanceof HTMLElement ? el : null;
}

function showSiteLoader() {
    const el = getSiteLoaderEl();
    if (!el) return 0;

    if (siteLoaderHideTimer) {
        clearTimeout(siteLoaderHideTimer);
        siteLoaderHideTimer = null;
    }

    siteLoaderTokenCounter += 1;
    const token = siteLoaderTokenCounter;
    el.dataset.siteLoaderToken = String(token);
    el.dataset.siteLoaderShownAt = String(nowMs());
    el.classList.remove('is-hidden');
    return token;
}

function hideSiteLoader(token, minDurationMs = 1000) {
    const el = getSiteLoaderEl();
    if (!el) return;

    const activeToken = Number(el.dataset.siteLoaderToken || '0');
    if (token !== activeToken) return;

    if (el.classList.contains('is-hidden')) {
        if (siteLoaderHideTimer) {
            clearTimeout(siteLoaderHideTimer);
            siteLoaderHideTimer = null;
        }
        return;
    }

    const shownAt = Number(el.dataset.siteLoaderShownAt || '0');
    const elapsed = nowMs() - shownAt;
    const remaining = Math.max(0, minDurationMs - elapsed);

    if (siteLoaderHideTimer) {
        clearTimeout(siteLoaderHideTimer);
    }

    siteLoaderHideTimer = setTimeout(() => {
        const currentToken = Number(el.dataset.siteLoaderToken || '0');
        if (currentToken !== token) return;
        el.classList.add('is-hidden');
        siteLoaderHideTimer = null;
    }, remaining);
}

function initSiteLoader() {
    const token = showSiteLoader();
    window.addEventListener('load', () => {
        hideSiteLoader(token, 1000);
    }, { once: true });
}

function getAppBasePathname() {
    const meta = document.querySelector('meta[name="api-base-url"]');
    const value = meta?.content;
    if (!value) return '';

    try {
        const baseUrl = new URL(value, window.location.origin);
        const path = (baseUrl.pathname || '/').replace(/\/$/, '');
        return path === '/' ? '' : path;
    } catch (e) {
        return '';
    }
}

function normalizeAppPathname(pathname) {
    const base = getAppBasePathname();
    const path = pathname || '';
    if (!base) return path;
    if (path === base) return '/';
    if (path.startsWith(base + '/')) return path.slice(base.length);
    return path;
}

function getSpaBgEl() {
    const el = document.getElementById('spa-bg');
    return el instanceof HTMLElement ? el : null;
}

function getSpaBgLayers(bgEl) {
    const a = bgEl.querySelector('[data-bg-layer="a"]');
    const b = bgEl.querySelector('[data-bg-layer="b"]');
    return {
        a: a instanceof HTMLElement ? a : null,
        b: b instanceof HTMLElement ? b : null,
    };
}

function buildSpaBgCss(url) {
    if (typeof url !== 'string' || url.trim() === '') {
        return '';
    }
    const safeUrl = url.replace(/"/g, '%22');
    return `linear-gradient(180deg, rgba(8, 12, 22, 0.55), rgba(8, 12, 22, 0.82)), url("${safeUrl}")`;
}

function getSpaBgUrlByGroup(bgEl, group) {
    if (group === 'home') return bgEl.dataset.bgHome || '';
    if (group === 'videos') return bgEl.dataset.bgVideos || '';
    if (group === 'booklets') return bgEl.dataset.bgBooklets || '';
    return bgEl.dataset.bgOther || '';
}

function preloadSpaBgUrl(url) {
    if (typeof url !== 'string' || url.trim() === '') {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        const img = new Image();
        const done = () => resolve();
        img.onload = done;
        img.onerror = done;
        img.src = url;
        if (img.complete) {
            resolve();
        }
    });
}

function getSpaBgGroupFromUrlString(urlString) {
    let urlObj;
    try {
        urlObj = new URL(urlString, window.location.origin);
    } catch (e) {
        return '';
    }

    const pathname = normalizeAppPathname(urlObj.pathname || '');
    const cleanPath = (pathname || '/').replace(/\/$/, '') || '/';
    const typeParam = (urlObj.searchParams.get('type') || '').trim().toLowerCase();

    if (cleanPath === '/') return 'home';
    if (cleanPath.startsWith('/videos')) return 'videos';
    if (cleanPath.startsWith('/booklets') || cleanPath.startsWith('/notes')) return 'booklets';
    if (cleanPath.startsWith('/products') && typeParam) {
        if (typeParam === 'video') return 'videos';
        if (typeParam === 'note') return 'booklets';
    }
    return 'other';
}

function setSpaBackgroundGroup(group, { direction = 'forward', animate = true } = {}) {
    const bg = getSpaBgEl();
    if (!bg) return;

    const normalizedGroup = typeof group === 'string' && group.trim() !== '' ? group.trim() : 'other';
    const currentGroup = bg.dataset.activeGroup || '';
    if (currentGroup === normalizedGroup) return;

    const layers = getSpaBgLayers(bg);
    if (!layers.a || !layers.b) return;

    const activeLayerKey = bg.dataset.activeLayer === 'b' ? 'b' : 'a';
    const nextLayerKey = activeLayerKey === 'a' ? 'b' : 'a';
    const activeLayer = layers[activeLayerKey];
    const nextLayer = layers[nextLayerKey];

    const url = getSpaBgUrlByGroup(bg, normalizedGroup);
    nextLayer.style.backgroundImage = buildSpaBgCss(url);

    const enterClass = direction === 'forward' ? 'is-enter-from-left' : 'is-enter-from-right';
    const leaveClass = direction === 'forward' ? 'is-leave-to-right' : 'is-leave-to-left';

    activeLayer.classList.remove('is-enter-from-left', 'is-enter-from-right', 'is-leave-to-left', 'is-leave-to-right');
    nextLayer.classList.remove('is-enter-from-left', 'is-enter-from-right', 'is-leave-to-left', 'is-leave-to-right');

    if (!activeLayer.classList.contains('is-visible')) {
        activeLayer.classList.add('is-visible');
    }

    if (!animate) {
        nextLayer.classList.add('is-visible');
        activeLayer.classList.remove('is-visible');
        bg.dataset.activeLayer = nextLayerKey;
        bg.dataset.activeGroup = normalizedGroup;
        return;
    }

    nextLayer.classList.add('is-visible');
    nextLayer.classList.add(enterClass);
    activeLayer.classList.add(leaveClass);

    nextLayer.offsetHeight;

    requestAnimationFrame(() => {
        nextLayer.classList.remove(enterClass);
        activeLayer.classList.remove('is-visible');
    });

    setTimeout(() => {
        activeLayer.classList.remove(leaveClass);
        nextLayer.classList.remove('is-enter-from-left', 'is-enter-from-right', 'is-leave-to-left', 'is-leave-to-right');
        bg.dataset.activeLayer = nextLayerKey;
        bg.dataset.activeGroup = normalizedGroup;
    }, 420);
}

function updateSpaBackground() {
    const page = document.querySelector('#spa-content .spa-page[data-bg-group]');
    const groupFromDom = page instanceof HTMLElement ? (page.dataset.bgGroup || '') : '';
    const group = groupFromDom !== '' ? groupFromDom : getSpaBgGroupFromUrlString(window.location.href);
    setSpaBackgroundGroup(group, { animate: false });
}

function isPanelRoutePath(pathname) {
    return normalizeAppPathname(pathname).startsWith('/panel');
}

function isStreamRoutePath(pathname) {
    return normalizeAppPathname(pathname).includes('/stream');
}

function getMainNavDirection(targetLink) {
    const items = Array.from(document.querySelectorAll('.spa-nav a.spa-nav-item'));
    const targetIndex = items.indexOf(targetLink);
    if (targetIndex === -1) return 'forward';

    const currentPath = normalizeAppPathname(new URL(window.location.href, window.location.origin).pathname).replace(/\/$/, '');
    const currentIndex = items.findIndex((item) => {
        if (!item.href || item.href.includes('#')) return false;
        let itemPath = '';
        try {
            itemPath = normalizeAppPathname(new URL(item.href, window.location.origin).pathname).replace(/\/$/, '');
        } catch (e) {
            return false;
        }

        if (item.dataset.spaNav === 'home') {
            return itemPath === currentPath;
        }

        if (itemPath === currentPath) return true;
        if (currentPath.startsWith(itemPath)) {
            const nextChar = currentPath.charAt(itemPath.length);
            if (itemPath.endsWith('/') || nextChar === '/' || nextChar === '') {
                return true;
            }
        }
        return false;
    });

    if (currentIndex === -1 || currentIndex === targetIndex) return 'forward';
    return targetIndex > currentIndex ? 'forward' : 'back';
}

function initSPA() {
    // Intercept clicks on links
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        // Skip if link has target="_blank" or data-spa-ignore
        if (link.target === '_blank' || link.hasAttribute('data-spa-ignore')) return;

        const url = link.getAttribute('href');
        if (!url || url.startsWith('#') || url.startsWith('javascript:')) return;

        const currentUrl = window.location.href;

        if (url === currentUrl) {
            e.preventDefault();
            return;
        }

        // Check if it's a same-origin link
        let urlObj;
        try {
            urlObj = new URL(url, window.location.origin);
            if (urlObj.origin !== window.location.origin) return;
        } catch (err) {
            console.error('URL parse error:', err);
            return; // Invalid URL
        }

        if (link.hasAttribute('download')) return;
        if (isStreamRoutePath(urlObj.pathname || '')) return;

        e.preventDefault();

        const isPanelPath = isPanelRoutePath(urlObj.pathname || '');
        const isCurrentlyInPanel = isPanelRoutePath(window.location.pathname || '');
        const isInsidePanelShell = !!link.closest('[data-panel-shell]');
        const isPanelNavLink = !!link.closest('[data-panel-nav]');
        const shouldUsePanelMainTransition = isPanelPath && (isCurrentlyInPanel || isPanelNavLink || isInsidePanelShell);

        const direction = !shouldUsePanelMainTransition && link.classList.contains('spa-nav-item')
            ? getMainNavDirection(link)
            : 'forward';

        navigateTo(url, shouldUsePanelMainTransition ? 'panel-main' : 'full', direction);
    });

    // Handle browser back/forward
    window.addEventListener('popstate', () => {
        const container = document.getElementById('spa-content');
        const isInPanelShell = !!container?.querySelector('[data-panel-shell]');
        const nextPath = (() => {
            try {
                return new URL(window.location.href, window.location.origin).pathname || '';
            } catch (e) {
                return '';
            }
        })();
        loadPage(window.location.href, 'back', isInPanelShell && isPanelRoutePath(nextPath) ? 'panel-main' : 'auto');
    });

    // Initial load setup
    setupCurrentPage();
}

let isAnimating = false;

function setPanelMainLoading(isLoading) {
    const container = document.getElementById('spa-content');
    const main = container?.querySelector('[data-panel-main]');
    if (!main) return;

    let loader = main.querySelector('.panel-main-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'panel-main-loader';
        loader.innerHTML = `
            <div class="panel-main-hourglass" role="img" aria-label="در حال بارگذاری">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 2h12"></path>
                    <path d="M6 22h12"></path>
                    <path d="M6 2v6a6 6 0 0 0 6 6a6 6 0 0 0 6-6V2"></path>
                    <path d="M6 22v-6a6 6 0 0 1 6-6a6 6 0 0 1 6 6v6"></path>
                </svg>
            </div>
        `.trim();
        main.appendChild(loader);
    }

    main.classList.toggle('panel-main-is-loading', isLoading);
}

function navigateTo(url, mode = 'full', direction = 'forward') {
    if (isAnimating) return;
    history.pushState({}, '', url);
    loadPage(url, direction, mode);
}

async function loadPage(url, direction = 'forward', mode = 'auto') {
    isAnimating = true;
    const container = document.getElementById('spa-content');
    const currentPanelShell = container?.querySelector('[data-panel-shell]');
    const targetPath = (() => {
        try {
            return new URL(url, window.location.origin).pathname || '';
        } catch (e) {
            return '';
        }
    })();
    const shouldShowPanelLoader = mode === 'panel-main' || (mode === 'auto' && currentPanelShell && isPanelRoutePath(targetPath));
    const panelMainFadeDurationMs = 180;
    const currentMainForLoader = shouldShowPanelLoader ? container?.querySelector('[data-panel-main]') : null;
    const panelMainFadeStartedAt = (() => {
        if (!currentMainForLoader) return null;
        if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
            return performance.now();
        }
        return Date.now();
    })();

    if (shouldShowPanelLoader) {
        setPanelMainLoading(true);
        if (currentMainForLoader) {
            currentMainForLoader.classList.add('panel-main-is-fading');
        }
    }
    
    // 1. Fetch new content
    try {
        const response = await fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-SPA-Request': 'true'
            }
        });
        
        if (!response.ok) throw new Error('Network response was not ok');
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#spa-content');
        const newTitle = doc.querySelector('title');

        if (!newContent) {
            window.location.reload(); // Fallback if SPA content not found
            return;
        }

        if (newTitle) document.title = newTitle.innerText;

        const newPageForBg = newContent.firstElementChild;
        const nextGroupFromDom = newPageForBg instanceof HTMLElement ? (newPageForBg.dataset.bgGroup || '') : '';
        const nextBgGroup = nextGroupFromDom !== '' ? nextGroupFromDom : getSpaBgGroupFromUrlString(url);

        const bgEl = getSpaBgEl();
        if (bgEl) {
            const nextUrl = getSpaBgUrlByGroup(bgEl, nextBgGroup);
            try {
                await Promise.race([
                    preloadSpaBgUrl(nextUrl),
                    new Promise((resolve) => setTimeout(resolve, 900)),
                ]);
            } catch (e) {
            }
        }

        const nextPanelShell = newContent.querySelector('[data-panel-shell]');
        const shouldSwapPanelMain = mode === 'panel-main' || (mode === 'auto' && currentPanelShell && nextPanelShell);

        if (shouldSwapPanelMain) {
            const currentMain = container.querySelector('[data-panel-main]');
            const nextMain = newContent.querySelector('[data-panel-main]');

            if (currentMain && nextMain) {
                const loader = currentMain.querySelector('.panel-main-loader');
                const now = (() => {
                    if (typeof performance !== 'undefined' && typeof performance.now === 'function') {
                        return performance.now();
                    }
                    return Date.now();
                })();
                const elapsed = panelMainFadeStartedAt ? (now - panelMainFadeStartedAt) : panelMainFadeDurationMs;
                const remaining = Math.max(0, panelMainFadeDurationMs - elapsed);

                if (remaining > 0) {
                    await new Promise((resolve) => setTimeout(resolve, remaining));
                }

                currentMain.innerHTML = nextMain.innerHTML;
                if (loader) currentMain.appendChild(loader);
                currentMain.scrollTop = 0;

                requestAnimationFrame(() => {
                    currentMain.classList.remove('panel-main-is-fading');
                    currentMain.classList.remove('panel-main-is-loading');
                });

                isAnimating = false;
                setupCurrentPage();

                updateActiveNav(url);
                return;
            }

            if (mode === 'panel-main') {
                isAnimating = false;
                window.location.href = url;
                return;
            }
        }

        if (shouldShowPanelLoader) {
            setPanelMainLoading(false);
            if (currentMainForLoader) {
                currentMainForLoader.classList.remove('panel-main-is-fading');
            }
        }

        // 2. Prepare for animation
        const currentContent = container.firstElementChild;
        if (currentContent) {
            currentContent.classList.add('page-leave-active');
            // In RTL: forward means next page comes from left, current page goes to right
            currentContent.classList.add(direction === 'forward' ? 'slide-leave-to-right' : 'slide-leave-to-left');
        }

        // 3. Insert new content
        const newPage = newContent.firstElementChild; 
        if (newPage) {
            setSpaBackgroundGroup(nextBgGroup, { direction, animate: true });

            newPage.classList.add('page-enter-active');
            newPage.classList.add(direction === 'forward' ? 'slide-enter-from-left' : 'slide-enter-from-right');
            
            container.appendChild(newPage);
            
            // Force reflow
            newPage.offsetHeight;

            // Start animation
            requestAnimationFrame(() => {
                newPage.classList.remove('slide-enter-from-left', 'slide-enter-from-right');
            });

            // Cleanup after animation
            setTimeout(() => {
                if (currentContent) {
                    currentContent.remove();
                }
                newPage.classList.remove('page-enter-active');
                isAnimating = false;
                setupCurrentPage();
            }, 400); 
        } else {
            // If structure is different, just replace innerHTML
            container.innerHTML = newContent.innerHTML;
            isAnimating = false;
            setupCurrentPage();
        }

        updateActiveNav(url);

    } catch (error) {
        if (shouldShowPanelLoader) {
            setPanelMainLoading(false);
            if (currentMainForLoader) {
                currentMainForLoader.classList.remove('panel-main-is-fading');
            }
        }
        console.error('SPA Navigation Error:', error);
        isAnimating = false;
        window.location.href = url; // Fallback
    }
}

function setupCurrentPage() {
    // Re-initialize any JS plugins for the new page
    if (typeof window.initApp === 'function') {
        window.initApp();
    }
    
    // Highlight active nav item
    updateActiveNav(window.location.href);
    updatePanelNav(window.location.href);
    initPanelSidebarUi();
    updateSpaBackground();

    // Intercept Add to Cart forms
    document.querySelectorAll('form[action*="/cart/items"]').forEach(form => {
        if (form.dataset.ajaxIntercepted) return;
        form.dataset.ajaxIntercepted = "true";
        form.addEventListener('submit', handleCartSubmit);
    });

    // Intercept Contact Form
    const contactForm = document.querySelector('form[action*="/contact"]');
    if (contactForm && !contactForm.dataset.ajaxIntercepted) {
        contactForm.dataset.ajaxIntercepted = "true";
        contactForm.addEventListener('submit', handleContactSubmit);
    }
}

async function handleCartSubmit(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const url = form.action;
    const formData = new FormData(form);
    const token = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            refreshCart();
            openModal('cart-modal');
            if (window.showToast) window.showToast({ type: 'success', title: 'افزوده شد', message: data.message });
        } else if (data.message) {
            if (window.showToast) window.showToast({ type: 'warning', title: 'هشدار', message: data.message });
        }
    } catch (error) {
        console.error('Add to Cart Error:', error);
    }
}

async function handleContactSubmit(e) {
    e.preventDefault();
    const form = e.currentTarget;
    const url = form.action;
    const formData = new FormData(form);
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    submitBtn.disabled = true;
    submitBtn.textContent = 'در حال ارسال...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();
        if (response.ok) {
            if (window.showToast) window.showToast({ type: 'success', title: 'موفق', message: data.message || 'پیام شما با موفقیت ارسال شد.' });
            form.reset();
        } else {
            const errorMsg = data.message || 'خطا در ارسال پیام';
            if (window.showToast) window.showToast({ type: 'danger', title: 'خطا', message: errorMsg });
        }
    } catch (error) {
        console.error('Contact Submit Error:', error);
        if (window.showToast) window.showToast({ type: 'danger', title: 'خطا', message: 'خطا در برقراری ارتباط با سرور' });
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

function updateActiveNav(url) {
    const navItems = document.querySelectorAll('.spa-nav-item');
    const currentPath = new URL(url).pathname;

    navItems.forEach(item => {
        if (!item.href || item.href.includes('#')) return;
        
        const itemPath = new URL(item.href).pathname;
        const spaNav = item.dataset.spaNav;
        
        let isActive = false;

        if (spaNav === 'home') {
             // Home is active only on exact match (handling optional trailing slash)
             const cleanItemPath = itemPath.replace(/\/$/, '');
             const cleanCurrentPath = currentPath.replace(/\/$/, '');
             isActive = cleanItemPath === cleanCurrentPath;
        } else {
             // Other items: exact match OR prefix match with boundary
             if (itemPath === currentPath) {
                 isActive = true;
             } else if (currentPath.startsWith(itemPath)) {
                 // Ensure boundary to avoid partial matches (e.g. /car vs /cart)
                 const nextChar = currentPath.charAt(itemPath.length);
                 if (itemPath.endsWith('/') || nextChar === '/') {
                     isActive = true;
                 }
             }
        }

        if (isActive) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

function updatePanelNav(url) {
    const panelNav = document.querySelector('[data-panel-nav]');
    if (!panelNav) return;

    const currentPath = new URL(url).pathname.replace(/\/$/, '');

    panelNav.querySelectorAll('a.panel-nav-link').forEach(link => {
        if (!link.href || link.href.includes('#')) return;

        const itemPath = new URL(link.href).pathname.replace(/\/$/, '');

        let isActive = false;
        if (itemPath === currentPath) {
            isActive = true;
        } else if (currentPath.startsWith(itemPath)) {
            const nextChar = currentPath.charAt(itemPath.length);
            if (itemPath.endsWith('/') || nextChar === '/' || nextChar === '') {
                isActive = true;
            }
        }

        if (isActive) {
            link.classList.add('bg-white/10');
            link.setAttribute('aria-current', 'page');
        } else {
            link.classList.remove('bg-white/10');
            link.removeAttribute('aria-current');
        }
    });
}

let panelSidebarUiCleanup = null;

function initPanelSidebarUi() {
    if (typeof panelSidebarUiCleanup === 'function') {
        panelSidebarUiCleanup();
        panelSidebarUiCleanup = null;
    }

    const panelShell = document.querySelector('[data-panel-shell]');
    if (!panelShell) return;

    const sidebar = document.querySelector('[data-panel-sidebar]');
    if (!sidebar) return;

    const body = document.body;

    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'panel-menu-toggle';
    toggleButton.setAttribute('aria-label', 'منوی پنل');
    toggleButton.setAttribute('aria-controls', 'panel-sidebar');
    toggleButton.setAttribute('aria-expanded', 'false');
    toggleButton.innerHTML = `
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 6h16"></path>
            <path d="M4 12h16"></path>
            <path d="M4 18h16"></path>
        </svg>
    `.trim();

    const backdrop = document.createElement('div');
    backdrop.className = 'panel-sidebar-backdrop';

    const setOpen = (isOpen) => {
        body.classList.toggle('panel-sidebar-open', isOpen);
        toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    const onToggleClick = () => {
        setOpen(!body.classList.contains('panel-sidebar-open'));
    };

    const onBackdropClick = () => setOpen(false);

    const onKeyDown = (e) => {
        if (e.key === 'Escape') setOpen(false);
    };

    const onDocClickCapture = (e) => {
        const link = e.target?.closest?.('a.panel-nav-link');
        if (!link) return;
        setOpen(false);
    };

    toggleButton.addEventListener('click', onToggleClick);
    backdrop.addEventListener('click', onBackdropClick);
    window.addEventListener('keydown', onKeyDown);
    document.addEventListener('click', onDocClickCapture, true);

    body.appendChild(backdrop);
    body.appendChild(toggleButton);

    panelSidebarUiCleanup = () => {
        setOpen(false);
        toggleButton.removeEventListener('click', onToggleClick);
        backdrop.removeEventListener('click', onBackdropClick);
        window.removeEventListener('keydown', onKeyDown);
        document.removeEventListener('click', onDocClickCapture, true);
        toggleButton.remove();
        backdrop.remove();
    };
}

// Global helpers for modals
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
};

// Auth Modal Logic
window.switchAuthView = function(viewName) {
    const views = ['login', 'register', 'forgot'];
    const titles = {
        'login': 'ورود',
        'register': 'ثبت نام',
        'forgot': 'بازیابی رمز عبور'
    };

    views.forEach(view => {
        const el = document.getElementById(`auth-view-${view}`);
        if (el) el.hidden = (view !== viewName);
    });

    const titleEl = document.getElementById('auth-title');
    if (titleEl && titles[viewName]) {
        titleEl.textContent = titles[viewName];
    }
};

function initAuthModal() {
    // Login Method Toggle (Password <-> OTP)
    const btnToggleLogin = document.getElementById('btn-toggle-login-method');
    if (btnToggleLogin) {
        // Remove existing listener to prevent duplicates
        const newBtn = btnToggleLogin.cloneNode(true);
        btnToggleLogin.parentNode.replaceChild(newBtn, btnToggleLogin);
        
        newBtn.addEventListener('click', () => {
            const passwordGroup = document.getElementById('login-password-group');
            const otpGroup = document.getElementById('login-otp-group');
            const actionInput = document.getElementById('login-action');
            
            if (passwordGroup.hidden) {
                // Switch to Password
                passwordGroup.hidden = false;
                otpGroup.hidden = true;
                actionInput.value = 'login_password';
                newBtn.textContent = 'ورود با رمز یکبار مصرف';
            } else {
                // Switch to OTP
                passwordGroup.hidden = true;
                otpGroup.hidden = false;
                actionInput.value = 'login_otp';
                newBtn.textContent = 'ورود با رمز عبور';
            }
        });
    }

    // Send OTP Handlers
    const setupOtpBtn = (btnId, phoneInputId, purpose) => {
        const btn = document.getElementById(btnId);
        const phoneInput = document.getElementById(phoneInputId);
        
        if (btn && phoneInput) {
            // Remove existing listener to prevent duplicates
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', async () => {
                const phone = phoneInput.value;
                if (!phone) {
                    alert('لطفا شماره موبایل را وارد کنید');
                    return;
                }
                
                newBtn.disabled = true;
                newBtn.textContent = 'در حال ارسال...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    const url = document.querySelector('meta[name="otp-send-url"]').content;
                    
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone, purpose })
                    });

                    const data = await response.json();

                    if (response.ok && data.ok) {
                        if (window.showToast) {
                            window.showToast(data.toast);
                        } else {
                            alert(data.toast?.message || 'کد تایید ارسال شد');
                        }
                        
                        // Start cooldown timer
                        let cooldown = data.cooldown_seconds || 60;
                        const timer = setInterval(() => {
                            cooldown--;
                            if (cooldown <= 0) {
                                clearInterval(timer);
                                newBtn.disabled = false;
                                newBtn.textContent = 'ارسال مجدد کد';
                            } else {
                                newBtn.textContent = `${cooldown} ثانیه`;
                            }
                        }, 1000);
                    } else {
                        const errorMsg = data.message || 'خطا در ارسال کد';
                        if (window.showToast) {
                            window.showToast({
                                type: 'danger',
                                title: 'خطا',
                                message: errorMsg
                            });
                        } else {
                            alert(errorMsg);
                        }
                        throw new Error(errorMsg);
                    }
                } catch (error) {
                    console.error(error);
                    alert(error.message || 'خطا در برقراری ارتباط');
                    newBtn.disabled = false;
                    newBtn.textContent = 'ارسال کد';
                }
            });
        }
    };

    setupOtpBtn('btn-send-otp-login', 'login-phone', 'login');
    setupOtpBtn('btn-send-otp-register', 'register-phone', 'register');
    setupOtpBtn('btn-send-otp-forgot', 'forgot-phone', 'password_reset');
}

function initPasswordSetupModal() {
    const meta = document.querySelector('meta[name="force-password-setup"]');
    if (!meta) return;

    const value = String(meta.content || '').trim();
    if (value !== '1') return;

    if (typeof window.openModal === 'function') {
        window.openModal('password-setup-modal');
        return;
    }

    const modal = document.getElementById('password-setup-modal');
    if (modal) modal.classList.add('active');
}

// Cart Logic
window.toggleCart = function() {
    const modal = document.getElementById('cart-modal');
    if (!modal) return;
    
    const isActive = modal.classList.contains('active');

    if (!isActive) {
        refreshCart();
        window.openModal('cart-modal');
    } else {
        window.closeModal('cart-modal');
    }
};

async function refreshCart() {
    console.log('Refreshing cart...');
    const itemsContainer = document.getElementById('cart-modal-items');
    const footer = document.getElementById('cart-modal-footer');
    const totalPrice = document.getElementById('cart-total-price');
    const metaCartUrl = document.querySelector('meta[name="cart-url"]');
    
    if (!itemsContainer || !metaCartUrl) return;
    const cartUrl = metaCartUrl.content;

    try {
        const response = await fetch(cartUrl, {
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        });

        if (!response.ok) {
            throw new Error('Cart fetch failed: ' + response.status);
        }

        const data = await response.json();
        updateCartBadge(data.count);

        if (data.count === 0) {
            itemsContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 opacity-50 text-center w-full">
                    <svg class="w-16 h-16 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p class="h4 text-center w-full">سبد خرید شما خالی است</p>
                </div>
            `;
            if (footer) footer.classList.add('hidden');
            if (totalPrice) totalPrice.textContent = '';
        } else {
            itemsContainer.innerHTML = data.items.map(item => `
                <div class="cart-item">
                    <div class="cart-item__meta">
                        <div class="cart-item__title">${item.title}</div>
                    </div>
                    <div class="cart-item__price" style="font-weight: 800; color: var(--ds-brand);">
                        ${item.price.toLocaleString('fa-IR')} ${data.currency}
                    </div>
                    <div class="cart-item__actions">
                        <button onclick="removeFromCart(${item.id})" class="cart-remove-btn" aria-label="حذف آیتم">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            `).join('');
            
            if (totalPrice) totalPrice.textContent = `${data.subtotal.toLocaleString('fa-IR')} ${data.currency}`;
            if (footer) footer.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Refresh Cart Error:', error);
        itemsContainer.innerHTML = '<p class="text-center py-4 text-red-500">خطا در بارگذاری سبد خرید</p>';
    }
}

window.removeFromCart = async function(itemId) {
    const metaCartUrl = document.querySelector('meta[name="cart-url"]');
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (!metaCartUrl || !metaToken) return;

    const cartUrl = metaCartUrl.content;
    const token = metaToken.content;

    try {
        const response = await fetch(`${cartUrl}/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();
        if (data.success) {
            await refreshCart();
            if (window.showToast) window.showToast({ type: 'success', title: 'حذف شد', message: data.message });
        }
    } catch (error) {
        console.error('Remove Item Error:', error);
    }
};

function updateCartBadge(count) {
    const badge = document.getElementById('cart-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

window.initCart = function() {
    const metaCartUrl = document.querySelector('meta[name="cart-url"]');
    if (!metaCartUrl) return;
    
    const cartUrl = metaCartUrl.content;
    fetch(cartUrl, { 
        headers: { 
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        } 
    })
        .then(res => res.json())
        .then(data => updateCartBadge(data.count))
        .catch(() => {});
};
