# KZ Travel — Project Requirements

> **Status:** Draft for review  
> **Purpose:** Define scope and structure before implementation.

---

## 1. Overview

Build a **static travel agency website** for a small catalog of trips (initially **3–6 vacations**). The site is content-driven: all trip data lives in YAML files, and the app is built into static HTML/CSS/JS for free hosting on **GitHub Pages**.

### Goals

- Filter trips on the landing page by **category** and **tags**
- Dedicated detail page per trip
- Easy to add or edit trips and site-wide settings by changing YAML files (no code changes required for content updates)
- Zero backend; no database or API

### Non-goals (for v1)

- User accounts, login, or booking/checkout flow
- Admin UI or CMS
- Full-text search or multi-language support
- Payment integration

---

## 2. Tech Stack

| Layer | Choice |
|-------|--------|
| Framework | React |
| Build tool | Vite |
| Language | TypeScript |
| Output | Static site (pre-rendered at build time) |
| Hosting | GitHub Pages (free) |
| Content | YAML files in a `data/` folder |

### Static generation

- All pages are generated at **build time** from YAML content.
- No runtime fetching of trip data from external APIs.
- React Router (or equivalent) may be used for client-side navigation between statically generated routes.

### Hosting & custom domain

- Site is hosted on **GitHub Pages** and served at a **custom domain** (root URL, e.g. `https://example.com`).
- Vite `base` is `/` (no repository subpath prefix).
- Custom domain is configured in GitHub repo settings (DNS `CNAME` or `A` records pointing to GitHub Pages).
- Deploy via GitHub Actions (build on push, publish `dist/` to Pages) or equivalent.

---

## 3. Site Structure

### 3.1 Landing page (`/`)

Displays all trips in a **magazine-style grid** of cards/boxes.

Each trip card shows:

| Field | Source |
|-------|--------|
| **Title** | `name` from YAML |
| **Image** | First thumbnail, or auto-slideshow if `thumbnails` has more than one image |
| **Price** | Price of the **next upcoming available date** (see §4.5) |
| **Next date** | Earliest `date` where `available: true` and date is today or in the future |
| **Category / tags** | Optional badges on the card (primary category + tag chips) |

Clicking a card navigates to that trip’s detail page.

**Filtering:** Above the trip grid, show controls to narrow the list:

- **Category filter** — select one category, or “All” (see §4.3 for category vs tag semantics)
- **Tag filter** — select one or more tags; trip matches if it has **any** selected tag (OR logic)

Filtering is **client-side** on the landing page (no server, no extra routes required for v1). Selected filters may be reflected in the URL query string (e.g. `?category=beach&tags=family,summer`) so filtered views are shareable — TBD in open questions.

**Layout:** Classic magazine view — visual-first cards with strong imagery, title overlay or below image, price and date clearly visible. Responsive grid (e.g. 1 column mobile, 2–3 columns desktop). Empty state when no trips match the active filters.

**Global chrome:** Site title (browser tab / header), favicon, and optional full-page background image come from global settings (see §4.1).

### 3.2 Trip detail page (`/trips/:slug` or `/trips/:id`)

One page per vacation, showing full content from its YAML file:

| Section | Content |
|---------|---------|
| Hero / header | Title, description, categories, tags, thumbnail slideshow (same behavior as landing card) |
| Dates & pricing | Table or list of all `{ date, price, available }` entries |
| Gallery | Full-width or grid of `gallery` images |
| Itinerary | Day-by-day breakdown from `itinerary` |
| Included | Items from `included` (name + price) |
| Not included | Items from `excluded` (name + price) |

**Slug:** Derived from filename (e.g. `data/trips/albania-coast.yaml` → `/trips/albania-coast`) unless an explicit `slug` field is added later.

---

## 4. Data Model

### 4.1 File organization

```
data/
  site.yaml                  # global site settings (single file)
  trips/
    albania-coast.yaml
    greek-islands.yaml
    ...
```

- **`data/site.yaml`** — site-wide settings (title, favicon, background, etc.). Loaded once at build time and applied across all pages.
- **`data/trips/*.yaml`** — one file per vacation.
- Trip filename becomes the trip identifier/slug (kebab-case recommended).
- Images referenced by path (e.g. relative to `public/` or bundled assets folder).

### 4.2 Global settings schema (`data/site.yaml`)

