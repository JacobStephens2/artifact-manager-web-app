# Design System Strategy: The Digital Curator

## 1. Overview & Creative North Star
The "Creative North Star" for this design system is **The Digital Curator**. 

Moving away from the legacy site's flat, utility-focused aesthetic, this system treats data management as a premium gallery experience. We reject the "template" look of standard administration tools in favor of an **Editorial Minimalist** approach. This is achieved through intentional asymmetry—using generous whitespace (white space is not empty space; it is a luxury material)—and high-contrast typographic scales that prioritize the content over the container.

By leveraging tonal depth and sophisticated layering, the interface feels reliable and organized, reflecting the core brand personality while maintaining a modern, high-end digital signature.

---

## 2. Colors & Surface Logic

We have refined the previous palette into a sophisticated ecosystem of deep navies and breathable slates. 

### The Palette
- **Primary (`#1a2345`)**: Our "Deep Navy." Used for primary actions and authoritative grounding.
- **Secondary (`#505f76`)**: "Soft Slate." Used for supporting elements and non-critical navigation.
- **Tertiary (`#002939`)**: A dark teal-shadow used to anchor tertiary interactions.
- **Surface Tones**: A range from `surface_container_lowest` (#ffffff) to `surface_container_highest` (#e0e3e5).

### Core Visual Rules
*   **The "No-Line" Rule**: Explicitly prohibit the use of 1px solid borders for sectioning. Structural boundaries must be defined solely through background color shifts. For example, a `surface-container-low` section sitting directly on a `surface` background creates a natural, modern edge without the visual noise of a stroke.
*   **Surface Hierarchy & Nesting**: Treat the UI as physical layers. An application should feel like stacked sheets of fine vellum. Use `surface-container-low` for the main canvas, and `surface-container-lowest` for cards to create a subtle "lift."
*   **The "Glass & Gradient" Rule**: To provide visual "soul," use subtle linear gradients (e.g., `primary` to `primary_container`) on main CTAs. For floating navigation or overlays, apply **Glassmorphism**: use semi-transparent surface colors with a `12px` to `20px` backdrop-blur.

---

## 3. Typography

The system utilizes **Inter** as a singular, cohesive font family to ensure absolute readability and a modern, Swiss-inspired aesthetic.

*   **Display Scale (`display-lg` to `display-sm`)**: Used for "hero" moments and empty states. These are set with tight letter-spacing (-0.02em) to feel like a premium editorial headline.
*   **Headline & Title (`headline-md`, `title-lg`)**: These convey the brand's organized nature. Use `headline-md` for page titles and `title-sm` for card headers.
*   **Body (`body-lg` to `body-md`)**: Optimized for long-form data reading. High line-height (1.6) is mandatory to prevent "wall of text" fatigue.
*   **Label (`label-md`)**: Used for small, functional metadata. These should be set in All-Caps with slightly increased letter-spacing (+0.05em) for a sophisticated, "catalogued" feel.

---

## 4. Elevation & Depth

We eschew traditional drop-shadows in favor of **Tonal Layering** and **Ambient Light**.

*   **The Layering Principle**: Depth is achieved by "stacking" the surface-container tiers. Placing a `#ffffff` card on a `#f7f9fb` background provides a soft, natural lift that feels integrated into the environment.
*   **Ambient Shadows**: When an element must "float" (e.g., a modal or a primary button), use an extra-diffused shadow.
    *   *Shadow Spec:* `0px 12px 32px rgba(25, 28, 30, 0.06)`. The color is a tinted version of `on_surface`, never pure black.
*   **The "Ghost Border" Fallback**: If a border is required for accessibility, use the `outline_variant` token at **15% opacity**. 100% opaque borders are strictly forbidden as they clutter the minimalist profile.
*   **Glassmorphism**: Floating headers should use `surface_container_lowest` at 80% opacity with a `blur(16px)` filter.

---

## 5. Components

### Buttons
- **Primary**: Uses `primary` background with a subtle gradient transition to `primary_container` on hover. 8px (`DEFAULT`) radius.
- **Secondary**: A "Ghost" style. No fill, `outline_variant` at 20% opacity. Text in `primary`.
- **Tertiary**: Text-only using `secondary` color, reserved for low-priority actions like "Reset Password."

### Form Inputs
- **Container**: `surface_container_lowest` background to pop against the page. 
- **Radius**: Consistent 8px (`DEFAULT`).
- **Interaction**: On focus, the ghost border opacity increases to 100% using the `primary` color. Labels should be "floating" or positioned with 1.5 spacing above the field.

### Cards & Lists
- **The No-Divider Rule**: Forbid the use of horizontal divider lines. Separate list items using vertical whitespace (Spacing `4` or `5`) or alternating tonal shifts between `surface_container_low` and `surface_container_lowest`.
- **Artifact Cards**: Use an asymmetrical layout—image or icon on the left, high-contrast `title-md` on the right, with metadata tucked into the bottom-right in `label-sm`.

### Additional Specialized Components
- **Artifact Status Chips**: Small, pill-shaped indicators using `tertiary_container` for a muted, professional highlight rather than "loud" status colors.
- **Surface-Anchored Footer**: Unlike the legacy site's high-contrast bar, the footer should use `surface_container_high` with no border, creating a seamless transition to the end of the page experience.

---

## 6. Do's and Don'ts

### Do
- **Do** use `Spacing 16` and `20` for page margins to create a high-end, "un-crowded" gallery feel.
- **Do** use "Soft Slate" (`secondary`) for secondary text to reduce visual weight.
- **Do** ensure that all interactive elements have a clear `surface_tint` transition on hover.

### Don't
- **Don't** use 1px black or high-contrast grey borders to separate sections.
- **Don't** use "Default" shadows. If you can clearly see where the shadow starts and ends, it is too heavy.
- **Don't** mix font families. The strength of this system relies on the expert application of the **Inter** scale.
- **Don't** use the legacy sky-blue for large backgrounds; keep it reserved for tiny "tertiary" accents only.