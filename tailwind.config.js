/** @type {import('tailwindcss').Config} */
export default {
  prefix: 'tw-',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
    './node_modules/@headlessui/vue/**/*.{js,ts,jsx,tsx}',
    './node_modules/@heroicons/vue/**/*.{js,ts,jsx,tsx}',

  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          50:  '#eef6fd',
          100: '#d0e8f9',
          200: '#a3d0f3',
          300: '#68b1ea',
          400: '#2a92df',
          500: '#0b80d3',
          600: '#0876CF',
          700: '#0663ad',
          800: '#054f8a',
          900: '#04396a',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}

