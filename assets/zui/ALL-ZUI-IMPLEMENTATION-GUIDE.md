# ZUI Library — Complete Implementation Guide

> **Single source of truth.** This document defines exactly how to use the ZUI
> component library inside any plugin. It is derived directly from the
> component CSS files in `assets/zui/css/components/`. Every required HTML
> structure documented here comes from inspecting the library — none of it is
> invented or simplified.
>
> **If a developer follows this guide exactly, the library CSS automatically
> generates the intended UI without additional design work.**

---

# 1. Introduction

## 1.1 What ZUI is

ZUI is a shared CSS + asset component library distributed inside every consumer
plugin under `assets/zui/`. The library carries:

- A scoped reset (`reset.css`) — applies only inside `.zui-scope`
- Design tokens (`tokens.css`) — colors, spacing, radii, shadows, typography
- An aggregator (`zui.css`) — imports every component file
- One CSS file per component under `css/components/`
- A small JS entry (`js/zui.js`) for components that need init

## 1.2 How the library works

Every component CSS rule begins with `.zui-scope` as its prefix. Example:

```css
.zui-scope .zui-card { background: var(--zui-surface); border: 1px solid …; }
.zui-scope .zui-header__bar { height: 60px; display: flex; … }
.zui-scope .zui-section-header__title { font-size: 20px; font-weight: 600; … }
```

This means **two conditions must be true for a library rule to apply**:

1. The target element must be inside (or carry) the `.zui-scope` class.
2. The target element must carry the component's selector class (and, where the
   component has nested sub-elements, must also have those sub-elements with
   their selector classes).

## 1.3 Why structure matters

The library is **structural-first**. Components are defined as a hierarchy of
elements, not as a single class. A "Header" is not one class — it is:

```
.zui-header
  └── .zui-header__bar
        ├── .zui-header__lead
        │     └── .zui-brand
        │           ├── .zui-brand__emblem  (with .zui-brand__emblem-dot inside)
        │           ├── .zui-brand__name
        │           ├── .zui-brand__badge
        │           └── .zui-brand__tagline
        └── .zui-header-actions
              └── .zui-header-action  (with .zui-header-action--with-label OR --icon-only modifier)
```

Without those nested sub-elements, the library CSS has nothing to style. The
outer `.zui-header` class alone renders a plain `<div>` with background +
bottom border — none of the brand cluster, menu toggle, or action area
appears.

## 1.4 Why classes alone are not enough

Adding only the outer class (co-classing) to an existing legacy element
produces incomplete output for any component with sub-structure. The library
expects the full hierarchy. Adding only `.zui-section-header` to a custom
`<div>` produces a card-like banner with no title, no icon, no save button —
because none of those sub-elements exist.

## 1.5 How component CSS is designed

Each component CSS file:

- Documents the canonical HTML structure at the top of the file inside a comment block
- Defines rules in the form `.zui-scope .zui-X { … }` for the component's own properties
- Defines rules for every nested sub-element using BEM notation (`__element`, `--modifier`)
- May reference design tokens (`var(--zui-primary)`, `var(--zui-radius-xl)`, etc.)
- May depend on a parent container existing (`.zui-card .zui-row { … }`)

## 1.6 Why canonical HTML structure must be followed

A component is the library author's contract:

- "If you build this exact DOM, I will style it correctly."

Deviating from the DOM breaks the contract. Three common deviation patterns
that all produce broken UI:

1. Adding only the outer class — sub-element rules don't match anything
2. Using a different element type (e.g. `<div>` instead of `<nav>` for tabs) — semantic CSS selectors may fail
3. Replacing canonical sub-element class names with module class names — library rules can't find them

The library was designed against canonical HTML. Use canonical HTML.

---

# 2. Global Rules

## 2.1 `.zui-scope`

Every ZUI rule lives under `.zui-scope`. The entire admin shell must be
wrapped in an element that carries this class. Without it, **nothing the
library defines renders**.

```html
<div class="my-plugin-app zui-scope" id="my-plugin-app">
  <!-- entire ZUI page content here -->
</div>
```

- One `.zui-scope` per admin page (the root wrapper)
- May be co-classed with a plugin-specific identifier (`my-plugin-app`) for JS hooks
- Must NOT be applied to `<body>` — only to a child element
- Must NOT be applied near a React app's root — would leak library typography into the React tree
- Snackbar appends its `.zui-snackbar-stack` container **inside** `.zui-scope` so tokens resolve (falls back to `<body>` only if no scope exists on the page)

## 2.2 Component composition

A component is composed of:

- **One outer class** (e.g. `.zui-card`)
- **Required nested sub-elements** with their `__element` class names
- **Optional modifiers** with `--variant` class names
- **Optional plugin module classes** alongside the canonical class for plugin-specific deltas

Example composition (a card containing a form row):

```html
<div class="my-plugin-special-card zui-card">
  <div class="zui-row zui-row--inline">
    <div class="zui-row__head">
      <span class="zui-row__label">Field name</span>
    </div>
    <div class="zui-row__control">
      <input type="text" class="zui-input">
    </div>
  </div>
</div>
```

Notes:

- `my-plugin-special-card` is the plugin module class for delta styling
- `.zui-card` is the canonical primitive
- `.zui-row.zui-row--inline` is a composed primitive (card-aware row + inline modifier)
- `.zui-row__head` and `.zui-row__control` are the required nested sub-elements

## 2.3 Modifiers

Modifiers are additional classes on the same element that change a variant of
the component. They use double-dash BEM notation:

```html
<!-- Default -->
<a class="zui-btn-primary">Save</a>

<!-- Block-width modifier (composes with the variant) -->
<a class="zui-btn-primary zui-btn-block">Save</a>

<!-- Different variant (one variant class is mandatory) -->
<a class="zui-btn-ghost">Cancel</a>
```

Some components require one of several variant classes. Buttons require
exactly one of `.zui-btn-primary` / `.zui-btn-secondary` / `.zui-btn-ghost`.

## 2.4 Naming conventions

- **Block**: `.zui-X` (e.g. `.zui-card`, `.zui-header`)
- **Element**: `.zui-X__Y` (e.g. `.zui-card__head`, `.zui-header__bar`)
- **Modifier**: `.zui-X--Z` (e.g. `.zui-card-grid--cols-3`, `.zui-row--inline`)
- **State**: `.is-active`, `.is-selected`, `.is-open`, `.is-collapsed`
- **Composition modifier**: `.zui-btn-block` (composes onto variant)

## 2.5 BEM structure

Components follow BEM (Block + Element + Modifier) strictly:

- The block is the component name
- Elements are children of the block, named with `__`
- Modifiers describe variants, named with `--`
- States use `.is-X` class names, not `--X`

Example — Section Header:

```
Block:    .zui-section-header
Elements: .zui-section-header__main
          .zui-section-header__icon
          .zui-section-header__text
          .zui-section-header__titlewrap
          .zui-section-header__title
          .zui-section-header__badge
          .zui-section-header__sub
          .zui-section-header__actions
          .zui-section-header__action
Modifier: (none on the block — variants live on sub-elements like __action--ghost)
```

## 2.6 Parent-child requirements

Some component CSS rules require a specific parent. Form Row's padding only
applies when it sits inside a Card:

```css
.zui-scope .zui-card .zui-row { padding: 32px; … }
```

If you place `.zui-row` outside a `.zui-card`, padding does not apply. Each
component's section in this guide documents its parent requirement.

## 2.7 Preservation of functionality

When migrating an existing plugin to ZUI, the following must be preserved
verbatim:

- **IDs** — all `id="…"` attributes (referenced by `for=`, CSS, JS)
- **`name=` attributes** — form submission keys
- **AJAX action names** — `wp_ajax_*` handler bindings
- **Option keys** — `get_option(…)` / `update_option(…)` keys
- **Nonces** — nonce field names, action names, `wp_nonce_field()` calls
- **URLs** — `href`, redirect, AJAX URL paths, query params
- **JS hooks** — selector classes/IDs/data-attributes that JavaScript binds to

The pattern is **add ZUI classes alongside the legacy classes**, never
replace them when they're JS hooks:

```html
<!-- Legacy form save button — admin.js binds .my-plugin-save -->
<button class="my-plugin-save button-primary woocommerce-save-button">Save</button>

<!-- Migrated — ZUI variant added; legacy hooks preserved -->
<button class="my-plugin-save button-primary woocommerce-save-button zui-btn-primary">
  <span>Save</span>
</button>
```

---

# 3. Migration Workflow

For every component you plan to use, follow this workflow.

## Step 1 — Inspect component CSS

Open the relevant CSS file under `assets/zui/css/components/`. Read the
docblock at the top — it lists the canonical HTML structure and the source
this contract was extracted from. Read the selector list — note which
sub-elements have rules.

## Step 2 — Inspect component HTML structure

Note exactly:

- The outer element type (`<div>`, `<header>`, `<nav>`, `<aside>`, `<main>`, `<section>`, `<button>`, `<label>`, `<input>`, etc.)
- All required nested elements with their classes
- All optional modifiers
- Any parent the component depends on (e.g. `.zui-card` for form rows)
- Any JS hook the canonical contract expects (e.g. `data-zui-modal-close`)

## Step 3 — Build canonical structure

Construct the exact DOM. Place plugin module classes alongside the canonical
classes:

```html
<div class="my-plugin-foo zui-card">
  <div class="zui-section-header">
    <div class="zui-section-header__main">…</div>
    <div class="zui-section-header__actions">…</div>
  </div>
</div>
```

## Step 4 — Move existing functionality into canonical structure

Move into the new canonical structure:

- The original `name=`, `id=`, `value=` attributes
- The original JS hook classes (alongside the canonical class)
- The original `<form>` wrappers, hidden inputs, nonces
- Any data attributes (`data-zui-modal-close`, `data-section`, etc.)
- Any conditional PHP echoes / states

## Step 5 — Verify styling

Load the page. Confirm:

- The library CSS file (`zui.css`) loads BEFORE the plugin admin CSS
- The element renders with the expected visual (background, border, padding, typography)
- Sub-element styling applies (icons, titles, descriptions, buttons)
- No layout shift compared to the design

## Step 6 — Verify functionality

Trigger every interaction:

- Click each button — verify form posts, AJAX fires, URL navigates correctly
- Save the form — verify options persist
- Tab activation — verify sections show/hide
- Modal open/close — verify backdrop, focus, dismiss
- Tooltips, dropdowns, multi-selects — verify they bind

---

# Component Reference

This section documents every component file present in
`assets/zui/css/components/`. The components are organized by role.

> ### ⚠ Phase 1 (production) vs. Phase 2 (deferred)
>
> Not every documented component is loaded by the default `css/zui.css`
> aggregator. The aggregator only `@import`s **Phase 1** components — the
> ones currently in use across consumer plugins. The following components
> have full documentation but their CSS is **commented out in `css/zui.css`**
> until a plugin actually needs them:
>
> | Component | Status | To enable |
> |---|---|---|
> | `.zui-badge` | Phase 2 | Uncomment `@import "components/badge.css"` in `css/zui.css` |
> | `.zui-card-grid` | Phase 2 | Uncomment `@import "components/card-grid.css"` |
> | `.zui-color-input` | Phase 2 | Uncomment `@import "components/color-input.css"` |
> | `.zui-filter-bar` | Phase 2 | Uncomment `@import "components/filter-bar.css"` |
> | `.zui-list-row` | Phase 2 | Uncomment `@import "components/list-row.css"` |
> | `.zui-merge-tags` | Phase 2 | Uncomment `@import "components/merge-tags.css"` |
> | `.zui-note` | Phase 2 | Uncomment `@import "components/note.css"` |
> | `.zui-pagination` | Phase 2 | Uncomment `@import "components/pagination.css"` |
> | `.zui-table` | Phase 2 | Uncomment `@import "components/table.css"` |
> | `.zui-textarea` | Phase 2 | Uncomment `@import "components/textarea.css"` |
>
> Everything else (header, tabs, layout, sidebar, card, row, toggle, checkbox,
> input, select, multi-select, radio cards, segmented, checkcard, buttons,
> modal, slideout, upload, tipbox, tooltip, menu, actions, icon, snackbar,
> loader, notice, sections, savebtn, scope, tokens, license family) is Phase 1
> and works out of the box.

For each component:

1. **Purpose** — what role the component plays in a settings page
2. **Required Structure** — the exact canonical HTML
3. **Required classes** — classes the library expects
4. **Optional classes** — modifiers / states
5. **Variants** — variant classes (where applicable)
6. **Modifiers** — sub-modifiers
7. **Nested elements** — children that must exist
8. **Parent requirements** — what must wrap this component
9. **Child requirements** — what must live inside this component
10. **Expected visual result** — what the UI looks like when correct
11. **Common mistakes**
12. **Correct example**
13. **Incorrect example**
14. **Migration notes** — how to safely migrate existing markup

---

# FOUNDATION

## Scope (`.zui-scope`)

### Purpose

The root wrapper for any plugin page that uses ZUI. Carries the design tokens,
applies the scoped reset, and is the prefix on every component selector. Every
ZUI component must be a descendant of an element with `.zui-scope`.

### Required Structure

```html
<div class="zui-scope">
  <!-- entire ZUI page content -->
</div>
```

### Required classes

- `.zui-scope` — single class on the root element

### Optional classes

- A plugin-specific identifier co-classed for JS hooks (e.g. `class="my-plugin-app zui-scope" id="my-plugin-app"`)

### Variants

None.

### Modifiers

None.

### Nested elements

None directly. Components live inside.

### Parent requirements

A WordPress admin page wrapper (or any block-level element). Must NOT be `<body>`.

### Child requirements

Any ZUI component or plugin content.

### Expected visual result

The scope wrapper applies:

- `font-family: 'Inter', …, sans-serif` (loaded via Google Fonts in `tokens.css`)
- `color: #1e293b` (slate-800 body text)
- `background: #f8fafc` (slate-50 page background)
- `min-height: calc(100vh - 32px)` (fills the admin viewport)
- `display: flex; flex-direction: column` (vertical stack of children)
- `box-sizing: border-box` recursively to every descendant
- Custom 6px-wide scrollbar with `#cbd5e1` thumb
- `-webkit-font-smoothing: antialiased`
- WP admin `#wpcontent` / `#wpbody` / `#wpbody-content` background changed to `#f8fafc` so the scope blends with the WP chrome

### Common mistakes

- Adding `.zui-scope` to `<body>` — leaks library styles into WP admin chrome
- Adding `.zui-scope` near a React app root — React tree picks up library typography
- Nesting `.zui-scope` inside another `.zui-scope` — redundant but harmless; avoid for clarity
- Forgetting `.zui-scope` entirely — nothing the library defines renders

### Correct example

```html
<div class="my-plugin-app zui-scope" id="my-plugin-app">
  <header class="zui-header">…</header>
  <main class="zui-content">…</main>
</div>
```

### Incorrect example

```html
<!-- Missing .zui-scope -->
<div class="my-plugin-app">
  <header class="zui-header">…</header>  <!-- Library rules do not apply -->
</div>

<!-- Applied to <body> — leaks -->
<body class="zui-scope">…</body>

<!-- Wrapped around a React app -->
<div class="zui-scope">
  <div id="react-root"></div>  <!-- React typography contaminated -->
</div>
```

### Migration notes

Wrap the entire admin page render in a single `<div class="zui-scope">`. If
the plugin already has a root wrapper (e.g. `<div class="my-plugin-app">`),
add `zui-scope` as a co-class. Exclude any embedded React/iframe areas by
keeping them outside the scope wrapper or in a sibling element.

---

## Tokens (`tokens.css`)

### Purpose

Defines the design-token CSS variables used by every component. Tokens live on
`.zui-scope` so they can be overridden per plugin or per page.

### Required Structure

No HTML. Tokens are CSS variables declared on `.zui-scope`. Override per page
by setting a more specific selector:

```css
.my-plugin-page.zui-scope { --zui-primary: #c00; }
```

### Tokens defined

**Brand (full 50–900 scale):**

| Token | Value | Use |
|---|---|---|
| `--zui-primary` | `#3b64d3` | Alias of 500 (default primary) |
| `--zui-primary-hover` | `#2d4db5` | Primary button hover |
| `--zui-primary-50` | `#f0f4ff` | Light tint background |
| `--zui-primary-100` | `#e1e9fe` | Subtle accent background |
| `--zui-primary-200` | `#cbdafc` | Active highlight |
| `--zui-primary-300` | `#a7c1fa` | Active border |
| `--zui-primary-400` | `#7da0f6` | Mid-tone hover |
| `--zui-primary-500` | `#3b64d3` | Core brand blue |
| `--zui-primary-600` | `#2d4db5` | Hover state |
| `--zui-primary-700` | `#243c94` | Pressed state |
| `--zui-primary-800` | `#1d2e73` | Dark text on light |
| `--zui-primary-900` | `#19275e` | Darkest accent |

**Surfaces:**

| Token | Value | Use |
|---|---|---|
| `--zui-bg` | `#f8fafc` | Page background |
| `--zui-surface` | `#ffffff` | Card / panel background |
| `--zui-border` | `#e2e8f0` | Default border |
| `--zui-divider` | `#f1f5f9` | Soft divider between rows |

**Text:**

| Token | Value | Use |
|---|---|---|
| `--zui-text-strong` | `#0f172a` | Page title, h1 |
| `--zui-text` | `#1e293b` | Labels, body |
| `--zui-text-soft` | `#475569` | Section nav text |
| `--zui-text-muted` | `#94a3b8` | Descriptors, placeholders |

**Chips:**

| Token | Value | Use |
|---|---|---|
| `--zui-chip-bg` | `rgba(37, 99, 235, 0.10)` | Multi-select chip background |
| `--zui-chip-border` | `rgba(37, 99, 235, 0.20)` | Chip border |
| `--zui-chip-text` | `#1e40af` | Chip text |

**Radii:**

| Token | Value |
|---|---|
| `--zui-radius-lg` | `8px` |
| `--zui-radius-xl` | `12px` |
| `--zui-radius-2xl` | `16px` |

**Shadows:**

| Token | Value |
|---|---|
| `--zui-shadow-xs` | `0 1px 2px rgba(15, 23, 42, 0.04)` |
| `--zui-shadow-sm` | `0 1px 3px rgba(15, 23, 42, 0.08)` |
| `--zui-shadow-md` | `0 4px 10px rgba(15, 23, 42, 0.10)` |

**Typography:**

| Token | Value |
|---|---|
| `--zui-font` | `'Inter', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif` |
| `--zui-font-mono` | `'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace` |

### Migration notes

Override tokens scope-by-scope if a plugin needs a different brand color or
radius. Do not edit `tokens.css` directly in a plugin (sync would overwrite).
Instead, declare overrides in the plugin's own admin CSS, scoped to the
plugin's app class.

---

# CHROME / PAGE LAYOUT

## Header (`.zui-header`)

### Purpose

The top brand bar of the admin page. Contains a mobile menu toggle (drawer
trigger), the brand cluster (emblem + name + badge + tagline), and an actions
cluster (docs link, notifications, etc.). Tabs nest inside the header below
the bar.

### Required Structure

```html
<header class="zui-header" id="my-plugin-header">

  <div class="zui-header__bar">

    <div class="zui-header__lead">
      <button type="button" class="zui-header__menu-toggle"
              data-drawer-toggle aria-label="Open menu">
        <svg class="zui-icon"><!-- menu icon --></svg>
      </button>

      <div class="zui-brand">
        <span class="zui-brand__emblem" aria-hidden="true">
          A<span class="zui-brand__emblem-dot"></span>
        </span>
        <span class="zui-brand__name">Brand</span>
        <span class="zui-brand__badge">PRO</span>
        <span class="zui-brand__tagline">Tagline text</span>
      </div>
    </div>

    <div class="zui-header-actions">
      <a class="zui-header-action zui-header-action--with-label"
         href="…" target="_blank" rel="noreferrer noopener">
        <svg class="zui-icon"><!-- book-open --></svg>
        <span class="zui-header-action__label">Docs Portal</span>
      </a>
      <button type="button" class="zui-header-action zui-header-action--icon-only"
              aria-label="Notifications">
        <svg class="zui-icon"><!-- bell --></svg>
      </button>
    </div>

  </div>

  <!-- Tabs nav nested inside header — see Tabs component -->

</header>
```

### Required classes

- `.zui-header` on the outer `<header>`
- `.zui-header__bar` on the inner row container
- `.zui-header__lead` on the left cluster
- `.zui-brand` on the brand container
- `.zui-brand__emblem` on the letter / icon tile
- `.zui-brand__emblem-dot` on the dot accent
- `.zui-brand__name` on the brand name text
- `.zui-brand__badge` on the version badge (e.g. PRO)
- `.zui-brand__tagline` on the tagline text
- `.zui-header-actions` on the right cluster
- `.zui-header-action` on each action item
- `.zui-header-action--with-label` OR `.zui-header-action--icon-only` modifier on each action

### Optional classes

- `.zui-header__menu-toggle` for the mobile drawer trigger (when the page has a sidebar)
- `.zui-header-action__label` for the text label inside `--with-label` action

### Variants

None on the block. Two action variants:

- `--with-label` — pill button with icon + text
- `--icon-only` — square button with only an icon

### Modifiers

(See variants above.)

### Nested elements

- `__bar` (required)
- `__lead` (required)
- `__menu-toggle` (optional — for sidebar pages)
- `.zui-brand` and its children (required)
- `.zui-header-actions` and `__action` children (required)

### Parent requirements

Must be inside `.zui-scope`.

### Child requirements

The Tabs nav is expected to be nested inside `<header>` (below `__bar`) when
the page uses tabs.

### Expected visual result

- 60px tall white brand bar with bottom border
- Brand cluster on the left: 36×36 colored emblem tile with letter + corner dot, brand name (bold), version badge (uppercase pill), tagline (small muted, separated by border-inline-start)
- Action cluster on the right: outlined pill buttons (`--with-label`) and square icon buttons (`--icon-only`)
- On mobile (max-width 782px): menu toggle visible, tagline + action labels hide

### Auto-wiring (JS) — mobile drawer

The `__menu-toggle` button is wired by `_wireShell` when it carries `data-zui-drawer-toggle`. The sidebar's overlay and close button carry the matching `data-zui-drawer-close`:

- Click on `[data-zui-drawer-toggle]` → toggles `.zui-sidebar-open` on `.zui-scope` (slides the sidebar in on mobile)
- Click on `[data-zui-drawer-close]` → removes `.zui-sidebar-open` from `.zui-scope`

```html
<button type="button" class="zui-header__menu-toggle" data-zui-drawer-toggle aria-label="Open menu">
  <svg class="zui-icon">…</svg>
</button>

<!-- elsewhere in the layout: -->
<div class="zui-sidebar__overlay" data-zui-drawer-close></div>
<button class="zui-sidebar__close" data-zui-drawer-close aria-label="Close menu">…</button>
```

### Common mistakes

- Using `<div>` instead of `<header>` — semantic + library expects `<header>`
- Missing `__bar` wrapper — the 60px tall flex row never forms
- Putting tabs OUTSIDE the header — the canonical pattern places them inside
- Skipping `__emblem-dot` — the accent dot in the brand emblem doesn't render
- Missing `--with-label` or `--icon-only` modifier on action buttons — no visual

### Correct example

```html
<header class="zui-header" id="my-plugin-header">
  <div class="zui-header__bar">
    <div class="zui-header__lead">
      <button type="button" class="zui-header__menu-toggle" aria-label="Open menu">
        <svg class="zui-icon">…</svg>
      </button>
      <div class="zui-brand">
        <span class="zui-brand__emblem" aria-hidden="true">P<span class="zui-brand__emblem-dot"></span></span>
        <span class="zui-brand__name">Plugin Name</span>
        <span class="zui-brand__badge">PRO</span>
        <span class="zui-brand__tagline">Plugin Tagline</span>
      </div>
    </div>
    <div class="zui-header-actions">
      <a class="zui-header-action zui-header-action--with-label" href="…" target="_blank">
        <svg class="zui-icon">…</svg>
        <span class="zui-header-action__label">Docs</span>
      </a>
    </div>
  </div>
  <nav class="zui-tabs">…</nav>
</header>
```

### Incorrect example

```html
<!-- Outer element wrong, sub-elements missing -->
<div class="zui-header">
  <h1>Plugin Name</h1>
  <a href="…">Docs</a>
</div>
<!-- Result: empty 60px bar with default browser h1 + anchor — no brand cluster -->
```

### Migration notes

Replace the existing top-bar wrapper with a `<header class="zui-header">`.
Move any existing breadcrumb-like dynamic text into `.zui-brand__tagline` and
keep the legacy JS-hook class on it (so existing JS that updates the text
continues to find the element). Replace any legacy logo `<img>` with the
canonical letter emblem + dot.

---

## Tabs (`.zui-tabs`)

### Purpose

Horizontal primary navigation strip below the brand bar. Each item is an
underline-active link. Optional count badge sits at the right end of an item.

### Required Structure

```html
<nav class="zui-tabs" aria-label="Modules">
  <a class="zui-tabs__item is-active" href="?tab=settings" aria-current="page">
    Settings
  </a>
  <a class="zui-tabs__item" href="?tab=section-b">
    Section B
  </a>
  <a class="zui-tabs__item" href="?tab=section-c">
    Section C
    <span class="zui-tabs__badge">5</span>
  </a>
</nav>
```

### Required classes

- `.zui-tabs` on the outer `<nav>`
- `.zui-tabs__item` on every tab `<a>` (or `<button>`)
- `.is-active` on the currently-active item
- `aria-current="page"` on the active item (ARIA contract)

### Optional classes

- `.zui-tabs__badge` on a count badge inside an item
- `data-zui-tab="key"` on `__item` — enables auto client-side switching via the library JS (`_wireShell`). Matches a sibling `.zui-tab-panel[data-zui-tab="key"]`.

### Variants

None.

### Modifiers

None.

### Nested elements

- `__item` per tab (required)
- `__badge` per item (optional)

### Auto-wiring (JS)

When `__item` carries `data-zui-tab="key"`, the library's `_wireShell` (registered by `ZUI.scan()`) handles the activation client-side:

- Click on a `__item[data-zui-tab]` → adds `.is-active` + `aria-current="page"` to it, removes them from siblings
- Sets `hidden` on every `.zui-tab-panel[data-zui-tab]` whose key does NOT match
- Dispatches a `zui:tab` `CustomEvent` (detail: `{ tab: key }`) on the scope — plugin JS can listen for analytics / dirty-state tracking

If `__item` is an `<a>` with a non-hash `href`, the link follows normally (navigation, not client-side switch).

```html
<nav class="zui-tabs">
  <button class="zui-tabs__item is-active" data-zui-tab="general" aria-current="page">General</button>
  <button class="zui-tabs__item" data-zui-tab="advanced">Advanced</button>
</nav>

<div class="zui-tab-panel" data-zui-tab="general">…</div>
<div class="zui-tab-panel" data-zui-tab="advanced" hidden>…</div>
```

### Parent requirements

Canonically nested inside `<header class="zui-header">`. Can also sit
standalone at the top of a page if the page has no Header.

### Child requirements

One or more `__item` anchors.

### Expected visual result

- 44px-tall white strip with bottom border
- Items spaced 24px apart, padded 24px from the strip edges
- Items render as small-caps bold text (`font-size: 12px`, `font-weight: 700`, `letter-spacing: -0.01em`)
- Hover changes color to text-soft
- Active item shows a 2px primary-blue underline (border-block-end)
- Count badge: pill-shaped, primary-blue background, 20px tall, animates with `zui-badge-float` (gentle vertical bob)
- Mobile (782px): horizontal scroll when items overflow

### Common mistakes

- Using flat `<a>` siblings without `<nav class="zui-tabs">` wrapper — no strip layout, no underline-active state
- Using `<input type="radio">` + `<label>` CSS-toggle pattern — incompatible with the canonical activation model
- Forgetting `aria-current="page"` on active item — accessibility failure
- Missing `is-active` on active item — no underline

### Correct example

```html
<nav class="zui-tabs" aria-label="Settings sections">
  <a class="zui-tabs__item is-active" href="?tab=general" aria-current="page">General</a>
  <a class="zui-tabs__item" href="?tab=advanced">Advanced</a>
  <a class="zui-tabs__item" href="?tab=integrations">
    Integrations <span class="zui-tabs__badge">3</span>
  </a>
</nav>
```

### Incorrect example

```html
<!-- Flat structure, no nav wrapper -->
<a class="zui-tabs__item is-active">General</a>
<a class="zui-tabs__item">Advanced</a>
<!-- Result: items render but the 44px strip + bottom border + horizontal padding never apply -->
```

### Migration notes

Where a plugin uses a CSS-radio sibling toggle to switch sections without page
reload, the radios must be removed and the activation switched to URL-based
(`href="?tab=…"`). PHP determines the active tab from `$_GET['tab']` and
renders one section. JS that currently binds to radio change events needs to
be reviewed before this migration step.

---

## Tab Panel (`.zui-tab-panel`)

### Purpose

Per-tab container. One panel per top tab. Only the active panel is visible —
visibility is driven by the HTML `hidden` attribute (library honors it via
the scoped reset).

### Required Structure

```html
<div class="zui-tab-panel" data-tab="general">
  <!-- Active tab content -->
</div>

<div class="zui-tab-panel" data-tab="advanced" hidden>
  <!-- Hidden tab content -->
</div>
```

### Required classes

- `.zui-tab-panel`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

Content is plugin-determined — typically a Layout (sidebar + content) or just
a Section.

### Parent requirements

Must be inside `.zui-scope`. Sits below the Header.

### Child requirements

Any content (Layout, Section, Card, etc.).

### Expected visual result

`.zui-tab-panel` has no visual chrome — it is a pure wrapper that participates
in the `.zui-scope` flex column. Hidden panels are not rendered.

### Common mistakes

- Using `display:none` instead of the `hidden` attribute — works visually but inconsistent
- Forgetting `data-tab` attribute — breaks JS that expects to query by tab key

