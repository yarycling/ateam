(function () {
    const popup = document.querySelector('[data-ateam-popup]');
    if (!popup) return;

    const slides = Array.from(popup.querySelectorAll('.ateam-popup__slide'));
    const closeButtons = popup.querySelectorAll('[data-ateam-popup-close]');
    const prev = popup.querySelector('[data-ateam-popup-prev]');
    const next = popup.querySelector('[data-ateam-popup-next]');
    let activeIndex = 0;

    function storageKey(slide) {
        return 'ateam_popup_seen_' + slide.dataset.popupId;
    }

    function wasSeen(slide) {
        try {
            return sessionStorage.getItem(storageKey(slide)) === '1';
        } catch (error) {
            return false;
        }
    }

    function unseenSlides() {
        return slides.filter(function (slide) {
            return slide.dataset.frequency !== 'session' || !wasSeen(slide);
        });
    }

    function markSeen(slide) {
        if (slide.dataset.frequency !== 'session') return;
        try {
            sessionStorage.setItem(storageKey(slide), '1');
        } catch (error) {}
    }

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;
        slides.forEach(function (slide, slideIndex) {
            const isActive = slideIndex === activeIndex;
            slide.classList.toggle('is-active', isActive);
            if (isActive) markSeen(slide);
            slide.querySelectorAll('video').forEach(function (video) {
                if (isActive) {
                    video.muted = true;
                    video.play().catch(function () {});
                } else {
                    video.pause();
                }
            });
        });
    }

    function open() {
        const available = unseenSlides();
        if (!available.length) return;
        const firstAvailable = slides.indexOf(available[0]);
        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('ateam-popup-open');
        showSlide(firstAvailable > -1 ? firstAvailable : 0);
    }

    function close() {
        popup.classList.remove('is-open');
        popup.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('ateam-popup-open');
        slides.forEach(function (slide) {
            slide.querySelectorAll('video').forEach(function (video) { video.pause(); });
        });
    }

    closeButtons.forEach(function (button) {
        button.addEventListener('click', close);
    });

    if (prev) prev.addEventListener('click', function () { showSlide(activeIndex - 1); });
    if (next) next.addEventListener('click', function () { showSlide(activeIndex + 1); });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowLeft' && popup.classList.contains('is-open')) showSlide(activeIndex - 1);
        if (event.key === 'ArrowRight' && popup.classList.contains('is-open')) showSlide(activeIndex + 1);
    });

    window.setTimeout(open, Math.max(0, Number(popup.dataset.delay) || 0) * 1000);
})();
