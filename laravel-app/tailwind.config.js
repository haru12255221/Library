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
                primary: '#a8c8e1',
                success: '#b3d4b6',
                danger: '#f1a5a8',
                'primary-hover': '#8bb5d9',
                'success-hover': '#9bc49f',
                'danger-hover': '#ed8a8e',
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
