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
                primary: '#7ab0d4',
                success: '#8ec293',
                danger: '#e88a8d',
                'primary-hover': '#5e9cc4',
                'success-hover': '#74b37a',
                'danger-hover': '#e06e72',
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
