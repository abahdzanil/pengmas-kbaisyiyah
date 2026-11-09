# ECMS — Extensible Content Management System

How the KB 'Aisyiyah 2 Dau site's content is modeled, rendered, and edited
through `/admin`, and the pattern to follow when adding a new section.

## Architecture

Two kinds of content, three moving parts:

| Content type | Storage | Example |
|---|---|---|
| Global (reused on every page) | `site_settings` (single row) | address, phone, Instagram, map embed |
| Single-instance (one page, fixed fields) | `section_contents` (`section`, `key`, `value`, `type`) | Home hero title, About Us visi |
| Collection (repeatable, admin can add/remove) | its own table | Gallery photos, Artikel, Staf SDM |

Rendering reads through:
- `ecms('section.key')` helper (`app/Support/helpers.php`) or the
  `<x-ecms.text>` / `<x-ecms.richtext>` / `<x-ecms.image>` Blade components —
  single-instance fields, cached per-section forever
  (`SectionContent::forSection()`, invalidated on save via a model event).
- Plain Eloquent queries with the `published()`/`ordered()` scopes —
  collections. Public pages only ever see `published()` rows.

Admin writes go through a **generic, config-driven layer** instead of one
controller per collection:

- `config/ecms.php` — the registry. Each collection lists its model, its
  fields (name/type/validation rules), whether it has `sort_order`/`status`,
  and its image field (if any).
- `app/Services/CollectionService.php` — create/update/destroy/reorder/
  setStatus, entirely driven by that config.
- `app/Http/Controllers/Admin/CollectionController.php` — thin, resolves
  `{resource}` from the URL against the registry and calls the service.
- `resources/views/components/admin/collection-manager.blade.php` — resolves
  the same config (including `select` options) into a JSON schema.
- `public/js/admin.js`'s `CollectionManager` class — reads that schema and
  renders the list + create/edit form for **any** collection.

## Adding a new single-instance field to an existing page

