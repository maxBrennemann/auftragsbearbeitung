import type { Config } from 'tailwindcss';
import path from 'path';

const config: Config = {
    mode: 'jit',
    content: [
        path.resolve(__dirname, '../files/pages/*.php'),
        path.resolve(__dirname, '../files/views/*.php'),
        path.resolve(__dirname, '../files/layout/*.php'),
        path.resolve(__dirname, '../files/res/js/**/*.{js,ts}'),
        path.resolve(__dirname, '../classes/**/*.{html,js,php}'),
        path.resolve(__dirname, '../node_modules/js-classes/**/*.{js,ts}'), // TODO: compile tailwind in package
    ],
    theme: {
        fontFamily: {
            'sans': ['Open Sans'],
        },
        extend: {
            fontSize: {
                'xxs': '0.5rem',
            },
        },
    },
    plugins: [],
}

export default config;
