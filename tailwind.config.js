/** @type {import('tailwindcss').Config} */
module.exports = {
    mode: 'jit',
    content: [
        "./files/pages/*.php",
        "./files/views/*.php",
        "./files/layout/*.php",
        "./files/res/js/**/*.js",
        "./classes/**/*.{html,js,php}",
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