```yaml
title: KZ Travel                 # Site name; used in <title>, header, and meta tags
favicon: /images/favicon.ico     # Browser tab icon
background: /images/bg.jpg       # Optional full-page or landing background image
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `title` | string | yes | Appended to page titles (e.g. `Albania Coast — KZ Travel`) |
| `favicon` | string | yes | Path to `.ico`, `.png`, or `.svg` in `public/` |
| `background` | string | no | Site-wide background; omit for solid/default background |

Additional global fields (contact email, logo, tagline, etc.) can be added to this file later without changing the trip schema.

### 4.3 Trip YAML schema

```yaml
# Required
name: string                    # Display title
description: string             # Short or long intro text (markdown optional — TBD)

category: beach                 # Primary category (single value; used for main filter grouping)
tags:                           # Additional labels for finer-grained filtering
  - family-friendly
  - all-inclusive
  - summer

thumbnails:                     # Used on landing card and detail hero
  - /images/trips/albania/hero-1.jpg
  - /images/trips/albania/hero-2.jpg

dates:                          # Scheduled departures
  - date: 2026-07-15            # ISO date (YYYY-MM-DD)
    price: 450
    available: true
  - date: 2026-08-12
    price: 490
    available: false

gallery:                        # Detail page photo gallery
  - /images/trips/albania/gallery-1.jpg
  - /images/trips/albania/gallery-2.jpg

itinerary:                      # Day-by-day plan
  - day: 1
    title: Arrival in Tirana
    description: Transfer to hotel, welcome dinner.
  - day: 2
    title: Coastal drive
    description: ...

included:                       # Covered by the trip price
  - name: Accommodation (7 nights)
    price: 0                    # 0 or omitted = included at no extra charge
  - name: Breakfast
    price: 0

excluded:                       # Not covered; optional extras or out-of-pocket
  - name: Travel insurance
    price: 35
  - name: Airport transfer
    price: 25
```

#### Category vs tags

| Field | Cardinality | Purpose | Examples |
|-------|-------------|---------|----------|
| `category` | **one string** | Broad trip type; drives the primary filter control | `beach`, `city-break`, `adventure`, `cruise` |
| `tags` | **string array** | Cross-cutting attributes; secondary multi-select filter | `family-friendly`, `luxury`, `guided`, `summer` |

- Values are **kebab-case or lowercase strings** in YAML; displayed with human-friendly labels in the UI (e.g. `city-break` → “City break”).
- The set of categories and tags shown in filter controls is **derived at build time** from all trip files (union of unique values). No separate taxonomy file required for v1.
- Optional later: allowlist in `site.yaml` to enforce consistent spelling across trips.

### 4.4 Trip field reference

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `name` | string | yes | Trip title |
| `description` | string | yes | Shown on detail page; may support markdown in v2 |
| `category` | string | yes | Single primary category for filtering |
| `tags` | string[] | no | Zero or more tags; empty array OK |
| `thumbnails` | string[] | yes | At least one image; slideshow if length > 1 |
| `dates` | object[] | yes | At least one entry |
| `dates[].date` | string (ISO date) | yes | |
| `dates[].price` | number | yes | Currency TBD (assume EUR unless specified) |
| `dates[].available` | boolean | yes | `false` = sold out / not bookable |
| `gallery` | string[] | no | Empty array OK |
| `itinerary` | object[] | no | Each entry: `day`, `title`, `description` |
| `included` | object[] | no | Each entry: `name`, `price` |
| `excluded` | object[] | no | Each entry: `name`, `price` |

### 4.5 Derived values (computed at build time)

- **Next date:** smallest `dates[].date` where `available === true` and `date >= today`.
- **Display price on landing card:** `price` from that next available date.
- **Fallback:** If no upcoming available dates, show “Contact us” or last known price (behavior TBD — see open questions).
- **Filter index:** unique sorted lists of all `category` values and all `tags` across trips, used to populate landing-page filter controls.

---

## 5. UI / UX Requirements

### Landing page — filters

- **Category control:** Buttons, tabs, or dropdown — one active category at a time plus “All”.
- **Tag control:** Toggle chips; multiple tags can be active at once.
- **Combined logic:** A trip is shown when it matches the selected category (or “All”) **and** has at least one active tag (if any tags are selected). If no tags are selected, tag filter is ignored.
- **Counts (optional):** Show number of trips per category/tag when space allows.
- **Clear filters:** One action to reset to “All” / no tags.

### Landing page — trip card

- **Thumbnail slideshow:** If `thumbnails.length > 1`, auto-advance through images (interval TBD, e.g. 4–5 seconds). Single image: static, no controls needed.
- **Price:** Formatted with currency symbol (e.g. `€450`).
- **Next date:** Formatted for locale (e.g. `15 Jul 2026`).
- **Unavailable trips:** Still listed if YAML exists; card may show “Fully booked” when no available future dates (TBD).
- **Category / tags:** Show primary category and optionally 1–2 tag badges on the card; full tag list on detail page.

### Trip detail page

- Same thumbnail slideshow behavior in hero area.
- **Categories & tags:** Display `category` and all `tags`; tag/category clicks may apply that filter and navigate back to landing (optional enhancement).
- **Dates section:** All dates listed; unavailable dates visually distinct (strikethrough, badge, or greyed out).
- **Gallery:** Simple responsive grid or lightbox (lightbox optional for v1).
- **Itinerary:** Ordered by `day`; show day number, title, and description.
- **Included / excluded:** Two clear lists; show `name` and `price` (use “Included” label when `price` is 0).

### Global settings (from `site.yaml`)

- **Site title:** Shown in the document `<title>` on every page; visible in header or branding area on landing and detail pages.
- **Favicon:** Injected via `<link rel="icon">` in the HTML shell.
- **Background image:** Applied site-wide (e.g. fixed cover on `body` or landing hero backdrop). Should not reduce text readability; overlay or fallback color if image fails to load.

### General

- Mobile-first responsive layout
- Accessible images (`alt` text — from filename or optional `alt` field in YAML later)
- Fast load: optimized static assets, lazy-load gallery if needed

---

## 6. Build & Deploy

### Local development

```bash
npm install
npm run dev      # dev server with hot reload
npm run build    # static output to dist/
npm run preview  # preview production build locally
```

### Production deploy

- Target: **GitHub Pages** with **custom domain**
- Build produces static files in `dist/`
- Vite `base: '/'` (site served at domain root, not `/repo-name/`)
- CI workflow: on push to `main`, run `npm run build`, deploy `dist/` to Pages
- Custom domain: add `CNAME` file or configure in GitHub Pages settings; point DNS to GitHub

---

## 7. Suggested project layout (implementation)

```
kztravel/
  docs/
    requirements.md          # this file
  data/
    site.yaml                # global settings (title, favicon, background)
    trips/
      *.yaml                 # one file per trip
  public/
    images/                  # favicon, background, trip photos
  src/
    components/              # TripCard, Slideshow, Itinerary, etc.
    pages/                   # Home, TripDetail
    lib/                     # YAML loader, date/price helpers, filter index
    App.tsx
    main.tsx
  .github/workflows/         # GitHub Pages deploy (optional in v1)
  vite.config.ts
  package.json