### Correct example

```html
<div class="zui-tab-panel" data-tab="general">
  <div class="zui-layout">…</div>
</div>
<div class="zui-tab-panel" data-tab="advanced" hidden>
  <div class="zui-layout">…</div>
</div>
```

### Incorrect example

```html
<div class="zui-tab-panel" data-tab="advanced" style="display:none">
<!-- Works visually but inconsistent with library contract; use `hidden` attribute -->
```

### Migration notes

Wrap each top-tab's content in a `.zui-tab-panel` element with `data-tab="…"`.
Add `hidden` to inactive panels. If a plugin currently relies on a single
`<section>` per tab visible-via-CSS, replace that with `.zui-tab-panel` and
let PHP set the `hidden` attribute based on `$_GET['tab']`.

---

## Layout (`.zui-layout`)

### Purpose

The 2-column page body row inside a tab panel. Sidebar on the left, content
on the right. Has a full-width variant for pages with no sidebar.

### Required Structure

**Default (sidebar + content):**

```html
<div class="zui-layout">
  <div class="zui-sidebar__overlay" data-drawer-close></div>
  <aside class="zui-sidebar" id="page-sidebar">…</aside>
  <main class="zui-content" id="page-content">…</main>
</div>
```

**Full-width (no sidebar):**

```html
<div class="zui-layout zui-layout--full">
  <main class="zui-content zui-content--full">
    <!-- module content -->
  </main>
</div>
```

### Required classes

- `.zui-layout` on the outer container
- `.zui-content` on the right column

### Optional classes

- `.zui-layout--full` modifier — for pages without a sidebar
- `.zui-content--full` paired modifier on the content element

### Variants

- Default (with sidebar)
- Full-width

### Modifiers

- `--full` on both block and content element when no sidebar is used

### Nested elements

- `.zui-sidebar__overlay` (mobile drawer backdrop) — only with the default sidebar variant
- `.zui-sidebar` (sidebar component, left)
- `.zui-content` (main column, right) — must be `<main>`

### Parent requirements

`.zui-scope`. May sit inside a Tab Panel.

### Child requirements

`.zui-sidebar` + `.zui-content` (default), or `.zui-content` alone (full variant).

### Expected visual result

- `flex: 1 1 auto; display: flex; gap: 16px; padding: 24px; min-height: 0; align-items: flex-start` — horizontal row of sidebar + content
- The `--full` modifier is a marker class with no visual rules — its purpose is to declare intent and pair with `__full` on the content
- Mobile (782px): padding shrinks to 16px; sidebar becomes a drawer (positioned fixed, hidden by default)

### Common mistakes

- Using `<div>` for `.zui-content` instead of `<main>` — semantic deviation
- Forgetting `--full` modifier when there is no sidebar — content does not stretch
- Mixing `--full` on `.zui-layout` but not on `.zui-content` (or vice versa) — inconsistent variant

### Correct example

```html
<div class="zui-layout zui-layout--full">
  <main class="zui-content zui-content--full" id="content">
    <section class="zui-section">…</section>
  </main>
</div>
```

### Incorrect example

```html
<!-- Wrong: <div> instead of <main> -->
<div class="zui-layout">
  <aside class="zui-sidebar">…</aside>
  <div class="zui-content">…</div>
</div>
```

### Migration notes

For pages with no sidebar, use `--full` variant. For pages with a sidebar nav
(Settings page with sub-section nav), use the default variant. Mobile drawer
behavior requires `.zui-sidebar__overlay` to exist as the first child.

---

## Content (`.zui-content`)

### Purpose

The main content column. Holds Section panels (one per sidebar nav item) or
direct content (cards, rows). Element type is `<main>`.

### Required Structure

```html
<main class="zui-content" id="page-content">
  <form id="page-form" method="post">

    <section class="zui-section is-active" data-section="general">
      <!-- Section header + cards -->
    </section>

    <section class="zui-section" data-section="advanced" hidden>
      …
    </section>

  </form>
</main>
```

### Required classes

- `.zui-content` on `<main>`

### Optional classes

- `.zui-content--full` paired with `.zui-layout--full` for the full-width variant

### Variants

- Default
- `--full` (paired with the layout variant)

### Modifiers

- `--full`

### Nested elements

Typically `<form>` containing one or more `<section class="zui-section">`
panels. Sections may be inlined directly without a form when they don't post.

### Parent requirements

Inside `.zui-layout`.

### Child requirements

Any content. Canonical pattern is `<form>` → `<section class="zui-section">` cards.

### Expected visual result

- `flex: 1 1 auto; min-width: 0`
- The `--full` modifier has identical rules — exists to mark intent

### Common mistakes

- Using `<div>` instead of `<main>` — semantic deviation
- Multiple `.zui-content` siblings — only one main content column per layout

### Correct example

```html
<main class="zui-content zui-content--full" id="page-content">
  <form id="settings-form" method="post">
    <section class="zui-section" data-section="general">…</section>
  </form>
</main>
```

### Incorrect example

```html
<div class="zui-content">…</div>  <!-- wrong element type -->
```

### Migration notes

Convert the existing content wrapper `<div>` to `<main>` while preserving its
JS hook classes and id. If the page has multiple settings sections that
previously stacked one-after-another, wrap each in a `<section
class="zui-section">` and use the `hidden` attribute + URL routing to show
one at a time.

---

## Section (`.zui-section`)

### Purpose

A vertical stack of one section header + one or more cards. One section per
sidebar nav item. Hidden when not active.

### Required Structure

```html
<section class="zui-section" data-section="general">
  <div class="zui-section-header">…</div>
  <div class="zui-card">…</div>
  <div class="zui-card">…</div>
</section>

<section class="zui-section" data-section="advanced" hidden>
  …
</section>
```

### Required classes

- `.zui-section`

### Optional classes

- `.is-active` on the visible section (when JS manages visibility)
- `hidden` HTML attribute on inactive sections (when PHP/server manages visibility)

### Variants

None.

### Modifiers

None.

### Nested elements

- `.zui-section-header` (one)
- One or more `.zui-card` panels

### Parent requirements

Inside `.zui-content`.

### Child requirements

Per the canonical pattern, section header followed by cards.

### Expected visual result

- `display: flex; flex-direction: column; gap: 16px` — vertical stack with 16px gap between header and cards
- Honors the `hidden` attribute via the scoped reset (`[hidden] { display: none !important }`)

### Common mistakes

- Using `<div>` instead of `<section>` — semantic deviation
- Missing `data-section` attribute — breaks JS that queries sections by key
- Forgetting `hidden` attribute on inactive sections — all sections show at once

### Correct example

```html
<section class="zui-section" data-section="general">
  <div class="zui-section-header">…</div>
  <div class="zui-card">…rows…</div>
</section>
```

### Incorrect example

```html
<div class="zui-section">…</div>  <!-- not a <section> -->
```

### Migration notes

Replace existing settings-section wrappers with `<section class="zui-section"
data-section="…">`. Hide inactive ones with the `hidden` attribute; let PHP
decide which is active based on URL or saved state.

---

## Sidebar (`.zui-sidebar`)

### Purpose

The left column of the page layout. Holds section nav (one item per
`.zui-section`) plus an optional Quick Help block at the bottom. On mobile,
slides in as a drawer when `.zui-sidebar-open` is set on the scope root.

### Required Structure

```html
<aside class="zui-sidebar" id="page-sidebar">

  <div class="zui-sidebar__mobile-head">
    <span class="zui-sidebar__mobile-title">Plugin Settings</span>
    <button type="button" class="zui-sidebar__close" data-drawer-close aria-label="Close menu">
      <svg class="zui-icon"><!-- x --></svg>
    </button>
  </div>

  <nav class="zui-sidebar__nav" aria-label="Settings sections">
    <button type="button" class="zui-sidebar__item is-active"
            data-section="general"
            aria-controls="section-general"
            aria-current="true">
      <span class="zui-sidebar__icon"><svg class="zui-icon">…</svg></span>
      <span class="zui-sidebar__label">General</span>
    </button>

    <a class="zui-sidebar__item" href="?section=advanced" data-section="advanced">
      <span class="zui-sidebar__icon"><svg class="zui-icon">…</svg></span>
      <span class="zui-sidebar__label">Advanced</span>
      <span class="zui-sidebar__badge">3</span>
    </a>
  </nav>

  <!-- Quickhelp goes here — see Quick Help component -->

</aside>
```

### Required classes

- `.zui-sidebar` on `<aside>`
- `.zui-sidebar__nav` on the nav container
- `.zui-sidebar__item` on each item
- `.zui-sidebar__icon` on each item's icon wrapper
- `.zui-sidebar__label` on each item's label text

### Optional classes

- `.zui-sidebar__mobile-head` + `.zui-sidebar__mobile-title` + `.zui-sidebar__close` — mobile drawer header
- `.zui-sidebar__badge` — small count badge on a nav item
- `.is-active` on the current item

### Variants

None.

### Modifiers

None.

### Nested elements

- `__mobile-head` with `__mobile-title` + `__close` (optional)
- `__nav` with `__item` children (each containing `__icon` + `__label`, optionally `__badge`)

### Parent requirements

Inside `.zui-layout` (default variant).

### Child requirements

At least one `__item`. May also contain a Quick Help block as a sibling of
`__nav`.

### Expected visual result

- 290px-wide white panel with rounded `--zui-radius-2xl` corners, border, soft shadow, 20px padding
- Items are 100%-wide flex rows with icon (18×18) + label + optional badge
- Active item: tinted-blue background, primary-600 text, active icon recolored, label semibold
- Hover: soft gray background
- Mobile (782px): becomes a fixed-position drawer translated off-screen; `.zui-sidebar-open` on `.zui-scope` slides it in

### Auto-wiring (JS)

When `__item` carries `data-zui-section="key"`, the library's `_wireShell` (registered by `ZUI.scan()`) handles the activation client-side:

- Click on a `__item[data-zui-section]` → adds `.is-active` + `aria-current="true"` to it, removes them from siblings
- Toggles `.is-active` + `hidden` on every `.zui-section[data-zui-section]` based on whether its key matches
- Auto-closes the mobile drawer (`.zui-sidebar-open` removed from scope)
- Dispatches a `zui:section` `CustomEvent` (detail: `{ section: key }`) on the nav

```html
<aside class="zui-sidebar">
  <nav class="zui-sidebar__nav">
    <button class="zui-sidebar__item is-active" data-zui-section="general" aria-current="true">…</button>
    <button class="zui-sidebar__item" data-zui-section="advanced">…</button>
  </nav>
</aside>

<main class="zui-content">
  <section class="zui-section is-active" data-zui-section="general">…</section>
  <section class="zui-section" data-zui-section="advanced" hidden>…</section>
</main>
```

### Common mistakes

- Using `<div>` instead of `<aside>` — semantic deviation
- Missing `.zui-sidebar__icon` wrapper — icon doesn't render
- Missing `.zui-sidebar__nav` — items don't stack as a vertical nav
- Mixing `<a>` and `<button>` items inconsistently — both work but be consistent within a single sidebar

### Correct example

```html
<aside class="zui-sidebar" id="settings-sidebar">
  <nav class="zui-sidebar__nav">
    <a class="zui-sidebar__item is-active" href="?section=general" aria-current="true">
      <span class="zui-sidebar__icon"><svg class="zui-icon">…</svg></span>
      <span class="zui-sidebar__label">General</span>
    </a>
  </nav>
  <div class="zui-quickhelp">…</div>
</aside>
```

### Incorrect example

```html
<div class="zui-sidebar">  <!-- wrong element -->
  <a class="zui-sidebar__item">General</a>  <!-- no __icon or __label wrappers -->
</div>
```

### Migration notes

If the plugin currently uses an accordion stack instead of a sidebar, deciding
to add a sidebar is a UX decision. Once added, each accordion section becomes
a sidebar nav item + a separate `<section class="zui-section">` panel in the
content column. JS that programmatically expands accordions needs to be
updated to navigate via URL change (`?section=…`) or to add `is-active`
classes via JS.

---

## Quick Help (`.zui-quickhelp`)

### Purpose

Tinted help block at the bottom of the sidebar. Title, one-line description, 2
documentation links, and a small decorative illustration.

### Required Structure

```html
<div class="zui-quickhelp">
  <h4 class="zui-quickhelp__title">Quick Help</h4>
  <p class="zui-quickhelp__text">Learn how to configure for best results.</p>

  <div class="zui-quickhelp__links">
    <a class="zui-quickhelp__link" href="…" target="_blank" rel="noreferrer noopener">
      <span>View Documentation</span>
      <svg class="zui-icon"><!-- arrow-right --></svg>
    </a>
    <a class="zui-quickhelp__link zui-quickhelp__link--muted" href="…" target="_blank" rel="noreferrer noopener">
      <span>Get Support</span>
      <svg class="zui-icon"><!-- arrow-right --></svg>
    </a>
  </div>

  <div class="zui-quickhelp__art" aria-hidden="true">
    <span class="zui-quickhelp__paper zui-quickhelp__paper--1"></span>
    <span class="zui-quickhelp__paper zui-quickhelp__paper--2"></span>
    <span class="zui-quickhelp__sphere"></span>
    <span class="zui-quickhelp__check">✓</span>
  </div>
</div>
```

### Required classes

- `.zui-quickhelp`
- `.zui-quickhelp__title`
- `.zui-quickhelp__text`
- `.zui-quickhelp__links`
- `.zui-quickhelp__link` on each link
- `.zui-quickhelp__art` decorative wrapper
- `.zui-quickhelp__paper` (2× with `--1` and `--2` modifiers)
- `.zui-quickhelp__sphere`
- `.zui-quickhelp__check`

### Optional classes

- `.zui-quickhelp__link--muted` on secondary link

### Variants

None on the block.

### Modifiers

- `__paper--1` / `__paper--2` for the two paper accents
- `__link--muted` for the secondary link visual

### Nested elements

All listed above are required.

### Parent requirements

Inside `.zui-sidebar` (canonical placement).

### Child requirements

Title, text, links container, art container.

### Expected visual result

- Soft-blue tinted background, large rounded corners, 20px padding
- Title in strong dark text, description in muted slate, two action links with right-arrow icons
- Decorative illustration: layered paper rectangles, primary sphere, green check mark
- Acts as a "you can do more" affordance — visually invites exploration

### Common mistakes

- Missing `__art` block — the illustration doesn't render
- Missing `__paper--1` / `__paper--2` modifier — paper accents have no shape
- Putting Quick Help outside the sidebar — works but loses visual rhythm

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-quickhelp">
  <h4>Quick Help</h4>  <!-- missing __title class -->
  <p>Description</p>   <!-- missing __text class -->
  <a href="…">Docs</a> <!-- missing __link class -->
</div>
```

### Migration notes

Replace any existing "Help / Support / Docs" dropdown or hamburger menu with
a Quick Help block at the bottom of the sidebar. Move the existing
documentation + support URLs into the two `__link` anchors. Verify
documentation links open in a new tab (`target="_blank"`).

---

# SECTION BANNER

## Section Header (`.zui-section-header`)

### Purpose

The banner at the top of each section. Left cluster: large icon tile + title
+ optional badge + sub-description. Right cluster: optional secondary action
link + primary Save button.

### Required Structure

```html
<div class="zui-section-header">

  <div class="zui-section-header__main">
    <span class="zui-section-header__icon">
      <svg class="zui-icon"><!-- icon --></svg>
    </span>
    <div class="zui-section-header__text">
      <div class="zui-section-header__titlewrap">
        <h2 class="zui-section-header__title">Section Title</h2>
        <span class="zui-section-header__badge">PRO</span>
      </div>
      <p class="zui-section-header__sub">
        One-line description of the section's purpose.
      </p>
    </div>
  </div>

  <div class="zui-section-header__actions">

    <a class="zui-section-header__action" href="…">
      <svg class="zui-icon"><!-- eye --></svg>
      <span>Preview</span>
    </a>

    <span class="zui-savebtn-wrap">
      <button type="button" class="zui-savebtn woocommerce-save-button" disabled>
        <span class="zui-savebtn__label">Save Changes</span>
        <span class="zui-savebtn__spinner spinner workflow_spinner"></span>
      </button>
    </span>

  </div>

</div>
```

### Required classes

- `.zui-section-header`
- `.zui-section-header__main`
- `.zui-section-header__icon`
- `.zui-section-header__text`
- `.zui-section-header__titlewrap`
- `.zui-section-header__title` on `<h2>`
- `.zui-section-header__sub` on `<p>`
- `.zui-section-header__actions`

### Optional classes

- `.zui-section-header__badge` inside `__titlewrap`
- `.zui-section-header__action` for secondary action links

### Variants

None.

### Modifiers

None.

### Nested elements

All elements listed above are part of the canonical structure. Skipping any of
them breaks layout.

### Parent requirements

Inside `.zui-section`.

### Child requirements

Two children: `__main` (left cluster) and `__actions` (right cluster).

### Expected visual result

- White card with primary-200 hairline border, 2xl rounded corners, soft shadow, 24px padding
- Flex row with `justify-content: space-between` and 24px gap
- Icon: 64×64 primary-blue tile with shadow, white SVG icon (32×32)
- Title: 20px semibold, slightly tighter letter-spacing
- Sub: 12px muted text below title
- Badge: small uppercase JetBrains-Mono pill with primary-50 background
- Actions cluster on the right with secondary action link + primary Save button
- Mobile (782px): flex direction becomes column, items align to start

### Common mistakes

- Putting the title in a `<label>` instead of `<h2>` — typography wrong
- Skipping `__main` wrapper — left cluster doesn't flex
- Skipping `__text` wrapper — title and sub don't sit next to icon properly
- Skipping `__titlewrap` — title and badge don't sit side-by-side
- Forgetting `__sub` — section banner looks bare; sub-description is canonical
- Missing the Save button structure entirely

### Correct example

```html
<div class="zui-section-header">
  <div class="zui-section-header__main">
    <span class="zui-section-header__icon">
      <svg class="zui-icon">…</svg>
    </span>
    <div class="zui-section-header__text">
      <div class="zui-section-header__titlewrap">
        <h2 class="zui-section-header__title">General Settings</h2>
      </div>
      <p class="zui-section-header__sub">Configure display and routing options.</p>
    </div>
  </div>
  <div class="zui-section-header__actions">
    <span class="zui-savebtn-wrap">
      <button type="submit" class="zui-savebtn">
        <span class="zui-savebtn__label">Save Changes</span>
        <span class="zui-savebtn__spinner spinner"></span>
      </button>
    </span>
  </div>
</div>
```

### Incorrect example

```html
<div class="zui-section-header">
  <label>General Settings
    <button>Save</button>
  </label>
</div>
<!-- Result: no canonical layout, no icon tile, no title typography — looks bare -->
```

### Migration notes

If a plugin currently uses an accordion-heading row (with a `<label>` wrapper
and inline save button) as its section banner, the rebuild requires:

- Removing the `<label>` outer wrapper
- Building the `__main` + `__icon` + `__text` + `__titlewrap` + `__title` chain
- Adding a `__sub` description (often new copy)
- Moving the save button into `__actions` with canonical `.zui-savebtn` structure (label span + spinner span inside the button)
- Choosing an icon glyph per section

JS that targets the label or the chevron inside the label needs to be reviewed
before this migration step.

---

## Save Button (`.zui-savebtn`)

### Purpose

Primary save button used inside a Section Header banner. Composes with the WC
AJAX hook `.woocommerce-save-button` when the section is live.

### Required Structure

```html
<span class="zui-savebtn-wrap">
  <button type="button" class="zui-savebtn woocommerce-save-button" disabled>
    <span class="zui-savebtn__label">Save Changes</span>
    <span class="zui-savebtn__spinner spinner workflow_spinner"></span>
  </button>
</span>
```

### Required classes

- `.zui-savebtn-wrap` on the outer span
- `.zui-savebtn` on the button

### Optional classes

- `.woocommerce-save-button` JS hook for WC AJAX save handler
- `.zui-savebtn--inert` modifier for the pre-live placeholder variant

### Variants

- Default — interactive save button
- `--inert` — placeholder when the section isn't yet live (disabled)

### Modifiers

- `--inert`

### Nested elements

- `.zui-savebtn__label` (text span inside button)
- `.zui-savebtn__spinner` with optional `.spinner.workflow_spinner` co-classes

### Parent requirements

Inside `.zui-section-header__actions`. The `.zui-section-header .zui-savebtn`
descendant selector controls most of the visual styling.

### Child requirements

Label span + spinner span.

### Expected visual result

- Primary blue button: 10px×20px padding, white text, 12px semibold, 2xl rounded corners, soft shadow
- Hover: primary-hover color
- Disabled: 55% opacity, not-allowed cursor
- Spinner: hidden by default; JS toggles `.is-active` to show during save

### Common mistakes

- Not wrapping the button in `.zui-savebtn-wrap` — section header actions cluster doesn't flex correctly
- Putting label text directly in button without `__label` span — wrong typography
- Placing spinner outside the button (e.g. as a sibling `<div>`) — breaks the canonical `__spinner` slot

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<button class="zui-savebtn">Save Changes</button>  <!-- missing wrap + label/spinner spans -->
<div class="spinner"></div>  <!-- spinner outside button — wrong -->
```

### Migration notes

If the plugin currently uses a `<button class="my-plugin-save button-primary
woocommerce-save-button">Save</button>` pattern with an external spinner div,
the rebuild:

- Wraps the button in `<span class="zui-savebtn-wrap">`
- Wraps the button label text in `<span class="zui-savebtn__label">`
- Moves the spinner inside the button as `<span class="zui-savebtn__spinner spinner workflow_spinner">`
- Adds `.zui-savebtn` to the button (alongside existing JS-hook classes)

JS that toggles spinner visibility via `.spinner.is-active` continues to work
because the spinner element preserves those legacy class names.

### JS Lifecycle States

The button has three runtime states the library can drive for any AJAX save
flow. The label text inside `.zui-savebtn__label` is swapped automatically;
the original text is captured on the first transition and restored on reset.

| State class    | Visual                                         | When                                                                       |
|----------------|------------------------------------------------|----------------------------------------------------------------------------|
| (none)         | Primary blue button, full opacity              | Idle / dirty (ready to save)                                               |
| `.is-saving`   | 55% opacity, `cursor: wait`, pointer-events off, label → "Saving…" | While the AJAX request is in flight                                        |
| `.is-saved`    | Green background (`--zui-success`), label → "Saved" | After success — auto-clears back to idle after `delay` (default 3000 ms)   |

### Label-swap attributes (optional)

Override the default text used during the `is-saving` / `is-saved` transitions:

```html
<button type="button"
        class="zui-savebtn woocommerce-save-button"
        data-label-saving="Saving settings…"
        data-label-saved="All set ✓">
    <span class="zui-savebtn__label">Save Changes</span>
</button>
```

Defaults: `Saving…` and `Saved`. If `.zui-savebtn__label` exists it is used as
the text slot; otherwise the library falls back to the first non-icon `<span>`
child, then to the button itself.

### Public JS API

Available on `window.ZUI` once `zui.js` is loaded. Use these to drive the same
visual lifecycle from any plugin-specific AJAX flow (not just the section-
header save).

```js
// Enter the "Saving…" state — captures original label text.
ZUI.btnSaving( btn );

// Enter the green "Saved" state — auto-reverts after `delay` ms.
ZUI.btnSaved( btn, {
    delay: 3000,         // optional — ms before reverting to idle (default 3000)
    disableAfter: false, // optional — leave the button disabled when it reverts
} );

// Clear saving/saved without flashing the green confirmation
// (use when an external snackbar already confirms success).
ZUI.btnReset( btn );
```

### Auto-bound buttons

The library automatically calls `btnSaving` on click for these license-tab
controls — no plugin code needed:

- `.zui-lic-activate` (anchor that navigates to the activation URL)
- `.zui-lic-deactivate` (form submit button)

Both transitions complete on the subsequent page reload, so only the "entering
Saving…" half is wired by the library.

### Common JS mistake

Calling `btn.disabled = true` instead of `ZUI.btnSaving(btn)` — the button
will appear inert but the label won't change to "Saving…" and the user will
think nothing happened. Always use the lifecycle helpers so the visual
contract stays consistent across plugins.

---

# CONTAINERS

## Card (`.zui-card`)

### Purpose

Generic content container. White surface with hairline border and subtle
shadow. Composes with module modifier classes to add per-context styling
(carrier card, license card, plugin promo card, etc.).

### Required Structure

```html
<!-- Base -->
<div class="zui-card">
  <!-- content -->
</div>

<!-- Empty-state variant -->
<div class="zui-card zui-placeholder">
  <p class="zui-placeholder__title">Section name</p>
  <p class="zui-placeholder__text">Description of the empty state.</p>
</div>

<!-- Slotted card (for cards with their own title + body + footer) -->
<div class="zui-card">
  <div class="zui-card__head">
    <h4 class="zui-card__title">Section Title</h4>
    <p class="zui-card__sub">One-line description.</p>
  </div>
  <div class="zui-card__body">
    <!-- Free-content region (use this when not a list of .zui-row) -->
  </div>
  <div class="zui-card__foot">
    <span class="zui-card__foot-note">Last updated 2 min ago</span>
    <div class="zui-card__foot-actions">
      <button class="zui-btn-ghost">Cancel</button>
      <button class="zui-btn-primary">Save</button>
    </div>
  </div>
</div>

<!-- Composed with module modifier -->
<div class="zui-card my-plugin-feature-card" data-id="42">
  <!-- module class adds delta-specific layout / states -->
</div>
```

### Required classes

- `.zui-card`

### Optional classes

