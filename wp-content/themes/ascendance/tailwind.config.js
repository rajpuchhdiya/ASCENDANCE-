/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./*.php",
    "./template-parts/**/*.php",
    "./assets/js/**/*.js",
    "./assets/css/**/*.css"
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: '#0F1E35',
          deep: '#0A1628',
          mid: '#182D4A',
        },
        cream: '#F7F4EF',
        brand: {
          red: {
            DEFAULT: '#BC1B1D',
            light: '#E04B4B',
          },
          divider: {
            light: '#E8E4DC',
            dark: '#2A3A55',
          },
          text: {
            primary: '#1A1A2E',
            muted: '#6B6B7A',
            dek: '#56514B',
          }
        }
      },
      fontFamily: {
        serif: ["Noto Serif", "Georgia", "serif"],
        sans: ["Cooper Hewitt", "Barlow", "Roboto", "sans-serif"],
        mono: ["JetBrains Mono", "monospace"],
      },
      // Force all border-radius utilities to be compliant (<= 2px)
      borderRadius: {
        none: '0px',
        sm: '2px',
        DEFAULT: '2px',
        md: '2px',
        lg: '2px',
        xl: '2px',
        '2xl': '2px',
        '3xl': '2px',
        full: '2px',
      },
    },
  },
  plugins: [],
}
