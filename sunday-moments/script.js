(() => {
    const filterButtons = Array.from(document.querySelectorAll('.filter-btn'));
    const mediaCards = Array.from(document.querySelectorAll('.media-card'));
    const serviceGroups = Array.from(document.querySelectorAll('[data-service-group]'));

    const applyFilter = (selected) => {
        mediaCards.forEach((card) => {
            const category = card.getAttribute('data-category') || '';
            const shouldShow = selected === 'all' || selected === category;
            card.hidden = !shouldShow;
        });

        serviceGroups.forEach((group) => {
            const hasVisibleCards = Array.from(group.querySelectorAll('.media-card')).some((card) => !card.hidden);
            group.hidden = !hasVisibleCards;
        });
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            filterButtons.forEach((btn) => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            applyFilter(button.dataset.filter || 'all');
        });
    });

    const lightbox = document.getElementById('lightbox');
    const lightboxContent = document.getElementById('lightboxContent');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const closeTriggers = Array.from(document.querySelectorAll('[data-close-lightbox]'));

    const openLightbox = (trigger) => {
        if (!lightbox || !lightboxContent || !lightboxCaption) {
            return;
        }

        const src = trigger.getAttribute('data-src') || '';
        const type = trigger.getAttribute('data-type') || 'image';
        const caption = trigger.getAttribute('data-caption') || '';

        if (!src) {
            return;
        }

        lightboxContent.innerHTML = '';

        if (type === 'video') {
            const video = document.createElement('video');
            video.controls = true;
            video.autoplay = true;
            video.playsInline = true;
            video.src = src;
            const mimeType = trigger.getAttribute('data-mime') || '';
            if (mimeType) {
                video.setAttribute('type', mimeType);
            }
            lightboxContent.appendChild(video);
        } else {
            const image = document.createElement('img');
            image.src = src;
            image.alt = caption;
            image.loading = 'eager';
            lightboxContent.appendChild(image);
        }

        lightboxCaption.textContent = caption;
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeLightbox = () => {
        if (!lightbox || !lightboxContent) {
            return;
        }

        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        lightboxContent.innerHTML = '';
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.media-trigger').forEach((trigger) => {
        trigger.addEventListener('click', () => openLightbox(trigger));
    });

    closeTriggers.forEach((trigger) => {
        trigger.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });

    const revealItems = Array.from(document.querySelectorAll('.reveal-up'));
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.15,
            rootMargin: '0px 0px -40px 0px',
        }
    );

    revealItems.forEach((item) => observer.observe(item));

    applyFilter('all');
})();