- `.zui-placeholder` (empty-state composer with `__title` + `__text` slots)
- `.zui-card__head` — light in-card header (`__title` + `__sub` inside)
- `.zui-card__title` — 16px bold strong heading inside `__head`
- `.zui-card__sub` — 12px muted sub-description inside `__head`
- `.zui-card__body` — padded free-content region (use when card isn't a list of `.zui-row`)
- `.zui-card__foot` — bottom footer row with divider on top
- `.zui-card__foot-note` — small muted note on the left of `__foot`
- `.zui-card__foot-actions` — action button cluster on the right of `__foot`
- Module-specific modifier classes for per-context deltas

### Variants

- Default
- `.zui-card.zui-placeholder` (empty / coming-soon)
- Slotted (with `__head` / `__body` / `__foot` chain)
- Composition: `.zui-card.zui-lic-card`, `.zui-card.zui-lic-plugin`, etc. (license family)

### Modifiers

None on the block itself. Compose with module classes.

### Nested elements

None required on the base. Slot variants add `__head` (with `__title` + `__sub`), `__body`, `__foot` (with `__foot-note` + `__foot-actions`). Placeholder variant adds `__title` + `__text`.

### Parent requirements

Inside `.zui-section` (canonical) or anywhere in `.zui-scope`.

### Child requirements

Any plugin content.

### Expected visual result

- White surface (`--zui-surface`)
- 1px solid border (`--zui-border`)
- `--zui-radius-2xl` rounded corners (16px)
- Soft shadow (`--zui-shadow-sm`)
- **Slot variants:**
  - `__head`: 24px top/inline padding (no bottom padding)
  - `__title`: 16px bold strong text
  - `__sub`: 12px muted text, 4px top margin
  - `__body`: 24px padding (free-content region); when sits directly under `__head`, top padding shrinks to 16px to avoid double-pad
  - `__foot`: flex row, space-between, 20×24 padding, top divider hairline
  - `__foot-note`: small muted note (left aligned)
  - `__foot-actions`: flex cluster of buttons (right aligned)

### Common mistakes

- Adding padding inline — let the inner Form Row component (`.zui-card .zui-row`) handle padding canonically
- Mixing card modifiers — only one composition modifier per card
- Wrapping a card inside another card — flat structure preferred

### Correct example

```html
<div class="zui-card">
  <div class="zui-row">
    <div class="zui-row__head"><span class="zui-row__label">Field</span></div>
    <div class="zui-row__control"><input type="text" class="zui-input"></div>
  </div>
</div>
```

### Incorrect example

```html
<div class="zui-card" style="padding: 20px">  <!-- inline padding fights row padding -->
  …
</div>
```

### Migration notes

Replace plugin panel wrappers (`<div class="panel">`, `<div class="settings-card">`)
with `<div class="zui-card">`. Keep the legacy class alongside as a JS hook.
Inner padding comes from `.zui-row` automatically; do not add inline padding.

---

## Card Grid (`.zui-card-grid`)

### Purpose

Responsive grid container for equal-width children. Default 5-column auto-
responsive grid that drops to 4 / 3 / 2 / 1 columns at 1500 / 1300 / 900 / 600px.

### Required Structure

```html
<div class="zui-card-grid">
  <div class="zui-card">…</div>
  <div class="zui-card">…</div>
  <div class="zui-card">…</div>
</div>

<!-- Force 3 columns at all widths ≥ 900px -->
<div class="zui-card-grid zui-card-grid--cols-3">…</div>

<!-- Force 2 columns at all widths ≥ 600px -->
<div class="zui-card-grid zui-card-grid--cols-2">…</div>
```

### Required classes

- `.zui-card-grid`

### Optional classes

- `.zui-card-grid--cols-3`
- `.zui-card-grid--cols-2`

### Variants

- Default (5 → 4 → 3 → 2 → 1 responsive)
- `--cols-3` (forced 3-col at ≥ 900)
- `--cols-2` (forced 2-col at ≥ 600)

### Modifiers

(See variants.)

### Nested elements

Any equal-width children — typically `.zui-card` items.

### Parent requirements

Inside `.zui-scope` and `.zui-content`.

### Child requirements

One or more `.zui-card` items.

### Expected visual result

- CSS Grid with `grid-template-columns: repeat(5, 1fr)` by default
- 16px gap between cards
- Responsive breakpoints at 1500 / 1300 / 900 / 600px

### Common mistakes

- Mixing card widths inside the grid — they all become equal-width
- Forgetting `--cols-3` or `--cols-2` when intent is fixed columns

### Correct example

```html
<div class="zui-card-grid zui-card-grid--cols-3">
  <div class="zui-card">…</div>
  <div class="zui-card">…</div>
  <div class="zui-card">…</div>
</div>
```

### Incorrect example

```html
<div class="zui-card-grid">
  <div class="zui-card" style="width: 50%">…</div>  <!-- inline width fights grid -->
</div>
```

### Migration notes

Replace any custom flex/grid wrappers around repeated cards with
`.zui-card-grid`. Choose a variant modifier if the design specifies a fixed
column count.

---

# FORM PATTERNS

## Form Row (`.zui-row`)

### Purpose

The universal label + control row used inside every settings card. Two layout
variants: stacked (default — label/desc above, control below) and inline
(label-left + control-right — used for toggles and checkboxes).

### Required Structure

**Inline variant** (for toggles / checkboxes):

```html
<div class="zui-row zui-row--inline">

  <div class="zui-row__head">
    <span class="zui-row__label">
      Auto-detect carrier
      <span class="zui-tooltip" tabindex="0" role="img" aria-label="…">
        <svg class="zui-icon">…</svg>
        <span class="zui-tooltip__bubble">…<span class="zui-tooltip__arrow"></span></span>
      </span>
    </span>
    <p class="zui-row__desc">Detect provider from tracking number patterns.</p>
  </div>

  <div class="zui-row__control">
    <!-- Toggle or Checkbox -->
    <label class="zui-toggle">…</label>
  </div>

</div>
```

**Stacked variant** (for text/select/textarea/radio/multi-select):

```html
<div class="zui-row">

  <div class="zui-row__head">
    <span class="zui-row__label">
      Tracking provider
      <span class="zui-tooltip" …>…</span>
    </span>
    <p class="zui-row__desc">Choose the carrier for this shipment.</p>
  </div>

  <div class="zui-row__control">
    <input type="text" class="zui-input">
    <p class="zui-row__hint">
      Use this format for international shipments.
      <a href="…">View docs</a>
    </p>
    <span class="zui-row__notice"></span>
  </div>

</div>
```

### Required classes

- `.zui-row`
- `.zui-row__head`
- `.zui-row__label`
- `.zui-row__control`

### Optional classes

- `.zui-row--inline` modifier (label-left + control-right)
- `.zui-row--flush` modifier (drops the row's bottom divider — for grouped rows with explicit separators)
- `.zui-row--highlight` modifier (tinted edge-bleed band — for emphasised dependent rows)
- `.zui-row__desc` (one-line description under label)
- `.zui-row__hint` (small hint text below the control)
- `.zui-row__notice` (feedback area for inline messages)
- `.zui-row-divider` (single hairline between flush rows — sibling element, not a `.zui-row`)
- `.zui-control-group` (side-by-side controls inside one `__control` — e.g. input + select pair)
- `.zui-row.multiple_checkbox_label` (legacy schema modifier — block layout)
- `.zui-row.button` (legacy schema modifier — block layout for button-only rows)

### Variants

- Default (stacked)
- `--inline` (horizontal)
- `--flush` (no bottom divider — pair with `.zui-row-divider` for grouped sections)
- `--highlight` (tinted band — bleeds to card edge for emphasised sub-panel)

### Modifiers

- `--inline`
- `--flush`
- `--highlight`
- Two legacy schema modifiers on the block (`.multiple_checkbox_label`, `.button`) for compatibility

### Grouping with explicit dividers

When a schema groups several fields under a single visual separator (instead of a divider after every row), use `.zui-row--flush` to suppress each row's bottom divider and insert a `.zui-row-divider` element between groups:

```html
<div class="zui-card">
  <div class="zui-row zui-row--flush">…</div>
  <div class="zui-row zui-row--flush">…</div>
  <div class="zui-row-divider"></div>
  <div class="zui-row zui-row--flush">…</div>
  <div class="zui-row zui-row--flush">…</div>
</div>
```

### Side-by-side controls

When a single `__control` slot needs two adjacent controls (e.g. amount + comparator, input + copy button), wrap them in `.zui-control-group`:

```html
<div class="zui-row__control">
  <div class="zui-control-group">
    <input type="number" class="zui-input">
    <div class="zui-select-wrap">
      <select class="zui-select">…</select>
      <span class="zui-select-chevron">…</span>
    </div>
  </div>
</div>
```

### Nested elements

- `__head` (required) with `__label` + optional `__desc`
- `__control` (required) with the form control + optional `__hint` + `__notice`

### Parent requirements

Inside `.zui-card` (canonical). Padding rule is `.zui-card .zui-row { padding: 32px; border-block-end: 1px solid divider }`. Without `.zui-card` parent, padding does not apply.

### Child requirements

`__head` then `__control`, in that order.

### Expected visual result

- Each row: 32px padding, bottom border (`--zui-divider`) — except the last row which has no border-block-end
- Mobile (782px): padding shrinks to 24px
- Inline variant: flex with `justify-content: space-between`
- `__label`: 14px semibold; `__desc`: 12px muted; `__hint`: 11px muted
- Links inside `__desc` / `__hint`: primary blue, semibold, no underline (underline on hover)

### Common mistakes

- Putting `.zui-row` outside `.zui-card` — no padding, no border
- Forgetting `__head` and `__control` wrappers — content stacks without layout
- Mixing inline variant for stacked control types (e.g. textarea) — visual mismatch

### Correct example

```html
<div class="zui-card">
  <div class="zui-row">
    <div class="zui-row__head">
      <span class="zui-row__label">Field name</span>
      <p class="zui-row__desc">Field description.</p>
    </div>
    <div class="zui-row__control">
      <input type="text" class="zui-input">
      <p class="zui-row__hint">Helpful hint below.</p>
    </div>
  </div>
</div>
```

### Incorrect example

```html
<div class="zui-row">  <!-- outside .zui-card — no padding -->
  <label>Field</label>  <!-- missing __head and __label wrappers -->
  <input type="text">   <!-- missing __control wrapper -->
</div>
```

### Migration notes

Replace existing `<table class="form-table"><tr><th><label>…</label></th><td>…</td></tr></table>`
patterns with `.zui-row` divs inside `.zui-card`. Each `<tr>` becomes a
`.zui-row`. The `<th>` cell becomes `__head` with `__label`; `<td>` becomes
`__control`. Tooltips, hints, and notices move into their canonical slots.

---

## List Row (`.zui-list-row`)

### Purpose

Reorderable list-row pattern: drag handle + toggle + title + meta + status
pill + action icons. Used for repeated list items (rules, locations, reasons,
notification rows).

### Required Structure

```html
<div class="zui-list-row">

  <span class="zui-drag-handle">⋮⋮</span>

  <label class="zui-toggle">
    <input type="hidden" name="…" value="0">
    <input type="checkbox" class="zui-toggle__input" name="…" value="1">
    <span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
  </label>

  <div class="zui-list-row-body">
    <strong>Row title</strong>
    <span>Meta line</span>
  </div>

  <span class="zui-badge zui-badge--success">Active</span>

  <button class="zui-btn-ghost" title="Edit">…</button>
</div>
```

### Required classes

- `.zui-list-row`

### Optional classes

- `.is-collapsed` state
- `.zui-list-row-body` for the title + meta cluster
- `.zui-drag-handle` for the drag handle

### Variants

None.

### Modifiers

None.

### Nested elements

- `.zui-drag-handle` (optional)
- A toggle (`.zui-toggle`) for enable/disable
- `.zui-list-row-body` with `<strong>` title + `<span>` meta
- Optional `.zui-badge` / `.zui-status-dot` status indicator
- Optional action buttons / icon controls

### Parent requirements

Inside `.zui-scope`. Typically inside a card or section.

### Child requirements

At minimum: a title element (`.zui-list-row-body strong`).

### Expected visual result

- White surface, 1px border, `--zui-radius-md` rounded corners
- 16px padding, items flex-aligned center with 8px gap
- 8px gap between consecutive list rows (via `+ .zui-list-row { margin-top }`)
- Hover: border becomes primary blue + soft shadow
- `.is-collapsed`: background switches to surface-alt
- Drag handle: 18×18 muted, grab cursor

### Common mistakes

- Missing `.zui-list-row-body` wrapper — title and meta don't stack
- Putting actions on the wrong side — they should be at the end of the flex row
- Using `<table>` row instead of `<div>` — defeats the flex layout

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<tr class="zui-list-row">  <!-- table row instead of div -->
  <td>Title</td>
</tr>
```

### Migration notes

Replace any table-based list of repeated entries (rules, locations, slots)
with `<div class="zui-list-row">` blocks stacked vertically. Move drag-sort
JS hook classes onto `.zui-drag-handle`. Move toggle JS hook into `.zui-toggle`
canonical structure.

---

# FORM CONTROLS

## Toggle (`.zui-toggle`)

### Purpose

On/off switch using a label-wrap pattern with an explicit track + thumb span.
Cleaner DOM than pseudo-element tricks.

### Required Structure

```html
<label class="zui-toggle">
  <input type="hidden" name="field_key" value="0">
  <input type="checkbox"
         id="field_key"
         name="field_key"
         value="1"
         class="zui-toggle__input"
         checked>
  <span class="zui-toggle__track">
    <span class="zui-toggle__thumb"></span>
  </span>
</label>
```

### Required classes

- `.zui-toggle` on `<label>` (outer)
- `.zui-toggle__input` on the `<input type="checkbox">`
- `.zui-toggle__track` on the inner track span
- `.zui-toggle__thumb` on the thumb span inside the track

### Optional classes

- Any plugin JS hook class co-classed on the checkbox or label

### Variants

None.

### Modifiers

None.

### Nested elements

- Hidden `<input type="hidden" value="0">` BEFORE the checkbox — so WordPress receives `0` when the checkbox is unchecked
- Checkbox with `__input` class
- Track span with `__thumb` child

### Parent requirements

Typically inside `.zui-row__control` of a `.zui-row--inline`.

### Child requirements

Hidden input + checkbox + track containing thumb.

### Expected visual result

- 44×24 pill-shaped track
- 20×20 white thumb with subtle shadow
- Off state: border-colored track, thumb at left edge
- On state: primary-blue track, thumb translated 20px to the right
- Focus: 3px primary-100 ring on the track
- RTL: thumb translates -20px when on

### Common mistakes

- Missing the hidden `value="0"` input — checking + unchecking + saving results in option not being set to 0
- Missing `__thumb` span inside `__track` — the on-state animation has nothing to translate
- Using a different outer element instead of `<label>` — clicking text doesn't toggle the checkbox

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<span class="zui-toggle">  <!-- wrong: <span> instead of <label> -->
  <input type="checkbox" class="zui-toggle__input">
  <span class="zui-toggle__track"></span>  <!-- missing __thumb -->
</span>
```

### Migration notes

If a plugin uses a custom toggle pattern with pseudo-elements (e.g.
`<span class="legacy-toggle-parent"><input type="checkbox" class="legacy-toggle"><label class="legacy-toggle-btn"></label></span>`),
the canonical rebuild requires structural change:

- Outer becomes `<label class="zui-toggle">` instead of `<span>`
- Checkbox + label-with-pseudo-elements becomes input + track + thumb spans
- JS that binds to legacy classes must be reviewed — preserve legacy class names alongside canonical classes for compatibility

---

## Checkbox (`.zui-checkbox`)

### Purpose

Custom-styled checkbox using a label-wrap pattern with the native input
visually hidden and a visible `__box` span as the rendered checkbox. Clicking
the label toggles the native input; the box reflects checked / focus / hover
state via CSS.

### Required Structure

```html
<label class="zui-checkbox">
  <input type="hidden" name="field_key" value="0">
  <input type="checkbox" class="zui-checkbox__input" name="field_key" value="1">
  <span class="zui-checkbox__box"></span>
  <span>Label text</span>
</label>
```

### Required classes

- `.zui-checkbox` on `<label>` outer
- `.zui-checkbox__input` on the checkbox
- `.zui-checkbox__box` on the empty box span

### Optional classes

- Plugin JS-hook classes on the input

### Variants

None.

### Modifiers

None.

### Nested elements

- Hidden `value="0"` input (so unchecked state posts `0`)
- Native checkbox with `__input` class (visually hidden — bulletproof against WordPress core's `input[type=checkbox]:disabled { opacity:.7 }` leak)
- Visible `__box` span (the rendered checkbox — 18×18, slate-300 border, primary fill + white check-mark icon when checked)
- Label text (often wrapped in `<span>`)

### Parent requirements

Inside `.zui-scope`.

### Child requirements

Per the structure above.

### Expected visual result

- 18×18 box with slate-300 border, `--zui-radius-md` rounded corners
- Hover: border darkens to slate-400 (`--zui-text-muted`)
- Checked: primary-blue fill + white check-mark icon
- Focus: 3px primary-100 ring on the box
- Disabled: 50% opacity, not-allowed cursor
- The native input is hidden via absolute positioning + `opacity:0 !important` (beats WordPress core's `input[type=checkbox]:disabled { opacity:.7 }` rule); clicks + keyboard focus still work because the `<label>` wraps the input

### Common mistakes

- Treating Checkbox like Toggle — they have different DOM contracts
- Forgetting the hidden `value="0"` input — unchecked state doesn't post `0`
- Skipping the `__box` span — native input is hidden but no visible checkbox renders → invisible control
- Putting the label text without a `<span>` wrap — fine but inconsistent across the codebase

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<input type="checkbox" class="zui-checkbox">  <!-- wrong: no label wrap, no __input/__box -->
```

### Migration notes

Wrap each plain checkbox in `<label class="zui-checkbox">` and add the
`<span class="zui-checkbox__box"></span>` between the input and the text
label. The library hides the native input and renders the box span as the
visible control — skipping the box leaves an invisible checkbox.

---

## Input (`.zui-input`)

### Purpose

Universal text input. Used for `text`, `password`, `number`, `email`, `url` types.

### Required Structure

```html
<input type="text"
       id="field_key"
       name="field_key"
       value="…"
       placeholder="…"
       class="zui-input">
```

### Required classes

- `.zui-input`

### Optional classes

- Legacy WP classes: `regular-input`, `input-text` (preserved as JS hooks)

### Variants

None.

### Modifiers

None.

### Nested elements

None — `<input>` is void.

### Parent requirements

Inside `.zui-row__control` (canonical placement).

### Child requirements

None.

### Expected visual result

- 100% width, max 520px
- 40px tall, 14px horizontal padding
- `--zui-bg` background, 1px solid `--zui-border`, `--zui-radius-xl` rounded
- 13px text, `--zui-text` color
- Focus: white background, primary border, 3px primary-100 ring

### Common mistakes

- Adding inline width that fights the canonical max-width
- Using `.zui-input` on a `<select>` or `<textarea>` — they have their own primitives

### Correct example

```html
<input type="text" name="address" id="address" value="…" placeholder="123 Main St" class="zui-input">
```

### Incorrect example

```html
<input class="zui-input" style="height:30px">  <!-- inline fights canonical height -->
```

### Migration notes

Add `.zui-input` to every plain text/number/email/password input. Skip
vendor-attached inputs (datepickers, color pickers, multi-date pickers) where
adding `.zui-input` could conflict with vendor CSS — those preserve their own
visual.

---

## Textarea (`.zui-textarea`)

### Purpose

Multi-line text input with same border / focus styling as Input but with
auto-height and vertical resize.

### Required Structure

```html
<textarea class="zui-textarea" name="…" id="…" rows="3">…</textarea>

<!-- Code variant (mono font) -->
<textarea class="zui-textarea zui-textarea--code">…</textarea>
```

### Required classes

- `.zui-textarea`

### Optional classes

- `.zui-textarea--code` modifier (mono font for code/HTML preview)

### Variants

- Default
- `--code` (mono font)

### Modifiers

- `--code`

### Nested elements

None.

### Parent requirements

Inside `.zui-row__control`.

### Child requirements

None.

### Expected visual result

- 100% width, minimum 96px tall
- Surface background, 1px border, `--zui-radius-md` rounded
- Inter font, body size, slate text
- Vertical resize handle
- Focus: primary border + 3px primary-100 ring
- Disabled: neutral-bg + muted text + not-allowed cursor
- `--code` variant: JetBrains Mono font, smaller size

### Common mistakes

- Using `.zui-input` on a textarea — wrong styling
- Adding inline height that conflicts with min-height

### Correct example

```html
<textarea class="zui-textarea" name="restriction_message" rows="3" placeholder="…">…</textarea>
```

### Incorrect example

```html
<textarea class="zui-input">…</textarea>  <!-- wrong primitive -->
```

### Migration notes

Replace plain `<textarea>` styling with `.zui-textarea`. For code/HTML preview
contexts (template editing), add `.zui-textarea--code` for mono font.

---

## Select (`.zui-select`)

### Purpose

Native single-select with custom chrome — the native browser chevron is
suppressed via `appearance: none` and a custom chevron icon is drawn as a
sibling span.

### Required Structure

```html
<div class="zui-select-wrap">
  <select id="field_key" name="field_key" class="zui-select">
    <option value="a" selected>Option A</option>
    <option value="b">Option B</option>
  </select>
  <span class="zui-select-chevron">
    <svg class="zui-icon"><!-- chevron-down --></svg>
  </span>
</div>
```

### Required classes

- `.zui-select-wrap` on the outer wrap (required — position: relative parent for chevron)
- `.zui-select` on the `<select>`
- `.zui-select-chevron` on the absolutely-positioned chevron span

### Optional classes

- Legacy `.select` class on the select element (preserved as JS hook)

### Variants

None.

### Modifiers

None.

### Nested elements

- `<select>` with options
- Chevron span containing an SVG icon

### Parent requirements

Inside `.zui-row__control`.

### Child requirements

Native `<option>` elements.

### Expected visual result

- 100% width, max 320px (the wrap)
- 40px tall, 14px left padding, 38px right padding (leaves room for chevron)
- Surface background, 1px border, rounded
- 13px text
- Native browser chevron suppressed; custom chevron icon at the right edge
- Focus: primary border + 3px primary-100 ring

### Common mistakes

- Forgetting `.zui-select-wrap` — chevron has nowhere to position absolutely
- Forgetting `.zui-select-chevron` — native chevron is hidden but no custom chevron drawn
- Placing the chevron span before the select — visual order wrong

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<select class="zui-select">…</select>  <!-- no wrap, no chevron — native chevron hidden but no custom -->
```

### Migration notes

Wrap plain `<select>` elements in `.zui-select-wrap` and add `.zui-select-chevron`
sibling with an SVG icon. Skip Select2-enhanced selects — they have their own
chrome rendered by the vendor; the underlying native select is hidden anyway.

---

## Color Input (`.zui-color-input`)

### Purpose

Color dot swatch + hex text input combo. Used in status-color rows where each
status has a custom color shown as a small dot before the hex value.

### Required Structure

```html
<div class="zui-color-input">
  <span class="zui-color-dot" style="--zui-c: #3b64d3;">
    <input type="color" value="#3b64d3">
  </span>
  <input type="text" class="zui-input" value="#3b64d3" maxlength="7">
</div>
```

### Required classes

- `.zui-color-input`
- `.zui-color-dot`

### Optional classes

- None.

### Variants

None.

### Modifiers

None.

### Nested elements

- `.zui-color-dot` span with `--zui-c` custom property set to the hex
- Optional native `<input type="color">` layered on top of the dot for picking
- `<input type="text">` carrying the hex value (uses `.zui-input` for consistent styling)

### Parent requirements

Inside `.zui-row__control`.

### Child requirements

Dot + hex input.

### Expected visual result

- Inline-flex container with surface background, 1px border, rounded corners
- 16px circular color dot (background set via `--zui-c` CSS variable)
- Hex text input flush to the right of the dot
- Focus-within: primary border + 3px primary ring on the container

### Common mistakes

- Forgetting `style="--zui-c: #…"` on the dot — defaults to neutral
- Putting the text input outside the wrap — focus-within doesn't activate

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<input type="color">  <!-- raw color picker without dot + hex pattern -->
```

### Migration notes

Replace any plain `<input type="text">` hex field + adjacent `<input type="color">`
with the canonical `.zui-color-input` wrap. JS that reads the hex value can
continue to bind to the text input.

---

## Radio Cards (`.zui-radio-cards`)

### Purpose

2-column grid of clickable radio cards (title + description). Each card is a
`<label>` wrapping the native radio + visible body content. Useful for big
exclusive option selections.

### Required Structure

```html
<div class="zui-radio-cards">

  <label class="zui-radio-card is-selected">
    <input type="radio" name="field_key" value="a" class="zui-radio-card__input" checked>
    <span class="zui-radio-card__body">
      <span class="zui-radio-card__label">Title A</span>
      <span class="zui-radio-card__desc">Description for option A.</span>
    </span>
  </label>

  <label class="zui-radio-card">
    <input type="radio" name="field_key" value="b" class="zui-radio-card__input">
    <span class="zui-radio-card__body">
      <span class="zui-radio-card__label">Title B</span>
      <span class="zui-radio-card__desc">Description for option B.</span>
    </span>
  </label>

</div>
```

### Required classes

- `.zui-radio-cards` on the outer grid
- `.zui-radio-card` on each `<label>` card
- `.zui-radio-card__input` on each radio
- `.zui-radio-card__body` wrapping the visible text
- `.zui-radio-card__label`
- `.zui-radio-card__desc`

### Optional classes

- `.is-selected` on the currently-chosen card (PHP sets based on saved option)

### Variants

None on the block (each library deployment may add a `--stacked` modifier for
full-row layout).

### Modifiers

- `.is-selected` state on `<label>`

### Nested elements

- Radio input
- `__body` wrap containing `__label` + `__desc`

### Parent requirements

Inside `.zui-row__control` or directly inside `.zui-card`.

### Child requirements

Per the structure above.

### Expected visual result

- 2-column grid with 16px gap, max 640px wide
- Each card: flex row with radio on the left + text body on the right
- 16px padding, 1px border, `--zui-radius-xl` rounded
- 14px primary radio (`accent-color: var(--zui-primary)`)
- `is-selected`: primary-300 border, light-blue background tint
- 12px semibold label, 10px muted description

### Common mistakes

- Missing `__body` wrapper — title and description don't stack
- Missing `__label` or `__desc` class — text falls back to native typography
- Using `<div>` instead of `<label>` — clicking text doesn't toggle radio

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-radio-cards">
  <input type="radio" name="x" value="a">  <!-- no label wrap, no card structure -->
  <label>Option A</label>
</div>
```

### Migration notes

Replace `<table class="form-table">` with rows containing radios in `<th>`
cells with `.zui-radio-cards` grid. Move radio `name=` and `value=` verbatim
into the canonical structure. Use `is-selected` class to mirror the saved
option for the active card.

---

## Multi-select (`.zui-ms`)

### Purpose

Custom multi-select widget with hidden native `<select multiple>` as the
source of truth, visible chip-control on top, and JS-rendered dropdown. Used
for selecting multiple items (categories, countries, tags, etc.).

### Required Structure

```html
<div class="zui-ms" data-zui-multiselect data-placeholder="Select one or more…">

  <!-- Hidden native select — source of truth for saving -->
  <select multiple id="field_key" name="field_key[]" class="zui-ms__native" hidden>
    <option value="a" selected>A</option>
    <option value="b">B</option>
    <option value="c" selected>C</option>
  </select>

  <!-- Visible control with chips -->
  <div class="zui-ms__control" tabindex="0" role="combobox"
       aria-haspopup="listbox" aria-expanded="false">
    <div class="zui-ms__chips">
      <span class="zui-ms__chip" data-value="a">
        <span class="zui-ms__chip-label">A</span>
        <button type="button" class="zui-ms__chip-remove" aria-label="Remove A">×</button>
      </span>
      <span class="zui-ms__chip" data-value="c">
        <span class="zui-ms__chip-label">C</span>
        <button type="button" class="zui-ms__chip-remove" aria-label="Remove C">×</button>
      </span>
      <!-- OR placeholder when nothing selected -->
      <!-- <span class="zui-ms__placeholder">Select one or more…</span> -->
    </div>
    <span class="zui-ms__chevron">
      <svg class="zui-icon"><!-- chevron-down --></svg>
    </span>
  </div>

  <!-- Dropdown — JS-rendered options -->
  <div class="zui-ms__dropdown" role="listbox" hidden></div>

</div>
```

### Required classes

- `.zui-ms` on outer
- `.zui-ms__native` on hidden select (`hidden` attribute)
- `.zui-ms__control` (clickable area)
- `.zui-ms__chips` (chip container)
- `.zui-ms__chip` (each chip — `data-value=` for JS identity)
- `.zui-ms__chip-label`
- `.zui-ms__chip-remove`
- `.zui-ms__chevron`
- `.zui-ms__dropdown` (initially `hidden`)

### Optional classes

- `.zui-ms__placeholder` (shown when no chips)
- `.is-open` state on `.zui-ms` when dropdown is open
- `.zui-ms__option` and `.zui-ms__check` rendered by JS inside `__dropdown`
- `.is-selected` on options that match a chip
- `data-zui-multiselect` JS hook attribute on outer

### Variants

None.

### Modifiers

State classes `.is-open`, `.is-selected`.

### Nested elements

All listed above.

### Parent requirements

Inside `.zui-row__control`.

### Child requirements

Hidden native select + visible control + dropdown.

### Expected visual result

- Outer max 672px wide, position relative
- Control: min 42px tall, light-bg, 1px border, rounded
- Chips: small pills with `--zui-chip-bg` background, primary-tinted border, primary text
- Remove button: red × that darkens on hover
- Dropdown: white surface, 1px border, shadow, max 224px tall with overflow-y scroll
- Options: 12px text, hover bg
- Selected options: blue tint background + bold text + check icon

### Common mistakes

- Forgetting `hidden` on the native select — it renders visibly
- Missing `data-zui-multiselect` attribute — JS init can't find the widget
- Building chips inline in PHP without `data-value=` — JS can't sync state

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<select multiple>…</select>  <!-- raw native multi-select, no chip widget -->
```

### Migration notes

For Select2-enhanced multi-selects in legacy plugins, decide whether to:
- Keep Select2 (vendor-controlled, untouched) — works, just visually different
- Migrate to `.zui-ms` canonical widget — requires removing Select2 init JS and replacing with the library's multi-select JS

Most plugins keep Select2 in early migration rounds.

---

## Checkbox Grid (`.zui-checkgrid`)

### Purpose

Wrap-flow grid of small inline checkbox + label items. Used for
`multiple_checkbox` field type — an array of yes/no sub-options under one
row.

### Required Structure

```html
<div class="zui-checkgrid">

  <label class="zui-checkitem">
    <input type="hidden" name="key[a]" value="0">
    <input type="checkbox" name="key[a]" value="1" checked>
    <span>Option A</span>
  </label>

  <label class="zui-checkitem">
    <input type="hidden" name="key[b]" value="0">
    <input type="checkbox" name="key[b]" value="1">
    <span>Option B</span>
  </label>

</div>
```

### Required classes

- `.zui-checkgrid`
- `.zui-checkitem`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- One `<label class="zui-checkitem">` per option
- Inside each: hidden 0 + checkbox + label text span

### Parent requirements

Inside `.zui-row__control`.

### Child requirements

One or more `.zui-checkitem` labels.

### Expected visual result

- Flex wrap with 12px row gap, 24px column gap
- Each item: inline-flex with 8px gap, 13px text
- Native checkbox with `accent-color: var(--zui-primary)` for primary-blue check mark

### Common mistakes

- Forgetting the hidden `value="0"` per option — unchecked posts nothing
- Using `<div>` instead of `<label>` per item — clicking text doesn't toggle

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-checkgrid">
  <input type="checkbox" name="key[a]">  <!-- no label, no hidden 0 -->
</div>
```

### Migration notes

Replace any horizontally-laid-out multiple-checkbox group with `.zui-checkgrid`.
Add hidden `value="0"` inputs per option so unchecked state posts 0.

---

## Segmented Control (`.zui-segmented`)

### Purpose

A compact pill-shaped tab strip used to switch between sibling panels
**inside** a card / banner / page. Smaller and more "embedded" than the
top-level `.zui-tabs` page chrome. Used for: CSV Import "Manual / Automation"
toggle, License page "Active / Inactive" view, integration card
"Settings / Logs", any in-card view switcher.

### Required Structure

```html
<div class="zui-segmented" role="tablist">
  <button type="button"
          class="zui-segmented__item is-active"
          role="tab" aria-selected="true"
          data-…="<your-target-key>">
    Label A
  </button>
  <button type="button"
          class="zui-segmented__item"
          role="tab" aria-selected="false"
          data-…="<your-target-key>">
    Label B
  </button>
</div>
```

### Required classes

- `.zui-segmented` on the outer container
- `.zui-segmented__item` on each button
- `.is-active` on exactly one item

### Optional classes

None.

### Variants

None — same visual for any number of segments (2 is most common, 3 – 4 works).

### Modifiers

None.

### Nested elements

Each `.zui-segmented__item` is a `<button>` with a plain text label inside.

### Parent requirements

Anywhere inside `.zui-scope`. The strip is `display: inline-flex` so it does
not stretch to fill its parent — put it inside a flex parent if you need to
align it to a side (e.g. inside a banner's right side via `justify-content`).

### Child requirements

At least two `.zui-segmented__item` buttons. Each must be a real `<button type="button">` for keyboard / focus semantics.

### JS contract

The library does **not** wire `.zui-segmented` automatically — the plugin
provides the toggle handler (because the data-* contract is plugin-specific:
CSV Import uses `data-csvtab` ↔ `data-csvsub`; License page might use
`data-licview` ↔ `data-licpanel`; etc.). Minimum wiring:

```js
var tabs = root.querySelectorAll('.zui-segmented__item');
Array.prototype.forEach.call(tabs, function (btn) {
  btn.addEventListener('click', function () {
    var key = btn.getAttribute('data-mytab');           // your data attribute
    Array.prototype.forEach.call(tabs, function (b) {
      var on = b === btn;
      b.classList.toggle('is-active', on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    // …then show the matching panel.
  });
});
```

### Expected visual result

- **Container:** inline-flex, `bg: var(--zui-bg)`, `xl` radius (12 px),
  **5 px** padding, 4 px gap between items
- **Item:** transparent bg, `lg` radius (8 px), **8 px × 18 px** padding,
  12 px font / 800 weight, `--zui-text-muted` color, `1 px solid transparent`
  border (reserves space so the active border doesn't shift layout),
  160 ms ease transitions on color / background / shadow / border
- **Item hover:** color shifts to `--zui-text`
- **Item active (`.is-active`):** `bg: var(--zui-surface)` (white),
  `color: var(--zui-primary-600)`, **`border: 1 px solid rgba(15, 23, 42, 0.05)`**
  (crisp edge so the white pill reads on the light track), **two-layer soft
  shadow** (`0 1px 2px rgba(15,23,42,0.06), 0 2px 6px -2px rgba(15,23,42,0.08)`)
  — more refined raised look than a single small shadow

### Common mistakes

- Forgetting to add `aria-selected="true"` on the active item (screen
  readers won't announce active state)
- Using `<a>` instead of `<button>` (loses native focus / click semantics)
- Trying to wire the library's `_wireSegmentedTabs` for a non-CSV use case —
  it's CSV-specific; provide your own handler with your own data-* attributes

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- DON'T use links — segmented control is not navigation. -->
<div class="zui-segmented">
  <a href="#manual" class="zui-segmented__item is-active">Manual</a>
  <a href="#automated" class="zui-segmented__item">Automation</a>
</div>
```

### Migration notes

This primitive was extracted from a consumer-plugin CSV-import sub-tab pattern.
Any plugin adding an in-card view switcher should use `.zui-segmented` rather
than introducing a parallel class set.

---

## Calendar (`.zui-calendar`)

### Purpose

Standalone month-view calendar primitive. Used inline (filter cards, report
pages) or inside a popover floating off a date input. Supports single-date
selection, range selection (visual band between two endpoints), today
highlight, muted adjacent-month cells, disabled cells, and an optional
footer with quick-select presets (e.g. "Today", "Last 7 days").

A companion `.zui-datepicker` wrapper composes the calendar with a
`.zui-input` text field that shows the picked date and a calendar icon at
the inset-inline-end. The popover is the same `.zui-calendar` panel with
the `.zui-calendar--popover` modifier.

### Required Structure

Inline calendar:

```html
<div class="zui-calendar">

  <!-- Header: prev arrow | month label | next arrow -->
  <div class="zui-calendar__head">
    <button type="button"
            class="zui-calendar__nav zui-calendar__nav--prev"
            aria-label="Previous month">
      <svg class="zui-icon"><!-- chevron-left --></svg>
    </button>
    <div class="zui-calendar__title">June 2026</div>
    <button type="button"
            class="zui-calendar__nav zui-calendar__nav--next"
            aria-label="Next month">
      <svg class="zui-icon"><!-- chevron-right --></svg>
    </button>
  </div>

  <!-- Grid: 7 day-of-week cells, then 35-42 day cells -->
  <div class="zui-calendar__grid">
    <span class="zui-calendar__dow">Mo</span>
    <span class="zui-calendar__dow">Tu</span>
    <span class="zui-calendar__dow">We</span>
    <span class="zui-calendar__dow">Th</span>
    <span class="zui-calendar__dow">Fr</span>
    <span class="zui-calendar__dow">Sa</span>
    <span class="zui-calendar__dow">Su</span>

    <button type="button" class="zui-calendar__day zui-calendar__day--muted">31</button>
    <button type="button" class="zui-calendar__day">1</button>
    <!-- … -->
    <button type="button" class="zui-calendar__day zui-calendar__day--today">23</button>
    <button type="button" class="zui-calendar__day is-selected">24</button>
    <!-- … -->
  </div>

  <!-- Optional footer with quick-select presets -->
  <div class="zui-calendar__foot">
    <button type="button" class="zui-calendar__preset is-active">Today</button>
    <button type="button" class="zui-calendar__preset">Last 7 days</button>
    <button type="button" class="zui-calendar__preset">This month</button>
  </div>

</div>
```

Date input with popover calendar:

```html
<div class="zui-datepicker" data-zui-datepicker>
  <div class="zui-datepicker__input">
    <input type="text" id="field_key" name="field_key"
           class="zui-input" readonly value="Jun 23, 2026">
    <span class="zui-datepicker__icon">
      <svg class="zui-icon"><!-- calendar --></svg>
    </span>
  </div>
  <div class="zui-calendar zui-calendar--popover" hidden>
    <!-- same .zui-calendar__head + __grid + __foot structure as above -->
  </div>
</div>
```

### Required classes

Calendar panel:

- `.zui-calendar` on the outer panel
- `.zui-calendar__head` on the header row
- `.zui-calendar__title` on the month label
- `.zui-calendar__nav` on each prev/next button
- `.zui-calendar__grid` on the 7-column grid wrapper
- `.zui-calendar__dow` on each day-of-week header cell (7 total)
- `.zui-calendar__day` on each day cell (35–42 total)

Date input wrapper (when used inside a `.zui-datepicker`):

- `.zui-datepicker` on the outer (position: relative parent)
- `.zui-datepicker__input` on the input wrap
- `.zui-input` on the `<input>` itself (composes with the universal input)
- `.zui-datepicker__icon` on the absolutely-positioned calendar icon

### Optional classes

- `.zui-calendar__foot` — footer container for quick-select presets
- `.zui-calendar__preset` — each preset chip
- `.zui-calendar__nav--prev` / `.zui-calendar__nav--next` — direction marker
  on nav buttons (purely semantic — visual styling is identical)
- `data-zui-datepicker` — JS auto-init hook on `.zui-datepicker`

### Variants

- `.zui-calendar--popover` on `.zui-calendar` — floating panel (absolute
  position, large shadow, z-index 30). Use when the calendar opens off
  a date input.
- `.zui-calendar--range` on `.zui-calendar` — range-selection mode.
  Removes the inter-column gap so `--in-range` cells form a continuous
  band between the two endpoints.

### Modifiers

Day cell modifiers (applied alongside `.zui-calendar__day`):

- `.zui-calendar__day--muted` — date belongs to the previous or next
  month (rendered to fill out the grid). Faint text, dimmer on hover.
- `.zui-calendar__day--today` — today (when not selected). Primary-50
  background + primary-600 text.
- `.zui-calendar__day--in-range` — between range start and end (range
  variant only). Primary-50 band background, square corners.
- `.zui-calendar__day--range-start` / `.zui-calendar__day--range-end` —
  endpoints of a range. Primary fill, with rounded corners only on the
  outer edge (left for start, right for end).
- `.zui-calendar__day--disabled` — non-selectable. Same effect as the
  native `disabled` attribute on the `<button>`.

State classes:

- `.is-selected` on a `.zui-calendar__day` — single-selected day
  (primary fill, white text)
- `.is-active` on a `.zui-calendar__preset` — currently-selected preset
- `.is-open` on `.zui-datepicker` — popover is visible (tints the icon)

### Nested elements

- `__head` with two `__nav` buttons + a `__title`
- `__grid` containing 7 `__dow` headers followed by day-cell `<button>`s
- Optional `__foot` containing one or more `__preset` buttons
- For the date-picker wrapper: `__input` (containing a `.zui-input` and a
  `__icon`) followed by a `.zui-calendar--popover`

### Parent requirements

- Inline calendar: inside `.zui-scope` (any context — card, modal, sidebar)
- Popover calendar: must be a direct child of `.zui-datepicker` (its
  `position: relative` parent for the absolute floating panel)
- Inside form rows: place `.zui-datepicker` (not the bare `.zui-calendar`)
  inside `.zui-row__control`

### Child requirements

- `__head` requires exactly one `__title` and one or two `__nav` buttons
- `__grid` requires 7 `__dow` cells followed by day-cell `<button>`s
  (always a multiple of 7; typically 35 or 42 cells to keep complete rows)
- Each `__day` must be a `<button type="button">` so it is keyboard-
  focusable and the `disabled` attribute behaves natively

### Expected visual result

- 280px wide panel, surface background, 1px border, `--zui-radius-xl`
  corners, subtle `--zui-shadow-xs` (or `--zui-shadow-lg` in popover mode)
- Header: 28px tall nav buttons (transparent until hover), 13px semibold
  centered month title
- Day-of-week row: 11px uppercase muted labels, 28px tall
- Day cells: 32px tall, 12px medium, transparent background until hover
- Today: primary-50 fill, primary-600 text
- Selected: primary fill, white text, semibold
- Muted (adjacent month): text-faint color
- Range: primary fill on endpoints (half-rounded), primary-50 band on
  in-range cells (square corners)
- Disabled: 50% opacity, faint text, `not-allowed` cursor
- Focus: primary border + 2px primary-100 ring
- Footer: pill-shaped preset buttons, primary tint on hover, primary
  fill on `.is-active`
- Date input: same look as `.zui-input` (40px tall, light bg, rounded
  corners) with a 16px calendar icon at the inset-inline-end and 38px of
  right padding to clear the icon

### Common mistakes

- Bare `.zui-calendar` with no `__head` / `__grid` — renders an empty
  rounded panel
- Putting day cells directly inside `.zui-calendar` (skipping `__grid`)
  — the 7-column grid layout breaks
- Mixing `__dow` cells with `__day` cells out of order (must be all 7
  `__dow` cells first, then day cells)
- Using `<div>` instead of `<button>` for `__day` — loses keyboard focus,
  the `disabled` attribute, and the click affordance
- Forgetting `.zui-calendar--range` on the panel when using
  `--range-start` / `--range-end` / `--in-range` — endpoints will keep
  the column gap and the band will look broken
- For the date picker: forgetting `.zui-datepicker` on the outer wrap —
  the popover has no `position: relative` parent and floats off the page
- Placing the popover calendar before the input (must come after, so it
  drops below)

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- No grid wrapper — 7-col layout broken -->
<div class="zui-calendar">
  <button class="zui-calendar__day">1</button>
  <button class="zui-calendar__day">2</button>
  …
</div>

<!-- Day cells as <div> — no keyboard focus, can't disable -->
<div class="zui-calendar__grid">
  <div class="zui-calendar__day">1</div>
</div>

<!-- Range modifiers without --range variant — broken band -->
<div class="zui-calendar">
  <div class="zui-calendar__grid">
    <button class="zui-calendar__day zui-calendar__day--range-start">5</button>
    <button class="zui-calendar__day zui-calendar__day--in-range">6</button>
  </div>
</div>
```

### Migration notes

- Replace native `<input type="date">` with `.zui-datepicker` to ship a
  consistent picker UI across browsers (the native control varies wildly
  in look between Chromium / Safari / Firefox)
- For filter bars that previously used a vendor date library (jQuery UI
  datepicker, Pikaday, flatpickr), swap the rendered markup for the
  canonical structure above. Keep the underlying `<input>` element so the
  form submission still carries the chosen value — the `.zui-calendar`
  panel only writes back into that input
- The library only ships the visual layer. The consumer plugin owns the
  JS that:
  - Renders 35–42 day cells for the current month (and the muted
    adjacent-month cells that fill out the grid)
  - Toggles `hidden` on the popover and `.is-open` on `.zui-datepicker`
  - Wires `__nav` clicks to month change
  - Manages selection state (`.is-selected` / range modifiers) and
    writes the formatted value back to the underlying `.zui-input`

---

## Selection Card / Checkcard (`.zui-checkcard`)

### Purpose

Clickable card with a hidden checkbox + visible check box + text cluster
(strong + small). Used for opt-in selections that act like radio cards but
are checkboxes (multiple can be selected, each independently).

### Required Structure

```html
<label class="zui-checkcard">
  <input type="hidden" name="…" value="0">
  <input type="checkbox" name="…" value="1">
  <span class="zui-checkcard__box">
    <svg class="zui-icon"><!-- check --></svg>
  </span>
  <span class="zui-checkcard__text">
    <strong>Card title</strong>
    <small>Card description</small>
  </span>
</label>
```

### Required classes

- `.zui-checkcard`
- `.zui-checkcard__box` (visible check box)
- `.zui-checkcard__text` (title + description)

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- Hidden `value="0"` + checkbox (visually hidden via `position: absolute; opacity: 0`)
- `__box` with check icon
- `__text` containing `<strong>` title + `<small>` description

### Parent requirements

Inside `.zui-scope`.

### Child requirements

Per the structure above.

### Expected visual result

- Card-like layout: surface bg, divider border, `--zui-radius-xl` rounded, 14px padding
- 18×18 check box with primary-blue outline; check icon visible when checked
- Hover: subtle bg tint
- Title: 12px bold strong; description: 10px muted small

### Common mistakes

- Missing the empty `__box` span — the visible check indicator doesn't render
- Forgetting `<strong>` and `<small>` element types inside `__text` — typography wrong

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<label class="zui-checkcard">
  <input type="checkbox">
  <span>Some text</span>  <!-- missing __box and __text wrappers -->
</label>
```

### Migration notes

Replace any opt-in selection cards in modals or integration settings with
canonical `.zui-checkcard`. Preserve JS hook classes on the checkbox.

---

# BUTTONS

## Buttons (`.zui-btn-*`)

### Purpose

Flat single-class button variants. Five variants: primary (filled blue),
secondary (white outline), ghost (transparent outline), soft (tonal light
primary fill), danger (destructive). Plus a `-block` modifier for full-width
that composes with any variant.

### Required Structure

```html
<!-- Primary -->
<button type="submit" class="zui-btn-primary">
  <svg class="zui-icon"><!-- optional leading icon --></svg>
  <span>Save Changes</span>
  <span class="spinner workflow_spinner"></span>
</button>

<!-- Secondary -->
<button type="button" class="zui-btn-secondary">
  Test Connection
  <span class="spinner workflow_spinner"></span>
</button>

<!-- Ghost -->
<button type="button" class="zui-btn-ghost" data-zui-modal-close>
  Cancel
</button>

<!-- Soft (tonal — Apply / Export / Run / Send test) -->
<button type="button" class="zui-btn-soft">
  Apply
</button>

<!-- Danger (destructive — Turn off 2FA / Deactivate License) -->
<button type="button" class="zui-btn-danger">
  Deactivate License
</button>

<!-- Block modifier — composes with any variant -->
<button type="button" class="zui-btn-primary zui-btn-block">
  Sync All
</button>

<!-- As anchor link -->
<a href="…" class="zui-btn-primary">
  <svg class="zui-icon"><!-- zap --></svg>
  <span>Activate License</span>
</a>
```

### Required classes

- One of `.zui-btn-primary` / `.zui-btn-secondary` / `.zui-btn-ghost` / `.zui-btn-soft` / `.zui-btn-danger` — variant class is mandatory

### Optional classes

- `.zui-btn-block` width modifier (full-width)
- Plugin module override class composed as second class (e.g. `class="zui-btn-primary my-plugin-special-btn"`)
- Plugin JS hook classes

### Variants

- `-primary` (filled blue)
- `-secondary` (white outline)
- `-ghost` (transparent outline)
- `-soft` (tonal — light primary-50 fill, primary-600 text — Apply / Export / Run / Send test)
- `-danger` (destructive — white bg, red text + red-100 border — Deactivate / Turn off 2FA)

### Modifiers

- `-block` (full-width)

### Nested elements

- Optional leading SVG icon
- Optional text label in `<span>` (canonical convention)
- Optional `.spinner.workflow_spinner` for async actions

### Parent requirements

Anywhere inside `.zui-scope`.

### Child requirements

At minimum a text label (in `<span>` or directly).

### Expected visual result

- **Primary:** primary-blue fill, white text, 10×16 padding, semibold 12px, soft shadow, `--zui-radius-xl` rounded
- **Secondary:** white background, 1px slate-300 border, soft text, slate hover
- **Ghost:** surface background, soft text, slate-200 border, soft hover
- **Soft:** primary-50 background, primary-600 text, primary-100 border, primary-100 hover (lighter than ghost, hints at action)
- **Danger:** white background, red (#e11d48) text, red-100 border, danger-bg hover (destructive intent)
- **Block:** `width: 100%` + extra vertical padding
- Icon variants render with 15px icons inside

### Common mistakes

- Skipping the variant class — only generic button styling
- Putting `-block` without a variant class — half the styling missing
- Wrapping the button in a container that fights the inline-flex layout

### Correct example

```html
<button type="submit" class="zui-btn-primary">
  <span>Save Changes</span>
</button>
```

### Incorrect example

```html
<button class="zui-btn">Save</button>  <!-- no variant class -->
<button class="zui-btn-block">Save</button>  <!-- missing variant -->
```

### Migration notes

For every existing save / submit / activate button, add `.zui-btn-primary` as
a co-class alongside the existing classes. Wrap label text in `<span>` for
canonical structure. Preserve all JS hook classes (e.g. `woocommerce-save-button`,
plugin-specific save selectors).

---

# MODALS

## Modal (`.zui-modal`)

### Purpose

Centered dialog overlay with backdrop. Dialog has slot structure: head (title
+ sub + close) + body + foot (with actions).

### Required Structure

```html
<div class="zui-modal" id="my-modal" hidden>

  <div class="zui-modal__backdrop" data-zui-modal-close></div>

  <div class="zui-modal__dialog">

    <div class="zui-modal__head">
      <div>
        <h3 class="zui-modal__title">Title</h3>
        <p class="zui-modal__sub">Subtitle.</p>
      </div>
      <button type="button" class="zui-modal__close" data-zui-modal-close aria-label="Close">
        <svg class="zui-icon"><!-- x --></svg>
      </button>
    </div>

    <div class="zui-modal__body">
      <!-- Form fields (see Modal Field) -->
    </div>

    <div class="zui-modal__foot">
      <div class="zui-modal__actions">
        <button type="button" class="zui-btn-ghost" data-zui-modal-close>Cancel</button>
        <button type="submit" class="zui-btn-primary">Save</button>
      </div>
    </div>

  </div>

</div>
```

### Required classes

- `.zui-modal` on outer
- `.zui-modal__backdrop` (full-cover, click to close)
- `.zui-modal__dialog` (the centered card)
- `.zui-modal__head` (header row)
- `.zui-modal__title` on `<h3>`
- `.zui-modal__close`
- `.zui-modal__body`
- `.zui-modal__foot`

### Optional classes

- `.zui-modal__sub` for subtitle
- `.zui-modal__actions` for action button cluster in foot
- `.zui-modal__head--edit` variant (alt background for edit modal)
- `.zui-modal__head-row` (flex row inside edit head)
- `.zui-modal__navbtn` (prev/next icon buttons in modal head)
- `.zui-modal__search` (search field strip at top of body)
- `.zui-modal__search-icon`
- `.zui-modal__loading` (loading state)
- `data-zui-modal-close` JS hook attribute on close triggers

### Variants

- Default centered dialog
- `__head--edit` (different head background for edit context)

### Modifiers

- `__head--edit`

### Nested elements

- Backdrop
- Dialog containing head + body + foot
- Close button (absolutely positioned top-right)

### Parent requirements

Inside `.zui-scope`. Toggle visibility via the HTML `hidden` attribute.

### Child requirements

Backdrop + dialog (with head/body/foot inside dialog).

### Expected visual result

- Fixed-position overlay covering viewport (`inset: 0`, z-index 100050)
- Backdrop: semi-transparent slate (rgba(15,23,42,0.45)), pointer cursor
- Dialog: max 440px wide, max 90vh tall, surface background, large rounded corners, deep shadow
- Open animation: `zui-modal-in` 0.15s ease (scale 0.96 → 1, opacity 0 → 1)
- Head: 18×20 padding (right padding 56 to leave room for close button), bottom divider
- Body: 20px padding, scrollable
- Foot: 14×16 padding, top divider, slate-50 background
- Close button: absolutely positioned top-right, 30×30 surface with border, slate icon

### Common mistakes

- Skipping `__backdrop` — no dim overlay
- Skipping `__dialog` — the modal renders fullscreen instead of as a centered card
- Using inline `style="display:none"` instead of `hidden` attribute — works but inconsistent
- Missing `data-zui-modal-close` on backdrop or close button — clicks don't dismiss

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-modal">
  <div class="zui-modal__body">  <!-- no backdrop, no dialog wrapper -->
    <h3>Title</h3>  <!-- no __title class -->
  </div>
</div>
```

### Migration notes

Replace legacy modal markup (e.g. positioned absolute popup wrappers without
backdrop) with the canonical `.zui-modal` structure. JS that toggles visibility
should switch to setting/removing the `hidden` attribute. Add `data-zui-modal-close`
to backdrop + close button + Cancel button so a single JS handler can dismiss
from any of those triggers.

---

## Modal Field (`.zui-field`)

### Purpose

Compact form field used inside modal bodies. Vertical stack: label above +
input below. Composes `.zui-input` for the text input control.

### Required Structure

```html
<!-- Simple field -->
<div class="zui-field">
  <label class="zui-field__label" for="…">Field name</label>
  <input type="text" id="…" class="zui-input zui-field__input">
</div>

<!-- Field with tooltip on label -->
<div class="zui-field">
  <span class="zui-field__label">
    Custom name
    <span class="zui-tooltip" tabindex="0">…</span>
  </span>
  <input type="text" class="zui-input zui-field__input">
</div>

<!-- Field with select -->
<div class="zui-field">
  <label class="zui-field__label" for="…">Country</label>
  <div class="zui-select-wrap">
    <select id="…" class="zui-select">…</select>
    <span class="zui-select-chevron"><svg class="zui-icon">…</svg></span>
  </div>
</div>
```

### Required classes

- `.zui-field`
- `.zui-field__label`
- `.zui-field__input` on inputs (composes with `.zui-input`)

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- `__label` (either `<label>` or `<span>` if it wraps a tooltip)
- `__input` on the input control (composes with `.zui-input` or `.zui-select-wrap`)

### Parent requirements

Inside `.zui-modal__body`.

### Child requirements

Label + input.

### Expected visual result

- 6px vertical gap between label and input
- Label: 12px semibold, slate-soft text, inline-flex with 4px gap (room for tooltip)
- Input: 40px tall, surface background, 1px border, 12px text, soft shadow
- Focus: primary border + 3px primary-100 ring

### Common mistakes

- Using `.zui-row` inside a modal — wrong primitive (modal uses `.zui-field`)
- Missing `__input` class on the input — different border/shadow

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<label>Name</label>  <!-- no .zui-field wrapper, no canonical layout -->
<input type="text">
```

### Migration notes

Replace `<p><label>Name</label><input></p>` modal field patterns with the
canonical `.zui-field`. Compose `.zui-input` for text inputs and
`.zui-select-wrap` for selects.

---

## Upload Field (`.zui-upload`)

### Purpose

File picker pattern: text input (path/placeholder) + upload button.

### Required Structure

```html
<div class="zui-field">
  <label class="zui-field__label">Logo image URL</label>
  <div class="zui-upload">
    <input type="text" name="thumb_url" class="zui-input zui-field__input" placeholder="Select asset…" readonly>
    <button type="button" class="zui-upload__btn" hidden title="Remove">
      <svg class="zui-icon"><!-- x --></svg>
    </button>
    <button type="button" class="zui-upload__btn">
      <svg class="zui-icon"><!-- upload --></svg>
      <span>Upload</span>
    </button>
  </div>
</div>
```

### Required classes

- `.zui-upload`
- `.zui-upload__btn` on each button

### Optional classes

- `hidden` attribute on the Remove button (shown when a value exists)
- Plugin JS hooks (e.g. classes used by WP media frame init)

### Variants

None.

### Modifiers

None.

### Nested elements

- A text input (canonical: `<input type="text" readonly>` with `.zui-input.zui-field__input`)
- Optional Remove button
- Upload button

### Parent requirements

Inside a `.zui-field` (canonical) so the label sits above.

### Child requirements

Input + at least one upload button.

### Expected visual result

- Horizontal flex with 8px gap
- Text input grows (flex: 1 1 auto)
- Buttons: 40px tall, `--zui-bg` background, 1px border, `--zui-radius-xl` rounded
- Hover: slate-200 background
- Icon: 14px inside button

### Common mistakes

- Skipping the wrap `<div class="zui-upload">` — buttons don't sit beside input
- Forgetting to make the input `readonly` — user can type free text instead of using picker

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<input type="file">  <!-- raw file input, doesn't compose with WP media frame -->
```

### Migration notes

Replace any custom WP media frame UI with `.zui-upload`. JS that initializes
the WP media picker should target the upload button class. The text input
displays the picked file's URL (read-only).

---

# INFO / FEEDBACK

## Tipbox (`.zui-tipbox`)

### Purpose

Inline info card with icon + title + body text. Used for contextual help
inside settings panels.

### Required Structure

```html
<div class="zui-tipbox">
  <div class="zui-tipbox__head">
    <svg class="zui-icon"><!-- help-circle --></svg>
    <span>Tip title</span>
  </div>
  <p class="zui-tipbox__text">
    Body text with <code>%token%</code> placeholders and an
    <a href="…" target="_blank" rel="noreferrer noopener">inline link</a>.
  </p>
</div>
```

### Required classes

- `.zui-tipbox`
- `.zui-tipbox__head`
- `.zui-tipbox__text`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- Head with icon + title span
- Body text paragraph

### Parent requirements

Inside `.zui-scope` (often inside a `.zui-card`).

### Child requirements

Head + text.

### Expected visual result

- Tinted background (`--zui-bg`), 1px divider border, 12px padding, `--zui-radius-xl` rounded
- Vertical stack with 6px gap between head and text
- Head: 12px bold soft text + 15px muted icon
- Text: small muted body with primary-colored links

### Common mistakes

- Skipping `__head` — title and icon don't render correctly
- Skipping `__text` — body paragraph has wrong typography
- Adding inline padding that conflicts with canonical 12px

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-tipbox">
  <p>Tip body</p>  <!-- missing head, missing __text -->
</div>
```

### Migration notes

Replace inline "tip" or "help" callouts with `.zui-tipbox`. If the legacy
markup has heavy inline `style="…"`, externalize those styles or skip the
tipbox adoption until inline styles are removed.

---

## Lock Section (`.zui-lock-section`)

### Purpose

Centered PRO-upgrade promo card used at the top of a PRO-only section in a
free plugin's Settings page. Renders a round icon emblem + a title (often
followed by a "PRO" badge) + a short description + a primary CTA. The
section's actual fields are rendered **below** this banner in a read-only /
disabled state so users still see the gated UI while being directed to the
upgrade CTA.

### Required Structure

```html
<div class="zui-lock-section">
  <div class="zui-lock-section__icon">
    <svg class="zui-icon"><!-- lock icon --></svg>
  </div>
  <h3 class="zui-lock-section__title">
    Unlock Automated FTP Imports
    <span class="zui-lock-section__badge">PRO</span>
  </h3>
  <p class="zui-lock-section__desc">
    Schedule a remote CSV pickup so tracking numbers import on a cron — fully hands-off fulfillment.
  </p>
  <a class="zui-btn-primary zui-lock-section__cta" href="https://example.com/upgrade"
     target="_blank" rel="noopener noreferrer">
    Upgrade to PRO
  </a>
</div>

<!-- Optional: dimmed read-only preview of the gated fields -->
<div class="zui-lock-section__preview">
  <!-- Normal .zui-row / .zui-input markup, rendered visible but inert -->
</div>
```

### Required classes

- `.zui-lock-section`
- `.zui-lock-section__icon`
- `.zui-lock-section__title`
- `.zui-lock-section__desc`
- `.zui-lock-section__cta` (combine with `.zui-btn-primary`)

### Optional classes

| Class | Element | Effect |
|---|---|---|
| `.zui-lock-section__preview` | `<div>` placed **after** the lock-section | Dims the gated field area (`opacity: 0.55`) and disables pointer events + selection so the original fields can be shown as a read-only preview |
| `.zui-lock-section__badge` | `<span>` inside `__title` | Inline "PRO" pill badge next to the headline — small blue chip with white uppercase text, fully self-contained (no separate badge.css dependency) |

### Variants

None.

### Modifiers

None.

### Nested elements

- **`.zui-lock-section__icon`** — 48 × 48 px circle, primary-50 background, primary foreground color. Place exactly one `.zui-icon` SVG (22 × 22 px) inside — the `lock` icon is the canonical choice; any thematic icon works.
- **`.zui-lock-section__title`** — `<h3>` rendered inline-flex with a 10 px gap so an inline `.zui-lock-section__badge` chip sits next to the headline without extra wrappers.
- **`.zui-lock-section__desc`** — `<p>` with a max-width of 480 px so long descriptions wrap into a readable column rather than spanning the full card width.
- **`.zui-lock-section__cta`** — `<a>` anchor styled as a primary blue gradient pill button. Combine with `.zui-btn-primary` for the button look. Must have `target="_blank" rel="noopener noreferrer"` for external upgrade URLs.

### Parent requirements

Inside `.zui-scope`. Typically placed at the top of a `.zui-section` or as the only child of a `.zui-card` body so the centered layout reads correctly.

### Child requirements

Icon + title + desc + cta in that order. Skipping the icon collapses the visual identity of the card.

### CSS load order

`lock-section.css` is **Phase 1** — registered in the `zui.css` aggregator under "Inline info helpers" (alongside `tipbox.css`). No separate enqueue is required when the consumer plugin loads `zui.css`.

### Expected visual result

- Centered card with surface background, 1 px border, `--zui-radius-xl` rounded corners, and `--zui-shadow-xs` soft shadow.
- 48 × 48 px round blue-tinted emblem on top with a primary-blue lock icon.
- Bold 18 px title with an inline "PRO" badge to the right.
- Muted 13 px description paragraph (max 480 px wide), centered.
- Primary blue gradient CTA button (min-width 160 px) at the bottom.
- If `.zui-lock-section__preview` follows, the gated fields below appear at 55 % opacity and cannot be interacted with.

### Common mistakes

| Mistake | Result | Fix |
|---|---|---|
| Omitting `.zui-btn-primary` on the CTA | Anchor renders as a plain blue link | Combine both classes on the same `<a>` |
| Nesting `.zui-lock-section__preview` **inside** `.zui-lock-section` | Whole banner gets dimmed including its CTA | Place `__preview` as a **sibling** below the lock-section |
| Adding multiple icons inside `__icon` | Layout breaks (flex centering only works with one child) | Keep exactly one `.zui-icon` SVG inside `__icon` |
| Using `<button>` for the CTA | Wrong semantic for external upgrade nav + loses link behavior | Always use `<a href="…" target="_blank">` |

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- WRONG: preview nested inside lock-section — dims the CTA too -->
<div class="zui-lock-section">
  <div class="zui-lock-section__icon">…</div>
  <h3 class="zui-lock-section__title">…</h3>
  <p class="zui-lock-section__desc">…</p>
  <a class="zui-btn-primary zui-lock-section__cta">Upgrade</a>
  <div class="zui-lock-section__preview">…</div>  <!-- should be a sibling, not a child -->
</div>

<!-- WRONG: CTA missing .zui-btn-primary — renders as a bare link -->
<a class="zui-lock-section__cta" href="…">Upgrade to PRO</a>
```

### Migration notes

Replace ad-hoc "upgrade banner" markup at the top of PRO-only tabs (FTP import, PayPal sync, etc.) with `.zui-lock-section`. Move the field rows that were previously hidden into a sibling `.zui-lock-section__preview` wrapper so users can see what they'd unlock.

---

## PRO Feature Lock (`.zui-pro-feature`)

### Purpose

Compact two-element indicator — a solid-blue "PRO" pill plus a soft-tinted rounded-square chip containing a lock icon — used anywhere a setting should be visually flagged as PRO-only. The component is **layout-only** for the badge + lock pair. It does **not** include the disabled control next to it; the plugin places this pair wherever the indicator belongs (next to a disabled toggle in a settings row, in a tab label, on a card header, etc.).

### Required Structure

```html
<span class="zui-pro-feature">
  <span class="zui-pro-feature__badge">PRO</span>
  <span class="zui-pro-feature__lock" aria-hidden="true">
    <svg class="zui-icon"><!-- lock --></svg>
  </span>
</span>
```

### Required classes

- `.zui-pro-feature` (wrapper — provides inline-flex + 8 px gap layout)
- `.zui-pro-feature__badge`
- `.zui-pro-feature__lock`

### Optional classes

None — the badge and lock chip are always rendered together.

### Variants

None.

### Modifiers

None.

### Nested elements

- **`.zui-pro-feature`** — `inline-flex` wrapper with `align-items: center` and an 8 px gap so the badge and lock chip line up horizontally as one unit.
- **`.zui-pro-feature__badge`** — solid blue pill (`--zui-primary` background, white text, 11 px bold, uppercase). Self-contained — does **not** require `badge.css`.
- **`.zui-pro-feature__lock`** — 28 × 28 px rounded square with a soft primary-tinted background (`color-mix` of the primary token at 18 % opacity) and primary-colored foreground. Place exactly one `.zui-icon` SVG (14 × 14 px) inside; the `lock` icon is the canonical choice.

### Parent requirements

Inside `.zui-scope`. The wrapper is `inline-flex`, so it can sit on any line of content — in a `.zui-row__control`, a tab label, a card header, a list item, etc.

### Child requirements

Exactly two siblings inside `.zui-pro-feature` in this order:
1. `.zui-pro-feature__badge` containing the text "PRO".
2. `.zui-pro-feature__lock` containing exactly one `.zui-icon` SVG.

### Pairing with a disabled control

When the indicator sits next to a disabled control (a `.zui-toggle`, checkbox, etc.) in a settings row, the layout is the **plugin's** responsibility — typically by setting the wrapping `.zui-row__control` to `display: inline-flex; align-items: center; gap: 8px;` so the control and the `.zui-pro-feature` unit sit on one line:

```html
<div class="zui-row zui-row--inline">
  <div class="zui-row__head">
    <span class="zui-row__label">PRO-only feature</span>
    <p class="zui-row__desc">Short description.</p>
  </div>
  <div class="zui-row__control" style="display:inline-flex;align-items:center;gap:8px;">
    <label class="zui-toggle" aria-disabled="true">
      <input type="checkbox" class="zui-toggle__input" disabled>
      <span class="zui-toggle__track"><span class="zui-toggle__thumb"></span></span>
    </label>
    <span class="zui-pro-feature">
      <span class="zui-pro-feature__badge">PRO</span>
      <span class="zui-pro-feature__lock" aria-hidden="true">
        <svg class="zui-icon"><!-- lock --></svg>
      </span>
    </span>
  </div>
</div>
```

The library does not impose this layout because not every plugin pairs the indicator with a toggle — some show it standalone or alongside a checkbox, select, or static text.

### CSS load order

`pro-feature.css` is **Phase 1** — registered in the `zui.css` aggregator under "Inline info helpers" alongside `tipbox.css` and `lock-section.css`. No separate enqueue needed when the consumer plugin loads `zui.css`.

### Expected visual result

- A small solid-blue "PRO" pill with white uppercase text.
- Followed by a 28 × 28 px soft-blue rounded square containing a small lock icon.
- The two elements sit on one line with an 8 px gap, vertically centered, as a single visual unit.

### Common mistakes

| Mistake | Result | Fix |
|---|---|---|
| Putting the disabled control inside `.zui-pro-feature` | Library wrapper grows beyond the badge + lock pair, mixing plugin responsibility into the component | Keep the control as a **sibling** of `.zui-pro-feature`, not a child |
| Using a different icon inside `__lock` | Visual cue stops matching the rest of the Zorem family | Use the canonical `lock` icon |
| Placing the badge after the lock chip | Order doesn't match the design reference | Always: `__badge` then `__lock` |
| Wrapping `.zui-pro-feature` in a block-level element with no inline-flex parent | Indicator sits on its own line instead of inline with surrounding text | Either let surrounding context flow inline, or wrap the row in `display: inline-flex` |

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- WRONG: disabled control nested inside .zui-pro-feature -->
<span class="zui-pro-feature">
  <label class="zui-toggle" aria-disabled="true">…</label>
  <span class="zui-pro-feature__badge">PRO</span>
  <span class="zui-pro-feature__lock">…</span>
</span>

<!-- WRONG: badge after lock chip -->
<span class="zui-pro-feature">
  <span class="zui-pro-feature__lock">…</span>
  <span class="zui-pro-feature__badge">PRO</span>
</span>
```

### Migration notes

Replace any ad-hoc "PRO label + lock chip" markup (commonly an AST-local `.ast-pro-feature-lock` chip with a local `.zui-locked__badge` pill) with the standalone `.zui-pro-feature` wrapper. The new component preserves the exact visual but moves the styles into the shared library so every Zorem plugin renders the indicator identically. Plugins keep responsibility for the surrounding row layout — the library wrapper only owns the badge + lock pair.

---

## Tooltip (`.zui-tooltip`)

### Purpose

Question-mark icon trigger + dark bubble on hover/focus. Bubble + arrow are
explicit DOM elements (not pseudo-element tricks) so they can hold rich
content with accurate styling.

### Required Structure

```html
<span class="zui-tooltip" tabindex="0" role="img" aria-label="Tooltip text">
  <svg class="zui-icon"><!-- help-circle --></svg>
  <span class="zui-tooltip__bubble">
    Hover/focus body text
    <span class="zui-tooltip__arrow"></span>
  </span>
</span>
```

### Required classes

- `.zui-tooltip` on the trigger span
- `.zui-tooltip__bubble` on the popup span
- `.zui-tooltip__arrow` on the arrow tail

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- Trigger icon (SVG inside `.zui-tooltip`)
- Bubble containing body text + arrow

### Parent requirements

Anywhere inside `.zui-scope`. Typically inside a label inside a form row.

### Child requirements

Icon + bubble.

### Expected visual result

- Trigger: muted icon, help cursor, inline-flex with 6px start margin
- Hover/focus: trigger turns primary blue
- Bubble: hidden by default (opacity 0, visibility hidden); shown on hover/focus
- Bubble: dark slate-900 background, white 11px text, 256px wide, centered above the trigger, 10px padding, 1px slate-700 border, `--zui-radius-xl` rounded, drop shadow
- Arrow: 10×10 rotated square, positioned at the bottom-center of the bubble, dark slate background

### Common mistakes

- Missing `tabindex="0"` — keyboard users can't focus to reveal tooltip
- Missing `aria-label` — screen readers can't announce tooltip content
- Putting the bubble outside the `.zui-tooltip` — hover-reveal doesn't work
- Forgetting the arrow — bubble looks floating with no anchor

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<span class="zui-tooltip" title="Help text"></span>  <!-- no SVG, no bubble — relies on native title -->
```

### Migration notes

Replace any plain `title=`-attribute tooltips with the canonical
`.zui-tooltip` structure. If the plugin uses jQuery tipTip or another vendor
tooltip plugin, decide whether to keep the vendor (works) or migrate. Most
plugins keep tipTip in early migration rounds.

---

## Menu (`.zui-menu`)

### Purpose

Click-to-toggle dropdown / kebab popover. A 36 × 36 trigger button reveals a
floating action panel anchored to its bottom-end. Items support an optional
leading icon and an optional accent color (for the primary action). Use it
for: toolbar overflow ("Enable carriers / Add custom carrier / Sync"),
per-row actions ("Edit / Duplicate / Delete"), header user menu, etc.

### Required Structure

```html
<div class="zui-menu">
  <button type="button" class="zui-menu__btn"
          aria-haspopup="true" aria-expanded="false"
          aria-label="Actions">
    <svg class="zui-icon"><!-- more-vertical --></svg>
  </button>
  <div class="zui-menu__list" hidden>
    <!-- item with icon -->
    <button type="button" class="zui-menu__item">
      <svg class="zui-icon"><!-- layers --></svg>
      <span>Enable Carriers</span>
    </button>
    <!-- item without icon — flex/gap collapses naturally -->
    <button type="button" class="zui-menu__item">
      <span>Plain action</span>
    </button>
    <!-- optional separator between groups -->
    <div class="zui-menu__sep"></div>
    <!-- accent (primary) action -->
    <button type="button" class="zui-menu__item zui-menu__item--accent">
      <svg class="zui-icon"><!-- refresh-cw --></svg>
      <span>Sync Carriers</span>
    </button>
  </div>
</div>
```

### Required classes

- `.zui-menu` — relative-positioning anchor
- `.zui-menu__btn` — the trigger button
- `.zui-menu__list` — the floating panel (carries the native `hidden` attribute when closed)
- `.zui-menu__item` — each action button

### Optional classes

- `.zui-menu__sep` — 1 px horizontal divider between item groups
- `.zui-menu__item--accent` — paints the item (and its icon) in primary blue

### Variants

None on the container. Per-item variant: `--accent`.

### Modifiers

None.

### Nested elements

- Trigger icon inside `.zui-menu__btn` (typically `more-vertical`)
- Items inside `.zui-menu__list` — each is `<button>` with optional icon child + `<span>` label
- `.zui-menu__sep` divs between groups

### Parent requirements

Anywhere inside `.zui-scope`. The container is `position: relative`, so the
floating `.zui-menu__list` anchors to it via `position: absolute`.

### Child requirements

- The trigger button must be present
- The list must carry `hidden` when closed (JS toggles)
- Each item should be a real `<button type="button">` for native keyboard / focus semantics

### JS contract

The library does **not** wire `.zui-menu` automatically — you provide the
toggle handler in your plugin's JS (so click-outside-to-close behavior
matches the rest of the page). Minimum wiring:

```js
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  var isOpen = !list.hasAttribute('hidden');
  if (isOpen) {
    list.setAttribute('hidden', '');
    btn.setAttribute('aria-expanded', 'false');
  } else {
    list.removeAttribute('hidden');
    btn.setAttribute('aria-expanded', 'true');
  }
});
document.addEventListener('click', function (e) {
  if (!menuRoot.contains(e.target)) {
    list.setAttribute('hidden', '');
    btn.setAttribute('aria-expanded', 'false');
  }
});
```

### Expected visual result

- Trigger: 36 × 36 surface button, 1 px border, soft xs-shadow, `xl` radius
- Trigger hover: background shifts to `--zui-bg`
- Panel: 200 px wide, surface bg, 1 px border, md-shadow, `xl` radius,
  `inset-inline-end: 0`, `top: calc(100% + 6px)`, z-index 30
- Item: full-width flex button, `lg` radius, 12 px / 600 weight, soft text
  color, 8 px / 10 px padding, 8 px gap between icon and label
- Item icon: 15 × 15 px in `--zui-text-muted`
- Item hover: bg `--zui-bg`, text strong
- Accent item: text + icon in `--zui-primary-600`
- Separator: 1 px `--zui-divider`, 6 px / 4 px margins

### Common mistakes

- Wiring with `display: none` instead of the `hidden` attribute — CSS
  transitions on the panel may behave inconsistently
- Forgetting to toggle `aria-expanded` on the trigger — screen readers
  cannot announce open/closed state
- Re-implementing the visual with a `<select>` — loses icon + accent + keyboard
  focus styling

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- Bare div is not focusable + no aria — keyboard users can't open it. -->
<div class="zui-menu__btn" onclick="openMenu()">⋮</div>
```

### Migration notes

This primitive was extracted from a consumer-plugin toolbar kebab pattern.
Any plugin adding a kebab / dropdown should use `.zui-menu` rather than
introducing a parallel class set.

---

## Actions Menu (`.zui-actions`)

### Purpose

Three-dot / kebab toggle button + dropdown menu pair. The lightweight cousin of
`.zui-menu` — use it for **row-level / card-level action lists** (Edit /
Duplicate / Delete) where the trigger is an icon button and the menu attaches
directly to it. The library auto-wires open / close / outside-click via
`data-zui-*` attributes; no plugin JS is needed.

### Required Structure

```html
<div class="zui-actions">
    <button class="zui-actions-toggle" aria-haspopup="true" aria-expanded="false">
        <svg class="zui-icon"><!-- more-vertical --></svg>
    </button>
    <ul class="zui-actions-menu" hidden role="menu">
        <li><a href="#" role="menuitem">Edit</a></li>
        <li><a href="#" role="menuitem">Delete</a></li>
    </ul>
</div>
```

### Required classes

- `.zui-actions` on the wrapper
- `.zui-actions-toggle` on the trigger button
- `.zui-actions-menu` on the dropdown `<ul>` (starts with the `hidden` attribute)

### Variants / Modifiers

None.

### Parent requirements

Anywhere inside `.zui-scope`. Common parents: `.zui-card__head`, `.zui-list-row`,
table cell, toolbar end.

### Child requirements

- `<button class="zui-actions-toggle">` — typically wraps a single `.zui-icon`
  (`more-vertical` is the convention).
- `<ul class="zui-actions-menu" hidden>` — list of `<li>` with `<a>` or
  `<button>` items.

### Behaviour

Auto-wired in `js/zui.js`. Click the toggle to flip the menu's `hidden`
attribute and `aria-expanded` on the button. Clicking outside the actions
wrapper or pressing ESC closes the menu. No CustomEvent is emitted — listen on
the menu items themselves.

### Expected visual result

- Toggle: square 32×32 icon button, transparent background, hover = subtle
  background
- Menu: anchored to the toggle's right edge, white surface, 1 px border, 8 px
  radius, shadow-sm
- Items: 8×12 px padding, 13 px text, hover = `--zui-primary-50` background

### Common mistakes

- Forgetting `hidden` on the menu — it'll be visible on initial render
- Putting the toggle outside `.zui-actions` — the wiring won't reach it
- Reaching for `.zui-menu` for row actions — that primitive is for toolbar
  filters; `.zui-actions` is the lighter version

### Correct example

(See Required Structure above.)

---

## Slideout (`.zui-slideout`)

### Purpose

Right-edge drawer that overlays the page from the side. Use for **contextual
editors** where the surrounding page context should remain visible (Edit
Carrier, Integration config, Sync results). The mechanical inverse of
`.zui-modal`: modals interrupt, slideouts complement.

### Required Structure

```html
<button data-zui-slideout-toggle="edit-carrier">Edit</button>

<div class="zui-slideout" id="edit-carrier" hidden>
    <div class="zui-slideout__backdrop" data-zui-slideout-close></div>
    <div class="zui-slideout__panel" role="dialog" aria-modal="true"
         aria-labelledby="edit-carrier-title">
        <header class="zui-slideout__header">
            <h3 class="zui-slideout__title" id="edit-carrier-title">Edit Carrier</h3>
            <button class="zui-slideout__close" data-zui-slideout-close
                    aria-label="Close">
                <svg class="zui-icon"><!-- x --></svg>
            </button>
        </header>
        <div class="zui-slideout__body">
            <!-- form / content -->
        </div>
        <footer class="zui-slideout__footer">
            <button class="zui-btn zui-btn--ghost"
                    data-zui-slideout-close>Cancel</button>
            <button class="zui-btn zui-btn--primary" type="submit">Save</button>
        </footer>
    </div>
</div>
```

### Required classes

- `.zui-slideout` (block, starts with `hidden` attribute + unique `id`)
- `.zui-slideout__backdrop` (clickable to close)
- `.zui-slideout__panel` (the actual drawer)
- `.zui-slideout__header` + `.zui-slideout__title` + `.zui-slideout__close`
- `.zui-slideout__body`
- `.zui-slideout__footer` (optional — for actions row)

### Variants / Modifiers

None in v1.5.x. Width is fixed at 480 px (responsive: 100 % below 600 px).

### Data attributes (wiring)

| Attribute | Where | What it does |
|---|---|---|
| `data-zui-slideout-toggle="<id>"` | trigger button | Opens the slideout with that `id` |
| `data-zui-slideout-close` | inside the slideout (no value) | Closes the parent slideout |

### Parent requirements

Place the slideout markup near the bottom of `.zui-scope` (it's a fixed-position
overlay, position in the DOM doesn't affect placement, but late-in-document
keeps tab order sensible).

### Child requirements

`__header` + `__body` are required. `__footer` is optional.

### Programmatic API

```js
ZUI.slideout.open('edit-carrier');
ZUI.slideout.close('edit-carrier');
```

### Expected visual result

- Right-anchored panel slides in over 220 ms
- Backdrop: black at 30 % opacity, fades in over the same duration
- Panel: white surface, 480 px wide, full viewport height, subtle left shadow
- Header: 60 px tall with bottom border, title 16 px bold, close icon 14×14
- Body: flex-grow with internal scroll if content exceeds viewport
- Footer (optional): right-aligned action buttons, top border separator

### Custom events

- `zui:slideoutopen` fires on the slideout element after open
- `zui:slideoutclose` fires on the slideout element after close

### Common mistakes

- Forgetting `hidden` on the slideout root — it renders open on page load
- Multiple slideouts sharing the same `id` — only the first will open
- Forgetting `data-zui-slideout-close` on the backdrop — users can't dismiss
  by clicking outside
- Using `.zui-modal` markup for a side-drawer — they're separate components

### Correct example

(See Required Structure above.)

---

## Note (`.zui-note`)

### Purpose

Inline informational callout box with semantic color variants.

### Required Structure

```html
<div class="zui-note zui-note--info">
  <span class="dashicons dashicons-info"></span>
  <p>Informational message body.</p>
</div>

<div class="zui-note zui-note--success">
  <span class="dashicons dashicons-yes"></span>
  <p>Success message.</p>
</div>

<div class="zui-note zui-note--warning">
  <span class="dashicons dashicons-warning"></span>
  <p>Warning message.</p>
</div>

<div class="zui-note zui-note--danger">
  <span class="dashicons dashicons-no"></span>
  <p>Error message.</p>
</div>
```

### Required classes

- `.zui-note`
- One variant class: `--info` / `--success` / `--warning` / `--danger`

### Optional classes

None.

### Variants

- `--info` (light blue)
- `--success` (light green)
- `--warning` (light amber)
- `--danger` (light red)

### Modifiers

(See variants.)

### Nested elements

- An icon (dashicons or SVG)
- A `<p>` body

### Parent requirements

Inside `.zui-scope`.

### Child requirements

Icon + body paragraph.

### Expected visual result

- Flex row with 8px gap, items align flex-start
- 16px padding, `--zui-radius-md` rounded, 1px tinted border
- Icon: 18×18 with variant color
- Body: 14px slate text, flex 1
- Each variant has its tinted background + tinted border

### Common mistakes

- Forgetting the variant class — no color tint
- Forgetting `<p>` wrap on body — alignment off

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-note">Notice</div>  <!-- no variant, no icon -->
```

### Migration notes

Replace any inline notice / callout box with `.zui-note` + a variant.

---

## Badge (`.zui-badge`)

### Purpose

Pill-shaped status indicator with semantic color variants. Two siblings:
`.zui-badge` (pill) and `.zui-status-dot` (dot + colored label).

### Required Structure

**Pill badge:**

```html
<span class="zui-badge">Default</span>
<span class="zui-badge zui-badge--success">Active</span>
<span class="zui-badge zui-badge--info">Info</span>
<span class="zui-badge zui-badge--warning">Pending</span>
<span class="zui-badge zui-badge--danger">Error</span>
<span class="zui-badge zui-badge--neutral">Neutral</span>
<span class="zui-badge zui-badge--accent">Featured</span>
<span class="zui-badge zui-badge--count">5</span>
<span class="zui-badge zui-badge--outline">v1.0.0</span>
```

**Status dot:**

```html
<span class="zui-status-dot zui-status-dot--success">Active</span>
<span class="zui-status-dot zui-status-dot--warning">Pending</span>
```

### Required classes

- `.zui-badge`
- For status dot: `.zui-status-dot`

### Optional classes

- One variant: `--success` / `--info` / `--warning` / `--danger` / `--neutral` / `--accent` / `--count` / `--outline`

### Variants

- `--success` (green tint)
- `--info` (blue tint)
- `--warning` (amber tint)
- `--danger` (red tint)
- `--neutral` (grey tint)
- `--accent` (primary blue background, white text)
- `--count` (inverted, used on tabs as count badges)
- `--outline` (border-only, mono font, neutral — used for version pills)

### Modifiers

(See variants.)

### Nested elements

None — text content directly.

### Parent requirements

Anywhere inside `.zui-scope`.

### Child requirements

Text content (and optionally an SVG icon inside the pill).

### Expected visual result

- Inline-flex with 4px gap, 2×8 padding
- Tiny pill-shaped (`--zui-radius-pill`)
- Uppercase letter-spacing on most variants (except `--count` and `--outline`)
- Each variant has its tinted background + colored text
- `--count` (22×22, primary fill, white)
- `--outline` (transparent, 1px border, mono font)

### Common mistakes

- Skipping the variant class — gets default neutral
- Using `.zui-badge` on an interactive element (button/link) — it's a static indicator

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<span class="zui-badge--success">Active</span>  <!-- missing base .zui-badge class -->
```

### Migration notes

Replace plugin-specific status pills with `.zui-badge` + a variant.

---

## Icon (`.zui-icon`)

### Purpose

Universal SVG icon container. Lucide-style stroke icons inlined as `<svg>`.
Default size is 18×18 block; parent components override the size (e.g.
`.zui-section-header__icon .zui-icon` is 32×32, `.zui-header-action .zui-icon`
is 14×14).

### Required Structure

```html
<svg class="zui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <!-- Lucide path data -->
</svg>
```

### Required classes

- `.zui-icon`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

SVG path / shape elements.

### Parent requirements

Anywhere inside `.zui-scope`.

### Child requirements

SVG geometry.

### Expected visual result

- 18×18 block by default
- Color inherits from parent (`currentColor`)
- Parent components override size

### Common mistakes

- Forgetting `stroke="currentColor"` — icon won't pick up parent text color
- Using bitmap images instead of SVG — no `currentColor` benefit + blurry on retina

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<i class="dashicons dashicons-yes"></i>  <!-- dashicons is OK as a fallback but not .zui-icon -->
```

### PHP helper — `zui_icon( $name )` / `zui_get_icon( $name )`

The library ships an inline-SVG icon resolver at `assets/zui/icons.php` so consumer
plugins don't have to copy-paste raw SVG markup. Include the file once during your
plugin bootstrap (typically inside an `admin_init` hook or at the top of any view
that renders icons):

```php
require_once plugin_dir_path( __FILE__ ) . 'assets/zui/icons.php';
```

Then anywhere in your markup:

```php
<button class="zui-btn zui-btn--primary">
    <?php zui_icon( 'upload' ); ?>
    <span><?php esc_html_e( 'Upload', 'your-plugin' ); ?></span>
</button>
```

Both helpers exist and behave identically; pick by usage:

| Function | Behaviour | When to use |
|---|---|---|
| `zui_icon( $name )` | **Echoes** the SVG immediately | Inside view templates (most common) |
| `zui_get_icon( $name )` | **Returns** the SVG as a string | When you need to assign / concatenate / cache the markup |

If a name doesn't exist, the resolver falls back to the `sliders` icon (so a typo
never produces a hard error or a blank space).

### Available icon names (v1.5.1 — 42 icons)

All icons are Lucide-style strokes, 24×24 viewBox, stroke-width 2, rounded caps.
Pass any of these names to `zui_icon()`:

| Group | Names |
|---|---|
| Navigation | `menu`, `x`, `arrow-left`, `arrow-right`, `chevron-down`, `external-link` |
| Status | `check`, `check-circle`, `alert-circle`, `info`, `help-circle` |
| Actions | `plus`, `edit`, `search`, `download`, `upload`, `refresh-cw`, `more-vertical`, `settings` |
| Communication | `bell`, `mail`, `message-square`, `phone` |
| Commerce / fulfilment | `package`, `truck`, `store`, `credit-card`, `globe` |
| Data / layout | `database`, `server`, `app-window`, `layers`, `clipboard-list`, `sliders`, `sliders-horizontal` |
| Decorative | `eye`, `star`, `sparkles`, `zap`, `shield-check`, `book`, `book-open` |

To add a new icon: append a `'name' => '<path .../>'` row inside the `$paths` array
in `assets/zui/icons.php`. Bump the library version (it's an additive change — patch
bump unless you ship breaking renames). All consumer plugins pick it up on next
sync.

### Migration notes

Use `<svg class="zui-icon">` for canonical icons. WP dashicons are an
acceptable fallback when SVG isn't available — most icons in plugin views use
dashicons today. A future migration round may emit SVG via a helper.

---

# TABLES

## Table (`.zui-table`)

### Purpose

Data table with sortable columns, selectable rows, two-line cell support.
Wrap in `.zui-table-scroll` for horizontally-overflowing tables.

### Required Structure

```html
<div class="zui-table-scroll">
  <table class="zui-table">
    <thead>
      <tr>
        <th><input type="checkbox"></th>
        <th class="zui-sortable" data-zui-sort-dir="asc">Date</th>
        <th>Customer</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><input type="checkbox"></td>
        <td>
          <strong>Order #1234</strong>
          <small>2 hours ago</small>
        </td>
        <td>Jane Doe</td>
        <td><span class="zui-badge zui-badge--success">Active</span></td>
        <td><button class="zui-btn-ghost">Edit</button></td>
      </tr>
      <tr class="is-selected">
        …
      </tr>
    </tbody>
  </table>
</div>
```

### Required classes

- `.zui-table` on `<table>`

### Optional classes

- `.zui-table-scroll` outer wrap for overflow
- `.zui-sortable` on sortable `<th>` columns
- `data-zui-sort-dir="asc|desc"` on currently-sorted column
- `.is-selected` on rows

### Variants

None.

### Modifiers

None.

### Nested elements

Standard HTML table structure: `<thead>`, `<tbody>`, `<tr>`, `<th>`, `<td>`.

### Parent requirements

Inside `.zui-scope`.

### Child requirements

Standard table HTML.

### Expected visual result

- `border-collapse: separate`, surface background
- Thead: uppercase 12px muted text with letter-spacing, bottom border
- Tbody td: 16px padding, divider border between rows, vertical align middle
- Row hover: `--zui-bg` background tint
- `.is-selected` row: info-tinted background
- Two-line cells: `<strong>` (semibold) + `<small>` (muted 12px, 2px top margin)
- First and last columns auto-width (for checkbox / actions)
- Sortable column: cursor pointer, `↕` indicator → `↑` / `↓` when sorted

### Common mistakes

- Skipping `.zui-table-scroll` for wide tables — horizontal overflow renders broken on narrow viewports
- Using `<th>` for a checkbox column without auto-width — checkbox cell expands too wide

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<table>  <!-- missing .zui-table class -->
  <tr><th>Header</th></tr>
</table>
```

### Migration notes

For DataTables-driven tables, keep the DataTables JS engine and co-class the
`<table>` with `.zui-table`. DataTables-generated pagination footer doesn't
get `.zui-pagination` automatically (JS would need to inject the class), so
pagination styling stays vendor.

---

## Filter Bar (`.zui-filter-bar`)

### Purpose

Toolbar above data tables: row of `<select>` filters + search field + right-
aligned action buttons.

### Required Structure

```html
<div class="zui-filter-bar">

  <div class="zui-select-wrap">
    <select class="zui-select">
      <option>All statuses</option>
      <option>Active</option>
    </select>
    <span class="zui-select-chevron"><svg class="zui-icon">…</svg></span>
  </div>

  <input type="text" class="zui-input" placeholder="Search…">

  <button class="zui-btn-secondary">Apply</button>

  <div class="zui-filter-actions">
    <button class="zui-btn-ghost">Export CSV</button>
  </div>

</div>
```

### Required classes

- `.zui-filter-bar`

### Optional classes

- `.zui-filter-actions` for grouped trailing buttons
- `.zui-filter-loading` + `.zui-spinner` for inline loading state

### Variants

None.

### Modifiers

None.

### Nested elements

Mix of `.zui-select`, `.zui-input`, `.zui-search-field`, and `.zui-btn-*` buttons.

### Parent requirements

Inside `.zui-scope`. Typically above a `.zui-table`.

### Child requirements

Any combination of filters + buttons.

### Expected visual result

- Flex with wrap, 8px gap, 16px padding
- Surface background, 1px border, `--zui-radius-lg` rounded
- Each filter slot (select/input): `flex: 1 1 200px`, min-width 160px, max-width 280px
- Trailing buttons / `.zui-filter-actions` pushed to the right via `margin-left: auto`
- Mobile (≤700px): each slot becomes full-width

### Common mistakes

- Putting buttons before filters — they don't get pushed right
- Forgetting `.zui-filter-actions` wrap around grouped trailing buttons

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-filter-bar">
  <select>…</select>  <!-- raw select, not .zui-select -->
</div>
```

### Migration notes

Replace existing filter toolbar markup with `.zui-filter-bar` co-class.
Internal controls migrate to `.zui-select` / `.zui-input` per their own
component contracts.

---

## Pagination (`.zui-pagination`)

### Purpose

Pagination control with two variants: numbered pills (Previous / 1 / 2 /
Next) and simple page indicator (Page X of Y · ← · →).

### Required Structure

**Numbered:**

```html
<div class="zui-pagination">
  <button class="zui-page-btn" disabled>← Previous</button>
  <button class="zui-page-btn is-active">1</button>
  <button class="zui-page-btn">2</button>
  <button class="zui-page-btn">3</button>
  <button class="zui-page-btn">Next →</button>
</div>
```

**Simple:**

```html
<div class="zui-pagination zui-pagination--simple">
  <button class="zui-page-btn">←</button>
  <span>Page 1 of 5</span>
  <button class="zui-page-btn">→</button>
</div>
```

### Required classes

- `.zui-pagination`
- `.zui-page-btn` on each button

### Optional classes

- `.zui-pagination--simple` variant
- `.is-active` on the current page button
- `disabled` attribute on disabled buttons

### Variants

- Default (numbered)
- `--simple`

### Modifiers

- `--simple`

### Nested elements

`.zui-page-btn` buttons (and a `<span>` for the page indicator in simple variant).

### Parent requirements

Inside `.zui-scope`. Typically below a `.zui-table`.

### Child requirements

One or more page buttons.

### Expected visual result

- Flex with center alignment, 4px gap, 16px vertical padding
- Buttons: 32×32 minimum, surface background, 1px border, `--zui-radius-md` rounded, muted text, body-size font
- Hover: primary border + primary text
- `.is-active`: primary fill, white text
- Disabled: 50% opacity (typical)

### Common mistakes

- Forgetting `.zui-page-btn` on each button — they don't pick up styling
- Missing `.is-active` on the current page — no visual indicator

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-pagination">
  <button>1</button>  <!-- raw button, no .zui-page-btn -->
</div>
```

### Migration notes

If using DataTables, the vendor's pagination footer doesn't get the canonical
class automatically. To convert: write JS that adds `.zui-pagination` and
`.zui-page-btn` after DataTables init. Otherwise keep vendor pagination styling.

---

# NOTIFICATIONS

## Snackbar (`.zui-snackbar`) — Floating Glass Pill

### Purpose

Top-right glassmorphic pill toast for save / action feedback. Each toast
carries a circular icon, message, close button, and a bottom countdown
bar that drains over the auto-dismiss duration. Hovering pauses the
countdown so long messages stay readable.

Always shown / hidden via the JS API — never hand-author the markup,
because the library manages stacking, icons, the close handler, the
duration custom-prop, and the leave animation.

```js
ZUI.snackbar('Data saved successfully', { type: 'success' });
ZUI.snackbar('Failed to save data',     { type: 'error' });
ZUI.snackbar('License expires in 7 days', { type: 'warning' });
ZUI.snackbar('Sync started',            { type: 'info', duration: 5000 });
ZUI.snackbar('<b>Two</b> failed',       { type: 'error', html: true });
```

### Options

| key          | default      | meaning                                              |
|--------------|--------------|------------------------------------------------------|
| `type`       | `'info'`     | `'success'` / `'error'` (alias `'danger'`) / `'warning'` / `'info'` |
| `duration`   | `3000` (ms)  | how long before auto-dismiss; also drives the progress bar via `--zui-snackbar-duration` |
| `html`       | `false`      | when `true`, treat `message` as trusted HTML (use sparingly — never with user input) |
| `closeLabel` | `'Dismiss'`  | accessible label on the close button                 |

### Required Structure

Built by `ZUI.snackbar()` — documented for reference / SSR fallback only.

```html
<div class="zui-snackbar-stack">                              <!-- container, created once -->
  <div class="zui-snackbar zui-snackbar--success"
       style="--zui-snackbar-duration: 3000ms">
    <span class="zui-snackbar__icon">
      <svg><!-- check / cross / warning / info — picked by variant --></svg>
    </span>
    <span class="zui-snackbar__text">Data saved successfully</span>
    <button class="zui-snackbar__close" aria-label="Dismiss">
      <svg><!-- × --></svg>
    </button>
    <span class="zui-snackbar__progress"></span>
  </div>
</div>
```

### Required classes

Wrapper:
- `.zui-snackbar-stack` (one per page; created lazily by `ZUI.snackbar`)

Each toast:
- `.zui-snackbar`
- One variant modifier: `--success` / `--error` (or `--danger`) / `--warning` / `--info`

Children:
- `.zui-snackbar__icon` — 40 × 40 white pill holding the variant SVG
- `.zui-snackbar__text` — message body
- `.zui-snackbar__close` — dismiss button with × icon
- `.zui-snackbar__progress` — bottom countdown bar (`scaleX` animation)

### Optional classes

- `.is-leaving` on `.zui-snackbar` — applied by the JS API right before
  removal to trigger the slide-out + fade. Internal.

### Variants

| Modifier      | Accent (`--zui-snackbar-accent`) | Icon         |
|---------------|----------------------------------|--------------|
| `--success`   | `var(--zui-success)` — green     | ✓ check      |
| `--error`     | `var(--zui-danger)` — red        | × cross      |
| `--danger`    | (alias of `--error`)             | × cross      |
| `--warning`   | `var(--zui-warning)` — amber     | ⚠ triangle   |
| `--info`      | `var(--zui-info)` — blue (default) | i circle   |

One custom prop drives the **left accent strip**, the **icon stroke color**,
and the **progress-bar color** — so adding a new variant means defining that
one prop on a new modifier and nothing else. The pill body stays white.

### Modifiers

(See variants. `.is-leaving` is internal.)

### Parent requirements

`.zui-snackbar-stack` is appended **inside `.zui-scope`** when available so
the `--zui-*` tokens resolve. Falls back to `<body>` when no scope is found
on the page. You do not author this — the JS API handles it.

### Child requirements

Icon + text + close + progress are all required for the design's visual
balance. The text element can contain plain text (default) or trusted
HTML when `{ html: true }` is passed.

### Expected visual result

- **Stack:** fixed at `top: 56px` (below WP admin bar), `inset-inline-end: 24px`,
  `gap: 14px`, `pointer-events: none` (toasts re-enable their own)
- **Pill:** 340 – 440 px wide, ~64 – 72 px tall depending on text length,
  16 px border-radius, **clean white glass**: `rgba(255,255,255,0.92)`
  + `backdrop-filter: blur(18px) saturate(160%)` + 1 px slate-200 border
  + soft inner-highlight + layered drop shadow
- **Left accent strip:** 5 px wide `::before` pseudo-element, full height,
  variant accent color, matches the pill's 16 px corner radius on the left
- **Icon circle:** 40 × 40, white surface with 1 px inner-highlight, holds
  20 × 20 SVG stroked in the variant accent color, 2.5 stroke-width
- **Text:** 14 px / 600 weight / line-height 1.4, slate-900
- **Close button:** 26 × 26 hit area, 8 px radius, muted gray icon →
  darkens + light gray bg on hover
- **Progress bar:** 3 px tall, `position:absolute; bottom:0`, full width,
  variant-accent color, animates `scaleX(1) → scaleX(0)` over `duration`
  with `transform-origin: left center`; **paused on hover** so the user can
  finish reading
- **Open animation:** 280 ms `translateY(-12px) scale(.98) → 0/1` with
  cubic-bezier(0.16, 1, 0.3, 1)
- **Leave animation:** `.is-leaving` → fade + `translateX(20px) scale(.96)`
  over 240 ms; DOM removed after
- **z-index:** 100001 (above modal, below WP emergency UI)
- **Mobile (≤ 600 px):** stack hugs both edges with 12 px gutters; pills
  grow to fill width
- **Reduced motion:** intro animation collapses to ~1 ms; progress bar
  jumps straight to empty (no perpetual animation for accessibility)

### Common mistakes

- Hand-authoring `.zui-snackbar` markup instead of calling `ZUI.snackbar()` —
  you lose the icon, close handler, progress bar, and `--zui-snackbar-duration`
  custom-prop wiring
- Forgetting the variant modifier — falls back to info (blue), which is
  semantically wrong for save failures
- Passing user-controlled strings with `{ html: true }` — XSS risk; pass
  plain text instead
- Calling `ZUI.snackbar()` before library CSS is loaded — the toast still
  appears but the glass effect / progress bar look unstyled

### Correct example

```js
// Save handler:
jQuery.post(ajaxurl, form.serialize(), function () {
  ZUI.snackbar('Settings saved', { type: 'success' });
});
```

### Incorrect example

```html
<!-- DO NOT hand-render — call ZUI.snackbar() instead. -->
<div class="zui-snackbar zui-snackbar--success">Saved</div>
```

### Migration notes

Replace plugin-specific toast / notice mechanisms with `ZUI.snackbar()`.
A consumer plugin that already ships its own jQuery snackbar helper can keep
the old helper as a thin shim that delegates to `ZUI.snackbar()` when
`window.ZUI` is available — that way existing callsites pick up the new
glass-pill design with zero rename churn. New code should call
`ZUI.snackbar()` directly.

---

## Loader Overlay (`.zui-loader-overlay`)

### Purpose

A translucent (or solid) overlay + centered spinner shown while a panel,
modal body, drawer, card, or section is fetching data. Pair with
`aria-busy="true"` on the parent so screen readers announce the loading
state. Used by: integration modal open (waits for slideout AJAX),
FTP test-connection, sync details fetch, edit-carrier modal, any
AJAX-populated container.

### Required Structure

```html
<!-- The parent container MUST be position: relative (or absolute). -->
<div class="some-panel" style="position: relative;">

  <!-- Real content -->
  <div class="some-content">…</div>

  <!-- Overlay — hidden by default, JS toggles `hidden` attribute -->
  <div class="zui-loader-overlay" hidden>
    <div class="zui-loader-spinner" aria-hidden="true"></div>
    <span class="zui-loader-label">Loading…</span>
  </div>

</div>
```

### Required classes

- `.zui-loader-overlay` on the overlay container
- `.zui-loader-spinner` on the rotating circle

### Optional classes

- `.zui-loader-label` — small muted text below the spinner (e.g. "Loading…")
- `.zui-loader-overlay--solid` — fully opaque white background instead of translucent
- `.zui-loader-overlay--fade` — adds a 200 ms opacity transition for fade-out
- `.zui-loader-spinner--sm` — 18 × 18 px small spinner
- `.zui-loader-spinner--lg` — 48 × 48 px large spinner

### Variants

Two size modifiers on the spinner (`--sm` / `--lg`) and two bg modifiers
on the overlay (`--solid` / `--fade`).

### Modifiers

(See optional classes.)

### Nested elements

- `.zui-loader-spinner` (required) — pure CSS circular arc rotation
- `.zui-loader-label` (optional) — text label below spinner

### Parent requirements

The parent **must be `position: relative`** (or `absolute`) so the overlay
anchors to its bounding box. `.zui-modal__body` already sets
`position: relative` for this reason. For other consumers (card body,
custom panel), add `position: relative` on the immediate parent.

### Child requirements

Spinner is required; label is optional.

### JS contract

The library does **not** wire `.zui-loader-overlay` automatically — the
consumer toggles the `hidden` attribute on the overlay element and
mirrors the state in `aria-busy` on the parent:

```js
function showLoader(overlay, parent) {
  parent.setAttribute('aria-busy', 'true');
  overlay.removeAttribute('hidden');
}
function hideLoader(overlay, parent) {
  parent.setAttribute('aria-busy', 'false');
  overlay.setAttribute('hidden', '');
}

// Example: integration modal AJAX
showLoader(loaderEl, formEl);
fetch(ajaxUrl, …)
  .then(r => r.json())
  .then(populateModal)
  .finally(() => hideLoader(loaderEl, formEl));
```

### Expected visual result

- **Overlay:** fills parent (`inset: 0`), translucent white
  (`rgba(255,255,255,0.72)`) + 2 px backdrop blur, flex-centered children
  vertically, 10 px gap, `pointer-events: auto` (blocks clicks while busy)
- **Spinner:** 32 × 32 px circle, 3 px border in `--zui-border` with the
  top color in `--zui-primary`, 0.8 s linear spin animation
- **Label:** 12 px / 600 weight muted slate text
- **`--solid` variant:** opaque white background, no blur — use when the
  content behind is sensitive/unfinished and should be fully masked
- **`--sm` / `--lg`:** 18 × 18 px (2 px border) / 48 × 48 px (4 px border)
- **Reduced-motion:** spin slows to 3 s per rotation instead of 0.8 s

### Common mistakes

- Forgetting to set `position: relative` on the parent → overlay anchors
  to the nearest positioned ancestor (often the viewport) and covers
  the whole page
- Skipping `aria-busy` on the parent → screen readers don't announce the
  loading state
- Leaving the overlay visible after AJAX error → user is stuck looking
  at a spinner forever; always hide in `.catch()` / `.finally()`

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- DON'T: parent has no `position: relative` so the overlay covers the whole page -->
<div class="some-panel">
  <div class="zui-loader-overlay"><div class="zui-loader-spinner"></div></div>
</div>
```

### Migration notes

A new primitive with no plugin-side predecessor. Use it to mask any panel /
modal / section body while an async fetch populates its contents. Any plugin
loading data into a region asynchronously should adopt this primitive
instead of inventing its own loader.

---

## Notice (`.zui-notice`)

### Purpose

Persistent inline status banner — survives until the user dismisses it
or the page reloads. Pair with `.zui-snackbar` (transient toast) when
the message is important enough that a 3 s toast isn't enough: the
snackbar gets attention, the notice gives the user time to read and
act. Used by: license activation failure, settings save errors with
recovery hints, post-action warnings.

### Required Structure

```html
<div class="zui-notice zui-notice--error" role="alert">
  <span class="zui-notice__icon">
    <svg><!-- alert-circle / check-circle / info / triangle --></svg>
  </span>
  <div class="zui-notice__body">
    <strong class="zui-notice__title">Activation failed</strong>
    <p class="zui-notice__text">Cannot activate License. This license key
      is already active on another site.</p>
  </div>
  <button type="button" class="zui-notice__close" aria-label="Dismiss">×</button>
</div>
```

### Required classes

- `.zui-notice` on the outer container
- One variant: `--error` / `--warning` / `--success` / `--info` (or alias `--danger`)
- `.zui-notice__icon` (with an SVG inside)
- `.zui-notice__body` wrapping the title and text
- `.zui-notice__text` for the message paragraph

### Optional classes

- `.zui-notice__title` — bold first line above the message text
- `.zui-notice__close` — × dismiss button

### Variants

| Modifier      | Accent (`--zui-notice-accent`) | Background (`--zui-notice-bg`) |
|---------------|--------------------------------|-------------------------------|
| `--error`     | `var(--zui-danger)` — red      | light red                     |
| `--danger`    | (alias of `--error`)           | light red                     |
| `--warning`   | `var(--zui-warning)` — amber   | light amber                   |
| `--success`   | `var(--zui-success)` — green   | light green                   |
| `--info`      | `var(--zui-info)` — blue       | light blue                    |

Two custom props drive everything — adding a new variant is just two
property assignments on a new modifier.

### Modifiers

(See variants.)

### Nested elements

- `.zui-notice__icon` (with SVG, 20 × 20 px)
- `.zui-notice__body` (flex 1 container for title + text)
- `.zui-notice__title` (optional bold first line)
- `.zui-notice__text` (message paragraph)
- `.zui-notice__close` (optional × button)

### Parent requirements

Anywhere inside `.zui-scope`. The notice is a block-level flex
container, so it stretches to its parent's width.

### Child requirements

Icon + body are required. Title is optional (omit if the message is one
short line). Close button is optional (omit if the notice is meant to
be permanent until next page reload).

### JS contract

The library does **not** wire the close button automatically. Consuming
plugins decide whether dismiss is:
- Local (just hide the element): `<button onclick="this.closest('.zui-notice').setAttribute('hidden','')">`
- Persistent (option / cookie): send an AJAX call when clicking, store dismissed state, suppress on next render
- Removed from DOM entirely: `this.closest('.zui-notice').remove()`

### Expected visual result

- **Container:** flex row with 12 px gap, 14 × 16 px padding, `xl` (12 px) radius
- **Left accent:** 4 px `border-inline-start` in the variant accent color
- **Background:** tinted variant background (`--zui-{variant}-bg`)
- **Icon:** 20 × 20 px in the variant accent color, top-aligned
- **Title:** 700 weight, slate-900
- **Text:** 13 px / 1.5 line-height, slate-600
- **Close:** 24 × 24 px hit area, 6 px radius, muted gray → darkens on hover

### Common mistakes

- Using `<div role="alert">` without a `.zui-notice__title` for very long
  messages — screen readers announce the whole block; a short bold title
  gives context fast
- Forgetting the variant modifier — the default uses the `--error` accent
  via the custom-prop fallback; explicit is better
- Mixing transient (`.zui-snackbar`) and persistent (`.zui-notice`)
  patterns randomly — pick one based on whether the message needs to
  persist for reading or just confirm an action

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<!-- DON'T put the close button outside the body — flex layout breaks. -->
<div class="zui-notice zui-notice--error">
  <button class="zui-notice__close">×</button>
  <p>Error message</p>
</div>
```

### Migration notes

A new primitive with no plugin-side predecessor. Use it to render persistent
inline notices alongside transient snackbar toasts — typical pattern: red
snackbar (8 s) for immediate attention + this notice for the durable copy of
the error reason inside the page body.
On success the page reloads after 1.5 s (snackbar visible); on error,
the page does NOT auto-reload — the notice + cleaned URL let the user
retry without losing context.

---

## Merge Tags (`.zui-merge-tags`)

### Purpose

Row of clickable mono-font chip tags for inserting template tokens (e.g.
`{customer_first_name}`) into a textarea. Used in SMS templates, return
guidelines, email customizer text fields, etc.

### Required Structure

```html
<div class="zui-merge-tags">
  <span class="zui-merge-tag">{customer_first_name}</span>
  <span class="zui-merge-tag">{order_id}</span>
  <span class="zui-merge-tag">{shop_name}</span>
</div>
<p class="zui-merge-tags-hint">Click to insert</p>
```

### Required classes

- `.zui-merge-tags`
- `.zui-merge-tag` on each chip

### Optional classes

- `.zui-merge-tags-hint` for the helper text

### Variants

None.

### Modifiers

None.

### Nested elements

One or more `.zui-merge-tag` chips, and optionally a hint paragraph.

### Parent requirements

Inside `.zui-scope`. Typically near a related textarea.

### Child requirements

Chip text.

### Expected visual result

- Flex wrap with 4px gap, 8px top margin
- Each chip: inline-flex, 3×8 padding, mono font, small (sm), surface background, 1px border, small rounded
- Hover: primary border + text + light info background
- Hint: mono font, faint text, small size

### Common mistakes

- Using regular font for merge tags — they should be mono to look like template tokens
- Forgetting to wire JS to insert the tag text into the related textarea on click

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<span class="zui-merge-tag">{token}</span>  <!-- outside .zui-merge-tags wrap -->
```

### Migration notes

Replace existing template-token chip patterns with `.zui-merge-tags` + chips.
Bind click on `.zui-merge-tag` to insert the tag text into the related
textarea at cursor position.

---

# APP-LEVEL WIRING & DATA ATTRIBUTES

Every interactive behaviour ZUI ships is wired automatically by `js/zui.js` and
`js/zui-app.js` when those scripts run. Consumer plugins do **not** need to write
event handlers — they only need to emit the right markup with the right
`data-zui-*` attributes. The wiring is idempotent: each component is initialised
at most once, even if `js/zui.js` is loaded multiple times.

> **Phase status:** This entire reference is **Phase 1 (production)**. Every
> attribute below ships in the canonical `js/zui.js` and is safe to rely on.

## Tab switching — `data-zui-tab`

```html
<nav class="zui-tabs">
    <a class="zui-tabs__item is-active" href="#"
       data-zui-tab="general">General</a>
    <a class="zui-tabs__item" href="#"
       data-zui-tab="advanced">Advanced</a>
</nav>

<div class="zui-tab-panel" data-zui-tab-panel="general">…</div>
<div class="zui-tab-panel" data-zui-tab-panel="advanced" hidden>…</div>
```

- Click a `.zui-tabs__item[data-zui-tab=X]` → show the panel where
  `data-zui-tab-panel=X`, hide every other panel in the same `.zui-tabs` group.
- Emits `zui:tabswapped` on `document` with `detail = { tab: 'X' }` so plugin
  scripts can re-initialise per-tab widgets (DataTables column adjust, etc.).

## Section switching inside a sidebar — `data-zui-section`

```html
<a class="zui-sidebar__item" href="#" data-zui-section="general">General</a>
<a class="zui-sidebar__item" href="#" data-zui-section="emails">Emails</a>

<section class="zui-section" data-zui-section-panel="general">…</section>
<section class="zui-section" data-zui-section-panel="emails" hidden>…</section>
```

Identical mechanics to tab switching but scoped to a sidebar. Same `is-active`
toggle on the trigger, same `hidden` attribute on the inactive panels.

## Sidebar drawer — `data-zui-drawer-toggle`

```html
<button data-zui-drawer-toggle aria-label="Open menu"><svg …></svg></button>
<aside class="zui-sidebar">…</aside>
```

Clicking the toggle adds `.zui-sidebar-open` to `<body>`. The overlay
`.zui-sidebar__overlay` and the close button `.zui-sidebar__close` remove it.
ESC also closes. Only fires below 1100 px (above that the sidebar is permanently
visible).

## Modal — `data-zui-modal-toggle` / `data-zui-modal-close`

```html
<button data-zui-modal-toggle="confirm-delete">Delete…</button>

<div class="zui-modal" id="confirm-delete" hidden>
    <div class="zui-modal__backdrop" data-zui-modal-close></div>
    <div class="zui-modal__panel">
        <button class="zui-modal__close" data-zui-modal-close>×</button>
        …
    </div>
</div>
```

- `data-zui-modal-toggle="<id>"` opens the modal with that `id`. Removes
  `hidden` and adds `.is-open`. Locks `<body>` scroll.
- `data-zui-modal-close` (anywhere inside the modal, **no value**) closes it.
  Use it on the backdrop and on the explicit close button.
- ESC closes the topmost open modal.

## Slideout (side drawer) — `data-zui-slideout-toggle` / `data-zui-slideout-close`

Mechanically identical to Modal — same attribute pattern, same auto-wiring —
but renders as a right-edge drawer instead of a centred dialog. Use for
contextual settings (carrier edit, integration config) where the page context
should stay visible.

```html
<button data-zui-slideout-toggle="edit-carrier">Edit</button>

<div class="zui-slideout" id="edit-carrier" hidden>
    <div class="zui-slideout__backdrop" data-zui-slideout-close></div>
    <div class="zui-slideout__panel">
        <header class="zui-slideout__header">
            <h3 class="zui-slideout__title">Edit Carrier</h3>
            <button class="zui-slideout__close" data-zui-slideout-close>×</button>
        </header>
        <div class="zui-slideout__body">…</div>
    </div>
</div>
```

Programmatic API: `ZUI.slideout.open('edit-carrier')` /
`ZUI.slideout.close('edit-carrier')`.

## File dropzone — `data-zui-dropzone`

```html
<div class="zui-upload" data-zui-dropzone>
    <input type="file" name="csv" accept=".csv">
    <p>Drag &amp; drop a CSV here, or click to choose</p>
</div>
```

- During drag-over, `.is-dragover` is added to the dropzone (style this with a
  highlighted border / background — the library does **not** provide a default).
- On drop, the dropped `FileList` is assigned to the **first** descendant
  `<input type="file">`, then a `change` event is fired on it so any plugin JS
  listening to the input still runs.
- Click anywhere on the dropzone delegates to the file input.

## Auto-submit filter form — `data-zui-filter-form`

```html
<form class="zui-filter-bar" data-zui-filter-form action="" method="get">
    <input class="zui-input" name="search" placeholder="Search…">
    <select class="zui-select" name="status">…</select>
</form>
```

Any change on a control inside the form triggers `form.submit()` after a
**250 ms debounce** (so typing in the search box doesn't fire a request per
keystroke). The plugin server still owns the response — the library only handles
the trigger.

## Sortable table — `data-zui-sort` / `data-zui-sort-dir`

```html
<table class="zui-table">
    <thead>
        <tr>
            <th class="zui-sortable" data-zui-sort="order_date" data-zui-sort-dir="desc">
                Date
            </th>
            <th class="zui-sortable" data-zui-sort="order_id">Order #</th>
            <th>Status</th>  <!-- no data-zui-sort = not sortable -->
        </tr>
    </thead>
    …
</table>
```

- Click on any `.zui-sortable` `<th>` toggles `data-zui-sort-dir` between
  `asc` and `desc` (defaults to `asc` if unset).
- Other `<th>` in the same table get `data-zui-sort-dir` cleared.
- Emits `zui:sort` on the `<table>` with
  `detail = { column: 'order_date', dir: 'asc' }`. The plugin owns the actual
  re-sort — typically a re-query AJAX, or `DataTable.order()` if using DataTables.

## Kebab / actions menu — `.zui-actions` + `.zui-actions-toggle`

```html
<div class="zui-actions">
    <button class="zui-actions-toggle" aria-haspopup="true" aria-expanded="false">
        <svg class="zui-icon"><!-- more-vertical --></svg>
    </button>
    <ul class="zui-actions-menu" hidden role="menu">
        <li><a href="#" role="menuitem">Edit</a></li>
        <li><a href="#" role="menuitem">Duplicate</a></li>
        <li><a href="#" role="menuitem">Delete</a></li>
    </ul>
</div>
```

Click the toggle → unhides `.zui-actions-menu` and flips `aria-expanded`.
Outside-click / ESC closes. Use for row actions, card actions, anywhere a
three-dot menu is needed.

## Tab-conditional visibility — `data-zui-sidebar-tabs`

```html
<button class="zui-header__menu-toggle"
        data-zui-drawer-toggle
        data-zui-sidebar-tabs="general,emails">
    <svg class="zui-icon"><!-- menu --></svg>
</button>
```

For chrome elements that only make sense on certain tabs — most commonly the
mobile hamburger that opens the sidebar drawer, when only some top tabs
actually render a sidebar. The attribute is a comma-separated list of tab
slugs that should keep the element visible; on every other tab the library
sets `hidden` on it.

- Auto-fires on the `zui:tabswapped` document event (library's built-in tab
  swap).
- For plugins whose tab swap emits a different event name (e.g.
  `ast:tabswapped`), call `ZUI.syncSidebarToggles( tab )` directly from your
  own swap handler.
- Works on **any element**, not just the header toggle — anything you want
  to gate to a subset of tabs.

## Custom events emitted by the library

| Event | Target | When | `event.detail` |
|---|---|---|---|
| `zui:tabswapped` | `document` | After a `data-zui-tab` click swaps panels | `{ tab: <slug> }` |
| `zui:sort` | the `<table>` | After a `.zui-sortable` click | `{ column, dir }` |
| `zui:modalopen` | the modal element | After a modal opens | `{}` |
| `zui:modalclose` | the modal element | After a modal closes | `{}` |
| `zui:slideoutopen` | the slideout element | After a slideout opens | `{}` |
| `zui:slideoutclose` | the slideout element | After a slideout closes | `{}` |

Listen with `document.addEventListener('zui:tabswapped', fn)` (bubbling) for
broad hooks, or scope to a specific element where useful.

---

# PLUGIN CHROME REGISTRY

Two library-level PHP registries let every Zorem consumer plugin render the
same header brand cluster and the same License-tab ecosystem grid without
duplicating the data. A new plugin joining the family adds one entry per
registry; its `header.php` and `license.php` then read from the registry
rather than hardcoding the chrome inline.

Both registries are pure PHP — no markup, no CSS, no enqueue. Include the
file once per request and call the resolver.

## Brand registry — `zui_get_plugin_brand( $slug )`

### Purpose

Returns the chrome brand info for a plugin: the three-letter name shown in
the header, the small "PRO" pill, the tagline after it, and the icon +
colors painted on the `.zui-brand__emblem` tile.

### File

`brand.php` (library root). Include with
`require_once <plugin>/assets/zui/brand.php;`.

### Signature

```php
zui_get_plugin_brand( string $slug ): array|null
```

`$slug` is the plugin's main-file basename — what
`plugin_basename( $main_file )` returns (e.g. `'my-plugin/my-plugin.php'`).

### Returned array keys

| Key            | Type   | Meaning                                                 |
| -------------- | ------ | ------------------------------------------------------- |
| `name`         | string | 3-letter acronym for `.zui-brand__name`                 |
| `badge`        | string | Pill label for `.zui-brand__badge` (usually `'PRO'`)    |
| `tagline`      | string | One-line description for `.zui-brand__tagline`          |
| `icon`         | string | `zui_icon()` key rendered inside the emblem            |
| `emblem_bg`    | string | Hex value piped into `--zui-brand-emblem-bg` CSS var    |
| `emblem_color` | string | Hex value piped into `--zui-brand-emblem-color` CSS var |

Returns **`null`** when the slug isn't registered. The consumer must check
this and fall back to a hardcoded brand (see "Consumer call pattern" below).

### Registering a new plugin

Edit `brand.php` and add an entry keyed by the new plugin's basename. No
consumer-side code change needed — the next request picks it up.

### Consumer call pattern (header.php)

```php
require_once <plugin>/assets/zui/brand.php;
$brand = zui_get_plugin_brand( plugin_basename( $main_file ) );
if ( ! is_array( $brand ) ) {
    $brand = array(
        'name'         => 'XYZ',
        'badge'        => 'PRO',
        'tagline'      => __( 'My Plugin', 'my-textdomain' ),
        'icon'         => 'package',
        'emblem_bg'    => '#DBEAFE',
        'emblem_color' => '#2563EB',
    );
}
$emblem_style = sprintf(
    '--zui-brand-emblem-bg:%s;--zui-brand-emblem-color:%s;',
    $brand['emblem_bg'],
    $brand['emblem_color']
);
?>
<div class="zui-brand">
    <span class="zui-brand__emblem" aria-hidden="true" style="<?php echo esc_attr( $emblem_style ); ?>">
        <?php zui_icon( $brand['icon'] ); ?>
    </span>
    <span class="zui-brand__name"><?php echo esc_html( $brand['name'] ); ?></span>
    <span class="zui-brand__badge"><?php echo esc_html( $brand['badge'] ); ?></span>
    <span class="zui-brand__tagline"><?php echo esc_html( $brand['tagline'] ); ?></span>
</div>
```

The `--zui-brand-emblem-*` CSS variables are documented in
`css/components/header.css` and have sensible blue-tile defaults if the
inline style is omitted.

## Ecosystem registry — `zui_get_ecosystem_plugins( $current_slug = '' )`

### Purpose

Returns the ordered list of Zorem companion plugins displayed in the
ecosystem grid on every License tab. Same six entries everywhere; the
caller's own entry is auto-hidden so a plugin never advertises itself.

### File

`eco-plugins.php` (library root). Include with
`require_once <plugin>/assets/zui/eco-plugins.php;`.

### Signature

```php
zui_get_ecosystem_plugins( string $current_slug = '' ): array
```

`$current_slug` is the calling plugin's basename. Pass `''` (or omit) to
get the unfiltered list. If `$current_slug` matches a registered plugin's
key, that entry is removed and `array_values()` is applied so the result
is always a clean numerically-indexed list.

### Returned array shape

Each entry has these keys:

| Key      | Type   | Meaning                                              |
| -------- | ------ | ---------------------------------------------------- |
| `name`   | string | Human-readable plugin name                           |
| `slug`   | string | Plugin basename used by `is_plugin_active()`         |
| `icon`   | string | Fallback `zui_icon()` key (used when `logo` is empty)|
| `logo`   | string | **Absolute URL** to a PNG (resolved by the library)  |
| `accent` | string | Hex color used for the logo tile foreground          |
| `tint`   | string | Hex color used for the logo tile background          |
| `desc`   | string | One-paragraph description                            |
| `url`    | string | Get-Extension target                                 |
| `badge`  | string | Optional pill label (e.g. `"Recommended"`)           |
| `stat`   | string | Footer micro-copy ("15k+ active stores")             |

### Logo resolution

The `logo` value comes back as a fully-qualified URL pointing at
`<library>/images/eco/<file>.png`. Consumers should NOT prepend their own
asset path — they `<img src>` it directly.

### Consumer call pattern (license.php)

```php
require_once <plugin>/assets/zui/eco-plugins.php;
$eco_plugins = zui_get_ecosystem_plugins( plugin_basename( $main_file ) );

foreach ( $eco_plugins as $eco_p ) {
    $is_active = is_plugin_active( $eco_p['slug'] );
    // …render .zui-card.zui-lic-plugin… (see License Plugin Card docs below)
}
```

### Adding a new ecosystem plugin

Edit `eco-plugins.php`, append a new entry keyed by the new plugin's
basename, and (if it has a PNG logo) drop the image into
`<library>/images/eco/`. The grid picks it up on the next request.

---

# LICENSE PAGE FAMILY

The License page is a cross-plugin shared template. All license components
compose to produce an identical page structure across every plugin — only
plugin-specific config variables change.

## License Container (`.zui-lic`)

### Purpose

Outer wrapper for the License & Extensions page. Contains the page header,
2-column grid (license status + telemetry on left, documentation on right),
and the ecosystem plugin grid.

### Required Structure

```html
<div class="zui-lic" id="zui-lic">
  <div class="zui-lic-head">…</div>
  <div class="zui-lic-grid">…</div>
  <div class="zui-lic-eco">…</div>
</div>
```

### Required classes

- `.zui-lic`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

- `.zui-lic-head` (page header)
- `.zui-lic-grid` (2-column main area)
- `.zui-lic-eco` (ecosystem plugin grid)

### Parent requirements

Inside `.zui-scope`.

### Child requirements

Per the structure above.

### Expected visual result

- Vertical stack with 28px gap between major regions

### Common mistakes

- Skipping the outer `.zui-lic` — sub-sections don't share the 28px spacing rhythm

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-head">…</div>
<div class="zui-lic-grid">…</div>
<!-- Missing .zui-lic outer wrap — no spacing rhythm -->
```

### Migration notes

Copy the canonical license template verbatim and change the 11 plugin config
variables at the top of the file. The structure inside `.zui-lic` is fixed.

---

## License Page Header (`.zui-lic-head`)

### Purpose

Top header on the License page: eyebrow + title + sub + connection status
pill.

### Required Structure

```html
<div class="zui-lic-head">
  <div>
    <span class="zui-lic-eyebrow">
      <svg class="zui-icon"><!-- shield-check --></svg>
      <span>Store Ecosystem Management</span>
    </span>
    <h1 class="zui-lic-title">License & Extensions</h1>
    <p class="zui-lic-sub">Manage your secure zorem.com connection parameters.</p>
  </div>
  <div class="zui-lic-telemetry is-on">
    <span class="zui-lic-telemetry__dot"><span></span><span></span></span>
    <div>
      <div class="zui-lic-telemetry__label">Secure Connection</div>
      <div class="zui-lic-telemetry__val">
        <span>Synchronized</span>
        <span class="zui-lic-telemetry__time">(--:--:--)</span>
      </div>
    </div>
  </div>
</div>
```

### Required classes

- `.zui-lic-head`
- `.zui-lic-eyebrow`
- `.zui-lic-title` on `<h1>`
- `.zui-lic-sub`
- `.zui-lic-telemetry`
- `.zui-lic-telemetry__dot`
- `.zui-lic-telemetry__label`
- `.zui-lic-telemetry__val`

### Optional classes

- `is-on` / `is-off` state on the telemetry pill
- `.zui-lic-telemetry__time` for the live time element

### Variants

- `.is-on` (green dot, "Synchronized")
- `.is-off` (amber dot, "Unlinked")

### Modifiers

State classes only.

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic`.

### Child requirements

Per the structure above.

### Expected visual result

- Flex row with `justify-content: space-between` and 16px gap, items wrap on narrow viewports
- Bottom border (`--zui-border`), 20px bottom padding
- Eyebrow: 11px uppercase letter-spacing bold primary-600 text + icon
- Title: 22px extra-bold strong text
- Sub: 12px muted
- Telemetry pill: rounded `--zui-radius-xl` background with 10×16 padding, animated double-dot indicator

### Common mistakes

- Wrong heading level — should be `<h1>` (the page-level title)
- Missing `is-on` / `is-off` — telemetry pill has no color
- Missing the dot spans inside `__dot` — animation has nothing to bind to

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-head">
  <h2>License</h2>  <!-- wrong heading level + no canonical sub-elements -->
</div>
```

### Migration notes

Build the header from scratch using the canonical structure. Copy/translate
the eyebrow + title + sub strings into the plugin's textdomain. The
`is-on/is-off` state is driven by the plugin's license-active status.

---

## License Card (`.zui-lic-card`)

### Purpose

Slotted card variant composed with `.zui-card`. Used to wrap the SSO Gateway
section and the Telemetry preferences section.

### Required Structure

```html
<div class="zui-card zui-lic-card">
  <div class="zui-lic-card__head">
    <span class="zui-lic-card__bar"></span>
    <span class="zui-lic-card__title">Section Title</span>
  </div>
  <div class="zui-lic-card__body">
    <!-- Active or Inactive state OR Telemetry form -->
  </div>
</div>

<!-- Head row variant (title + save button on right) -->
<div class="zui-card zui-lic-card">
  <div class="zui-lic-card__head zui-lic-card__head--row">
    <span class="zui-lic-card__title">…</span>
    <div class="zui-lic-save">…</div>
  </div>
  <form class="zui-lic-telemetry-form">…</form>
</div>
```

### Required classes

- `.zui-card.zui-lic-card` outer composition
- `.zui-lic-card__head`
- `.zui-lic-card__bar` (vertical accent bar)
- `.zui-lic-card__title`
- `.zui-lic-card__body`

### Optional classes

- `.zui-lic-card__head--row` modifier for split-row head layout

### Variants

- Default head (with `__bar` + `__title`)
- `__head--row` (head row with title on left + actions on right)

### Modifiers

- `__head--row`

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic-col-main`.

### Child requirements

`__head` then `__body`.

### Expected visual result

- Composes `.zui-card` chrome (surface, border, radius, shadow)
- `__head`: 16×22 padding, soft slate-50 background, bottom divider, 10px gap between bar and title
- `__bar`: 4×18 primary-blue rounded pill
- `__title`: 11px uppercase letter-spacing extra-bold soft text + icon
- `__body`: 22px padding

### Common mistakes

- Forgetting the `.zui-card` outer composition — card chrome missing
- Skipping `__bar` — head looks bare without the vertical accent
- Mixing the `__head--row` modifier without a flex child cluster on the right

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-card">  <!-- missing .zui-card -->
  <div class="zui-lic-card__head"><span>Title</span></div>  <!-- missing __bar and __title -->
</div>
```

### Migration notes

Build from scratch using the canonical license template.

---

## License Save Button (`.zui-lic-savebtn`)

### Purpose

Compact primary save button used inside a `.zui-lic-card__head--row` row to
persist an inline form (e.g. the Telemetry preferences form). Smaller and
denser than `.zui-savebtn`, but shares the primary-blue/saved-green visual
language so the lifecycle reads identically across the plugin.

### Required structure

```html
<div class="zui-lic-card__head zui-lic-card__head--row">
    <span class="zui-lic-card__title">…</span>
    <div class="zui-lic-save">
        <button type="submit" form="my-form" class="zui-lic-savebtn"
                data-label-saving="Saving…"
                data-label-saved="Saved">Save</button>
    </div>
</div>
```

### Required classes

- `.zui-lic-savebtn` on the `<button>`

### Optional attributes

- `form="<id>"` — submit a sibling form from outside it
- `data-label-saving` — text shown while AJAX is in flight (default `Saving…`)
- `data-label-saved` — text shown briefly after success (default `Saved`)

### Variants

| State class    | Background                  | Cursor      | Pointer events |
|----------------|------------------------------|-------------|----------------|
| (none)         | `--zui-primary`              | `pointer`   | enabled        |
| `.is-saving`   | unchanged, 55% opacity       | `wait`      | disabled       |
| `.is-saved`    | `--zui-success`              | `default`   | disabled       |

### Nested elements

None — the button itself carries the label text (no inner `__label` span
required; the lifecycle helpers swap `textContent` directly).

### Expected visual result

- 7×14 padding, 11px / 800 weight, `--zui-radius-lg` corners
- Primary-blue background; darker on `:hover` (`--zui-primary-hover`)
- Saving: 55% opacity, `cursor: wait`
- Saved: green background for ~3 s, then helper-reverts to default

### Public JS lifecycle

Drive the visual states with the same helpers used by `.zui-savebtn`:

```js
ZUI.btnSaving( btn );                              // enter Saving…
ZUI.btnSaved(  btn );                              // enter Saved → auto-revert after 3 s
ZUI.btnReset(  btn );                              // clear without showing the green badge
```

Pair with a snackbar for the success/failure toast:

```js
ZUI.snackbar( 'Data saved successfully', { type: 'success' } );
ZUI.snackbar( 'Save failed',             { type: 'error'   } );
```

### Common mistakes

- Hard-coding `background: #0f172a` (slate) — diverges from the primary-blue
  language. Use `var(--zui-primary)` so all save buttons read identically.
- Forgetting `ZUI.snackbar()` on AJAX success — users get no confirmation toast
  even though the button briefly flashes "Saved".
- Calling `btn.disabled = true` directly during save — the label won't change.
  Use `ZUI.btnSaving()` instead.

### Incorrect example

```html
<button class="zui-lic-savebtn" style="background:#0f172a;">Save</button>
<!-- inline color override — diverges from token-driven theming -->
```

### Migration notes

If you previously rendered this save row with a custom `<button>` using
slate-900 background, swap to `.zui-lic-savebtn` so token theming kicks in
and the saving/saved lifecycle works out of the box via the helpers.

---

## License Active State (`.zui-lic-active`)

### Purpose

Display block inside `.zui-lic-card__body` for an active license. Shows a
green-tinted card with check icon + license name + licensed-domain readout +
deactivate row below.

### Required Structure

```html
<div class="zui-lic-active">
  <div class="zui-lic-active__left">
    <span class="zui-lic-active__icon">
      <svg class="zui-icon"><!-- check-circle --></svg>
    </span>
    <div>
      <span class="zui-lic-active__eyebrow">Active Premium License</span>
      <h4 class="zui-lic-active__name">Plugin Name</h4>
    </div>
  </div>
  <div class="zui-lic-active__domain">
    <span>Licensed Domain:</span>
    <span class="zui-lic-active__domain-val">example.com</span>
  </div>
</div>

<!-- Below — deactivate row (sibling of .zui-lic-active inside __body) -->
<div class="zui-lic-deactivate-row">
  <p>Want to deactivate the license for any reason?</p>
  <form method="post" id="plugin-license-form">
    <input type="hidden" name="license_key" value="…">
    <input type="hidden" name="action" value="plugin_license_deactivate">
    <button class="zui-lic-deactivate">Deactivate License</button>
  </form>
</div>
<p class="zui-lic-msg" id="plugin-license-message"></p>
```

### Required classes

- `.zui-lic-active`
- `.zui-lic-active__left`
- `.zui-lic-active__icon`
- `.zui-lic-active__eyebrow`
- `.zui-lic-active__name` on `<h4>`
- `.zui-lic-active__domain`
- `.zui-lic-active__domain-val`
- `.zui-lic-deactivate-row`
- `.zui-lic-deactivate` on the button
- `.zui-lic-msg` on the placeholder feedback paragraph

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic-card__body`.

### Child requirements

`__left` cluster + `__domain` cluster. Followed by `.zui-lic-deactivate-row` and `.zui-lic-msg` as siblings inside the card body.

### Expected visual result

- Flex row with `justify-content: space-between`, 16px gap, wrap on mobile, 18px padding
- Green-tinted background (`rgba(16, 185, 129, 0.06)`), green-tinted border, `--zui-radius-2xl` rounded
- Icon tile: 44×44 with `#10b981` green background, white check icon (24×24), soft shadow
- Eyebrow: 10px uppercase letter-spacing extra-bold green-600 text
- Name: 16px extra-bold strong heading
- Domain readout: mono font, right-aligned, 11px muted with bold value
- Deactivate row: 16px gap, 20px top margin + 18px top padding + top border, "Deactivate License" outline button in red

### Common mistakes

- Missing `__left` wrap — icon and text don't flex together on the left
- Missing `__domain` — right side has no readout
- Putting the deactivate row outside `.zui-lic-card__body` — wrong placement

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-active">
  <h4>Active</h4>  <!-- missing __left, __icon, __eyebrow, __name, __domain -->
</div>
```

### Migration notes

Build from scratch using the canonical license template.

---

## License Inactive State (`.zui-lic-inactive`)

### Purpose

Display block inside `.zui-lic-card__body` for an inactive license. Shows an
"Action Needed" amber badge + title + description + CTA row with Activate
button.

### Required Structure

```html
<div class="zui-lic-inactive">
  <span class="zui-lic-inactive__badge">
    <svg class="zui-icon"><!-- alert-circle --></svg>
    <span>Action Needed</span>
  </span>
  <h3 class="zui-lic-inactive__title">Activate your license</h3>
  <p class="zui-lic-inactive__text">
    Securely connect your installation via zorem.com to receive automatic
    updates and premium support.
  </p>
  <div class="zui-lic-inactive__action">
    <a class="zui-btn-primary zui-lic-activate" href="…">
      <svg class="zui-icon"><!-- zap --></svg>
      <span>Activate License</span>
    </a>
    <span class="zui-lic-inactive__note">Redirects safely to the zorem.com gateway.</span>
  </div>
</div>
```

### Required classes

- `.zui-lic-inactive`
- `.zui-lic-inactive__badge`
- `.zui-lic-inactive__title` on `<h3>`
- `.zui-lic-inactive__text`
- `.zui-lic-inactive__action`
- `.zui-lic-activate` on the activate button (composes with `.zui-btn-primary`)

### Optional classes

- `.zui-lic-inactive__note` for the small note next to the activate button

### Variants

None.

### Modifiers

None.

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic-card__body`.

### Child requirements

`__badge` + `__title` + `__text` + `__action` in that order.

### Expected visual result

- Vertical stack with 10px gap
- Badge: amber-tinted pill, `align-self: flex-start`, 3×10 padding, uppercase 10px extra-bold
- Title: 18px extra-bold strong
- Text: 12px line-height 1.6 muted with max-width 560px
- Action row: 14px gap, flex wrap, 10px top margin + 16px top padding + top divider
- Activate button: composes `.zui-btn-primary` + size override (12×22 padding, 8px gap, no text-decoration)

### Common mistakes

- Putting the badge inside the title (instead of as a sibling) — wrong visual layout
- Missing `.zui-btn-primary` on the activate button — no primary fill (just the size override applies)

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-inactive">
  <h3>Activate</h3>  <!-- missing __title class -->
  <a>Activate</a>    <!-- missing __action wrap + .zui-btn-primary -->
</div>
```

### Migration notes

Build from scratch using the canonical license template.

---

## License Help Sidebar (`.zui-lic-help`)

### Purpose

Documentation links sidebar shown in the License page's right column.

### Required Structure

```html
<div class="zui-lic-help">
  <h3 class="zui-lic-help__title">Documentation & Help</h3>
  <ul class="zui-lic-help__list">
    <li>
      <a href="…" target="_blank" rel="noopener noreferrer">
        <span>Knowledge Base</span>
        <svg class="zui-icon"><!-- external-link --></svg>
      </a>
    </li>
    <li>
      <a href="…" target="_blank" rel="noopener noreferrer">
        <span>Support Ticket</span>
        <svg class="zui-icon"><!-- external-link --></svg>
      </a>
    </li>
  </ul>
</div>
```

### Required classes

- `.zui-lic-help`
- `.zui-lic-help__title`
- `.zui-lic-help__list`

### Optional classes

None.

### Variants

None.

### Modifiers

None.

### Nested elements

Title + `<ul>` list of `<li>` anchors.

### Parent requirements

Inside `.zui-lic-col-side`.

### Child requirements

Title + list.

### Expected visual result

- Tinted background (`--zui-bg`), 1px border, `--zui-radius-2xl` rounded, 20px padding
- Title: 11px uppercase letter-spacing extra-bold soft text, 14px bottom margin
- List: no list-style markers, 10px vertical padding per item, top border between items (no border on first item)

### Common mistakes

- Forgetting `.zui-lic-help__list` — list bullets render
- Putting it outside `.zui-lic-col-side` — wrong placement

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-help">
  <ul>  <!-- missing __list class -->
    <li>Docs</li>
  </ul>
</div>
```

### Migration notes

Build from scratch using the canonical license template.

---

## License Ecosystem Section (`.zui-lic-eco`)

### Purpose

Bottom section of the License page: heading + filter pills + search + grid of
plugin promo cards.

### Required Structure

```html
<div class="zui-lic-eco">

  <div class="zui-lic-eco__head">
    <div class="zui-lic-eco__heading">
      <span class="zui-lic-eco__icon"><svg class="zui-icon"><!-- sparkles --></svg></span>
      <div>
        <h3>Ecosystem</h3>
        <p>Install complimentary extensions.</p>
      </div>
    </div>
    <div class="zui-lic-eco__filters">
      <button class="zui-lic-eco__filter is-active" data-filter="all">All</button>
      <button class="zui-lic-eco__filter" data-filter="active">Active</button>
      <button class="zui-lic-eco__filter" data-filter="addons">Add-ons</button>
    </div>
  </div>

  <div class="zui-lic-eco__search">
    <span class="zui-lic-eco__search-icon"><svg class="zui-icon"><!-- search --></svg></span>
    <input type="text" class="zui-input" placeholder="Search…">
  </div>

  <div class="zui-lic-eco__grid">
    <!-- Plugin cards (see License Plugin Card) -->
    <div class="zui-lic-eco__empty" hidden>No plugins matched.</div>
  </div>

</div>
```

### Required classes

- `.zui-lic-eco`
- `.zui-lic-eco__head`
- `.zui-lic-eco__heading`
- `.zui-lic-eco__icon`
- `.zui-lic-eco__filters`
- `.zui-lic-eco__filter` on each filter button
- `.zui-lic-eco__search`
- `.zui-lic-eco__search-icon`
- `.zui-lic-eco__grid`
- `.zui-lic-eco__empty`

### Optional classes

- `.is-active` on the current filter
- `data-filter=` JS hook attribute on each filter button

### Variants

None.

### Modifiers

None.

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic`.

### Child requirements

Head + search + grid (with optional empty-state).

### Expected visual result

- Vertical stack with 18px gap, 8px top padding, top border (`--zui-divider`)
- Head: flex with `justify-content: space-between`, wraps on narrow viewports
- Eco icon: 7px padding, primary-50 background, `--zui-radius-lg` rounded, primary color
- Filter pills: segmented control look (4px gap, 4px padding, slate-50 background, 1px border, `--zui-radius-xl` rounded)
- Filter buttons: 5×12 padding, 11px bold muted text, transparent background; `.is-active` gets white background + strong text + shadow
- Search: position relative, max 380px, search icon absolutely positioned at left, input has 36px left padding
- Grid: responsive 1 → 2 → 3 columns at 760 / 1180px breakpoints, 20px gap
- Empty state: spans full row, 40px padding, dashed border, soft tinted background, centered muted text

### Common mistakes

- Forgetting filter pills — losing the segmented control UX
- Missing `data-filter=` attributes — JS can't bind filter logic
- Hiding the empty state with CSS instead of the `hidden` attribute — inconsistent

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-eco">
  <h3>Ecosystem</h3>  <!-- missing __head, __filters, __search, __grid -->
</div>
```

### Migration notes

Build from scratch using the canonical license template.

---

## License Plugin Card (`.zui-lic-plugin`)

### Purpose

Plugin promo card in the License ecosystem grid. Composes with `.zui-card`.

### Required Structure

```html
<div class="zui-card zui-lic-plugin" data-name="Plugin Name" data-active="1">

  <div class="zui-lic-plugin__head">
    <span class="zui-lic-plugin__logo" style="background: rgba(13, 148, 136, 0.1);">
      <img src="…" alt="…" loading="lazy">
    </span>
    <div class="zui-lic-plugin__id">
      <h4 class="zui-lic-plugin__name">Plugin Name</h4>
      <span class="zui-lic-plugin__type">WOO EXTENSION</span>
    </div>
    <span class="zui-lic-plugin__badge">Recommended</span>
  </div>

  <div class="zui-lic-plugin__body">
    <p class="zui-lic-plugin__desc">Plugin description.</p>
    <div class="zui-lic-plugin__foot">

      <!-- Active state -->
      <span class="zui-lic-plugin__active">
        <span class="zui-lic-plugin__dot"></span>
        Active
      </span>
      <span class="zui-lic-plugin__stat">Active in this store</span>

      <!-- OR Inactive state (instead of __active + __stat above) -->
      <!--
      <a class="zui-lic-plugin__get" href="…" target="_blank">
        <span>Get Extension</span>
        <svg class="zui-icon">…</svg>
      </a>
      <span class="zui-lic-plugin__stat">15k+ active stores</span>
      -->

    </div>
  </div>

</div>
```

### Required classes

- `.zui-card.zui-lic-plugin` outer composition
- `.zui-lic-plugin__head`
- `.zui-lic-plugin__logo`
- `.zui-lic-plugin__id`
- `.zui-lic-plugin__name` on `<h4>`
- `.zui-lic-plugin__type`
- `.zui-lic-plugin__body`
- `.zui-lic-plugin__desc`
- `.zui-lic-plugin__foot`
- `.zui-lic-plugin__active` (active state) OR `.zui-lic-plugin__get` (inactive CTA)
- `.zui-lic-plugin__dot` (only with __active state)
- `.zui-lic-plugin__stat`

### Optional classes

- `.zui-lic-plugin__badge` (only shown when inactive — e.g. "Recommended" or "Highly Rated")
- `data-name=` / `data-active=` JS attributes for filtering

### Variants

- Active (in this store) — shows `__active` pill + stat
- Inactive — shows `__get` Get Extension link + stat

### Modifiers

None.

### Nested elements

Per the structure above.

### Parent requirements

Inside `.zui-lic-eco__grid`.

### Child requirements

`__head` + `__body` (with `__foot` inside `__body`).

### Expected visual result

- Composes `.zui-card` chrome with lighter shadow
- `__head`: 16px padding, soft slate-50 tint background, bottom divider, 12px gap
- `__logo`: 44×44, `--zui-radius-xl` rounded, image inside with 8px padding
- `__name`: 12px bold strong, ellipsis on overflow
- `__type`: 9px JetBrains Mono uppercase muted, 2px top margin
- `__badge`: primary-tinted pill, 9px extra-bold uppercase
- `__body`: 16px padding, vertical stack with 18px gap, flex 1 (so card fills grid cell height)
- `__desc`: 11px line-height 1.6 soft text
- `__foot`: 8px gap, top divider, 12px top padding, margin-top auto (sticks to bottom)
- `__get`: small primary-tinted button with arrow icon, hover turns primary fill + white
- `__active`: green-tinted pill with `__dot` (6×6 green circle)
- `__stat`: JetBrains Mono, 10px muted

### Common mistakes

- Including both `__active` and `__get` simultaneously — render only one based on state
- Putting `__dot` outside `__active` — visual misplacement
- Skipping `data-active=` — JS filter by "active" can't work

### Correct example

(See Required Structure above.)

### Incorrect example

```html
<div class="zui-lic-plugin">  <!-- missing .zui-card composition -->
  <h4>Plugin</h4>  <!-- missing __head, __logo, __id, __name, __type, __body, __desc, __foot -->
</div>
```

### Migration notes

Build from scratch using the canonical license template. The 6-plugin
ecosystem array is identical across every Zorem plugin (just exclude the
current plugin and use the plugin's own image-asset filenames).

---

# Component Dependency Map

Components depend on each other in specific parent-child relationships.
Follow this map to ensure correct nesting.

```
Scope (.zui-scope)  [foundation]
│
├── Header (.zui-header)
│    └── Tabs (.zui-tabs)    [nested below __bar]
│
├── Tab Panel (.zui-tab-panel)
│    │
│    └── Layout (.zui-layout)
│         │
│         ├── Sidebar (.zui-sidebar)   [default variant only]
│         │    ├── Sidebar Nav (.zui-sidebar__nav)
│         │    └── Quick Help (.zui-quickhelp)
│         │
│         └── Content (.zui-content)
│              │
│              └── Section (.zui-section)
│                   │
│                   ├── Section Header (.zui-section-header)
│                   │    ├── Section Header Main
│                   │    │    ├── Section Header Icon
│                   │    │    └── Section Header Text
│                   │    │         ├── Title Wrap (with Title + Badge)
│                   │    │         └── Sub
│                   │    │
│                   │    └── Section Header Actions
│                   │         ├── Section Header Action (optional secondary)
│                   │         └── Save Button (.zui-savebtn-wrap > .zui-savebtn)
│                   │
│                   └── Card (.zui-card)
│                        │
│                        ├── Form Row (.zui-row)  [inline or stacked]
│                        │    ├── Row Head (Label + optional Tooltip + optional Desc)
│                        │    └── Row Control (any form control + optional Hint + optional Notice)
│                        │         │
│                        │         ├── Toggle (.zui-toggle)            [for --inline rows]
│                        │         ├── Checkbox (.zui-checkbox)        [for --inline rows]
│                        │         ├── Input (.zui-input)              [stacked]
│                        │         ├── Textarea (.zui-textarea)        [stacked]
│                        │         ├── Select (.zui-select-wrap + .zui-select)  [stacked]
│                        │         ├── Color Input (.zui-color-input)
│                        │         ├── Radio Cards (.zui-radio-cards)
│                        │         ├── Multi-select (.zui-ms)
│                        │         ├── Checkbox Grid (.zui-checkgrid)
│                        │         ├── Segmented Control (.zui-segmented)  [in-card view switcher]
│                        │         ├── Calendar / Date Picker (.zui-calendar | .zui-datepicker)
│                        │         └── Selection Card (.zui-checkcard)
│                        │
│                        └── Card variants:
│                             ├── .zui-card.zui-placeholder (empty state)
│                             ├── .zui-card.zui-lic-card (license card composer)
│                             └── .zui-card.zui-lic-plugin (plugin promo composer)
│
├── Card Grid (.zui-card-grid)   [responsive grid of cards]
│    └── Card (.zui-card) [or .zui-checkcard]
│
├── List Row (.zui-list-row)     [reorderable repeated items]
│    ├── Drag Handle (.zui-drag-handle)
│    ├── Toggle (.zui-toggle)
│    ├── List Row Body (title + meta)
│    ├── Badge (.zui-badge)
│    └── Button (.zui-btn-*)
│
├── Buttons (.zui-btn-primary | -secondary | -ghost)  [anywhere]
│    └── Block modifier: .zui-btn-block  (composes)
│
├── Filter Bar (.zui-filter-bar) + Table (.zui-table) + Pagination (.zui-pagination)
│    [for data-table pages — used above + below the table]
│
├── Modal (.zui-modal)
│    │
│    ├── Modal Backdrop (.zui-modal__backdrop)
│    │
│    └── Modal Dialog (.zui-modal__dialog)
│         │
│         ├── Modal Head (.zui-modal__head) — title + sub + close
│         │
│         ├── Modal Body (.zui-modal__body)
│         │    │
│         │    └── Modal Field (.zui-field)
│         │         ├── Field Label (.zui-field__label)
│         │         └── Field Input (.zui-input.zui-field__input)
│         │              └── OR Upload Field (.zui-upload)
│         │
│         └── Modal Foot (.zui-modal__foot)
│              └── Modal Actions (.zui-modal__actions) — buttons
│
├── Info / Feedback components:
│    ├── Tooltip (.zui-tooltip)   [inside labels, etc.]
│    ├── Tipbox (.zui-tipbox)     [inline info card]
│    ├── Note (.zui-note)         [callout box]
│    └── Badge / Status Dot       [inline status indicators]
│
├── Overlays:
│    └── Menu (.zui-menu)         [kebab/dropdown popover, JS-toggled]
│
├── Icon (.zui-icon)              [universal SVG container]
│
├── Merge Tags (.zui-merge-tags)  [near a textarea]
│
└── License Page Family:
     │
     └── License Container (.zui-lic)
          │
          ├── License Head (.zui-lic-head)
          │    ├── Eyebrow + Title + Sub
          │    └── Telemetry Pill (.zui-lic-telemetry)
          │
          ├── License Grid (.zui-lic-grid)
          │    │
          │    ├── License Main Column (.zui-lic-col-main)
          │    │    │
          │    │    ├── License Card (.zui-card.zui-lic-card) — Gateway / SSO
          │    │    │    └── Active State (.zui-lic-active) OR Inactive State (.zui-lic-inactive)
          │    │    │
          │    │    └── License Card (.zui-card.zui-lic-card) — Telemetry preferences
          │    │
          │    └── License Side Column (.zui-lic-col-side)
          │         └── License Help (.zui-lic-help) — docs links sidebar
          │
          └── License Ecosystem (.zui-lic-eco)
               ├── Heading + Filter Pills
               ├── Search
               └── Grid of License Plugin Cards (.zui-lic-eco__grid)
                    └── License Plugin Card (.zui-card.zui-lic-plugin)

Snackbar Stack (.zui-snackbar-stack) [appended inside .zui-scope by ZUI.snackbar()]
└── Snackbar (.zui-snackbar.zui-snackbar--{success|error|warning|info})  [glass pill]
     ├── Icon circle (.zui-snackbar__icon)        — variant SVG
     ├── Text (.zui-snackbar__text)               — message body
     ├── Close button (.zui-snackbar__close)      — × dismiss
     └── Progress bar (.zui-snackbar__progress)   — bottom countdown
```

---

# Validation Checklist

For every page built with ZUI, verify all of these:

## Structure

- [ ] Page is wrapped in a single `<div class="zui-scope">` (with optional plugin co-class)
- [ ] `.zui-scope` is NOT applied to `<body>`
- [ ] `.zui-scope` is NOT applied near a React app root
- [ ] Header uses `<header class="zui-header">` element (not `<div>`)
- [ ] Header contains `__bar` + `__lead` + `__brand` cluster + `__header-actions` cluster
- [ ] Brand cluster contains emblem (with `__emblem-dot`) + name + badge + tagline
- [ ] Tabs use `<nav class="zui-tabs">` (not flat anchors)
- [ ] Active tab has both `.is-active` class AND `aria-current="page"`
- [ ] Layout uses `.zui-layout--full` + `.zui-content--full` when no sidebar
- [ ] Content uses `<main class="zui-content">` (not `<div>`)
- [ ] Sidebar uses `<aside class="zui-sidebar">`
- [ ] Section uses `<section class="zui-section">`
- [ ] Section Header has all 5 levels: `__main` → `__icon` + `__text` → `__titlewrap` (with `__title` and optional `__badge`) → `__sub`
- [ ] Section Header `__title` is an `<h2>`
- [ ] Save button uses `.zui-savebtn-wrap` outer + `.zui-savebtn` button with `__label` + `__spinner` spans inside
- [ ] Cards use `<div class="zui-card">`
- [ ] Form rows use `.zui-row` with `__head` + `__control` slots
- [ ] Inline rows use `.zui-row--inline` modifier
- [ ] Form rows are direct children of `.zui-card` (for padding to apply)
- [ ] Toggle uses `<label class="zui-toggle">` with hidden 0 + checkbox + track + thumb
- [ ] Selects use `.zui-select-wrap` outer + `.zui-select` + `.zui-select-chevron`
- [ ] Radio cards use `<label class="zui-radio-card">` per card with `__input` + `__body` (containing `__label` + `__desc`)
- [ ] Modals use full `.zui-modal` structure with `__backdrop` + `__dialog` (containing `__head` + `__body` + `__foot`)
- [ ] Modal close triggers have `data-zui-modal-close`
- [ ] Modal visibility toggles via the `hidden` HTML attribute
- [ ] Buttons carry exactly one of `.zui-btn-primary` / `.zui-btn-secondary` / `.zui-btn-ghost`
- [ ] Tooltips use `<span class="zui-tooltip" tabindex="0" role="img" aria-label="…">` with SVG icon + bubble + arrow
- [ ] License page uses the full `.zui-lic` template with head + grid + ecosystem

## Classes

- [ ] Every required sub-element class is present on its element
- [ ] No invented class names
- [ ] No alternative or simplified markup variants

## Nesting

- [ ] Form rows live inside cards
- [ ] Save button lives inside `.zui-section-header__actions`
- [ ] Quick Help lives inside `.zui-sidebar`
- [ ] Tabs nest inside `<header class="zui-header">` (when header exists)
- [ ] Section Header lives inside `.zui-section`

## Functionality preservation

- [ ] All form `name=` attributes preserved
- [ ] All `id=` attributes preserved
- [ ] All `value=` attributes preserved
- [ ] All hidden inputs preserved
- [ ] All nonce fields preserved (`wp_nonce_field()` calls)
- [ ] All AJAX action names preserved
- [ ] All option keys read/written preserved
- [ ] All URLs (`href`, redirect, AJAX endpoints) preserved
- [ ] All JS hook classes carried as co-classes alongside canonical classes
- [ ] All `data-*` attributes preserved
- [ ] All form submission paths verified (each form posts the expected keys)

## Styling

- [ ] Library CSS (`zui.css`) loads BEFORE plugin admin CSS in `<head>`
- [ ] Plugin admin CSS declares `array('zui')` dependency on every `wp_enqueue_style()` call
- [ ] Inter + JetBrains Mono fonts load from Google Fonts (visible in Network tab)
- [ ] No 404s on library asset paths
- [ ] No console errors related to ZUI selectors

---

# Migration Checklist

Reusable checklist for migrating any plugin to ZUI.

## Pre-migration

- [ ] Identify the plugin's admin pages (settings, dashboards, modals)
- [ ] Identify all forms, AJAX actions, nonces, option keys
- [ ] Identify all JS event bindings that may break under DOM restructure
- [ ] Identify all vendor-controlled markup (Select2, DataTables, jQueryUI, WP media frame, etc.) — these get co-classed only, not rebuilt
- [ ] Identify React/Vue applications — they stay OUTSIDE `.zui-scope`
- [ ] Collect the new design (Figma, Google Studio, screenshots) — use as visual reference

## Foundation phase

- [ ] Add the plugin folder name to `zorem-ui/sync.sh` (or run the sync command manually)
- [ ] Run `sync.sh` from the library root — verify `assets/zui/VERSION` is current
- [ ] In the plugin's asset enqueue function, register the `zui` style + script handles from `assets/zui/css/zui.css` and `assets/zui/js/zui.js`
- [ ] Declare `array('zui')` dependency on every plugin admin CSS enqueue
- [ ] Add `<div class="my-plugin-app zui-scope" id="my-plugin-app">` wrapper around the entire admin page render
- [ ] Confirm wrapper closes with `</div>` after every PHP branch
- [ ] React app pages — confirm `.zui-scope` is NOT applied near the React root

## Header + Tabs + Layout phase

- [ ] Replace top header `<div>` with `<header class="zui-header">` + full brand cluster + actions cluster
- [ ] Preserve any JS-updated breadcrumb element by keeping its class on a tagline span inside the brand cluster
- [ ] Wrap tabs in `<nav class="zui-tabs">` with `<a class="zui-tabs__item">` per tab + `.is-active` + `aria-current` on the active one
- [ ] Add `.zui-tabs__badge` count badges where the design specifies
- [ ] Wrap the layout body in `<div class="zui-layout zui-layout--full">` (full-width) or `<div class="zui-layout">` (with sidebar)
- [ ] Convert content `<div>` to `<main class="zui-content">`

## Per-tab phase

- [ ] For tabs with a sub-section sidebar: build the `<aside class="zui-sidebar">` with `__nav` items + Quick Help
- [ ] Implement section sub-routing (URL-driven `?section=…` or JS-driven `.is-active`)
- [ ] Each section content goes in `<section class="zui-section">` with `data-section="…"` and `hidden` attribute for inactive
- [ ] Build a section header per section with `__main` + `__actions` (and save button)

## Per-card / per-row phase

- [ ] Wrap each content panel in `<div class="zui-card">`
- [ ] Replace `<table class="form-table">` form-rows with `.zui-row` divs inside the card
- [ ] Use `.zui-row--inline` for toggle / checkbox rows
- [ ] Use stacked `.zui-row` for text/select/textarea/radio/multi-select
- [ ] Add `__head` with `__label` + optional `__desc`
- [ ] Add `__control` with the form control + optional `__hint` + `__notice`

## Per-control phase

- [ ] Toggles → canonical `.zui-toggle` structure
- [ ] Plain checkboxes → canonical `.zui-checkbox` structure
- [ ] Text/number/email inputs → `.zui-input` class
- [ ] Textareas → `.zui-textarea` class
- [ ] Selects → `.zui-select-wrap` + `.zui-select` + `.zui-select-chevron`
- [ ] Radio cards → `.zui-radio-cards` + `.zui-radio-card` per option
- [ ] Color inputs → `.zui-color-input` + `.zui-color-dot` + hex input
- [ ] Multi-selects → either keep vendor Select2 OR canonical `.zui-ms` widget
- [ ] Checkbox grids → `.zui-checkgrid` + `.zui-checkitem`
- [ ] Selection cards → `.zui-checkcard`

## Buttons phase

- [ ] Every save / submit / activate button gets `.zui-btn-primary`
- [ ] Every test-connection / customize button gets `.zui-btn-secondary`
- [ ] Every cancel / reset button gets `.zui-btn-ghost`
- [ ] Wrap label text in `<span>` for canonical structure
- [ ] Preserve all JS-hook classes alongside the variant class

## Modals phase

- [ ] Each modal uses full `.zui-modal` structure: backdrop + dialog + head + body + foot
- [ ] Hidden by default via `hidden` attribute
- [ ] Add `data-zui-modal-close` on backdrop, close button, Cancel button
- [ ] Modal fields use `.zui-field` (not `.zui-row`)
- [ ] Upload fields use `.zui-upload`
- [ ] Selection cards inside modals use `.zui-checkcard`

## License phase

- [ ] Copy the canonical license template structure
- [ ] Change the 11 plugin config variables at the top of the template
- [ ] Replace textdomain strings throughout
- [ ] Build the 6-plugin ecosystem array (exclude the current plugin, use the plugin's own image filenames)
- [ ] Preserve the existing license form id, action name, nonce field, and option keys
- [ ] Verify activate / deactivate links and forms post to the same URLs as before

## Verification phase

- [ ] Load every modified page in the browser
- [ ] Compare visually against the design reference
- [ ] Open DevTools → Network — confirm `zui.css` 200 OK + Inter / JetBrains Mono fonts load
- [ ] Open DevTools → Console — confirm no JS errors
- [ ] Test every interaction: save form, switch section, open modal, activate license, deactivate, dismiss, search filter, click ecosystem plugin link
- [ ] Test on mobile / narrow viewport: drawer opens, tabs scroll, cards stack
- [ ] Test on RTL site (if applicable): logical CSS properties handle direction

## Rollout phase

- [ ] Tag a clean baseline commit before deletion of legacy CSS
- [ ] Document the migration date + commit hash in the plugin's CLAUDE.md
- [ ] Plan the CSS deletion round AFTER markup adoption is verified
- [ ] Verify on multiple WordPress versions and PHP versions before release

---

## Pro Upsell Panel — `zui-upsell`

**Phase:** 2 (deferred — not imported by `zui.css`; load `css/components/upsell.css` directly)

**Purpose:** Full-width promotional panel displayed on the Settings page of a free plugin to upsell the PRO version. Contains a branded header (icon emblem + eyebrow badge + headline + description), a responsive 3-column feature checklist with optional "NEW" badges, and a footer with an optional coupon offer and a primary CTA anchor.

No JavaScript is required. This component is purely static HTML.

**When to use:** Place once per Settings page, directly after the main settings `<form>` and before `</main>`. Never nest inside a `.zui-card` — it is a full-width sibling of cards, not a card itself.

---

### Required Structure

```html
<section class="zui-upsell" aria-labelledby="upsell-title">

  <!-- Header: emblem + text cluster -->
  <header class="zui-upsell__head">
    <span class="zui-upsell__emblem" aria-hidden="true">
      <!-- any .zui-icon SVG — shield, star, zap, etc. -->
      <svg class="zui-icon" ...></svg>
    </span>
    <div class="zui-upsell__head-text">
      <span class="zui-upsell__eyebrow">PLUGIN PRO</span>
      <h3 class="zui-upsell__title" id="upsell-title">Headline here</h3>
      <p class="zui-upsell__sub">Short description (1–2 sentences).</p>
    </div>
  </header>

  <!-- Feature grid (default: 3 columns) -->
  <ul class="zui-upsell__features">

    <!-- Plain feature -->
    <li class="zui-upsell__feature">
      <span class="zui-upsell__check" aria-hidden="true">
        <svg class="zui-icon" ...><!-- check icon --></svg>
      </span>
      <span class="zui-upsell__label">Feature name</span>
    </li>

    <!-- Feature with NEW badge -->
    <li class="zui-upsell__feature">
      <span class="zui-upsell__check" aria-hidden="true">
        <svg class="zui-icon" ...><!-- check icon --></svg>
      </span>
      <span class="zui-upsell__label">
        Feature name <span class="zui-upsell__new">NEW</span>
      </span>
    </li>

    <!-- … repeat for each feature … -->
  </ul>

  <!-- Footer: offer + CTA -->
  <footer class="zui-upsell__foot">
    <div class="zui-upsell__offer">
      <span class="zui-upsell__offer-label">LAUNCH OFFER</span>
      <span class="zui-upsell__offer-body">
        Get 20% off — use code
        <span class="zui-upsell__code">PLUGINPRO20</span>
        at checkout.
      </span>
      <span class="zui-upsell__offer-note">★ for new customers only</span>
    </div>
    <a class="zui-upsell__cta" href="https://example.com/upgrade"
       target="_blank" rel="noopener noreferrer">
      Upgrade to PRO <span aria-hidden="true">→</span>
    </a>
  </footer>

</section>
```

---

### Required Classes

| Class | Element | Required |
|---|---|---|
| `.zui-upsell` | Root `<section>` | Yes |
| `.zui-upsell__head` | `<header>` inside root | Yes |
| `.zui-upsell__emblem` | Icon container `<span>` | Yes |
| `.zui-upsell__head-text` | Text wrapper `<div>` | Yes |
| `.zui-upsell__eyebrow` | Eyebrow badge `<span>` | Yes |
| `.zui-upsell__title` | `<h3>` headline | Yes |
| `.zui-upsell__sub` | Description `<p>` | Yes |
| `.zui-upsell__features` | Feature list `<ul>` | Yes |
| `.zui-upsell__feature` | Each feature `<li>` | Yes |
| `.zui-upsell__check` | Check circle `<span>` | Yes |
| `.zui-upsell__label` | Feature text `<span>` | Yes |
| `.zui-upsell__foot` | `<footer>` inside root | Yes |
| `.zui-upsell__cta` | CTA anchor `<a>` | Yes |

### Optional Classes

| Class | Element | Effect |
|---|---|---|
| `.zui-upsell__offer` | `<div>` inside `__foot` | Coupon offer block (omit if no coupon) |
| `.zui-upsell__offer-label` | `<span>` | Small all-caps label ("LAUNCH OFFER") |
| `.zui-upsell__offer-body` | `<span>` | Sentence containing the code |
| `.zui-upsell__code` | `<span>` inside `__offer-body` | Monospace coupon chip |
| `.zui-upsell__offer-note` | `<span>` | Disclaimer text |
| `.zui-upsell__new` | `<span>` inside `__label` | Purple "NEW" badge |

### Modifier

| Modifier | Applied to | Effect |
|---|---|---|
| `.zui-upsell--cols-2` | Root `.zui-upsell` | Feature grid uses 2 columns instead of 3 |

Responsive breakpoints are built in regardless of modifier:
- ≤ 960 px → 2 columns
- ≤ 640 px → 1 column, full-width CTA

---

### BEM Element Hierarchy

```
.zui-upsell
├── .zui-upsell__head
│     ├── .zui-upsell__emblem           ← icon container
│     └── .zui-upsell__head-text
│           ├── .zui-upsell__eyebrow    ← "PLUGIN PRO" pill
│           ├── .zui-upsell__title      ← <h3>
│           └── .zui-upsell__sub        ← <p>
├── .zui-upsell__features               ← <ul> grid
│     └── .zui-upsell__feature (×N)     ← <li>
│           ├── .zui-upsell__check      ← green circle + check icon
│           └── .zui-upsell__label      ← text (+ optional .zui-upsell__new)
└── .zui-upsell__foot
      ├── .zui-upsell__offer            ← coupon block (optional)
      │     ├── .zui-upsell__offer-label
      │     ├── .zui-upsell__offer-body
      │     │     └── .zui-upsell__code ← coupon chip
      │     └── .zui-upsell__offer-note
      └── .zui-upsell__cta              ← <a> upgrade button
```

---

### Nested Element Details

**`.zui-upsell__emblem`**
— 52 × 52 px rounded square with a primary blue gradient background and white foreground color. Place a `.zui-icon` SVG (26 × 26 px) directly inside. The shield icon is the canonical choice for security/verification plugins; use whatever fits your plugin's brand.

**`.zui-upsell__eyebrow`**
— Small all-caps pill rendered in primary-100 tint with primary-700 text. Set it to your plugin's PRO product name (e.g., "CEV PRO", "AST PRO").

**`.zui-upsell__check`**
— 18 × 18 px green circle (success token colors). Place a `.zui-icon` check SVG with `stroke-width="3"` inside for the canonical look.

**`.zui-upsell__new`**
— Inline purple badge. Place directly inside `.zui-upsell__label` after the feature text. No space element needed — the `margin-inline-start: 4px` provides the gap.

**`.zui-upsell__code`**
— Yellow gradient monospace chip with a dashed amber border. Uses `--zui-font-mono`. Always use `text-transform: uppercase` on the content itself (the CSS does not enforce case).

**`.zui-upsell__cta`**
— `<a>` anchor styled as a blue gradient pill button. Must have `target="_blank" rel="noopener noreferrer"` since it links to an external sales page. The `→` arrow should be wrapped in `<span aria-hidden="true">` to keep it decorative for screen readers.

---

### Parent Requirements

- Must be inside `.zui-scope` (required by all ZUI components).
- Must **not** be nested inside `.zui-card`. Place as a direct child of the page's `<main>` or content wrapper.

### Child Requirements

- `.zui-upsell__features` must be a `<ul>` element; each feature must be a `<li>`.
- `.zui-upsell__emblem` requires exactly one `.zui-icon` SVG as a direct child.
- `.zui-upsell__check` requires exactly one `.zui-icon` SVG as a direct child.

---

### CSS Load Order

`upsell.css` is **Phase 2** — it is not imported by `zui.css`. Load it explicitly in the plugin's settings page enqueue:

```php
wp_enqueue_style(
    'my-plugin-zui-upsell',
    plugin_dir_url( __FILE__ ) . 'assets/zui/css/components/upsell.css',
    [ 'my-plugin-zui' ],
    '1.8.0'
);
```

Or if you load ZUI CSS files directly via `<link>` in a PHP template:

```php
<link rel="stylesheet" href="<?php echo esc_url( $zui_url . 'css/components/upsell.css' ); ?>">
```

---

### Expected Visual Result

- A card-like panel with a 4 px blue top accent stripe and a subtle blue-tinted background gradient.
- Header row: 52 px blue gradient icon square on the left; to the right, a small all-caps pill, a large bold headline, and a muted description.
- Feature grid: 3 responsive columns, each row being a green check circle followed by feature text. Features with `.zui-upsell__new` show a small purple "NEW" badge inline.
- Footer: light blue tinted strip; on the left, a vertically stacked "LAUNCH OFFER" label + coupon sentence with a yellow dashed chip + disclaimer; on the right, a blue gradient pill button.

---

### Common Mistakes

| Mistake | Result | Fix |
|---|---|---|
| Placing `.zui-upsell` inside `.zui-card` | Extra padding + border inside a card | Place `.zui-upsell` as a sibling of `.zui-card`, not inside one |
| Using `<div>` for `__features` instead of `<ul>` | Grid renders but accessibility broken | Use `<ul>` / `<li>` |
| Missing `id` on `__title` + missing `aria-labelledby` on root | Section has no accessible name | Add matching `id` / `aria-labelledby` pair |
| Omitting `aria-hidden="true"` on `__emblem` and `__check` | Screen readers announce decorative icons | Add `aria-hidden="true"` to both |
| Loading `upsell.css` after plugin-specific CSS that targets `.cev-pro-promo` | No conflict — they are separate class families | n/a |
| Using `<button>` for `__cta` | Opens same tab if not wired; semantically wrong for external nav | Always use `<a href="…" target="_blank">` |

---

### Correct Example (CEV implementation)

```html
<section class="zui-upsell" aria-labelledby="cev-upsell-title">

  <header class="zui-upsell__head">
    <span class="zui-upsell__emblem" aria-hidden="true">
      <?php echo $render_icon( 'shield' ); ?>
    </span>
    <div class="zui-upsell__head-text">
      <span class="zui-upsell__eyebrow"><?php esc_html_e( 'CEV PRO', 'customer-email-verification-for-woocommerce' ); ?></span>
      <h3 class="zui-upsell__title" id="cev-upsell-title">
        <?php esc_html_e( 'Unlock Advanced Email Verification with CEV PRO', 'customer-email-verification-for-woocommerce' ); ?>
      </h3>
      <p class="zui-upsell__sub">
        <?php esc_html_e( 'Secure your WooCommerce store with advanced email verification, login authentication, analytics, and anti-spam controls.', 'customer-email-verification-for-woocommerce' ); ?>
      </p>
    </div>
  </header>

  <ul class="zui-upsell__features">
    <?php foreach ( $cev_pro_features as $feature ) : ?>
      <li class="zui-upsell__feature">
        <span class="zui-upsell__check" aria-hidden="true"><?php echo $render_icon( 'check' ); ?></span>
        <span class="zui-upsell__label">
          <?php echo esc_html( $feature['label'] ); ?>
          <?php if ( ! empty( $feature['is_new'] ) ) : ?>
            <span class="zui-upsell__new"><?php esc_html_e( 'NEW', 'customer-email-verification-for-woocommerce' ); ?></span>
          <?php endif; ?>
        </span>
      </li>
    <?php endforeach; ?>
  </ul>

  <footer class="zui-upsell__foot">
    <div class="zui-upsell__offer">
      <span class="zui-upsell__offer-label"><?php esc_html_e( 'Launch offer', 'customer-email-verification-for-woocommerce' ); ?></span>
      <span class="zui-upsell__offer-body">
        <?php esc_html_e( 'Get 20% off — use code', 'customer-email-verification-for-woocommerce' ); ?>
        <span class="zui-upsell__code">CEVPRO20</span>
        <?php esc_html_e( 'at checkout.', 'customer-email-verification-for-woocommerce' ); ?>
      </span>
      <span class="zui-upsell__offer-note"><?php esc_html_e( '★ for new customers only', 'customer-email-verification-for-woocommerce' ); ?></span>
    </div>
    <a class="zui-upsell__cta" href="<?php echo esc_url( $cev_upgrade_url ); ?>"
       target="_blank" rel="noreferrer noopener">
      <?php esc_html_e( 'Upgrade to CEV PRO', 'customer-email-verification-for-woocommerce' ); ?>
      <span aria-hidden="true">→</span>
    </a>
  </footer>

</section>
```

---

### Incorrect Example

```html
<!-- WRONG: nested inside a .zui-card — adds double border + padding -->
<div class="zui-card">
  <section class="zui-upsell">…</section>
</div>

<!-- WRONG: using <div> for the feature list — breaks list semantics -->
<div class="zui-upsell__features">
  <div class="zui-upsell__feature">…</div>
</div>

<!-- WRONG: CTA as a <button> — wrong semantic for external navigation -->
<button class="zui-upsell__cta">Upgrade to PRO →</button>
```

---

### Migration Notes (CEV plugin)

CEV currently uses its own `cev-pro-promo` class family scoped to `.zui-scope.cev-admin`. To migrate:

1. Replace every `cev-pro-promo` class with the corresponding `zui-upsell` class per the table below.
2. Load `upsell.css` from the ZUI library instead of keeping the styles in `cev-admin.css`.
3. Delete the `cev-pro-promo` block from `cev-admin.css` once the new markup is verified.

| Old class | New class |
|---|---|
| `cev-pro-promo` | `zui-upsell` |
| `cev-pro-promo__head` | `zui-upsell__head` |
| `cev-pro-promo__emblem` | `zui-upsell__emblem` |
| `cev-pro-promo__head-text` | `zui-upsell__head-text` |
| `cev-pro-promo__eyebrow` | `zui-upsell__eyebrow` |
| `cev-pro-promo__title` | `zui-upsell__title` |
| `cev-pro-promo__sub` | `zui-upsell__sub` |
| `cev-pro-promo__features` | `zui-upsell__features` |
| `cev-pro-promo__feature` | `zui-upsell__feature` |
| `cev-pro-promo__check` | `zui-upsell__check` |
| `cev-pro-promo__feature-label` | `zui-upsell__label` |
| `cev-pro-promo__new` | `zui-upsell__new` |
| `cev-pro-promo__foot` | `zui-upsell__foot` |
| `cev-pro-promo__offer` | `zui-upsell__offer` |
| `cev-pro-promo__offer-label` | `zui-upsell__offer-label` |
| `cev-pro-promo__offer-body` | `zui-upsell__offer-body` |
| `cev-pro-promo__code` | `zui-upsell__code` |
| `cev-pro-promo__offer-note` | `zui-upsell__offer-note` |
| `cev-pro-promo__cta` | `zui-upsell__cta` |

No markup structure changes are needed — the DOM hierarchy is identical.

---

**End of guide.**

If you follow every documented structure exactly, the library CSS will
produce the intended UI automatically. Any visual problem can be traced back
to one of: missing required class, missing nested element, wrong element type,
or CSS load order. The Validation Checklist + Component Dependency Map are
designed to surface these problems quickly.

---

# 🚨 STRICT RULE — DO NOT SKIP

**You have now finished reading this guide. STOP. Do NOT start writing
code yet. You are not done with the required reading.**

Before you write, edit, improve, refactor, fix, or review **any** code
that uses ZUI — in the library itself or in any plugin that ships it
under `assets/zui/` — you MUST now also open and read the second
reference file:

## ➡️ Read `ZUI-COMPONENTS-PREVIEW.html`

It lives next to this file (library root, or `assets/zui/` inside a
consumer plugin). Open it in a browser, or read the source if you are
an AI assistant. Find the live preview block for **every component you
intend to touch** and copy the canonical HTML / PHP snippet shown
there. That snippet is the verified, ready-to-use markup.

## Why this is mandatory

This guide tells you *what the contract is* (required structure,
classes, modifiers, common mistakes). The preview HTML shows you *what
the contract looks like rendered*, and gives you the exact copy-paste
snippet to start from. Reading one without the other is not enough:

- Reading only this guide → you may match class names but miss the
  rendered look and copy a structure that is slightly off.
- Reading only the preview → you may copy markup but miss the
  documented modifiers, parent / child requirements, and common
  mistakes called out in this guide.

**Both files together = the contract. One without the other = guessing.**

## The hard rule

Until **both** `ALL-ZUI-IMPLEMENTATION-GUIDE.md` (this file) **and**
`ZUI-COMPONENTS-PREVIEW.html` have been actually opened and read for
every component your task touches, you MUST NOT:

- Write or edit code
- Propose markup
- Suggest class names
- Invent component shapes
- Start broad codebase searches in the consumer plugin
- Read plugin source files unrelated to the references

Coding before both files are read is the single biggest cause of
broken UI, drifted markup, and invented class names that don't exist
in the library.

## For AI assistants specifically

If the user has handed you this library (or a plugin that ships it)
and asked you to add / edit / improve / refactor / fix / review
anything, your obligation is sequential and non-negotiable:

1. Read this guide (`ALL-ZUI-IMPLEMENTATION-GUIDE.md`) — done if you
   are reading this sentence.
2. Now read `ZUI-COMPONENTS-PREVIEW.html`.
3. Briefly tell the user you have consulted both references.
4. Only then begin the implementation.

If you skip step 2 and go straight to code, you are violating the
explicit instruction the user has given for this project. Do not skip
it. Do not "summarise from memory". Open the preview file. Read it.

---

**This is the final word. Read `ZUI-COMPONENTS-PREVIEW.html` now, then
return to the task.**
