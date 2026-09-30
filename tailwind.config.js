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
            },
            colors: {
                brand: {
                    primary: '#6C5CE7',
                    hover: '#4F46E5',
                    secondary: '#A29BFE',
                    bg: '#F5F3FF',
                    card: '#FFFFFF',
                    text: '#2D2A3E',
                    border: '#E2E0F7',
                },
            },
        },
    },

    plugins: [forms],
};
