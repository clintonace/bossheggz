/**
 * SeggzApp front-end behaviour (vanilla JS, driven by data-* attributes).
 *
 *  - Top click loader on every navigation, form submit and button click
 *  - Dropdowns / mobile menu toggles   [data-toggle="#target"]
 *  - Property gallery + lightbox        [data-gallery]
 *  - Delete confirmations               [data-confirm="message"]
 *  - Scroll reveal                      [data-reveal]
 *  - Upload previews                    [data-image-preview="#target"]
 *  - Auto-dismissing toasts             [data-toast]
 *  - Pop-up (major) advert              [data-advert-popup]
 *  - Advert form placement dropdowns    [data-advert-form]
 *  - Light / dark / auto switch         [data-appearance-form]
 */

/* ------------------------------------------------------------------ */
/* Top click loader                                                    */
/* ------------------------------------------------------------------ */
const loader = (() => {
    const element = document.getElementById('page-loader');
    const bar = element?.querySelector('.bar');
    let progress = 0;
    let trickleTimer = null;
    let hideTimer = null;

    const render = () => {
        if (bar) {
            bar.style.transform = `scaleX(${progress})`;
        }
    };

    const start = () => {
        if (!element) return;
        clearTimeout(hideTimer);
        clearInterval(trickleTimer);
        element.classList.add('is-active');
        progress = Math.max(progress, 0.08);
        render();

        trickleTimer = setInterval(() => {
            const remaining = 0.94 - progress;
            progress += remaining * (progress < 0.5 ? 0.12 : 0.05);
            render();
        }, 220);
    };

    const done = () => {
        if (!element) return;
        clearInterval(trickleTimer);
        progress = 1;
        render();
        hideTimer = setTimeout(() => {
            element.classList.remove('is-active');
            setTimeout(() => {
                progress = 0;
                if (bar) {
                    bar.style.transition = 'none';
                    render();
                    void bar.offsetWidth;
                    bar.style.transition = '';
                }
            }, 300);
        }, 250);
    };

    /** A short sweep for click actions that don't leave the page. */
    const pulse = () => {
        start();
        setTimeout(done, 350);
    };

    return { start, done, pulse };
})();

window.PageLoader = loader;

const isNavigatingLink = (link, event) => {
    if (event.defaultPrevented || event.button !== 0) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download') || link.dataset.noLoader !== undefined) return false;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
        return false;
    }

    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin) return false;

    const samePageAnchor = url.pathname === window.location.pathname && url.search === window.location.search && url.hash;
    return !samePageAnchor;
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (link) {
        if (isNavigatingLink(link, event)) {
            loader.start();
        }
        return;
    }

    const button = event.target.closest('button, [role="button"]');
    if (button && button.type !== 'submit' && button.dataset.noLoader === undefined) {
        loader.pulse();
    }
});

document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;

    const form = event.target;
    loader.start();

    // Defer so the clicked submit button's name/value is still sent with the form.
    setTimeout(() => {
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.disabled = true;
            if (button.dataset.loadingText) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = `<span class="inline-block size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></span>${button.dataset.loadingText}`;
            }
        });
    }, 0);
});

window.addEventListener('pageshow', (event) => {
    loader.done();

    if (event.persisted) {
        document.querySelectorAll('button[disabled][data-original-text]').forEach((button) => {
            button.innerHTML = button.dataset.originalText;
        });
        document.querySelectorAll('form button[disabled]').forEach((button) => {
            button.disabled = false;
        });
    }
});

/* ------------------------------------------------------------------ */
/* Confirmations — runs in the capture phase, before the loader.       */
/* ------------------------------------------------------------------ */
document.addEventListener(
    'submit',
    (event) => {
        const message = event.target.dataset.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    },
    true,
);

/* ------------------------------------------------------------------ */
/* Toggles: dropdowns, mobile menu, filter drawers                     */
/* ------------------------------------------------------------------ */
/**
 * A trigger may set data-toggle-class="is-open" to toggle a class instead of
 * the hidden attribute (useful for elements that are always visible on desktop).
 */
const isOpen = (trigger, target) =>
    trigger.dataset.toggleClass ? target.classList.contains(trigger.dataset.toggleClass) : !target.hidden;

const setOpen = (trigger, target, open) => {
    if (trigger.dataset.toggleClass) {
        target.classList.toggle(trigger.dataset.toggleClass, open);
    } else {
        target.hidden = !open;
    }
    trigger.setAttribute('aria-expanded', String(open));
};

