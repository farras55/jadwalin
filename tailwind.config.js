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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#6C5CE7',
                    hover: '#5B4BD5',
                    secondary: '#A29BFE',
                    bg: '#F8FAFC',
                    card: '#FFFFFF',
                    text: '#0F172A',
                    border: '#E2E8F0',
                },
            },
        },
    },

    plugins: [forms],
};
