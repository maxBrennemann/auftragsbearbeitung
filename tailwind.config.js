/** @type {import('tailwindcss').Config} */
module.exports = {
  mode: 'jit',
  content: [
    "./files/*.{html,js,php}",
    "./files/res/js/*.{html,js,php}",
    "./classes/project/**/*.{html,js,php}",
    "upgrade.php",
  ],
  theme: {
    fontFamily: {
      'sans': ['Open Sans'],
    },
    extend: {},
  },
  plugins: [],
}
