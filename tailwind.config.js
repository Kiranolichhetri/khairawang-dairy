/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './public/**/*.html',
    './resources/views/**/*.html',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        // Primary colors
        'dark-brown': '#201916',
        'accent-orange': '#FD7C44',
        'cream': '#F7EFDF',
        
        // Neutral colors
        'light-gray': '#F5F5F5',
        
        // State colors
        'success-green': '#22C55E',
        'error-red': '#EF4444',
        
        // Brand aliases
        primary: '#201916',
        accent: '#FD7C44',
        background: '#F7EFDF',
      },
      fontFamily: {
        poppins: ['Poppins', 'sans-serif'],
        'dm-sans': ['DM Sans', 'sans-serif'],
        heading: ['Poppins', 'sans-serif'],
        body: ['DM Sans', 'sans-serif'],
      },
      fontSize: {
        'xs': ['12px', { lineHeight: '16px' }],
        'sm': ['14px', { lineHeight: '20px' }],
        'base': ['16px', { lineHeight: '24px' }],
        'lg': ['18px', { lineHeight: '28px' }],
        'xl': ['20px', { lineHeight: '28px' }],
        '2xl': ['24px', { lineHeight: '32px' }],
        '3xl': ['30px', { lineHeight: '36px' }],
        '4xl': ['36px', { lineHeight: '40px' }],
        '5xl': ['48px', { lineHeight: '52px' }],
        '6xl': ['60px', { lineHeight: '64px' }],
      },
      fontWeight: {
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
      },
      spacing: {
        '18': '4.5rem',
        '22': '5.5rem',
        '26': '6.5rem',
        '30': '7.5rem',
        '34': '8.5rem',
        '38': '9.5rem',
      },
      borderRadius: {
        'xl': '12px',
        '2xl': '16px',
        '3xl': '24px',
        '4xl': '32px',
      },
      boxShadow: {
        'soft': '0 2px 8px rgba(32, 25, 22, 0.08)',
        'soft-md': '0 4px 16px rgba(32, 25, 22, 0.10)',
        'soft-lg': '0 8px 24px rgba(32, 25, 22, 0.12)',
        'soft-xl': '0 12px 32px rgba(32, 25, 22, 0.15)',
        'card': '0 4px 12px rgba(32, 25, 22, 0.08)',
        'card-hover': '0 8px 24px rgba(32, 25, 22, 0.12)',
        'button': '0 4px 12px rgba(253, 124, 68, 0.25)',
        'button-hover': '0 6px 20px rgba(253, 124, 68, 0.35)',
      },
      animation: {
        'fade-in': 'fadeIn 0.6s ease-out forwards',
        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
        'fade-in-down': 'fadeInDown 0.6s ease-out forwards',
        'slide-in-left': 'slideInLeft 0.6s ease-out forwards',
        'slide-in-right': 'slideInRight 0.6s ease-out forwards',
        'scale-in': 'scaleIn 0.4s ease-out forwards',
        'bounce-soft': 'bounceSoft 2s infinite',
        'pulse-soft': 'pulseSoft 2s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        fadeInUp: {
          '0%': { opacity: '0', transform: 'translateY(20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        fadeInDown: {
          '0%': { opacity: '0', transform: 'translateY(-20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        slideInLeft: {
          '0%': { opacity: '0', transform: 'translateX(-30px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        slideInRight: {
          '0%': { opacity: '0', transform: 'translateX(30px)' },
          '100%': { opacity: '1', transform: 'translateX(0)' },
        },
        scaleIn: {
          '0%': { opacity: '0', transform: 'scale(0.9)' },
          '100%': { opacity: '1', transform: 'scale(1)' },
        },
        bounceSoft: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
        pulseSoft: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.8' },
        },
      },
      transitionDuration: {
        '250': '250ms',
        '350': '350ms',
        '400': '400ms',
      },
      transitionTimingFunction: {
        'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
        'bounce': 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-gradient': 'linear-gradient(135deg, rgba(32, 25, 22, 0.85) 0%, rgba(32, 25, 22, 0.6) 100%)',
        'card-gradient': 'linear-gradient(180deg, rgba(247, 239, 223, 0) 0%, rgba(247, 239, 223, 0.8) 100%)',
      },
    },
  },
  plugins: [],
}
