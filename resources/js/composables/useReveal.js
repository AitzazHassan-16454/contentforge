import { onBeforeUnmount, onMounted } from 'vue';

/**
 * Reveals any element carrying a [data-reveal] attribute as it scrolls
 * into view by adding the `.is-visible` class.
 */
export function useReveal() {
    let observer;

    onMounted(() => {
        const targets = document.querySelectorAll('[data-reveal]');

        if (!targets.length || typeof IntersectionObserver === 'undefined') {
            targets.forEach((el) => el.classList.add('is-visible'));

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

        targets.forEach((el) => observer.observe(el));
    });

    onBeforeUnmount(() => observer?.disconnect());
}
