(() => {
    'use strict';

    const getCaptchaImage = (trigger) => {
        if (trigger instanceof HTMLImageElement) {
            return trigger;
        }

        const form = trigger.closest('form');

        if (form) {
            const img = form.querySelector('img[data-captcha-image]');

            if (img) {
                return img;
            }
        }

        return document.querySelector('img[data-captcha-image]');
    };

    const refreshCaptcha = (trigger) => {
        const img = getCaptchaImage(trigger);

        if (!img) {
            return;
        }

        const currentSrc = img.getAttribute('src') || '';
        const [base, hash] = currentSrc.split('#');
        const separator = base.includes('?') ? '&' : '?';

        img.src = base + separator + '_=' + Date.now() + (hash ? '#' + hash : '');
    };

    const bindTrigger = (trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            refreshCaptcha(trigger);
        });

        if (
            trigger.tagName !== 'BUTTON' &&
            trigger.tagName !== 'A' &&
            !trigger.hasAttribute('tabindex')
        ) {
            trigger.setAttribute('tabindex', '0');
        }

        trigger.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                refreshCaptcha(trigger);
            }
        });
    };

    const init = () => {
        const triggers = document.querySelectorAll('[data-captcha-refresh]');

        triggers.forEach(bindTrigger);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();