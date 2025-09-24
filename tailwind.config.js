/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./resources/**/*.ts",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Financial App Design System Colors
        teal: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: 'rgb(77, 208, 225)', // teal-light dari design system
          500: 'rgb(32, 178, 170)', // teal-primary dari design system
          600: 'rgb(0, 131, 143)',  // teal-dark dari design system
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
          950: '#042f2e',
        },
        coral: {
          50: '#fff8f8',
          100: '#ffe0e0',
          200: '#ffc8c8',
          300: '#ffb0b0',
          400: 'rgb(255, 138, 128)', // coral-light dari design system
          500: 'rgb(255, 107, 107)', // coral-primary dari design system
          600: 'rgb(229, 57, 53)',   // coral-dark dari design system
          700: '#cd2820',
          800: '#b41919',
          900: '#9b1313',
          950: '#780f0f',
        },
      },
      fontFamily: {
        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        'lg': '1rem',
        'xl': '1.25rem',
        '2xl': '1.5rem',
        '3xl': '1.875rem',
      }
    },
  },
  plugins: [],
}