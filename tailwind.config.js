import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                'pulse-subtle': {
                    '0%, 100%': { 
                        opacity: '1', 
                        transform: 'scale(1)',
                        boxShadow: '0 0 0 0 rgba(239, 68, 68, 0.7)'
                    },
                    '50%': { 
                        opacity: '1', 
                        transform: 'scale(1.2)',
                        boxShadow: '0 0 0 4px rgba(239, 68, 68, 0)'
                    },
                },
            },
            animation: {
                'pulse-subtle': 'pulse-subtle 0.8s ease-in-out 3',
            },
        },
    },

    plugins: [forms],
};
