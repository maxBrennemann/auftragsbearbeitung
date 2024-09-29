/** @type {import('tailwindcss').Config} */
module.exports = {
    mode: 'jit',
    content: [
        "./files/*.{html,js,php}",
        "./files/res/js/*.{html,js,php}",
        "./files/res/js/sticker/*.{html,js,php}",
        "./files/res/js/classes/*.{html,js,php}",
        "./files/res/views/*.{html,js,php}",
        "./classes/project/**/*.{html,js,php}",
        "upgrade.php",
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