const closeAllToggles = (except = null) => {
    document.querySelectorAll('[data-toggle][aria-expanded="true"]').forEach((trigger) => {
        const target = document.querySelector(trigger.dataset.toggle);
        if (target && target !== except && trigger.dataset.togglePersist === undefined) {
            setOpen(trigger, target, false);
        }
    });
};

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-toggle]');

    if (trigger) {
        const target = document.querySelector(trigger.dataset.toggle);
        if (!target) return;
        event.preventDefault();
        const willOpen = !isOpen(trigger, target);
        closeAllToggles(target);
        setOpen(trigger, target, willOpen);

        // Don't let flash messages cover a menu that was just opened.
        if (willOpen) document.querySelectorAll('[data-toast]').forEach((toast) => toast.remove());
        return;
    }

    if (!event.target.closest('[data-toggle-panel]')) {
        closeAllToggles();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeAllToggles();
});

/* ------------------------------------------------------------------ */
/* Gallery + lightbox                                                   */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const images = JSON.parse(gallery.dataset.gallery || '[]');
    if (!images.length) return;

    const main = gallery.querySelector('[data-gallery-main]');
    const counter = gallery.querySelector('[data-gallery-counter]');
    const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
    const lightbox = document.querySelector(gallery.dataset.lightbox || '#lightbox');
    const lightboxImage = lightbox?.querySelector('[data-lightbox-image]');
    const lightboxCounter = lightbox?.querySelector('[data-lightbox-counter]');
    let index = 0;

    const show = (next) => {
        index = (next + images.length) % images.length;

        if (main) {
            main.style.opacity = '0';
            setTimeout(() => {
                main.src = images[index];
                main.style.opacity = '1';
            }, 150);
        }

        if (lightboxImage) lightboxImage.src = images[index];
        const label = `${index + 1} / ${images.length}`;
        if (counter) counter.textContent = label;
        if (lightboxCounter) lightboxCounter.textContent = label;

        thumbs.forEach((thumb, thumbIndex) => {
            thumb.classList.toggle('ring-brass-500', thumbIndex === index);
            thumb.classList.toggle('ring-transparent', thumbIndex !== index);
            thumb.classList.toggle('opacity-60', thumbIndex !== index);
        });
    };

    const openLightbox = () => {
        if (!lightbox) return;
        lightboxImage.src = images[index];
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    };

    const closeLightbox = () => {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
    };

    thumbs.forEach((thumb, thumbIndex) => thumb.addEventListener('click', () => show(thumbIndex)));
    gallery.querySelectorAll('[data-gallery-prev]').forEach((button) => button.addEventListener('click', () => show(index - 1)));
    gallery.querySelectorAll('[data-gallery-next]').forEach((button) => button.addEventListener('click', () => show(index + 1)));
    gallery.querySelectorAll('[data-gallery-open]').forEach((element) => element.addEventListener('click', openLightbox));

    lightbox?.querySelector('[data-lightbox-close]')?.addEventListener('click', closeLightbox);
    lightbox?.querySelector('[data-lightbox-prev]')?.addEventListener('click', () => show(index - 1));
    lightbox?.querySelector('[data-lightbox-next]')?.addEventListener('click', () => show(index + 1));
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (event) => {
        if (!lightbox || lightbox.hidden) return;
        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') show(index - 1);
        if (event.key === 'ArrowRight') show(index + 1);
    });

    show(0);
});

/* ------------------------------------------------------------------ */
/* Scroll reveal                                                        */
/* ------------------------------------------------------------------ */
const revealTargets = document.querySelectorAll('[data-reveal]');

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const delay = Number(entry.target.dataset.revealDelay || 0);
                    setTimeout(() => entry.target.classList.add('is-visible'), delay);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );
    revealTargets.forEach((element) => observer.observe(element));
} else {
    revealTargets.forEach((element) => element.classList.add('is-visible'));
}

/* ------------------------------------------------------------------ */
/* Header shadow on scroll                                              */
/* ------------------------------------------------------------------ */
const header = document.querySelector('[data-sticky-header]');
if (header) {
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/* ------------------------------------------------------------------ */
/* Toasts                                                               */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-toast]').forEach((toast) => {
    const dismiss = () => {
        toast.style.transition = 'opacity .3s ease, transform .3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        setTimeout(() => toast.remove(), 300);
    };
    toast.querySelector('[data-toast-close]')?.addEventListener('click', dismiss);
    setTimeout(dismiss, Number(toast.dataset.toast || 6000));
});

/* ------------------------------------------------------------------ */
/* Upload previews                                                      */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-image-preview]').forEach((input) => {
    const target = document.querySelector(input.dataset.imagePreview);
    if (!target) return;

    input.addEventListener('change', () => {
        target.innerHTML = '';
        Array.from(input.files || []).forEach((file) => {
            const figure = document.createElement('figure');
            figure.className = 'relative aspect-[4/3] overflow-hidden rounded-xl bg-ink-100 ring-1 ring-ink-200';
            const image = document.createElement('img');
            image.className = 'size-full object-cover';
            image.alt = file.name;
            image.src = URL.createObjectURL(file);
            const caption = document.createElement('figcaption');
            caption.className = 'absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-ink-950/70 px-2 pt-4 pb-1.5 text-[11px] text-white';
            caption.textContent = file.name;
            figure.append(image, caption);
            target.append(figure);
        });
        target.hidden = target.children.length === 0;
    });
});

