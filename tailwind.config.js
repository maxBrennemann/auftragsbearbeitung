/** @type {import('tailwindcss').Config} */
module.exports = {
    mode: 'jit',
    content: [
        "./files/pages/*.{html,js,php}",
        "./files/views/*.{html,js,php}",
        "./files/layout/*{html,js,php}",
        "./files/res/js/*.{html,js,php}",
        "./files/res/js/sticker/*.{html,js,php}",
        "./files/res/js/classes/*.{html,js,php}",
        "./files/res/js/auftrag/*.{html,js,php}",
        "./classes/Project/**/*.{html,js,php}",
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
