# KZ Travel → WordPress Theme Migration Guide

> **Audience:** Developers familiar with React/TypeScript but new to WordPress.  
> **Context:** This site is today a Vite + React static site with YAML content, deployed to GitHub Pages. This document explains how to rebuild it as a WordPress theme and operate it in production.

---

## Table of contents

1. [Should you migrate?](#1-should-you-migrate)
2. [Recommended architecture](#2-recommended-architecture)
3. [What maps to what](#3-what-maps-to-what)
4. [Prerequisites](#4-prerequisites)
5. [Local development setup](#5-local-development-setup)
6. [Theme project structure](#6-theme-project-structure)
7. [Implementation phases](#7-implementation-phases)
8. [Migrating YAML content](#8-migrating-yaml-content)
9. [Porting the frontend](#9-porting-the-frontend)
10. [Client-side filters](#10-client-side-filters)
11. [Content editing workflow (for editors)](#11-content-editing-workflow-for-editors)
12. [Deployment](#12-deployment)
13. [Updates and maintenance](#13-updates-and-maintenance)
14. [Staging workflow](#14-staging-workflow)
15. [Alternatives considered](#15-alternatives-considered)
16. [Checklist](#16-checklist)

---

## 1. Should you migrate?

### What you gain

| Today (static) | With WordPress |
|----------------|----------------|
| Edit YAML in GitHub | Edit trips in wp-admin (forms, media library) |
| Rebuild + deploy on every content change | Content updates are live immediately |
| No admin UI | Built-in roles (admin vs editor) |
| Free GitHub Pages hosting | Paid hosting (~€5–15/mo shared, more for managed) |

### What you lose or complicate

| Today | With WordPress |
|-------|----------------|
| Zero server maintenance | PHP runtime, MySQL, security patches, backups |
| Trivial hosting | Need HTTPS, backups, plugin updates |
| Entire app in one repo | Theme + plugins + WP core (usually not in your repo) |
| Client-side React SPA | PHP templates + optional vanilla JS |

### When migration makes sense

- Non-technical staff will add/edit trips regularly.
- You want a media library instead of committing images to Git.
- You are OK with ongoing hosting cost and maintenance.

### When to stay static

- Content changes are rare and done by a developer.
- You want zero server attack surface.
- GitHub Pages + YAML is working fine.

If you proceed, the rest of this guide assumes a **classic custom theme** (PHP templates), not a page builder and not headless.

---

## 2. Recommended architecture

```
┌─────────────────────────────────────────────────────────┐
│  WordPress (PHP + MySQL)                                │
│                                                         │
│  Custom Post Type: trip                                 │
│  Taxonomies: trip_country, trip_category                  │
│  ACF fields: dates, itinerary, included, excluded, …      │
│  Options page: site settings (replaces site.yaml)       │
│  Pages: Contact, Booking (replaces static routes)       │
│                                                         │
│  Theme: kztravel/                                       │
│    PHP templates (header, footer, single-trip, home)    │
│    style.css (ported from src/index.css)                │
│    assets/js/filters.js (ported from src/lib/filters.ts)│
└─────────────────────────────────────────────────────────┘
```

**Plugins to plan for (keep the list short):**

| Plugin | Role | Free? |
|--------|------|-------|
| [Advanced Custom Fields](https://www.advancedcustomfields.com/) (ACF) | Structured trip fields (dates repeater, itinerary, etc.) | Free tier is enough to start; Pro if you want repeater on free (see note below) |
| [WP Migrate](https://deliciousbrains.com/wp-migrate-db/) or Duplicator | DB/files sync between local ↔ staging ↔ prod | Duplicator free for basic moves |
| [Wordfence](https://www.wordfence.com/) or similar | Firewall / brute-force protection | Free tier |
| UpdraftPlus (or host backups) | Automated backups | Free tier |

> **ACF note:** Repeaters (dates, itinerary rows, inclusion rows) require **ACF Pro** (~$49/year) or an alternative field plugin (Meta Box, Pods). For a trip catalog with nested data, budget for Pro or pick Meta Box (free repeaters).

**Do not install** a heavy page builder (Elementor, Divi) for this project — your layout is custom and fixed; a builder adds bloat and fights your CSS.

---

## 3. What maps to what

### Routes / pages

| Current (React) | WordPress |
|-----------------|-----------|
| `/` (Home + trip grid + filters) | `front-page.php` or `home.php` |
| `/trips/:slug` | Custom post type `trip`, template `single-trip.php` |
| `/contact` | Page slug `contact`, template `page-contact.php` |
| `/booking` | Page slug `booking`, template `page-booking.php` |

Set **Settings → Reading → Your homepage displays → A static page** and pick a “Home” page, *or* use `front-page.php` directly (common for themes).

### Content files

| Current YAML | WordPress |
|--------------|-----------|
| `data/site.yaml` | ACF Options page (`kztravel_settings`) |
| `data/booking.yaml` | Booking page content + optional ACF fields |
| `data/trips/*.yaml` | `trip` posts (one post per file) |
| `trip.country` | Taxonomy `trip_country` *or* ACF select |
| `trip.category[]` | Taxonomy `trip_category` |
| `trip.dates[]` | ACF Repeater: `date`, `price`, `price_bgn`, `status`, optional discount fields |
| `trip.itinerary[]` | ACF Repeater: `day`, `title`, `description` |
| `trip.included[]` / `excluded[]` | ACF Repeater: `name`, `price`, `price_bgn` |
| `trip.thumbnails[]` / `gallery[]` | ACF Gallery or Media fields |
| `public/images/*` | WordPress Media Library (`wp-content/uploads/`) |

### Logic to port

| TypeScript module | WordPress equivalent |
|-------------------|---------------------|
| `src/lib/tripUtils.ts` (`enrichTrip`) | PHP function `kztravel_enrich_trip( $post_id )` in `inc/trip-utils.php` |
| `src/lib/filters.ts` | `assets/js/filters.js` (same URL query params: `?country=…&category=…`) |
| `src/lib/formatters.ts` | `inc/formatters.php` |
| `src/lib/strings.ts` (`countryLabels`) | `inc/strings.php` or gettext `.po` files for i18n |
| `src/lib/theme.tsx` (dark mode) | Same CSS `[data-theme]` + small `theme.js` in theme footer |

---

## 4. Prerequisites

On your Windows machine:

- **PHP 8.1+** and **Composer** (optional but useful)
- **Node.js 22+** (only if you bundle theme JS with Vite/webpack; optional)
- **Git**
- A local WordPress environment (see §5)
- Basic comfort with PHP (templates are mostly HTML + `<?php … ?>`)

You do **not** need to run the React app in production after migration. The React repo can be archived or kept as reference.

---

## 5. Local development setup

Pick **one** local stack. All give you `http://kztravel.local` (or similar) with wp-admin.

### Option A: Local (by WP Engine) — easiest on Windows

1. Download [Local](https://localwp.com/).
2. **+ Create a new site** → name: `kztravel` → Preferred: **Custom** → PHP 8.2, nginx or Apache, MySQL 8.
3. After creation, open **Site folder** → `app/public/` is your web root (`wp-content/themes/` lives here).
4. Default admin: shown in Local UI (e.g. `admin` / random password).

### Option B: wp-env (Docker, good if you already use Docker)

From a folder that will hold the theme:

```bash
# In theme repo root, after adding .wp-env.json (see below)
npm init -y
npm install @wordpress/env --save-dev
npx wp-env start
```

`.wp-env.json` example:

```json
{
  "core": "WordPress/WordPress",
  "phpVersion": "8.2",
  "themes": ["./"],
  "plugins": [
    "https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip"
  ],
  "port": 8888
}
```

- Site: `http://localhost:8888`
- Admin: `http://localhost:8888/wp-admin` (user `admin`, password `password`)

### Option C: Laragon (Windows, lightweight)

1. [Laragon](https://laragon.org/) → Quick app → WordPress.
2. Document root: `C:\laragon\www\kztravel`.
3. Create DB via Laragon, run WP installer at `http://kztravel.test`.

### After WordPress is running

1. Log in to **wp-admin**.
2. **Appearance → Themes → Add New** — ignore defaults; symlink or copy your theme into `wp-content/themes/kztravel/`.
3. Activate **KZ Travel** theme.
4. Install and activate **ACF** (and ACF Pro if licensed).
5. **Settings → Permalinks → Post name** → Save (required for pretty `/trips/slug` URLs).

### Symlinking the theme (recommended during dev)

Keep the theme in your existing repo under `wordpress-theme/` and symlink it into Local’s themes folder so you edit in one place:

```powershell
# Example — adjust paths to your Local site folder
New-Item -ItemType SymbolicLink `
  -Path "C:\Users\Nikola\Local Sites\kztravel\app\public\wp-content\themes\kztravel" `
  -Target "C:\Users\Nikola\source\repos\nedevski\kztravel\wordpress-theme"
```

On Windows, symlink creation may require **Developer Mode** or an elevated PowerShell.

### Debugging locally

In `wp-config.php` (Local often has a “Open site shell” shortcut):

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

Logs: `wp-content/debug.log`.

---

## 6. Theme project structure

Create a sibling folder in the repo (keeps WP separate from the old static app):

```
kztravel/
  wordpress-theme/           ← new theme (deploy this folder)
    style.css                ← theme header comment + main CSS
    functions.php
    index.php
    front-page.php
    single-trip.php
    page-contact.php
    page-booking.php
    header.php
    footer.php
    inc/
      setup.php              ← theme supports, menus, enqueue scripts
      post-types.php         ← register trip CPT + taxonomies
      acf-fields.php         ← ACF field groups (PHP export or JSON in acf-json/)
      trip-utils.php         ← enrichTrip port
      formatters.php
      strings.php
    template-parts/
      trip-card.php
      filter-bar.php
      dates-table.php
      gallery.php
      itinerary.php
      inclusion-list.php
    assets/
      js/
        filters.js
        theme-toggle.js
      images/                ← favicon, bg (or use Customizer / ACF options)
    acf-json/                ← ACF Local JSON (version-controlled field definitions)
```

Minimum `style.css` header (required by WordPress):

```css
/*
Theme Name: KZ Travel
Theme URI: https://example.com
Author: Your Name
Description: Travel agency catalog theme for KZ Travel.
Version: 1.0.0
Requires at least: 6.4
Requires PHP: 8.1
Text Domain: kztravel
*/
```

Register the trip CPT in `inc/post-types.php`:

```php
register_post_type( 'trip', [
    'labels'       => [ 'name' => 'Trips', 'singular_name' => 'Trip' ],
    'public'       => true,
    'has_archive'  => false,
    'rewrite'      => [ 'slug' => 'trips' ],
    'menu_icon'    => 'dashicons-palmtree',
    'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    'show_in_rest' => true,
] );
```

This gives URLs like `/trips/bulgaria-veliko-tarnovo`, matching the current site.

---

## 7. Implementation phases

Work in this order to avoid rework.

### Phase 1 — Skeleton (1–2 days)

- [ ] Local WP running, theme activated
- [ ] Register `trip` CPT + taxonomies
- [ ] `header.php` / `footer.php` with site title, nav (Home, Contact, Booking)
- [ ] Enqueue `style.css` and confirm layout loads

### Phase 2 — Data model (1–2 days)

- [ ] ACF field groups for trips (all YAML fields)
- [ ] ACF Options page for `site.yaml` fields (contact, footer, favicon, background)
- [ ] Booking page fields or block content
- [ ] Export field groups to `acf-json/` for Git versioning

### Phase 3 — Templates (3–5 days)

- [ ] `template-parts/trip-card.php`
- [ ] `front-page.php` — query all trips, render grid
- [ ] `single-trip.php` — hero, dates table, gallery, itinerary, inclusions
- [ ] `page-contact.php`, `page-booking.php`
- [ ] Port `tripUtils` logic to PHP for “next date”, display price, `ended`, etc.

### Phase 4 — Filters & polish (2–3 days)

- [ ] Port `filters.ts` → `filters.js` (URL query string unchanged for bookmarkable filters)
- [ ] Theme toggle JS + `localStorage`
- [ ] Responsive pass, empty states, Bulgarian copy from `strings.ts`

### Phase 5 — Content migration (1 day)

- [ ] Run import script (§8) or manual entry for first few trips
- [ ] Upload images to Media Library
- [ ] Verify every trip page against the static site

### Phase 6 — Deploy (1 day)

- [ ] Production hosting, SSL, DNS
- [ ] Migrate DB + uploads
- [ ] Smoke test filters, contact page, mobile

**Rough total:** ~2 weeks part-time for one developer, depending on ACF familiarity.

---

## 8. Migrating YAML content

### One-time import script (recommended)

Add a WP-CLI command or a temporary `tools/import-trips.php` that:

1. Reads `data/trips/*.yaml` and `data/site.yaml` from the old repo (or copy YAML into the theme `import/` folder).
2. For each trip file:
   - `wp_insert_post([ 'post_type' => 'trip', 'post_name' => $slug, 'post_title' => $name, 'post_content' => $description ])`
   - Set taxonomies from `country` and `category`
   - `update_field()` for dates, itinerary, gallery URLs
3. Downloads remote images (Unsplash URLs in some YAML files) into the Media Library with `media_sideload_image()`.

Run once via WP-CLI:

```bash
wp eval-file wordpress-theme/tools/import-trips.php
```

Then **delete or disable** the import script on production.

### Manual migration (small catalog)

With only ~10 trips, manual entry in wp-admin is viable:

1. **Trips → Add New**
2. Fill title, description, ACF repeaters
3. Set featured image + gallery
4. Assign country/category terms

Use the static site side-by-side as reference.

### Slugs

Keep slugs identical to YAML filenames (`bulgaria-veliko-tarnovo`) so old links redirect cleanly:

```
/trips/bulgaria-veliko-tarnovo  →  same on WordPress
```

If the domain changes, add 301 redirects in `.htaccess` or a redirect plugin.

---

## 9. Porting the frontend

### CSS

- Copy `src/index.css` → `wordpress-theme/style.css` (below the theme header comment).
- Replace any `/images/...` paths with `<?php echo get_template_directory_uri(); ?>/assets/images/...` in PHP templates, or use `wp_enqueue_style` with proper URI helpers.

### React components → PHP partials

| React component | PHP partial |
|-----------------|-------------|
| `Layout.tsx` | `header.php` + `footer.php` |
| `TripCard.tsx` | `template-parts/trip-card.php` |
| `FilterBar.tsx` | `template-parts/filter-bar.php` |
| `DatesTable.tsx` | `template-parts/dates-table.php` |
| `Gallery.tsx` | `template-parts/gallery.php` |
| `Itinerary.tsx` | `template-parts/itinerary.php` |
| `InclusionList.tsx` | `template-parts/inclusion-list.php` |

Example trip card usage on home:

```php
<?php
$trip_query = new WP_Query( [ 'post_type' => 'trip', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ] );
while ( $trip_query->have_posts() ) :
    $trip_query->the_post();
    $trip = kztravel_enrich_trip( get_the_ID() );
    get_template_part( 'template-parts/trip', 'card', [ 'trip' => $trip ] );
endwhile;
wp_reset_postdata();
```

### `enrichTrip` in PHP

Port the logic from `src/lib/tripUtils.ts` verbatim in spirit:

- Parse ACF `dates` repeater into an array
- Compute `nextDate`, `displayPrice`, `ended`, `fullyBooked`, `moreAvailableDates`
- Return a plain PHP array/stdClass consumed by templates

Unit-test this file if possible (PHPUnit or a small CLI script) — it’s the most business-critical code in the theme.

---

## 10. Client-side filters

The static site filters in the browser and syncs to the URL (`src/pages/Home.tsx` + `src/lib/filters.ts`). **Keep the same behavior** in WordPress:

1. `front-page.php` renders **all** trip cards in the DOM (or a `data-*` JSON blob).
2. `filters.js` reads/writes `URLSearchParams` exactly like today.
3. Show/hide cards with CSS classes — no admin-ajax needed for ~20 trips.

This avoids custom REST endpoints and keeps hosting simple.

If the catalog grows past ~50 trips, consider:

- FacetWP (paid plugin), or
- A custom `WP_REST` endpoint with server-side filtering.

For KZ Travel’s scale, client-side is fine.

---

## 11. Content editing workflow (for editors)

Document this for the agency staff replacing the README YAML section.

| Task | Where in wp-admin |
|------|-------------------|
| Add a trip | **Trips → Add New** |
| Change prices/dates | Edit trip → **Dates** repeater |
| Upload photos | **Gallery** field or Media Library |
| Change phone/email | **Site Settings** (ACF Options) |
| Edit booking info | **Pages → Booking** |
| Change homepage text | **Pages → Home** or theme Customizer |

**Roles:**

- **Editor** — can manage trips and pages, not plugins/themes
- **Administrator** — full access (limit to developers)

Train editors on:

- Setting date `status` to `available` / `lastSpots` / `soldout`
- Featured image vs gallery
- Not uploading 10 MB originals (use compressed JPEG/WebP)

---

## 12. Deployment

### Hosting options

| Type | Examples | Cost | Notes |
|------|----------|------|-------|
| Shared PHP hosting | SiteGround, SuperHosting.bg, Hostinger | €5–15/mo | Fine for small catalog; confirm PHP 8.1+ |
| Managed WordPress | Kinsta, WP Engine, Raidboxes | €25+/mo | Backups, staging, updates handled |
| VPS + Cloudways | DigitalOcean + Cloudways | €15+/mo | More control, more ops work |

**Requirements:** PHP 8.1+, MySQL 8 or MariaDB 10.4+, HTTPS, daily backups, ≥512 MB PHP memory.

GitHub Pages is **not** used for WordPress (PHP + database required).

### What gets deployed

| Artifact | How |
|----------|-----|
| Theme folder `wordpress-theme/` | SFTP, Git pull on server, or CI/CD |
| `wp-content/uploads/` | Migrated with DB or rsync |
| Database | SQL dump import |
| Plugins | Install via wp-admin or Composer (Bedrock — advanced) |

WordPress **core** is usually installed on the server via the host’s installer, not committed to your repo.

### First production deploy

1. **Provision hosting** + domain DNS (`A` / `CNAME` to host).
2. **Install WordPress** on the host (one-click installer).
3. **Export local site:**
   - **Duplicator:** package = archive + installer.php
   - **Or** `wp db export` + zip `wp-content/uploads` + copy theme
4. **Import on production** via Duplicator installer or manual DB import (phpMyAdmin).
5. Run **search-replace** on URLs: `http://kztravel.local` → `https://yourdomain.com`  
   - Use [WP-CLI](https://developer.wordpress.org/cli/commands/search-replace/):  
     `wp search-replace 'http://kztravel.local' 'https://yourdomain.com' --all-tables`
   - Or plugin “Better Search Replace”
6. **Settings → Permalinks → Save** (flush rewrite rules).
7. Enable **SSL** (Let’s Encrypt via host panel).
8. Test trips, filters, contact form (if any), mobile layout.

### Deploying theme updates only (ongoing dev)

When you change PHP/CSS/JS but **not** the database:

**Manual:**

```bash
# rsync example (Git Bash / WSL)
rsync -avz --delete wordpress-theme/ user@host:/path/to/wp-content/themes/kztravel/
```

**Git-based (better):**

1. On server: `cd wp-content/themes/kztravel && git pull origin main`
2. Or GitHub Actions on push to `main`:

```yaml
# .github/workflows/deploy-theme.yml (example sketch)
on:
  push:
    branches: [main]
    paths: ['wordpress-theme/**']
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Deploy theme via SFTP
        uses: SamKirkland/FTP-Deploy-Action@v4
        with:
          server: ${{ secrets.FTP_HOST }}
          username: ${{ secrets.FTP_USER }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./wordpress-theme/
          server-dir: /public_html/wp-content/themes/kztravel/
```

Never deploy `wp-config.php` with secrets to a public repo.

### DNS cutover from GitHub Pages

1. Build and deploy WordPress on the new host **before** switching DNS.
2. Lower TTL on DNS records 24h ahead.
3. Point domain A/CNAME to new host.
4. Add 301 redirects from any old paths if URL structure changed.
5. Keep GitHub Pages repo read-only for a month as rollback reference.

---

## 13. Updates and maintenance

### Update cadence

| Item | Frequency | How |
|------|-----------|-----|
| WordPress core | Within 1 week of security releases | Host auto-update or wp-admin → Dashboard → Updates |
| Plugins | Monthly (after staging test) | wp-admin → Plugins |
| Theme (your code) | As needed | Git deploy |
| PHP version | Yearly | Host panel; test on staging first |
| Backups | Daily automated | Host or UpdraftPlus → cloud storage |

### Safe update procedure

1. **Backup** DB + `wp-content/uploads` + theme.
2. Apply updates on **staging** clone.
3. Click through: home, one trip, contact, filters, dark mode.
4. Apply to production in a low-traffic window.
5. If white screen: restore backup; check `debug.log`.

### Security basics

- Strong admin password + **2FA** (plugin: Two Factor Authentication)
- No `admin` username; use a custom login URL only if you accept plugin overhead
- Remove unused plugins/themes
- `DISALLOW_FILE_EDIT` in `wp-config.php` on production (disables theme editor in admin)
- Keep file permissions: dirs `755`, files `644`

```php
// wp-config.php (production)
define( 'DISALLOW_FILE_EDIT', true );
```

### Monitoring

- Uptime: UptimeRobot (free)
- SSL expiry: host usually auto-renews
- Optional: email alerts from Wordfence on lockouts

---

## 14. Staging workflow

```
Local (dev)  →  Staging (staging.yourdomain.com)  →  Production (yourdomain.com)
     │                    │                                    │
  Git theme            DB sync                            Live traffic
  wp-env/Local         Duplicator / WP Migrate              Backups
```

1. Hosts often provide **staging** with one click (SiteGround, Kinsta).
2. Content edits happen on **production** (editors) or **staging** (if you prefer review-first).
3. Code (theme) flows: Local → Git → deploy to staging → deploy to production.
4. **Do not** overwrite production DB with staging casually — you’ll lose live content. Sync production → staging for testing, not the reverse.

---

## 15. Alternatives considered

### Headless WordPress (keep React, WP as CMS)

- **Pros:** Reuse existing React components; modern DX.
- **Cons:** Two deployments (WP + static/Node frontend), REST API auth, image handling, more moving parts.
- **Verdict:** Overkill for this small catalog unless you plan a mobile app or multi-channel content.

### Full site editing (block theme)

- **Pros:** Native WP 6.x approach, block patterns.
- **Cons:** Your UI is highly custom (filters, trip card slideshow, dates table); fighting blocks may be slower than PHP templates.
- **Verdict:** Classic theme + ACF is faster for this design.

### Stay on static + Decap CMS / Forestry

- **Pros:** Keeps GitHub Pages; YAML editing with a UI.
- **Cons:** Still rebuild-on-publish; not WordPress.
- **Verdict:** Good middle ground if the goal is “easier editing” without PHP hosting.

### Embed current React build inside a blank WP theme

- **Pros:** Fastest initial deploy.
- **Cons:** Trip content still in YAML inside the bundle; defeats the purpose of WP; SEO and routing pain.
- **Verdict:** Do not do this.

---

## 16. Checklist

### Before go-live

- [ ] All trips imported, slugs match old site
- [ ] Permalinks = Post name
- [ ] HTTPS works, no mixed content warnings
- [ ] Contact info correct in options
- [ ] Filters work with URL sharing
- [ ] Dark/light mode persists
- [ ] Mobile layout checked
- [ ] 301 redirects from old domain/paths if needed
- [ ] Backups configured and tested (restore once)
- [ ] Editor account created (not shared admin password)
- [ ] `WP_DEBUG` off on production
- [ ] Import script removed/disabled

### Repo hygiene

- [ ] `wordpress-theme/` in Git
- [ ] `acf-json/` committed (field definitions)
- [ ] Secrets in host/env only, not in repo
- [ ] Old `data/*.yaml` can remain as import source or archive
- [ ] README updated to point editors to wp-admin

---

## Quick reference: day-one commands

```bash
# Local with wp-env
npx wp-env start
npx wp-env run cli wp plugin install advanced-custom-fields --activate
npx wp-env run cli wp theme activate kztravel

# Flush permalinks after CPT registration
npx wp-env run cli wp rewrite flush

# Export DB from local
npx wp-env run cli wp db export /var/www/html/kztravel.sql
```

```powershell
# Create theme symlink (Local) — adjust paths
New-Item -ItemType SymbolicLink -Path "<local-site>\app\public\wp-content\themes\kztravel" -Target "<repo>\wordpress-theme"
```

---

## Related docs in this repo

- [`requirements.md`](./requirements.md) — data model and page specs (source of truth for fields)
- [`../README.md`](../README.md) — current static-site content editing guide (replace with wp-admin guide after migration)

---

*Last updated: June 2026*