1. Add a row for it in `database/seeders/EcmsSeeder.php` (`putSection()`
   call for that page's section), with a `type` of `text`, `richtext`, or
   `image`.
2. Render it with `<x-ecms.text section="..." key="..." tag="p" class="..." />`
   (or `.richtext` / `.image`) in the page's Blade view.
3. Add `<x-admin.field section="..." key="..." label="..." type="..." />`
   to that section's panel in `resources/views/admin/dashboard.blade.php`.
4. Run `php artisan migrate:fresh --seed` locally to pick up the new key.

No controller, route, or JS change needed — `admin.js`'s per-panel
"Simpan Perubahan" button already PATCHes every `[data-ecms-content]` field
it finds, and image fields upload immediately on change.

## Adding a brand-new collection

1. **Migration**: a table with at minimum the fields you need, plus
   `sort_order` (if it should be reorderable) and/or `status` (if it should
   support draft/published) and `SoftDeletes` if you want soft deletes.
2. **Model**: `#[Fillable([...])]`, `use SoftDeletes` if applicable, and
   `scopePublished()` / `scopeOrdered()` matching the existing collections
   (e.g. `app/Models/Facility.php`) if the table has those columns.
3. **Registry entry** in `config/ecms.php`:
   ```php
   'your-resource-slug' => [
       'model' => \App\Models\YourModel::class,
       'label' => 'Human label for the admin panel',
       'label_field' => 'title', // which column identifies a row in the list
       'has_status' => true,
       'has_sort' => true,
       'image_field' => null, // or the column name that holds the image path
       'fields' => [
           ['name' => 'title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
           ['name' => 'description', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
           // type can be: text, textarea, richtext, boolean, date, select, image, repeater
           // 'select' also needs 'options' => fn () => SomeModel::pluck('name', 'id'),
           // 'repeater' is for a child hasMany edited inline — see below.
       ],
   ],
   ```
4. **Wire it into a page** — query it in the relevant `PageController`
   method and loop over it in the Blade view (see `PageController::gallery()`
   + `resources/views/pages/gallery.blade.php` for the pattern).
5. **Wire it into the admin panel** — one line in
   `resources/views/admin/dashboard.blade.php`:
   `<x-admin.collection-manager resource="your-resource-slug" />`.

That's it — no new controller, route, FormRequest, or JS. The route group
in `routes/web.php` already exposes
`GET/POST /admin/{resource}`, `PUT|PATCH/DELETE /admin/{resource}/{id}`,
`POST /admin/{resource}/reorder`, and `PATCH /admin/{resource}/{id}/status`
for every registry key.

## Adding a child list edited inline on a parent's form (a 'repeater' field)

Sometimes a child collection makes more sense edited *inside* its parent's
own form than as its own top-level collection — e.g. a staff member's
education history (`education_histories`, belongsTo `StaffMember`). Add it
as a `repeater` field on the parent instead of a separate registry entry:

```php
'staff' => [
    // ...existing fields...
    'fields' => [
        // ...name, role, photo...
        [
            'name' => 'education_histories', // must match the relation's JSON key (snake_case of the method name)
            'type' => 'repeater',
            'label' => 'Riwayat Pendidikan',
            'relation' => 'educationHistories', // the parent's hasMany method name
            'child_fields' => [
                ['name' => 'degree', 'type' => 'text', 'label' => 'Jenjang / Gelar', 'rules' => ['required', 'string', 'max:150']],
                ['name' => 'institution', 'type' => 'text', 'label' => 'Institusi', 'rules' => ['required', 'string', 'max:150']],
                ['name' => 'year', 'type' => 'text', 'label' => 'Tahun', 'rules' => ['nullable', 'string', 'max:20']],
            ],
        ],
    ],
],
```

Requirements: the parent model needs a `hasMany` relation method matching
`relation`, and the child model needs its own migration + `#[Fillable]`
(it doesn't need `published()`/`ordered()` scopes or its own registry entry
— it's never queried as a standalone collection). `CollectionService`
re-syncs the whole relation on every parent save: it deletes the existing
children and recreates them from whatever rows were submitted (simplest
correct approach for small child lists; the admin form always resubmits
the full set). If the parent uses `SoftDeletes`, deleting the parent from
the admin panel is a *soft* delete, so a DB-level `cascadeOnDelete()` FK
on the child table won't fire — the children are deliberately left intact
(and correct again if the parent is restored); only `forceDelete()` on the
parent actually cascades. `public/js/admin.js`'s `CollectionManager`
renders the add/remove-row UI for any `repeater` field from this config
alone — no new JS needed either.

## Media uploads

`app/Services/MediaService.php` validates (jpeg/jpg/png/webp/gif only, no
SVG — it isn't raster-safe without an XML sanitize pass this project
doesn't have — max 2 MB), stores under a randomized UUID filename on the
`public` disk, and deletes the old file when an image is replaced. Files
are served from `storage/app/public` via the `public/storage` symlink
(`php artisan storage:link`).

## Security

- **Rich text is sanitized on save**, not on output. Any field of type
  `richtext` (single-instance `section_contents` rows, or a collection
  field) goes through `app/Services/RichTextSanitizer.php`
  (`mews/purifier`, strict allow-list in `config/purifier.php` — no
  `img`/`style`/`iframe`/`class`/`id`). Plain `text`/`textarea` fields are
  never treated as HTML anywhere, so Blade's `{{ }}` escaping is enough for
  them.
- **Auth**: session-based, gated by the `admin` route middleware
  (`app/Http/Middleware/EnsureUserIsAdmin.php`), which checks the
  `access-admin` Gate defined in `AppServiceProvider` (`users.is_admin`).
- **Rate limiting**: `/login` (10/min route + a 5-attempt-per-email+IP
  lockout in `LoginRequest`), `/contact` (5/min), and the whole `/admin/*`
  group (300/min — generous for the dashboard's own multi-panel traffic,
  enough to blunt scripted abuse).
- **Mass assignment**: every model declares `#[Fillable([...])]` explicitly.

## Email (SMTP) — notifikasi formulir Contact Us

Submitting the public Contact Us form stores the message (visible under
"Pesan Masuk") *and* emails the school. The recipient is the address in
site_settings, editable under "Kelola Contact" — not a hardcoded value —
and Reply-To is set to the visitor, so replying from the inbox reaches
them directly.

Fill these in `.env`, then set `MAIL_MAILER=smtp`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com     # provider's SMTP host
MAIL_PORT=587
MAIL_USERNAME=alamat-pengirim@gmail.com
MAIL_PASSWORD=app-password   # Gmail: an App Password, not the account password
MAIL_FROM_ADDRESS="alamat-pengirim@gmail.com"   # Gmail requires this to match MAIL_USERNAME
```

`MAIL_MAILER=log` (the default while credentials are unset) writes the
rendered email to `storage/logs/laravel.log` instead of sending — useful
for checking the template without a real mailbox.

A transport failure (wrong credentials, provider down) is caught and
logged: the message is still saved and the visitor still gets the success
message, because the enquiry is already safe in the database and a
mail problem isn't something they can act on. Check
`storage/logs/laravel.log` for "Gagal mengirim notifikasi pesan kontak"
if mail silently isn't arriving.

Note for tests: `Mail::fake()` does not render the mail template, so a
broken mail view passes the suite. Verify the rendered output through the
`log` mailer when changing `resources/views/mail/`.

## Default admin account

Seeded by `EcmsSeeder`:

```
email:    admin@dau.com
password: password
```

**Change this password before deploying to production.**

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

(`composer run setup` does all of the above except copying `.env`, which
it also handles.) There is no frontend build step — Tailwind and Google
Fonts are loaded from a CDN (see `resources/views/partials/head-assets.blade.php`),
and `public/js/app.js` / `public/js/admin.js` are served as plain static
files.

## Testing

```bash
php artisan test
```

- `tests/Feature/PublicPagesTest.php` — every public route renders,
  draft collection rows are hidden from public pages, the contact form
  stores a message, article routing (published/draft/unknown slug).
- `tests/Feature/Admin/AdminBackendTest.php` — auth gate (guest/non-admin/
  admin), dashboard render, single-instance content update, full
  collection CRUD + status + reorder + image upload/replace, unknown
  collection 404, article auto-slug, settings update, message inbox.
- `tests/Feature/SecurityTest.php` — XSS payloads stripped from both
  richtext write paths, disallowed-mime/oversized upload rejection,
  login/contact rate limiting, the admin group's throttle middleware.

Run `php artisan migrate:fresh --seed` before comparing the public site
against the `proto/` design by eye — the seeded content is a 1:1 port of
what was hardcoded there.