```

---

## 8. Example content

See schemas in §4.2 and §4.3. At launch, seed **`site.yaml`** plus **3–6 sample trip YAML files** with placeholder copy and images so the layout can be reviewed end-to-end.

---

## 9. Open questions (please confirm before build)

1. **Currency:** EUR only, or configurable per trip / globally?
2. **Sold-out trips:** Hide from landing page, or show with “Fully booked”?
3. **No upcoming dates:** What should the landing card show for price/date?
4. **Description format:** Plain text only, or Markdown on detail page?
5. **Booking CTA:** Email link, phone number, external form, or none on detail page?
6. **Site branding beyond `site.yaml`:** Logo, tagline, footer contact info — add to global settings now or later?
7. **Background image:** Full-page fixed backdrop on all pages, or landing page only?
8. **Image paths:** Store under `public/images/...` only, or support external URLs?
9. **Custom domain:** Exact domain name (for `CNAME` and optional canonical URLs in meta tags)?
10. **Filter URL state:** Persist `category` / `tags` in query string for shareable links?
11. **Tag filter logic:** Match **any** selected tag (OR) vs **all** selected tags (AND)?
12. **Controlled vocabulary:** Free-form category/tags per trip, or fixed lists defined in `site.yaml`?

---

## 10. Acceptance criteria (v1)

- [ ] Vite + React + TypeScript project builds without errors
- [ ] Global settings loaded from `data/site.yaml` (title, favicon, background)
- [ ] All trips loaded from `data/trips/*.yaml` at build time
- [ ] Landing page shows magazine-style cards with title, image(s), next date, and price
- [ ] Landing page filters trips by category and tags (client-side)
- [ ] Each trip YAML includes `category` and optional `tags`
- [ ] Multi-thumbnail trips auto-slideshow on landing and detail pages
- [ ] Each trip has a detail page with gallery, itinerary, included, and excluded sections
- [ ] Site deploys to GitHub Pages and works at the custom domain (root URL)
- [ ] Adding a new trip = add trip YAML + images, rebuild, redeploy (no code change)

---

## Revision history

| Date | Change |
|------|--------|
| 2026-06-12 | Initial draft from product requirements |
| 2026-06-12 | Custom domain hosting; global `site.yaml` settings (title, favicon, background) |
| 2026-06-12 | Trip `category` and `tags`; landing-page filtering |
