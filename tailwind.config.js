/** @type {import('tailwindcss').Config} */
module.exports = {
  mode: 'jit',
  content: [
    "./files/*.{html,js,php}",
    "./files/res/js/*.{html,js,php}",
    "./classes/project/modules/**/*.{html,js,php}"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
