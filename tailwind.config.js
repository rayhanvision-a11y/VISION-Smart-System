import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: [
        'lg:w-16', 'lg:w-64', 'lg:ml-16', 'lg:ml-64',
        'w-16', 'w-64', 'ml-16', 'ml-64',
        'translate-x-0', '-translate-x-full',
        'rotate-180', 'justify-center',
        'px-0', 'px-1', 'px-2', 'px-3', 'gap-0', 'gap-3',
    ],

    plugins: [forms],
};
