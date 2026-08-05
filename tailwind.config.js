import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                soft: '0 1px 2px 0 rgb(9 9 11 / 0.04), 0 4px 16px -4px rgb(9 9 11 / 0.06)',
                lift: '0 2px 4px 0 rgb(9 9 11 / 0.06), 0 12px 32px -8px rgb(9 9 11 / 0.12)',
            },
        },
    },

    plugins: [forms, typography],
};