/* ------------------------------------------------------------------ */
/* Listing type toggles rent-period field                               */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-rent-toggle]').forEach((select) => {
    const target = document.querySelector(select.dataset.rentToggle);
    const sync = () => {
        const selected = select.querySelector('input:checked')?.value ?? select.value;
        if (target) target.hidden = selected !== 'rent';
    };
    select.addEventListener('change', sync);
    sync();
});

/* ------------------------------------------------------------------ */
/* Copy to clipboard                                                    */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            const original = button.innerHTML;
            button.textContent = 'Copied!';
            setTimeout(() => (button.innerHTML = original), 1500);
        } catch {
            // Clipboard unavailable — ignore.
        }
    });
});

/* ------------------------------------------------------------------ */
/* Pop-up (major) advert: once per visit, auto-closes after N seconds. */
/* Clicking anywhere on the advert opens the advertiser; only ✕ closes. */
/* ------------------------------------------------------------------ */
const popup = document.querySelector('[data-advert-popup]');

if (popup) {
    const storageKey = `advert-popup-seen-${popup.dataset.advertPopup}`;
    const seconds = Math.max(3, Number(popup.dataset.seconds || 8));
    const progress = popup.querySelector('[data-advert-popup-progress]');
    const countdown = popup.querySelector('[data-advert-popup-countdown]');
    let timers = [];

    const alreadySeen = () => {
        try {
            return sessionStorage.getItem(storageKey) === '1';
        } catch {
            return false;
        }
    };

    const close = () => {
        timers.forEach(clearTimeout);
        timers = [];
        popup.hidden = true;
        document.body.style.overflow = '';
    };

    const open = () => {
        try {
            sessionStorage.setItem(storageKey, '1');
        } catch {
            // Storage unavailable — the pop-up may show again on the next page.
        }

        popup.hidden = false;
        document.body.style.overflow = 'hidden';
        popup.querySelector('[data-advert-popup-close]')?.focus({ preventScroll: true });

        if (progress) {
            progress.style.transition = 'none';
            progress.style.transform = 'scaleX(1)';
            void progress.offsetWidth;
            progress.style.transition = `transform ${seconds}s linear`;
            progress.style.transform = 'scaleX(0)';
        }

        for (let remaining = seconds - 1; remaining >= 0; remaining--) {
            timers.push(setTimeout(() => countdown && (countdown.textContent = String(remaining)), (seconds - remaining) * 1000));
        }
        timers.push(setTimeout(close, seconds * 1000));
    };

    popup.querySelector('[data-advert-popup-close]')?.addEventListener('click', (event) => {
        event.stopPropagation();
        close();
    });
    popup.querySelector('[data-advert-popup-link]')?.addEventListener('click', () => setTimeout(close, 0));
    popup.addEventListener('click', (event) => {
        if (event.target === popup) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !popup.hidden) close();
    });

    if (!alreadySeen()) {
        setTimeout(open, 900);
    }
}

/* ------------------------------------------------------------------ */
/* Advert form: format → fields, page → sections, section → positions  */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-advert-form]').forEach((form) => {
    const pageSelect = form.querySelector('[name="page"]');
    const sectionSelect = form.querySelector('[name="section"]');
    const positionSelect = form.querySelector('[name="position"]');
    const widthField = form.querySelector('[data-advert-width]');

    const format = () => form.querySelector('[name="format"]:checked')?.value ?? 'inline';

    const selectFirstVisible = (select) => {
        const current = select.options[select.selectedIndex];
        if (!current || current.hidden) {
            const first = [...select.options].find((option) => !option.hidden);
            if (first) select.value = first.value;
        }
    };

    const sync = () => {
        const isPopup = format() === 'popup';

        form.querySelectorAll('[data-format-only]').forEach((element) => {
            element.hidden = element.dataset.formatOnly !== format();
        });

        [...pageSelect.options].forEach((option) => {
            option.hidden = option.disabled = option.value === 'all' && !isPopup;
        });
        selectFirstVisible(pageSelect);

        [...sectionSelect.options].forEach((option) => {
            option.hidden = option.disabled = option.dataset.page !== pageSelect.value;
        });
        selectFirstVisible(sectionSelect);

        const allowed = (sectionSelect.options[sectionSelect.selectedIndex]?.dataset.positions ?? '').split(',');
        [...positionSelect.options].forEach((option) => {
            option.hidden = option.disabled = !allowed.includes(option.value);
        });
        selectFirstVisible(positionSelect);

        if (widthField) widthField.hidden = isPopup || positionSelect.value === 'inside';
    };

    form.addEventListener('change', (event) => {
        if (['format', 'page', 'section', 'position'].includes(event.target.name)) sync();
    });
    sync();
});

