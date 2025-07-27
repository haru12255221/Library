import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: '#3d7ca2',
                success: '#669C6F',
                danger: '#e3595b',
                'primary-hover': '#2a5a7a',
                'success-hover': '#c4d470',
                'danger-hover': '#d63d3f',
                background: '#f8f9fa',
                'header-bg': '#ffffff',
                'text-primary': '#4f4f4f',
                'text-secondary': '#6b7280',
                'text-light': '#9ca3af',
                'text-white': '#ffffff',
                'border-neutral': '#9ca3af',
                'border-light': '#d1d5db',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
