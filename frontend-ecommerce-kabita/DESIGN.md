---
name: Kabita Design System
colors:
  surface: '#faf8ff'
  surface-dim: '#d9d9e5'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3fe'
  surface-container: '#ededf9'
  surface-container-high: '#e7e7f3'
  surface-container-highest: '#e1e2ed'
  on-surface: '#191b23'
  on-surface-variant: '#434655'
  inverse-surface: '#2e3039'
  inverse-on-surface: '#f0f0fb'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#943700'
  on-tertiary: '#ffffff'
  tertiary-container: '#bc4800'
  on-tertiary-container: '#ffede6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffdbcd'
  tertiary-fixed-dim: '#ffb596'
  on-tertiary-fixed: '#360f00'
  on-tertiary-fixed-variant: '#7d2d00'
  background: '#faf8ff'
  on-background: '#191b23'
  surface-variant: '#e1e2ed'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.02em
  display-md:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '700'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 21px
    letterSpacing: 0em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: 0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  container-margin: 24px
  gutter: 16px
---

## Brand & Style
The design system is engineered for a modern UMKM (SME) e-commerce platform that balances professional reliability with accessible entrepreneurship. The aesthetic is rooted in **Corporate Modernism**, prioritizing clarity, functional efficiency, and a high-degree of perceived trust. 

The visual language utilizes generous white space, a refined systematic approach to hierarchy, and subtle depth to guide users through the marketplace. The emotional response should be one of confidence and ease, ensuring that both local vendors and customers feel they are using a world-class commerce engine.

## Colors
The palette is centered around a trustworthy **Blue 600** as the primary driver for actions and brand recognition. **Emerald 500** serves as a secondary accent, specifically reserved for positive reinforcement, success states, and promotional highlights.

The neutral scale is critical for the e-commerce experience:
- **Surface**: The main background uses a very light gray to reduce eye strain and provide a canvas for pure white cards.
- **Headings**: Near-black for high legibility and a sense of authority.
- **Body**: A balanced gray that maintains readability while creating a clear visual distinction from titles.

## Typography
This design system utilizes **Inter** exclusively to leverage its systematic, neutral, and highly legible qualities. 

### Scale & Hierarchy
- **Headings**: Defined by a bold weight and tight tracking (negative letter-spacing) to create a "locked-in," professional look suitable for product names and section titles.
- **Body**: Set to a 1.5 line-height ratio to ensure maximum readability during long-form product descriptions or vendor bios.
- **Labels**: Used for metadata, badges, and micro-copy, utilizing a medium weight to maintain clarity at smaller scales.

## Layout & Spacing
The system operates on an **8px linear grid**. All spatial relationships between elements must be multiples of 8 (e.g., 8, 16, 24, 32).

### Grid Model
- **Desktop**: 12-column fluid grid with 24px margins and 16px gutters.
- **Tablet**: 8-column fluid grid with 24px margins.
- **Mobile**: 4-column fluid grid with 16px margins.

### Component Spacing
Product cards must strictly adhere to a **24px internal padding** (spacing.lg) to provide a premium, breathable feel for product imagery and data.

## Elevation & Depth
Depth is achieved through **Ambient Shadows** and tonal layering rather than harsh borders. 

- **Level 0 (Base)**: Use the neutral background (#F9FAFB).
- **Level 1 (Cards)**: White background (#FFFFFF) with a very soft, diffused shadow: `0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1)`.
- **Level 2 (Hover/Dropdowns)**: A more pronounced shadow to indicate interactivity and "lift": `0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1)`.

Interactive elements should utilize subtle micro-interactions, such as a 2px vertical lift on card hover or a slight scale-down (0.98) on button press.

## Shapes
The shape language is "Soft-Modern." It avoids the playfulness of fully rounded pills while steering clear of the clinical feel of sharp corners.

- **Cards**: Use an 8px radius to accommodate larger surface areas and provide a friendly container for product photography.
- **Buttons & Inputs**: Use a slightly tighter 6px radius. This creates a subtle visual distinction between "containers" (cards) and "actions" (buttons), making the UI feel more structured and intentional.

## Components

### Buttons
- **Primary**: Solid #2563EB with white text. 6px radius. Subtle shadow on hover.
- **Secondary**: Ghost style with #2563EB border and text.
- **Success**: Solid #10B981 for checkout or "Order Completed" actions.

### Cards
- **Product Card**: 8px radius, white background, 24px padding. Image at the top should have a subtle 1px inner stroke to define edges against the white background.
- **Vendor Card**: Includes a circular avatar and secondary text for location/rating.

### Inputs & Form Fields
- **Default State**: 6px radius, white background, 1px border (#E5E7EB).
- **Focus State**: 1px border #2563EB with a soft 3px blue outer glow (ring).
- **Labels**: Use `label-sm` typography, positioned above the field.

### Chips & Badges
- Used for categories (e.g., "Electronics," "Local Food").
- 4px radius (sharper than buttons) with light-tinted backgrounds (e.g., Primary at 10% opacity) and full-strength text color.

### Lists
- Clean rows with 16px vertical padding and a 1px border-bottom divider (#F3F4F6). Use chevron-right icons for navigable list items.