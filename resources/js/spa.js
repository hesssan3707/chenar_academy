document.addEventListener('DOMContentLoaded', () => {
    initSPA();
    initAuthModal();
    if (typeof window.initCart === 'function') {
        window.initCart();
    }
});

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
        try {
            const urlObj = new URL(url, window.location.origin);
            if (urlObj.origin !== window.location.origin) return;
        } catch (err) {
            console.error('URL parse error:', err);
            return; // Invalid URL
        }

        e.preventDefault();
        navigateTo(url);
    });

    // Handle browser back/forward
    window.addEventListener('popstate', () => {
        loadPage(window.location.href, 'back');
    });

    // Initial load setup
    setupCurrentPage();
}

let isAnimating = false;

function navigateTo(url) {
    if (isAnimating) return;
    history.pushState({}, '', url);
    loadPage(url, 'forward');
}

async function loadPage(url, direction = 'forward') {
    isAnimating = true;
    const container = document.getElementById('spa-content');
    
    // 1. Fetch new content
    try {
        const response = await fetch(url, {
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
        console.error('SPA Navigation Error:', error);
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
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
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
                    <p class="h4">سبد خرید شما خالی است</p>
                </div>
            `;
            if (footer) footer.classList.add('hidden');
        } else {
            itemsContainer.innerHTML = data.items.map(item => `
                <div class="panel p-4 bg-white/5 border border-white/10 rounded-xl flex items-center gap-4 group">
                    <div class="w-16 h-16 rounded-lg bg-white/10 overflow-hidden flex-shrink-0">
                        <img src="${item.thumb || '/assets/img/placeholder.png'}" alt="${item.title}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold text-white truncate mb-1">${item.title}</h4>
                        <div class="text-brand font-bold text-sm">${item.price.toLocaleString('fa-IR')} ${data.currency}</div>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="p-2 text-gray-500 hover:text-red-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
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
            refreshCart();
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
