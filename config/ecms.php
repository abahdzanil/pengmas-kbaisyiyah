<?php

use App\Models\Article;
use App\Models\DevelopmentAspect;
use App\Models\Facility;
use App\Models\Feature;
use App\Models\GalleryCategory;
use App\Models\MissionPoint;
use App\Models\Photo;
use App\Models\StaffMember;

/**
 * Registry of every ECMS collection, keyed by its route/resource slug.
 *
 * Adding a brand-new collection to the admin panel should only ever need:
 *   1. a migration for the table (sort_order/status as needed),
 *   2. an Eloquent model with published()/ordered() scopes,
 *   3. one entry here.
 * No changes to AdminCollectionController itself. See docs/ECMS.md.
 *
 * Field shape: ['name' => string, 'type' => text|textarea|number|boolean|date|select|image|repeater, 'rules' => array, 'options' => ?callable]
 *
 * 'image_field' alone is enough for an upload control — the admin form
 * synthesizes the file input from it, so 'fields' needs no image entry.
 *
 * 'repeater' is for a child hasMany relation edited inline in the parent's
 * own form (e.g. a staff member's education history) instead of as its own
 * top-level collection — it needs 'relation' (the parent's Eloquent hasMany
 * method name) and 'child_fields' (same field shape, minus repeater/image).
 * CollectionService re-syncs the whole relation (delete + recreate) on
 * every save, see CollectionService::syncRepeater().
 */
return [
    'collections' => [
        'features' => [
            'model' => Feature::class,
            'label' => 'Keunggulan (Home)',
            'label_field' => 'title',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => null,
            'fields' => [
                ['name' => 'icon', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:40']],
                ['name' => 'title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                ['name' => 'description', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            ],
        ],

        'mission-points' => [
            'model' => MissionPoint::class,
            'label' => 'Misi (About Us)',
            'label_field' => 'content',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => null,
            'fields' => [
                ['name' => 'content', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:300']],
            ],
        ],

        'development-aspects' => [
            'model' => DevelopmentAspect::class,
            'label' => 'Aspek Perkembangan (About Us)',
            'label_field' => 'title',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => null,
            'fields' => [
                ['name' => 'title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                ['name' => 'description', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            ],
        ],

        'facilities' => [
            'model' => Facility::class,
            'label' => 'Fasilitas (About Us)',
            'label_field' => 'title',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => null,
            'fields' => [
                ['name' => 'title', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                ['name' => 'description', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:500']],
            ],
        ],

        'staff' => [
            'model' => StaffMember::class,
            'label' => 'Staf & Tenaga Pendidik (SDM)',
            'label_field' => 'name',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => 'photo_path',
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                ['name' => 'role', 'type' => 'text', 'rules' => ['required', 'string', 'max:120']],
                // No 'initials' field here on purpose — it's only a fallback
                // avatar for when no photo is set, auto-derived from name
                // (StaffMember::booted()). The photo upload control itself
                // is rendered from 'image_field' above.
                [
                    'name' => 'education_histories', 'type' => 'repeater', 'label' => 'Riwayat Pendidikan',
                    'relation' => 'educationHistories',
                    'child_fields' => [
                        ['name' => 'degree', 'type' => 'text', 'label' => 'Jenjang / Gelar', 'rules' => ['required', 'string', 'max:150']],
                        ['name' => 'institution', 'type' => 'text', 'label' => 'Institusi', 'rules' => ['required', 'string', 'max:150']],
                        ['name' => 'year', 'type' => 'text', 'label' => 'Tahun', 'rules' => ['nullable', 'string', 'max:20']],
                    ],
                ],
            ],
        ],

        'gallery-categories' => [
            'model' => GalleryCategory::class,
            'label' => 'Kategori Gallery',
            'label_field' => 'name',
            'has_status' => false,
            'has_sort' => true,
            'image_field' => null,
            'fields' => [
                ['name' => 'name', 'type' => 'text', 'rules' => ['required', 'string', 'max:80']],
                ['name' => 'slug', 'type' => 'text', 'rules' => ['required', 'string', 'max:80', 'alpha_dash']],
            ],
        ],

        'photos' => [
            'model' => Photo::class,
            'label' => 'Foto Gallery',
            'label_field' => 'caption',
            'has_status' => true,
            'has_sort' => true,
            'image_field' => 'image_path',
            'fields' => [
                ['name' => 'caption', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
                [
                    'name' => 'gallery_category_id', 'type' => 'select', 'rules' => ['nullable', 'exists:gallery_categories,id'],
                    'options' => fn () => GalleryCategory::query()->ordered()->pluck('name', 'id'),
                ],
            ],
        ],

        'articles' => [
            'model' => Article::class,
            'label' => 'Artikel',
            'label_field' => 'title',
            'has_status' => true,
            'has_sort' => false,
            'image_field' => 'cover_image',
            'fields' => [
                ['name' => 'title', 'type' => 'text', 'rules' => ['required', 'string', 'max:150']],
                ['name' => 'slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:170', 'alpha_dash']],
                ['name' => 'excerpt', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:300']],
                ['name' => 'body', 'type' => 'richtext', 'rules' => ['required', 'string', 'max:20000']],
                ['name' => 'published_at', 'type' => 'date', 'rules' => ['nullable', 'date']],
                ['name' => 'is_highlight', 'type' => 'boolean', 'rules' => ['sometimes', 'boolean']],
            ],
        ],
    ],
];
