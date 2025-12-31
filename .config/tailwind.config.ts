import type { Config } from 'tailwindcss';
import path from 'path';

const config: Config = {
    mode: 'jit',
    content: [
        path.resolve(__dirname, '../public/pages/*.php'),
        path.resolve(__dirname, '../public/views/*.php'),
        path.resolve(__dirname, '../public/layout/*.php'),
        path.resolve(__dirname, '../public/res/js/**/*.{js,ts}'),
        path.resolve(__dirname, '../src/**/*.{html,js,php}'),
        path.resolve(__dirname, '../node_modules/js-classes/**/*.{js,ts}'), // TODO: compile tailwind in package
    ],
    theme: {
        extend: {
            fontFamily: {
                'sans': ['"Open Sans"', 'ui-sans-serif', 'system-ui'],
                'mono': ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'monospace'],
            },
            fontSize: {
                'xxs': '0.5rem',
            },
        },
    },
    plugins: [],
}

export default config;
