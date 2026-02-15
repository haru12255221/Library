import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import dadsTheme from '@digital-go-jp/tailwind-theme-plugin';

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
                primary: '#008bf2',
                success: '#51b883',
                danger: '#ff5454',
                'primary-hover': '#0066be',
                'success-hover': '#259d63',
                'danger-hover': '#fa0000',
                background: '#f2f2f2',
                'header-bg': '#ffffff',
                'text-primary': '#4d4d4d',
                'text-secondary': '#767676',
                'text-light': '#949494',
                'text-white': '#ffffff',
                'border-neutral': '#999999',
                'border-light': '#cccccc',
            },
            fontFamily: {
                sans: ['"Noto Sans JP"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, dadsTheme],
};
