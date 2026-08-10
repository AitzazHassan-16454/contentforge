import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Reveals any element carrying a [data-reveal] attribute as it scrolls
 * into view by adding the `.is-visible` class.
 *
 * The `.reveal` class is applied automatically so templates only need the
 * `data-reveal` attribute to opt in to the scroll-triggered animation.
 */
export function useReveal() {
    let observer;

    const reveal = (el) => {
        el.classList.add('reveal');
        el.classList.add('is-visible');
    };

    onMounted(() => {
        const targets = document.querySelectorAll('[data-reveal]');

        if (!targets.length) {
            return;
        }

        if (typeof IntersectionObserver === 'undefined') {
            targets.forEach(reveal);

            return;
        }

        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 },
        );

        targets.forEach((el) => {
            el.classList.add('reveal');
            observer.observe(el);
        });
    });

    onBeforeUnmount(() => observer?.disconnect());
}
