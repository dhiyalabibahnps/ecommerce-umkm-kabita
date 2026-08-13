import { definePreset } from '@primevue/themes';
import Aura from '@primevue/themes/aura';

const KabitaPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '#eef2ff',
      100: '#dbe1ff',
      200: '#b4c5ff',
      300: '#8da8ff',
      400: '#5681ff',
      500: '#2563eb',
      600: '#004ac6',
      700: '#003ea8',
      800: '#00317f',
      900: '#00245c',
      950: '#00174b'
    },

    surface: {
      0: '#ffffff',
      50: '#faf8ff',
      100: '#f3f3fe',
      200: '#ededf9',
      300: '#e7e7f3',
      400: '#e1e2ed',
      500: '#c3c6d7',
      600: '#737686',
      700: '#434655',
      800: '#2e3039',
      900: '#191b23',
      950: '#191b23'
    },

    formField: {
      background: '#ffffff',
      disabledBackground: '#f3f3fe',
      filledBackground: '#faf8ff',
      filledHoverBackground: '#f3f3fe',
      filledFocusBackground: '#ffffff',
      borderColor: '#c3c6d7',
      hoverBorderColor: '#737686',
      focusBorderColor: '#2563eb',
      invalidBorderColor: '#ba1a1a',
      color: '#191b23',
      disabledColor: '#737686',
      placeholderColor: '#737686',
      floatLabelColor: '#737686',
      floatLabelFocusColor: '#2563eb',
      floatLabelActiveColor: '#434655',
      floatLabelInvalidColor: '#ba1a1a',
      iconColor: '#737686',
      shadow: 'none'
    },

    focusRing: {
      width: '3px',
      style: 'solid',
      color: 'rgba(37, 99, 235, 0.20)',
      offset: '0px',
      shadow: 'none'
    }
  },

  components: {
    button: {
      root: {
        borderRadius: '6px',
        fontWeight: '500'
      }
    },

    card: {
      root: {
        borderRadius: '8px',
        background: '#ffffff',
        shadow: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)'
      },
      body: {
        padding: '24px'
      }
    },

    inputtext: {
      root: {
        borderRadius: '6px'
      }
    },

    textarea: {
      root: {
        borderRadius: '6px'
      }
    },

    select: {
      root: {
        borderRadius: '6px'
      }
    },

    multiselect: {
      root: {
        borderRadius: '6px'
      }
    },

    badge: {
      root: {
        borderRadius: '4px',
        fontWeight: '500'
      }
    },

    chip: {
      root: {
        borderRadius: '4px'
      }
    }
  }
});

export default KabitaPreset;