/* ------------------------------------------------------------------ */
/* Site manager: live preview of colours, fonts, size, mode, sidebar  */
/* ------------------------------------------------------------------ */
document.querySelectorAll('[data-site-manager]').forEach((manager) => {
    const recipes = JSON.parse(manager.dataset.recipes);
    const fonts = JSON.parse(manager.dataset.fonts);
    const form = manager.querySelector('#site-form');
    const root = manager.querySelector('[data-preview-root]');
    const zoom = manager.querySelector('[data-preview-zoom]');
    const chromes = manager.querySelectorAll('[data-preview-chrome]');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
    const loadedFonts = new Set();

    const field = (name) => form.querySelector(`[name="${name}"]:not([type="hidden"])`);
    const checked = (name) => form.querySelector(`[name="${name}"]:checked`)?.value;
    const isHex = (value) => /^#[0-9a-fA-F]{6}$/.test(value);

    const applyRecipe = (element, recipe, colors) => {
        Object.entries(recipe).forEach(([variable, template]) => {
            element.style.setProperty(variable, template.replaceAll('{A}', colors.A).replaceAll('{P}', colors.P).replaceAll('{B}', colors.B));
        });
    };

    const loadFont = (key) => {
        const font = fonts[key];
        if (!font?.bunny || loadedFonts.has(key)) return;
        loadedFonts.add(key);
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `https://fonts.bunny.net/css?family=${font.bunny}:${font.weights}&display=swap`;
        document.head.append(link);
    };

    const update = () => {
        const colors = { A: field('accent').value, P: field('primary').value, B: field('background').value };

        if ([colors.A, colors.P, colors.B].every(isHex)) {
            const mode = checked('mode');
            const dark = mode === 'dark' || (mode === 'system' && prefersDark.matches);
            applyRecipe(root, dark ? recipes.dark : recipes.light, colors);
            root.style.colorScheme = dark ? 'dark' : 'light';

            // Sidebars are designed light: "auto" follows the mode, "light"/"dark" pin a palette.
            // The landing footer is designed dark: "dark" keeps it, "light" inverts it.
            const sidebar = checked('sidebar');
            const footer = checked('footer');
            chromes.forEach((element) => {
                const recipe = sidebar !== undefined
                    ? (sidebar === 'dark' || (sidebar === 'auto' && dark) ? recipes.dark : recipes.light)
                    : (footer === 'dark' ? recipes.light : recipes.dark);
                applyRecipe(element, recipe, colors);
            });
        }

        const heading = field('heading_font').value;
        const body = field('body_font').value;
        loadFont(heading);
        loadFont(body);
        root.style.setProperty('--font-display', fonts[heading].stack);
        root.style.setProperty('--font-sans', fonts[body].stack);
        manager.querySelectorAll('[data-font-sample]').forEach((sample) => {
            const key = field(sample.dataset.fontSample).value;
            loadFont(key);
            sample.style.fontFamily = fonts[key].stack;
        });

        const size = Number(field('font_size').value);
        zoom.style.zoom = String(size / 16);
        manager.querySelector('[data-font-size-value]').textContent = String(size);
    };

    manager.querySelectorAll('[data-color-for]').forEach((picker) => {
        const text = field(picker.dataset.colorFor);
        picker.addEventListener('input', () => {
            text.value = picker.value;
            update();
        });
        text.addEventListener('input', () => {
            if (isHex(text.value)) picker.value = text.value.toLowerCase();
        });
    });

    manager.querySelectorAll('[data-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const preset = JSON.parse(button.dataset.preset);
            ['accent', 'primary', 'background'].forEach((key) => {
                field(key).value = preset[key];
                manager.querySelector(`[data-color-for="${key}"]`).value = preset[key];
            });
            update();
        });
    });

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    prefersDark.addEventListener?.('change', update);
    update();
});

/* ------------------------------------------------------------------ */
/* Appearance switch (profile menu): instant, then saved in background */
/* Runs in the capture phase so the page loader never starts for it.  */
/* ------------------------------------------------------------------ */
document.addEventListener(
    'submit',
    (event) => {
        const form = event.target.closest('[data-appearance-form]');
        const mode = event.submitter?.value;
        if (!form || !mode) return;

        event.preventDefault();
        document.documentElement.dataset.mode = mode;
        document.querySelectorAll('[data-appearance-option]').forEach((option) => {
            option.setAttribute('aria-pressed', String(option.value === mode));
        });

        const body = new FormData(form);
        body.set('mode', mode);
        fetch(form.action, {
            method: 'POST',
            body,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        }).catch(() => {
            // Offline: the change still applies to this page; it will be saved next time.
        });
    },
    true,
);
