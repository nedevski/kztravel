# KZ Travel — React → WordPress Theme Implementation Plan

> **Audience:** AI coding agents and developers executing the migration.  
> **Goal:** Rebuild the Vite + React static site (`kztravelreact/`) as a classic WordPress theme that **looks the same** in the browser, with trips and site settings editable from wp-admin.  
> **Companion doc:** [`wordpress-migration.md`](./wordpress-migration.md) (hosting, deployment, editor workflow).

---

## Table of contents

1. [Success criteria](#1-success-criteria)
2. [Source-of-truth reference](#2-source-of-truth-reference)
3. [Architecture decisions](#3-architecture-decisions)
4. [Prerequisites](#4-prerequisites)
5. [Theme scaffold](#5-phase-0--theme-scaffold)
6. [Data model (CPT, taxonomies, ACF)](#6-phase-1--data-model)
7. [Theme settings (site.yaml → admin)](#7-phase-2--theme-settings)
8. [Port CSS and assets](#8-phase-3--port-css-and-assets)
9. [PHP business logic](#9-phase-4--php-business-logic)
10. [Templates and partials](#10-phase-5--templates-and-partials)
11. [Client-side JavaScript](#11-phase-6--client-side-javascript)
12. [Pages: Contact and Booking](#12-phase-7--contact-and-booking-pages)
13. [Content import](#13-phase-8--content-import)
14. [Visual parity checklist](#14-phase-9--visual-parity-checklist)
15. [Deferred / out of scope for v1](#15-deferred--out-of-scope-for-v1)
16. [Agent execution order](#16-agent-execution-order)
17. [File manifest](#17-file-manifest)

---

## 1. Success criteria

The migration is complete when all of the following are true:

| Area | Requirement |
|------|-------------|
| **Visual** | Home, trip detail, contact, and booking pages match the React site at the same viewport widths (mobile + desktop). Same CSS class names where possible. |
| **URLs** | `/`, `/trips/{slug}`, `/contact`, `/booking` work with pretty permalinks. Slugs match YAML filenames (e.g. `bulgaria-veliko-tarnovo`). |
| **Trips in admin** | Editors can create/edit trips under **Trips → Add New** without touching code. |
| **Hero image** | One **Featured Image** per trip (card + detail hero). No multi-image slideshow required in v1. |
| **Gallery** | Multiple images via ACF Gallery field; rendered as a **simple responsive grid** (one thumbnail per image). No carousel, pagination, or lightbox in v1. |
| **Filters** | Client-side filters on the home page; URL query params unchanged (`?country=…&category=…&duration=…&price=…&discount=1`). |
| **Site settings** | Fields from `site.yaml` that have no native WP equivalent live on an **ACF Options** page (“Site Settings”) in wp-admin. |
| **Dark mode** | Theme toggle persists via `localStorage`; `[data-theme]` CSS works as today. |

---

## 2. Source-of-truth reference

Before writing code, read these files from `kztravelreact/`:

| Purpose | Path |
|---------|------|
| Global settings schema | `data/site.yaml` |
| Trip schema example | `data/trips/bulgaria-veliko-tarnovo.yaml` |
| Booking page content | `data/booking.yaml` |
| Data types | `src/lib/types.ts` |
| Trip enrichment (critical) | `src/lib/tripUtils.ts` |
| Filters | `src/lib/filters.ts` |
| Formatters | `src/lib/formatters.ts` |
| UI strings (Bulgarian) | `src/lib/strings.ts` |
| All styles | `src/index.css` |
| Page structure | `src/pages/Home.tsx`, `TripDetail.tsx`, `Contact.tsx`, `Booking.tsx` |
| Components to port | `src/components/*.tsx` |
| Requirements | `docs/requirements.md` |

**React → WordPress mapping (routes):**

| React route | WordPress |
|-------------|-----------|
| `/` | `front-page.php` |
| `/trips/:slug` | CPT `trip`, `single-trip.php` |
| `/contact` | Page `contact`, `page-contact.php` |
| `/booking` | Page `booking`, `page-booking.php` |

---

## 3. Architecture decisions

These are fixed for v1 — do not second-guess during implementation.

### 3.1 Classic PHP theme (not headless, not block theme)

WordPress renders HTML server-side. Vanilla JS only for filters, theme toggle, and mobile menu. Do **not** embed the React build.

### 3.2 Custom Post Type: `trip`

- Rewrite slug: `trips` → URLs like `/trips/bulgaria-veliko-tarnovo`
- Supports: `title`, `editor` (description), `thumbnail` (hero), `excerpt` (optional)
- No archive page (`has_archive => false`)

### 3.3 Taxonomies

| Taxonomy | Slug | Cardinality | Notes |
|----------|------|-------------|-------|
| `trip_country` | `country` | one term per trip | Replaces YAML `country` |
| `trip_category` | `category` | multiple terms | Replaces YAML `category[]` |

Use hierarchical `false` for both. Term slugs should match YAML values (`bulgaria`, `култура` → sanitize to slug).

`duration` stays as a plain ACF text field (not a taxonomy) — matches YAML `duration: "3 дни"`.

### 3.4 Images

| React field | WordPress v1 |
|-------------|--------------|
| `thumbnails[]` (1–N images, slideshow) | **Featured Image only** (first image on import) |
| `gallery[]` | **ACF Gallery** field `trip_gallery` |

Editors pick images from the **Media Library**. On import, sideload remote/local URLs into attachments.

### 3.5 Gallery UI (simplified)

Skip everything in `Gallery.tsx` beyond the thumbnail grid:

- **Do:** `foreach` gallery images → `<button>` or `<a>` with `<img class="gallery__thumb">` inside `<div class="gallery">`
- **Do not:** carousel nav, desktop pagination (`IMAGES_PER_PAGE`), lightbox, drag-scroll, wheel hijack

Keep existing CSS classes `.gallery`, `.gallery__thumb`, `.gallery__img` so styles from `index.css` apply.

### 3.6 Hero / slideshow (simplified)

Skip `Slideshow.tsx` auto-advance and swipe for v1:

- **Trip card:** single `<img>` from featured image
- **Trip detail hero:** single `<img>` in `.trip-detail__slideshow` wrapper (static, no JS)

CSS for `.slideshow` can remain; only one slide is rendered.

### 3.7 Plugins

| Plugin | Required | Purpose |
|--------|----------|---------|
| Advanced Custom Fields (ACF) | Yes | Trip fields, options page, gallery |
| ACF Pro | Recommended | Repeaters for dates, itinerary, inclusions |

If ACF Free only: use Meta Box (free repeaters) or flatten dates into a custom table — **prefer ACF Pro** for parity with YAML repeaters.

Optional later: Wordfence, UpdraftPlus, WP Migrate — not needed for local dev.

### 3.8 Theme folder location

Create the theme at repo root:

```
kztravel/
  kztravelwp/          ← WordPress theme (this plan)
  kztravelreact/       ← React reference (keep until parity verified)
  docs/
```

Symlink into Local WP: `wp-content/themes/kztravel` → `kztravelwp/` (see `wordpress-migration.md` §5).

---

## 4. Prerequisites

**Agent:** confirm or set up before Phase 0.

1. [Local](https://localwp.com/) site named `kztravel` (PHP 8.2+, MySQL 8).
2. WordPress installed; permalinks = **Post name**.
3. ACF (and ACF Pro if available) installed and activated.
4. Symlink or copy `kztravelwp/` into `wp-content/themes/kztravel`.
5. `WP_DEBUG` on locally (`wp-config.php`).

**WP-CLI** (from Local site shell):

```bash
wp plugin install advanced-custom-fields --activate
wp theme activate kztravel
wp rewrite flush
```

---

## 5. Phase 0 — Theme scaffold

**Objective:** Empty theme loads without errors.

### Step 0.1 — Create minimum files

Create `kztravelwp/style.css` with the WordPress theme header:

```css
/*
Theme Name: KZ Travel
Description: Travel agency catalog theme for KZ Travel.
Version: 1.0.0
Requires at least: 6.4
Requires PHP: 8.1
Text Domain: kztravel
*/
```

Create `kztravelwp/index.php`:

```php
<?php
get_header();
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        the_content();
    }
}
get_footer();
```

Create `kztravelwp/functions.php`:

```php
<?php
defined( 'ABSPATH' ) || exit;

define( 'KZTRAVEL_VERSION', '1.0.0' );
define( 'KZTRAVEL_DIR', get_template_directory() );
define( 'KZTRAVEL_URI', get_template_directory_uri() );

require_once KZTRAVEL_DIR . '/inc/setup.php';
require_once KZTRAVEL_DIR . '/inc/post-types.php';
require_once KZTRAVEL_DIR . '/inc/acf-fields.php';
require_once KZTRAVEL_DIR . '/inc/strings.php';
require_once KZTRAVEL_DIR . '/inc/formatters.php';
require_once KZTRAVEL_DIR . '/inc/trip-utils.php';
```

### Step 0.2 — `inc/setup.php`

Responsibilities:

- `add_theme_support( 'title-tag', 'post-thumbnails' )`
- Register nav menu `primary` (Home, Contact, Booking — or hardcode links like React `HeaderNav.tsx`)
- `wp_enqueue_style( 'kztravel', get_stylesheet_uri(), [], KZTRAVEL_VERSION )`
- `wp_enqueue_script` for `assets/js/theme-toggle.js`, `assets/js/filters.js`, `assets/js/mobile-menu.js` with `defer`
- Hook: `after_setup_theme`, `wp_enqueue_scripts`

### Step 0.3 — Stub remaining `inc/` files

Each file can start with `<?php defined( 'ABSPATH' ) || exit;` until its phase.

### Step 0.4 — Verify

```bash
wp theme activate kztravel
```

Open site URL — blank page, no PHP fatal errors. Check `wp-content/debug.log`.

---

## 6. Phase 1 — Data model

**Objective:** Trip CPT, taxonomies, and ACF field groups defined and version-controlled.

### Step 1.1 — `inc/post-types.php`

Register on `init`:

**Post type `trip`:**

```php
register_post_type( 'trip', [
    'labels'       => [
        'name'          => __( 'Trips', 'kztravel' ),
        'singular_name' => __( 'Trip', 'kztravel' ),
        'add_new_item'  => __( 'Add New Trip', 'kztravel' ),
    ],
    'public'       => true,
    'has_archive'  => false,
    'rewrite'      => [ 'slug' => 'trips' ],
    'menu_icon'    => 'dashicons-palmtree',
    'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    'show_in_rest' => true,
] );
```

**Taxonomy `trip_country`:** slug `country`, object type `trip`, single selection in UI (use radio or require max 1 term in save hook).

**Taxonomy `trip_category`:** slug `category`, object type `trip`, multiple terms.

Flush rewrite rules once after registration (`flush_rewrite_rules` on theme switch only — already handled by WP).

### Step 1.2 — ACF field group: Trip

Create in `inc/acf-fields.php` using `acf_add_local_field_group()` **or** build in wp-admin and export to `acf-json/`. Prefer **Local JSON** in `kztravelwp/acf-json/` for Git.

**Field group:** Trip Details  
**Location:** Post Type == `trip`

| Field name | ACF type | Maps from YAML | Notes |
|------------|----------|----------------|-------|
| `trip_duration` | Text | `duration` | e.g. `3 дни` |
| `trip_dates` | Repeater | `dates[]` | Subfields below |
| ↳ `date` | Date Picker | `date` | Return format `Y-m-d` |
| ↳ `price` | Number | `price` | EUR |
| ↳ `price_bgn` | Number | `priceBgn` | BGN |
| ↳ `discounted_price` | Number | `discountedPrice` | Optional |
| ↳ `discounted_price_bgn` | Number | `discountedPriceBgn` | Optional |
| ↳ `status` | Select | `status` | Choices: `available`, `lastSpots`, `soldout` |
| `trip_gallery` | Gallery | `gallery[]` | Return format: Image Array |
| `trip_itinerary` | Repeater | `itinerary[]` | |
| ↳ `day` | Number | `day` | |
| ↳ `title` | Text | `title` | |
| ↳ `description` | Textarea | `description` | |
| `trip_included` | Repeater | `included[]` | |
| ↳ `name` | Text | `name` | |
| ↳ `price` | Number | `price` | 0 = included |
| ↳ `price_bgn` | Number | `priceBgn` | Optional |
| `trip_excluded` | Repeater | `excluded[]` | Same subfields |

**Post title** → YAML `name`  
**Post content (editor)** → YAML `description` (plain text; no markdown in v1)  
**Featured image** → first YAML `thumbnails[]` entry on import  
**`trip_country` taxonomy** → YAML `country`  
**`trip_category` taxonomy** → YAML `category[]`

### Step 1.3 — Enable ACF Local JSON

In `inc/acf-fields.php`:

```php
add_filter( 'acf/settings/save_json', fn() => KZTRAVEL_DIR . '/acf-json' );
add_filter( 'acf/settings/load_json', function( $paths ) {
    $paths[] = KZTRAVEL_DIR . '/acf-json';
    return $paths;
} );
```

After saving field groups in admin, confirm JSON files appear in `acf-json/`. Commit them.

### Step 1.4 — Verify

1. wp-admin → **Trips** menu exists.
2. Add a test trip with dates repeater, gallery, featured image.
3. Visit `/trips/test-slug` — theme template can be blank for now; post must resolve (not 404).

---

## 7. Phase 2 — Theme settings

**Objective:** Replace `data/site.yaml` with an ACF Options page editable in wp-admin.

### Step 2.1 — Register options page

In `inc/acf-fields.php`:

```php
if ( function_exists( 'acf_add_options_page' ) ) {
    acf_add_options_page( [
        'page_title' => __( 'Site Settings', 'kztravel' ),
        'menu_title' => __( 'Site Settings', 'kztravel' ),
        'menu_slug'  => 'kztravel-settings',
        'capability' => 'edit_theme_options',
        'icon_url'   => 'dashicons-admin-site-alt3',
        'position'   => 59,
    ] );
}
```

### Step 2.2 — Options field group

**Location:** Options Page == `kztravel-settings`

Map every `site.yaml` field:

| site.yaml | ACF field | Type | Native WP alternative? |
|-----------|-----------|------|------------------------|
| `title` | `site_title` | Text | Partially — use this for branding in header/`wp_title` filter |
| `favicon` | `site_favicon` | Image | No — output `<link rel="icon">` in `header.php` |
| `background` | `site_background` | Image | No — CSS `--site-bg-image` on `.site` |
| `contact.phone` | `contact_phone` | Text | No |
| `contact.email` | `contact_email` | Email | No |
| `contact.address` | `contact_address` | Textarea | No |
| `contact.mapEmbedUrl` | `contact_map_embed_url` | URL | No |
| `contact.workingHours` | `contact_working_hours` | Repeater → `hours` (Text) | 7 rows, one per weekday |
| `contact.bankDetails.bankName` | `bank_name` | Text | No |
| `contact.bankDetails.iban` | `bank_iban` | Text | No |
| `contact.bankDetails.holder` | `bank_holder` | Text | No |
| `footer.registration` | `footer_registration` | Textarea | No |
| `footer.company` | `footer_company` | Text | No |

**Helper** in `inc/setup.php` or new `inc/options.php`:

```php
function kztravel_get_option( string $key, $default = '' ) {
    if ( function_exists( 'get_field' ) ) {
        $value = get_field( $key, 'option' );
        return $value !== null && $value !== '' ? $value : $default;
    }
    return $default;
}
```

### Step 2.3 — Home page intro copy

React hardcodes `ui.homeHeading` / `ui.homeSubheading` in `strings.ts`. Options:

- **A)** Keep in `inc/strings.php` (matches React — no admin edit)
- **B)** Add optional ACF fields `home_heading`, `home_subheading` on options page

**Default for v1:** Option A unless product owner wants editable hero text — then B.

### Step 2.4 — Verify

Fill Site Settings with values from `site.yaml`. `get_field( 'site_title', 'option' )` returns correct data via `wp shell` or temporary template dump.

---

## 8. Phase 3 — Port CSS and assets

**Objective:** Visual foundation matches React.

### Step 3.1 — Copy stylesheet

1. Copy `kztravelreact/src/index.css` → append after theme header in `kztravelwp/style.css` (or enqueue as `assets/css/main.css` if you prefer separation).
2. Search for hardcoded `/images/` paths — replace with PHP in templates, not in CSS. CSS custom property `--site-bg-image` is set inline on `.site` in `header.php` (same as `Layout.tsx`).

### Step 3.2 — Copy static assets

| From | To |
|------|-----|
| `kztravelreact/public/images/favicon.svg` | `kztravelwp/assets/images/favicon.svg` |
| `kztravelreact/public/images/bg.svg` | `kztravelwp/assets/images/bg.svg` |
| `kztravelreact/public/icons.svg` | `kztravelwp/assets/images/icons.svg` |

Trip images come from Media Library after import — do not bundle trip photos in the theme.

### Step 3.3 — `header.php` / `footer.php`

Port structure from `src/components/Layout.tsx`:

**`header.php`:**

- `<html>` with `data-theme` attribute (set by `theme-toggle.js` before paint — use inline script in `<head>` to prevent flash, port logic from `src/lib/theme.tsx`)
- `.site` wrapper with optional `style="--site-bg-image: url(...)"` from `kztravel_get_option( 'site_background' )`
- `.site-header` with brand link (`site_title`), mobile menu toggle, `HeaderNav` links, `HeaderContact` phone/email
- `wp_head()` before `</head>`
- Open `.site-main`

**`footer.php`:**

- Footer registration + company + year (port from `Layout.tsx`)
- Links to `/booking` and `/contact`
- Close `.site`, `wp_footer()`, `</html>`

Use `get_template_part( 'template-parts/header', 'nav' )` etc. if files grow large.

### Step 3.4 — Verify

Activate theme on a bare page — header/footer styled, background visible, fonts and colors match React.

---

## 9. Phase 4 — PHP business logic

**Objective:** Port `tripUtils.ts`, `formatters.ts`, `strings.ts` faithfully.

### Step 9.1 — `inc/strings.php`

Port constants from `src/lib/strings.ts`:

- `$kztravel_ui` associative array (all `ui.*` strings)
- `$kztravel_country_labels` from `countryLabels`
- `$kztravel_weekday_labels` from `weekdayLabels`
- Helper: `kztravel_ui( string $key, ...$args )`

### Step 9.2 — `inc/formatters.php`

Port from `src/lib/formatters.ts`:

| Function | Purpose |
|----------|---------|
| `kztravel_format_label( string $slug )` | Display category slug |
| `kztravel_format_country_label( string $slug )` | Bulgarian country name |
| `kztravel_format_date( string $iso )` | `15.05.2026` or locale match React |
| `kztravel_format_price( float $eur )` | `€195` |
| `kztravel_format_price_bgn( float $bgn )` | `381 лв.` |
| `kztravel_image_alt_from_attachment( $attachment_id, string $trip_name )` | Alt text |

### Step 9.3 — `inc/trip-utils.php` (critical)

Port **every** function from `src/lib/tripUtils.ts`:

- `kztravel_normalize_date( array $entry ): array`
- `kztravel_get_today_iso(): string`
- `kztravel_is_date_past( string $date ): bool`
- `kztravel_get_upcoming_bookable_dates( array $dates ): array`
- `kztravel_get_next_available_date( array $dates ): ?array`
- `kztravel_get_lowest_bookable_date( array $dates ): ?array`
- `kztravel_get_additional_bookable_date_count( array $dates ): int`
- `kztravel_is_trip_ended( array $dates ): bool`
- `kztravel_is_fully_booked( array $dates ): bool`
- `kztravel_trip_has_active_discount( array $dates ): bool`
- **`kztravel_enrich_trip( int $post_id ): array`** — main export

`kztravel_enrich_trip()` returns an associative array matching the React `Trip` interface:

```php
[
    'id'           => $post_id,
    'slug'         => $post->post_name,
    'name'         => get_the_title(),
    'description'  => get_the_content(), // apply filters as needed
    'country'      => 'bulgaria',        // term slug
    'duration'     => '3 дни',
    'category'     => [ 'култура', ... ],
    'dates'        => [ ... ],
    'gallery'      => [ [ 'id' =>, 'url' =>, 'alt' => ], ... ],
    'itinerary'    => [ ... ],
    'included'     => [ ... ],
    'excluded'     => [ ... ],
    'hero_url'     => get_the_post_thumbnail_url( 'large' ),
    'next_date'    => ?array,
    'last_date'    => ?array,
    'display_price' => ?float,
    'display_price_bgn' => ?float,
    'display_discounted_price' => ?float,
    'display_discounted_price_bgn' => ?float,
    'ended'        => bool,
    'fully_booked' => bool,
    'more_available_dates' => int,
]
```

**Agent:** line-by-line port `enrichTrip()` — this drives card pricing and badges. Add a simple test script `tools/test-trip-utils.php` runnable via `wp eval-file` comparing output against known YAML trip.

### Step 9.4 — Filter index builder

Add `kztravel_build_filter_index( array $trips ): array` in new `inc/filters.php` — port `buildFilterIndex()` from `src/lib/filters.ts`.

---

## 10. Phase 5 — Templates and partials

**Objective:** All pages render with correct HTML structure and CSS classes.

### Step 10.1 — Template parts

Create under `template-parts/`:

| File | React source | Notes |
|------|--------------|-------|
| `trip-card.php` | `TripCard.tsx` | Uses `hero_url` single image; no slideshow |
| `filter-bar.php` | `FilterBar.tsx` | Server-render filter UI; counts from PHP |
| `dates-table.php` | `DatesTable.tsx` | Status badges, discounted prices |
| `gallery.php` | `Gallery.tsx` | **Simplified** — loop only |
| `itinerary.php` | `Itinerary.tsx` | |
| `inclusion-list.php` | `InclusionList.tsx` | `variant` = included \| excluded |
| `price-display.php` | `PriceDisplay.tsx` | Chip and table variants |
| `header-contact.php` | `HeaderContact.tsx` | |
| `header-nav.php` | `HeaderNav.tsx` | |
| `theme-toggle.php` | `ThemeToggle.tsx` | Markup only; JS separate |

**`template-parts/gallery.php` (v1 minimal):**

```php
<?php
/** @var array $args ['images' => array, 'trip_name' => string] */
$images    = $args['images'] ?? [];
$trip_name = $args['trip_name'] ?? '';
if ( empty( $images ) ) return;
?>
<div class="gallery">
    <?php foreach ( $images as $i => $image ) : ?>
        <div class="gallery__thumb" tabindex="0">
            <img
                src="<?php echo esc_url( $image['url'] ); ?>"
                alt="<?php echo esc_attr( $image['alt'] ); ?>"
                class="gallery__img"
                loading="lazy"
            />
        </div>
    <?php endforeach; ?>
</div>
```

Use `<div>` instead of `<button>` since there is no lightbox click handler in v1.

**`template-parts/trip-card.php`:**

- Root: `<a href="<?php echo esc_url( get_permalink() ); ?>" class="trip-card ...">`
- Media: single `<img>` with classes `trip-card__slideshow` or wrap in `.trip-card__media`
- Add `data-country`, `data-categories`, `data-duration-days`, `data-price`, `data-discounted` attributes for `filters.js`
- Port badge, pricing, and date markup from `TripCard.tsx`

### Step 10.2 — `front-page.php`

```php
get_header();
$trip_query = new WP_Query( [
    'post_type'      => 'trip',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
] );
$trips = [];
while ( $trip_query->have_posts() ) {
    $trip_query->the_post();
    $trips[] = kztravel_enrich_trip( get_the_ID() );
}
wp_reset_postdata();
$filter_index = kztravel_build_filter_index( $trips );
?>
<!-- Port Home.tsx markup: .home, .home__intro, FilterBar, .trip-grid -->
<?php
get_footer();
```

**Settings → Reading:** set “Your homepage displays” to **A static page** and assign a “Home” page, **or** rely on `front-page.php` template hierarchy (WordPress uses `front-page.php` automatically for the front page).

### Step 10.3 — `single-trip.php`

Port `TripDetail.tsx` sections in order:

1. Back link to home
2. Hero: title + card with static hero image + badges + CTA to `/contact?trip={slug}`
3. Description
4. Dates table
5. Gallery (if not empty)
6. Itinerary
7. Included / excluded
8. Suggested trips (3 random same-country) — port `pickRandomTrips()` to PHP
9. Footer CTA

Country badge links to `/?country={slug}`.

### Step 10.4 — `page-contact.php` and `page-booking.php`

Assign templates via `Template Name:` comment or `page-{slug}.php` convention.

Port `Contact.tsx` and `Booking.tsx`. Contact reads `?trip=` query param for pre-filled inquiry subject (match React).

### Step 10.5 — `404.php`

Redirect or show link home — React uses `<Navigate to="/">` for unknown trips.

### Step 10.6 — Verify

With one fully populated trip, compare side-by-side:

- `http://kztravel.local/` vs `npm run dev` home
- `/trips/{slug}` detail pages

---

## 11. Phase 6 — Client-side JavaScript

**Objective:** Filters and theme toggle behave like React.

### Step 11.1 — `assets/js/filters.js`

Port `src/lib/filters.ts` to vanilla JS:

- `parseFiltersFromSearch( search, priceRanges )`
- `filterTrips( trips, filters )` — trips sourced from `data-*` on `.trip-card` elements **or** a `<script type="application/json" id="kztravel-trips">` blob emitted by `front-page.php`
- `buildFilterParams( filters )`
- URL sync via `history.replaceState` (no full page reload)

**Filter dimensions** (must match React):

| Param | Logic |
|-------|-------|
| `country` | Single slug |
| `category` | Comma-separated; OR logic |
| `duration` | Comma-separated day counts |
| `price` | Price range id |
| `discount` | `1` = discounted only |

On DOMContentLoaded:

1. Parse cards / JSON into trip objects with filter metadata.
2. Read URL params → apply filters → show/hide cards (class `trip-card--hidden` or `display: none`).
3. Wire filter bar clicks to update URL and re-filter.
4. Empty state: show `.empty-state` block (hidden by default in PHP).

`filter-bar.php` should emit the same HTML structure and classes as React `FilterBar.tsx` so CSS applies unchanged.

### Step 11.2 — `assets/js/theme-toggle.js`

Port `src/lib/theme.tsx`:

- Read `localStorage` key (use same key as React if possible — check `theme.tsx`)
- Set `document.documentElement.dataset.theme` = `light` | `dark`
- Toggle button in header
- Respect `prefers-color-scheme` on first visit
- Optional: `html.theme-transition` class during toggle

### Step 11.3 — `assets/js/mobile-menu.js`

Port mobile menu open/close from `Layout.tsx` (`site-header--menu-open` class on header).

### Step 11.4 — Verify

- `/?country=bulgaria` filters correctly
- Share URL and reload — same filtered set
- Clear filters works
- Dark mode persists across page loads

---

## 12. Phase 7 — Contact and Booking pages

### Step 12.1 — Create WordPress pages

```bash
wp post create --post_type=page --post_title='Контакти' --post_name=contact --post_status=publish
wp post create --post_type=page --post_title='Как да резервирам' --post_name=booking --post_status=publish
```

### Step 12.2 — Booking content

**Option A (recommended):** ACF fields on the Booking page — `booking_intro` (textarea), `booking_sections` repeater (`title`, `items` repeater) — mirrors `booking.yaml`.

**Option B:** Gutenberg block editor content entered manually once.

Import `data/booking.yaml` via import script.

### Step 12.3 — Contact page

Most data comes from Site Settings options. Page template only provides layout shell and map `<iframe src="...">` from `contact_map_embed_url`.

---

## 13. Phase 8 — Content import

**Objective:** All YAML trips and site settings migrated once.

### Step 13.1 — Import script

Create `kztravelwp/tools/import-content.php`. Run once:

```bash
wp eval-file wp-content/themes/kztravel/tools/import-content.php
```

**Script logic:**

1. Load `kztravelreact/data/site.yaml` → `update_field( ..., 'option' )` for each setting.
2. For each `kztravelreact/data/trips/*.yaml`:
   - Parse YAML (`symfony/yaml` via Composer in theme, or Spyc, or manual `yaml_parse` if ext-yaml available).
   - `wp_insert_post` or update existing by slug.
   - Set taxonomies, featured image (sideload first thumbnail), gallery (sideload each).
   - `update_field` for repeaters.
3. Import `booking.yaml` into booking page fields.

**Image sideloading:**

```php
media_sideload_image( $url, $post_id, $desc, 'id' );
```

For local files under `kztravelreact/public/`, use `media_handle_sideload` with temp copy.

### Step 13.2 — Post-import cleanup

- Delete or rename `tools/import-content.php` on production.
- Regenerate thumbnails if needed: `wp media regenerate --yes`

### Step 13.3 — Verify

| Check | Expected |
|-------|----------|
| Trip count | Same as `data/trips/*.yaml` file count |
| Slugs | Match filenames |
| `/trips/bulgaria-veliko-tarnovo` | Loads with dates, gallery, itinerary |
| Site title / contact | Match `site.yaml` |
| Gallery images | In Media Library, attached to trip |

---

## 14. Phase 9 — Visual parity checklist

Agent or human should verify each item against React `npm run preview` at 375px and 1280px width.

### Global

- [ ] Header: brand, nav links, phone, email, theme toggle, mobile hamburger
- [ ] Footer: registration text, company, booking/contact links
- [ ] Background image on `.site`
- [ ] Favicon
- [ ] Dark / light themes

### Home

- [ ] Heading and subheading
- [ ] Filter bar (all dimensions that exist in data)
- [ ] Trip grid columns responsive
- [ ] Trip cards: image, badges, title, duration, price chip, date, “fully booked”, “ended” states
- [ ] Empty filter state + clear button
- [ ] Filter URL sharing

### Trip detail

- [ ] Back link
- [ ] Hero image, title, badges, duration, CTA
- [ ] Description
- [ ] Dates table (all statuses, discounts, past dates styled)
- [ ] Gallery grid (all images visible as thumbnails)
- [ ] Itinerary days
- [ ] Included / excluded lists with prices
- [ ] Suggested trips section
- [ ] Bottom CTA

### Contact

- [ ] Phone, email, address, hours table, bank details, map embed
- [ ] `?trip=slug` query highlights trip name

### Booking

- [ ] Title, intro, all sections from YAML

---

## 15. Deferred / out of scope for v1

Do **not** implement unless explicitly requested later:

| Feature | React location | v1 WP behavior |
|---------|----------------|----------------|
| Gallery carousel / pagination | `Gallery.tsx` | Simple grid loop |
| Gallery lightbox | `Gallery.tsx` | None |
| Hero / card slideshow | `Slideshow.tsx` | Featured image only |
| Multiple hero thumbnails | YAML `thumbnails[]` | Import first only |
| Markdown descriptions | — | Plain text from editor |
| Contact form submission | — | `mailto:` / `tel:` links only (match React) |
| REST API / headless | — | Not used |
| Page builder | — | Not used |
| i18n / Polylang | — | Bulgarian strings hardcoded in PHP |
| GitHub Pages deploy | — | Replaced by WP hosting |

---

## 16. Agent execution order

Strict sequence for an AI agent — complete each phase before the next.

```
Phase 0  Theme scaffold (functions.php, setup, activate)
   ↓
Phase 1  CPT + taxonomies + Trip ACF fields + acf-json
   ↓
Phase 2  Site Settings options page + ACF fields
   ↓
Phase 3  CSS + assets + header.php + footer.php
   ↓
Phase 4  trip-utils.php + formatters.php + strings.php + filters.php
   ↓
Phase 5  Template parts → front-page.php → single-trip.php → pages
   ↓
Phase 6  filters.js + theme-toggle.js + mobile-menu.js
   ↓
Phase 7  Create contact/booking pages + booking ACF
   ↓
Phase 8  import-content.php + run import + verify all trips
   ↓
Phase 9  Visual parity checklist + fix diffs
```

**Per-phase agent protocol:**

1. Read the referenced React source files.
2. Create or edit the listed PHP/JS/CSS files.
3. Run verification steps; fix errors before proceeding.
4. Do not refactor unrelated React code.
5. Keep CSS class names identical to React unless WordPress constraints force a change.
6. Commit theme files (`kztravelwp/`, `acf-json/`) — not `node_modules` or uploads.

---

## 17. File manifest

Final theme tree when complete:

```
kztravelwp/
  style.css
  functions.php
  index.php
  front-page.php
  single-trip.php
  page-contact.php
  page-booking.php
  header.php
  footer.php
  404.php
  inc/
    setup.php
    post-types.php
    acf-fields.php
    options.php          # optional helpers
    trip-utils.php
    formatters.php
    strings.php
    filters.php
  template-parts/
    trip-card.php
    filter-bar.php
    dates-table.php
    gallery.php
    itinerary.php
    inclusion-list.php
    price-display.php
    header-nav.php
    header-contact.php
    theme-toggle.php
  assets/
    css/                 # optional if splitting CSS
    js/
      filters.js
      theme-toggle.js
      mobile-menu.js
    images/
      favicon.svg
      bg.svg
      icons.svg
  acf-json/
    group_*.json         # committed field definitions
  tools/
    import-content.php   # dev/staging only
    test-trip-utils.php  # optional
```

---

## Appendix A — `data-*` attributes for filter JS

Each `.trip-card` on the home page should expose:

```html
<a class="trip-card"
   data-country="bulgaria"
   data-categories="култура,екскурзия,уикенд,с-водач"
   data-duration-days="3"
   data-price="185"
   data-has-discount="1"
   href="/trips/bulgaria-veliko-tarnovo">
```

`data-price` = effective display price (discounted if applicable) from `kztravel_enrich_trip()`.

---

## Appendix B — Editor workflow summary

| Task | wp-admin location |
|------|-------------------|
| Add / edit trip | **Trips → Add New** |
| Hero image | Featured image meta box |
| Photo gallery | Trip Details → Gallery field |
| Dates / prices | Trip Details → Dates repeater |
| Country / category | Trip taxonomies meta box |
| Phone, email, footer | **Site Settings** |
| Booking page text | **Pages → Booking** (or ACF on page) |
| Contact layout | **Pages → Contact** (data from Site Settings) |

---

## Appendix C — Related documents

- [`wordpress-migration.md`](./wordpress-migration.md) — Local setup, deployment, staging, maintenance
- [`../kztravelreact/docs/requirements.md`](../kztravelreact/docs/requirements.md) — original data model and UX specs
- [`../kztravelreact/data/site.yaml`](../kztravelreact/data/site.yaml) — settings to migrate

---

*Last updated: June 2026*
