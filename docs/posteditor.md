# Trip post editor — implementation guide

> **For agents:** This doc is self-contained. Read [Agent briefing](#agent-briefing), pick an [approach](#which-approach-to-implement), then follow that approach’s playbook end-to-end. Update the [decision log](#decision-log) when you start and finish.

---

## Table of contents

1. [Agent briefing](#agent-briefing)
2. [Which approach to implement](#which-approach-to-implement)
3. [Data model reference](#data-model-reference)
4. [Approach A — Disable Gutenberg only](#approach-a--disable-gutenberg-only)
5. [Approach B — Clean classic screen](#approach-b--clean-classic-screen)
6. [Approach C+ — Unified SCF form (recommended)](#approach-c--unified-scf-form-recommended)
7. [Approach D — Custom admin page](#approach-d--custom-admin-page)
8. [Approaches not to implement](#approaches-not-to-implement)
9. [Decision log](#decision-log)

---

## Agent briefing

### Problem

The `trip` CPT uses the **block editor** by default. Editors only need a structured form (title + description + hero + taxonomies + repeaters). Gutenberg is the wrong UI.

### Product goals (in priority order)

1. **Polished editor UX** for agency staff (Bulgarian labels, one cohesive form).
2. **Greenfield site** — no legacy posts; storage choices are free.
3. **Theme-owned code** — SCF field defs in `acf-fields.php`, not third-party UI plugins.

### Default recommendation

Implement **Approach C+** unless the user explicitly asked for something else.

### Repo layout (relevant paths)

| Path | Purpose |
|------|---------|
| `kztravelwp/inc/post-types.php` | CPT registration (`trip`, taxonomies) |
| `kztravelwp/inc/acf-fields.php` | SCF field groups (trip schema lives here) |
| `kztravelwp/inc/trip-utils.php` | `kztravel_enrich_trip()` — frontend data assembly |
| `kztravelwp/inc/trip-meta-boxes.php` | Fallback admin UI when SCF inactive; reference for Approach D |
| `kztravelwp/inc/admin-trip-editor.php` | **Create this** for A/B/C+ admin hooks |
| `kztravelwp/assets/css/trip-edit-admin.css` | **Create this** for C+ layout polish |
| `kztravelwp/assets/css/trip-meta-admin.css` | Existing styles for custom meta-box tables |
| `kztravelwp/assets/js/trip-meta-boxes.js` | Repeater JS for Approach D |
| `kztravelwp/tools/import-content.php` | YAML → WP import CLI |
| `kztravelwp/functions.php` | Requires `inc/*.php` files |
| `docs/wordpress-migration.md` §11 | Editor workflow doc (update after shipping) |

### Prerequisites

- **Secure Custom Fields (SCF)** plugin active in wp-admin (ACF-compatible API). Do not install ACF alongside SCF.
- Local WP with theme symlinked or copied (see `docs/wordpress-migration.md` §5).
- For manual verification: wp-admin → **Екскурзии → Добави**.

### What not to break

- Frontend trip pages (`single-trip.php`) consume `kztravel_enrich_trip()` — keep its return shape stable.
- Taxonomy slugs `trip_country` / `trip_category` are used in filters and URLs.
- `set_object_terms` hook in `post-types.php` enforces single country per trip — keep working.
- Import CLI: `wp eval-file kztravelwp/tools/import-content.php` (or project’s documented command).

---

## Which approach to implement

| Approach | Polish | Effort | Pick when |
|----------|--------|--------|-----------|
| **C+** Unified SCF form | Very good | ~1 day | **Default.** Greenfield, polish priority. |
| **D** Custom admin page | Excellent | 2–4 days | C+ repeaters still feel wrong; want `trip-meta-boxes.php` table UI. |
| **B** Clean classic screen | Good (ceiling) | ~0.5 day | Same-day stopgap while C+ is in progress. |
| **A** Disable Gutenberg only | OK | ~1 hour | Emergency patch only. |

```
                    ┌─────────────────────────────────────┐
                    │  User wants trip editor improved?   │
                    └─────────────────┬───────────────────┘
                                      │
              ┌───────────────────────┼───────────────────────┐
              │                       │                       │
              ▼                       ▼                       ▼
     Need it today only?      Default path            C+ not enough?
              │                       │                       │
              ▼                       ▼                       ▼
         Approach A              Approach C+            Approach D
         (or B if a few hrs)     (recommended)          (custom page)
```

**Do not implement** Approach C (description stays in `post_content`) — it is a worse C+; see [Approaches not to implement](#approaches-not-to-implement).

---

## Data model reference

### Current storage (before any approach)

| Data | Storage today | Read by |
|------|---------------|---------|
| Trip name | `post_title` | `kztravel_enrich_trip()` → `name` |
| Description | `post_content` | `description`, `description_plain` |
| Hero image | `_thumbnail_id` (featured image) | `hero_url` via `get_the_post_thumbnail_url()` |
| Country | `trip_country` taxonomy | `country` (first term slug) |
| Categories | `trip_category` taxonomy | `category` (term slugs) |
| Duration, dates, gallery, itinerary, included, excluded | SCF meta | `kztravel_get_trip_field()` |

CPT today (`post-types.php`):

```php
'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
'show_in_rest' => true,
```

### Target storage (Approach C+ and D)

| Data | Storage |
|------|---------|
| Trip name | `post_title` only |
| Description | SCF `trip_description` |
| Hero image | SCF `trip_hero` + synced `_thumbnail_id` |
| Country / categories | Taxonomies (via SCF taxonomy fields in C+, or `wp_set_object_terms` in D) |
| Everything else | Unchanged SCF field names |

### SCF field names (do not rename without updating theme + import)

| Field name | Type | Frontend key |
|------------|------|--------------|
| `trip_description` | textarea | `description_plain` |
| `trip_hero` | image | `hero_url` (via thumbnail sync) |
| `trip_duration` | text | `duration` |
| `trip_dates` | repeater | `dates` |
| `trip_gallery` | gallery | `gallery` |
| `trip_itinerary` | repeater | `itinerary` |
| `trip_included` | repeater | `included` |
| `trip_excluded` | repeater | `excluded` |

Date repeater sub-fields: `date`, `price`, `price_bgn`, `discounted_price`, `discounted_price_bgn`, `status` (`available` | `lastSpots` | `soldout`).

Itinerary sub-fields: `day`, `title`, `body`.

### Tab layout (C+ and optionally D)

| Tab label | Fields |
|-----------|--------|
| Основни | description, hero, country, categories, duration |
| Дати и цени | `trip_dates` |
| Галерия | `trip_gallery` |
| Маршрут | `trip_itinerary` |
| Услуги | `trip_included`, `trip_excluded` |

---

## Approach A — Disable Gutenberg only

**Status:** Emergency fallback. Do not ship as final solution.

### When to use

- Need block editor gone in under an hour.
- C+ / B will follow immediately.

### Files to create or modify

| File | Action |
|------|--------|
| `kztravelwp/inc/admin-trip-editor.php` | Create |
| `kztravelwp/functions.php` | Add `require_once` |

### Implementation checklist

- [ ] Create `inc/admin-trip-editor.php` with Gutenberg disable filter (see snippet below).
- [ ] `require_once KZTRAVEL_DIR . '/inc/admin-trip-editor.php';` in `functions.php` (after `post-types.php`).
- [ ] Set `'show_in_rest' => false` on `trip` in `post-types.php` (optional but recommended with classic editor).

### Code — `inc/admin-trip-editor.php`

```php
<?php
defined( 'ABSPATH' ) || exit;

add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'trip' === $post_type ? false : $use;
	},
	10,
	2
);
```

### Verification

- [ ] **Екскурзии → Add New** shows classic textarea, not block editor.
- [ ] Saving a trip still works; frontend unchanged.

### Out of scope

Do not reorganize ACF, change storage, or add CSS.

---

## Approach B — Clean classic screen

**Status:** Acceptable stopgap. Not the target end state.

### When to use

- Need visible improvement today (~half day).
- C+ is scheduled next.

### Files to create or modify

| File | Action |
|------|--------|
| `kztravelwp/inc/admin-trip-editor.php` | Create (extends A) |
| `kztravelwp/inc/post-types.php` | Remove `excerpt` from supports; `show_in_rest => false` |
| `kztravelwp/inc/acf-fields.php` | Add tabs; set field group position/style |
| `kztravelwp/assets/css/trip-edit-admin.css` | Create (optional layout tweaks) |
| `kztravelwp/functions.php` | Require admin-trip-editor |

### Implementation checklist

- [ ] Complete [Approach A](#approach-a--disable-gutenberg-only) steps.
- [ ] In `post-types.php`: `'supports' => array( 'title', 'editor', 'thumbnail' )`, `'show_in_rest' => false`.
- [ ] In `admin-trip-editor.php`, hide meta boxes:

```php
add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'slugdiv', 'trip', 'normal' );
		remove_meta_box( 'postcustom', 'trip', 'normal' );
	},
	99
);
```

- [ ] In `acf-fields.php`, on field group `group_kztravel_trip_details`:
  - Add `'position' => 'acf_after_title'`
  - Add `'style' => 'seamless'`
  - Add `'label_placement' => 'top'`
  - Insert `tab` fields before each section (duration stays under first tab „Основни“; description remains core editor above tabs).
- [ ] Enqueue `trip-edit-admin.css` on `post.php` / `post-new.php` for `trip` (copy enqueue block from [C+ snippet](#step-3--create-admintrip-editorphp)).
- [ ] CSS: widen main column; optional hide `#postbox-container-1` on large screens.

### Verification

- [ ] No excerpt box.
- [ ] No slug box.
- [ ] ACF fields appear directly under title in tabs.
- [ ] Description still in post content; `description_plain` on frontend still works.
- [ ] Featured image meta box still in sidebar.

### Out of scope

- Do not add `trip_description` or move taxonomies into SCF (that is C+).

---

## Approach C+ — Unified SCF form (recommended)

**Status:** Recommended default implementation.

### Goal

One SCF field group = entire editor. CPT supports **title only**. Description, hero, and taxonomies live in SCF. Hero syncs to featured thumbnail so frontend code can keep using `get_the_post_thumbnail_url()`.

### Files to create or modify

| File | Action |
|------|--------|
| `kztravelwp/inc/post-types.php` | `supports => array('title')`, `show_in_rest => false` |
| `kztravelwp/inc/admin-trip-editor.php` | **Create** — Gutenberg off, hide meta boxes, hero sync, CSS enqueue |
| `kztravelwp/inc/acf-fields.php` | Restructure trip field group (new fields + tabs + group metadata) |
| `kztravelwp/inc/trip-utils.php` | Read `trip_description` instead of `post_content` |
| `kztravelwp/tools/import-content.php` | Write `trip_description` + `trip_hero`; clear `post_content` |
| `kztravelwp/assets/css/trip-edit-admin.css` | **Create** — full-width form layout |
| `kztravelwp/functions.php` | `require_once` admin-trip-editor |
| `docs/wordpress-migration.md` §11 | Update editor workflow table |
| `docs/posteditor.md` decision log | Mark C+ implemented |

Optional cleanup (ask user or do if confident):

| File | Action |
|------|--------|
| `kztravelwp/inc/trip-meta-boxes.php` | Remove require from `functions.php` if SCF is mandatory in prod |

### Step 1 — `post-types.php`

Change registration:

```php
'supports'     => array( 'title' ),
'show_in_rest' => false,
```

Keep existing labels, `enter_title_here` filter, and `set_object_terms` single-country hook unchanged.

### Step 2 — `acf-fields.php` field group

On `group_kztravel_trip_details`, add group-level keys:

```php
'position'        => 'acf_after_title',
'style'           => 'seamless',
'label_placement' => 'top',
```

Replace the `fields` array order with:

1. **Tab** `Основни` (`field_trip_tab_main`)
2. **New** `trip_description` — textarea, 4 rows, instructions: „Кратко описание за hero секцията на страницата.“, `new_lines => ''`
3. **New** `trip_hero` — image, `return_format => array`, `preview_size => medium`
4. **New** `trip_country` — taxonomy `trip_country`, `field_type => select`, `return_format => id`, `add_term => 1`
5. **New** `trip_category` — taxonomy `trip_category`, `field_type => multi_select`, `return_format => id`, `add_term => 1`
6. Existing `trip_duration`
7. **Tab** `Дати и цени` → existing `trip_dates` repeater
8. **Tab** `Галерия` → existing `trip_gallery`
9. **Tab** `Маршрут` → existing `trip_itinerary`
10. **Tab** `Услуги` → existing `trip_included`, `trip_excluded`

Use unique `key` values prefixed with `field_trip_`. Full field definitions are in the [data model](#data-model-reference) section above.

**Note:** SCF taxonomy fields write to the same taxonomies as `wp_set_object_terms`. `kztravel_enrich_trip()` reads terms via `wp_get_post_terms` — no change needed for country/category on frontend.

### Step 3 — Create `admin-trip-editor.php`

```php
<?php
defined( 'ABSPATH' ) || exit;

add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return 'trip' === $post_type ? false : $use;
	},
	10,
	2
);

add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'slugdiv', 'trip', 'normal' );
		remove_meta_box( 'postcustom', 'trip', 'normal' );
		remove_meta_box( 'postimagediv', 'trip', 'side' );
		remove_meta_box( 'trip_countrydiv', 'trip', 'side' );
		remove_meta_box( 'trip_categorydiv', 'trip', 'side' );
	},
	99
);

add_filter(
	'acf/update_value/name=trip_hero',
	function ( $value, $post_id ) {
		if ( 'trip' !== get_post_type( $post_id ) ) {
			return $value;
		}
		$attachment_id = is_array( $value ) ? (int) ( $value['ID'] ?? 0 ) : (int) $value;
		if ( $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		} else {
			delete_post_thumbnail( $post_id );
		}
		return $value;
	},
	10,
	2
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || 'trip' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_style(
			'kztravel-trip-edit-admin',
			KZTRAVEL_URI . '/assets/css/trip-edit-admin.css',
			array(),
			KZTRAVEL_VERSION
		);
	}
);
```

Add to `functions.php` after `post-types.php`:

```php
require_once KZTRAVEL_DIR . '/inc/admin-trip-editor.php';
```

### Step 4 — `trip-utils.php`

In `kztravel_enrich_trip()`, replace description lines:

```php
// Before
'description'       => apply_filters( 'the_content', $post->post_content ),
'description_plain' => wp_strip_all_tags( $post->post_content ),

// After
$description = (string) kztravel_get_trip_field( $post_id, 'trip_description', '' );
'description'       => apply_filters( 'the_content', $description ),
'description_plain' => wp_strip_all_tags( $description ),
```

Leave `hero_url` as `get_the_post_thumbnail_url( $post_id, 'large' )` — hero sync hook maintains thumbnail.

### Step 5 — `import-content.php`

In the trip import loop:

```php
// wp_insert_post args — use empty content
'post_content' => '',

// After insert, replace relying on post_content for description:
kztravel_update_trip_field( $post_id, 'trip_description', trim( $trip['description'] ?? '' ) );

// After hero sideload (existing block ~lines 264–269), also set SCF field:
if ( ! empty( $hero_id ) ) {
	kztravel_update_trip_field( $post_id, 'trip_hero', $hero_id );
}
```

Keep `wp_set_object_terms` for country/category OR rely on SCF-only if you switch import to set taxonomy fields via `update_field` — either works; `wp_set_object_terms` is fine and already tested.

### Step 6 — `trip-edit-admin.css`

Create minimal layout polish:

```css
/* Single-column trip edit — main form uses full width */
.post-type-trip #post-body.columns-2 #postbox-container-1 {
	display: none;
}

.post-type-trip #post-body.columns-2 #post-body-content {
	margin-right: 0;
}

.post-type-trip #post-body-content .metabox-holder {
	padding-top: 0;
}
```

Adjust if publish box should remain visible in a slim sidebar — product choice; default above hides empty sidebar.

### Step 7 — Verification checklist

**Admin**

- [ ] **Екскурзии → Добави** — no block editor, no excerpt, no featured-image sidebar box, no taxonomy sidebar boxes.
- [ ] Title + tabbed SCF form: Основни → Дати → Галерия → Маршрут → Услуги.
- [ ] Save draft / publish works without PHP notices.
- [ ] Hero image in SCF appears on frontend trip page.
- [ ] Country (single) and categories save correctly; country still limited to one term.

**Frontend**

- [ ] Trip detail page shows description, hero, dates, gallery, itinerary, inclusions.
- [ ] Homepage trip cards show correct data (filters use country, category, price from enriched trip).
- [ ] Re-import or edit does not blank existing fields.

**Import**

- [ ] Run import CLI; spot-check one trip: `trip_description`, thumbnail, gallery, dates populated.

**Regression**

- [ ] `kztravel_enrich_trip()` return keys unchanged (consumers: `single-trip.php`, `trip-card.php`, `filter-bar.php`, `filters.js` data attributes).

### Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| Block editor still shows | Cache or filter priority | Confirm `use_block_editor_for_post_type` runs; `show_in_rest` false |
| No SCF fields | SCF inactive | Activate Secure Custom Fields plugin |
| Hero missing on frontend | Sync hook not firing | Save trip again; check `trip_hero` attachment ID; verify `set_post_thumbnail` |
| Country not saving | Taxonomy field name mismatch | Field `name` must be `trip_country`; taxonomy slug `trip_country` |
| Duplicate field UI | `trip-meta-boxes.php` active without SCF detection | SCF must be active; meta boxes only show when `! kztravel_uses_acf_trip_fields()` |

---

## Approach D — Custom admin page

**Status:** Implement only if C+ is insufficient or user explicitly requests maximum custom UI.

### Goal

Replace `post.php` for trips with theme-owned page `kztravel-trip-edit`. Reuse HTML/JS/CSS from `trip-meta-boxes.php`.

### Architecture decision (pick one)

| Storage backend | Editor UI | Notes |
|-----------------|-----------|-------|
| **D1 — SCF backend** | Custom form calls `update_field()` | Schema stays in `acf-fields.php`; disable SCF field group location for `trip` so fields don’t render twice |
| **D2 — Meta backend** | Custom form calls `kztravel_update_trip_field()` | Can drop SCF trip group entirely; `trip-meta-boxes.php` save logic is the reference |

**Recommended:** D1 if SCF is already deployed; reuse `kztravel_update_trip_field()` wrapper in save handler.

### Files to create or modify

| File | Action |
|------|--------|
| `kztravelwp/inc/admin-trip-edit-page.php` | **Create** — menu registration, render, save, redirects |
| `kztravelwp/inc/admin-trip-editor.php` | Redirects only (or merge into edit-page file) |
| `kztravelwp/assets/css/trip-edit-page.css` | **Create** — page layout wrapper |
| `kztravelwp/inc/trip-meta-boxes.php` | Refactor: extract render functions for reuse (optional) |
| `kztravelwp/functions.php` | Require new file |
| All C+ data-layer files | Same `trip_description` / hero sync if using unified storage |

### Implementation checklist

#### Phase 1 — Routing

- [ ] Register submenu page:

```php
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'edit.php?post_type=trip',
			'Редактирай екскурзия',
			'—', // hidden from menu with null parent trick, or omit menu title
			'edit_posts',
			'kztravel-trip-edit',
			'kztravel_render_trip_edit_page'
		);
	}
);
```

Use `add_submenu_page` with slug only accessed via redirect (common pattern: register with same capability as `edit_posts`).

- [ ] Redirect default edit URLs:

```php
add_filter(
	'get_edit_post_link',
	function ( $link, $post_id ) {
		if ( 'trip' === get_post_type( $post_id ) ) {
			return admin_url( 'edit.php?post_type=trip&page=kztravel-trip-edit&post=' . (int) $post_id );
		}
		return $link;
	},
	10,
	2
);

add_action(
	'load-post-new.php',
	function () {
		if ( isset( $_GET['post_type'] ) && 'trip' === $_GET['post_type'] ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=trip&page=kztravel-trip-edit' ) );
			exit;
		}
	}
);
```

- [ ] List table “Add New” link: filter `post_type_link` or `admin_url` for `post-new.php?post_type=trip` → custom page.

#### Phase 2 — Render form

- [ ] Page reads `post` query arg; `0` or missing = new trip.
- [ ] Load data via `get_post`, `kztravel_get_trip_field()`, `wp_get_post_terms`.
- [ ] Output form sections matching [tab layout](#tab-layout-c-and-optionally-d):
  - Reuse `kztravel_render_trip_details_meta_box()` internals OR include `trip-meta-boxes.php` render functions.
  - Add fields for title, description, hero (media button), country select, category multi-select.
- [ ] Enqueue `trip-meta-admin.css`, `trip-meta-boxes.js`, `trip-edit-page.css`.
- [ ] Nonce field: `wp_nonce_field( 'kztravel_save_trip_edit', 'kztravel_trip_edit_nonce' )`.

#### Phase 3 — Save handler

On POST:

- [ ] Verify nonce, `current_user_can( 'edit_post', $post_id )` (or `edit_posts` for new).
- [ ] Sanitize title → `wp_insert_post` / `wp_update_post` with `post_type => trip`, `post_status` from submit button.
- [ ] Save description → `kztravel_update_trip_field( $id, 'trip_description', ... )`.
- [ ] Save hero attachment ID → `kztravel_update_trip_field( $id, 'trip_hero', $id )` + `set_post_thumbnail`.
- [ ] Save taxonomies → `wp_set_object_terms` (country max 1 — consistent with `post-types.php` hook).
- [ ] Save repeaters — copy sanitization from `save_post_trip` in `trip-meta-boxes.php` (lines 70–153).
- [ ] Redirect with `&updated=1` message; handle `wp_safe_redirect` after save.

#### Phase 4 — Polish

- [ ] Top bar: title input, Publish / Save draft / Preview (preview link: `get_preview_post_link()`).
- [ ] Delete / trash link for existing posts.
- [ ] Bulgarian validation messages for required title.

#### Phase 5 — Disable duplicate UIs

- [ ] If using SCF storage: remove `trip` from SCF field group `location` rules OR hide group via `acf/load_field_group` for trip post type.
- [ ] Do **not** show both SCF meta boxes and custom page.

### Verification checklist

- [ ] All list-table edit links open custom page.
- [ ] Add new trip flow works end-to-end.
- [ ] Media modal picks hero and gallery images.
- [ ] Preview opens correct trip URL.
- [ ] Capabilities: user with Editor role can save; unauthorized users cannot.
- [ ] Frontend identical to C+ for same data.
- [ ] No JavaScript errors on repeater add/remove rows.

### Escape hatch

Keep `post.php` reachable for developers via direct URL `post.php?post=ID&action=edit` — do not block, only redirect default links.

---

## Approaches not to implement

| Approach | Reason |
|----------|--------|
| **C** — Unified tabs but description in `post_content` | Split form; worse than C+ for same effort on greenfield |
| **E** — Gutenberg locked template | Heavy; frontend ignores blocks |
| **F** — Meta Box / Pods / Toolset | Duplicates `acf-fields.php` |
| JSON blob in `post_content` | Breaks WP conventions |
| Two parallel UIs (SCF screen + custom page) | Double maintenance |

---

## Decision log

| Date | Agent / note | Status |
|------|--------------|--------|
| 2025-06-14 | Initial analysis | Options documented |
| 2025-06-14 | Greenfield + polish priority | **C+ recommended** |
| 2026-06-14 | Approach C+ implemented | Unified SCF form, hero sync, import + `trip-utils` updated |

**When you implement:** add a row with approach chosen (A/B/C+/D), PR or commit ref, and date completed.

---

## Post-ship updates (any approach)

After merging trip editor work, update:

1. `docs/wordpress-migration.md` §11 — table of “where to edit what” in wp-admin.
2. This file’s decision log.
3. If C+ or D: remove references to “post content description” / “Featured image sidebar” from editor docs.
