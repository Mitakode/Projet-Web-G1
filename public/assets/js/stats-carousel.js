// Generic carousel behavior used by the dashboard stats partials.
//
// Markup contract:
// - container: [data-carousel-scope]
// - viewport:  [data-carousel]
// - track:     [data-carousel-track] (children are "cards")
// - buttons:   [data-carousel-prev], [data-carousel-next]
// - optional dots container: [data-carousel-dots]
document.querySelectorAll('[data-carousel-scope]').forEach((scope) => {
    const carousel = scope.querySelector('[data-carousel]');
    const track = scope.querySelector('[data-carousel-track]');
    const prev = scope.querySelector('[data-carousel-prev]');
    const next = scope.querySelector('[data-carousel-next]');
    const dotsContainer = scope.querySelector('[data-carousel-dots]');

    if (!carousel || !track || !prev || !next) {
        return;
    }

    const cards = Array.from(track.children);
    let index = 0;
    let pageCount = 1;
    let pageStep = 0;

    const getStep = () => {
        // Distance (in px) between two consecutive cards.
        const card = cards[0];
        if (!card) {
            return 0;
        }
        const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0');
        return card.getBoundingClientRect().width + (Number.isNaN(gap) ? 0 : gap);
    };

    const getMaxTranslate = () => {
        // Total scrollable width minus viewport width.
        const totalWidth = track.scrollWidth;
        const viewport = carousel.getBoundingClientRect().width;
        return Math.max(0, totalWidth - viewport);
    };

    const recalcPages = () => {
        // Determine how many cards fit in the viewport, then derive page count/step.
        const step = getStep();
        const gap = parseFloat(getComputedStyle(track).gap || '0');
        const viewport = carousel.getBoundingClientRect().width;
        const visible = step > 0 ? Math.max(1, Math.floor((viewport + gap) / step)) : 1;
        pageCount = Math.max(1, Math.ceil(cards.length / visible));
        pageStep = step * visible;
        if (pageCount < 1) pageCount = 1;
    };

    const update = () => {
        // Apply the translate to the track and update dots state.
        const maxTranslate = getMaxTranslate();
        const translate = Math.min(index * pageStep, maxTranslate);
        track.style.transform = `translateX(-${translate}px)`;
        if (dotsContainer) {
            dotsContainer.querySelectorAll('.stats-dot').forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === index);
            });
        }
    };

    const clampIndex = () => {
        // Keep index within [0, pageCount-1].
        if (index < 0) index = 0;
        if (index > pageCount - 1) index = pageCount - 1;
    };

    const move = (delta) => {
        // Move by a whole page (delta = -1 or +1).
        index += delta;
        clampIndex();
        update();
    };

    prev.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));

    const renderDots = () => {
        // (Re)build dots according to current pageCount.
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (let i = 0; i < pageCount; i += 1) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'stats-dot' + (i === 0 ? ' is-active' : '');
            dot.addEventListener('click', () => {
                // Jump to page i.
                index = i;
                update();
            });
            dotsContainer.appendChild(dot);
        }
    };

    recalcPages();
    renderDots();
    update();

    window.addEventListener('resize', () => {
        // Recompute when responsive layout changes card sizes.
        recalcPages();
        renderDots();
        clampIndex();
        update();
    });
});
