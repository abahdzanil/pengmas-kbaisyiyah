{{-- Generic, config-driven CRUD UI for any resource registered in
     config('ecms.collections'). public/js/admin.js reads data-fields to
     render the list + create/edit form for ANY collection — adding a new
     one to config/ecms.php is enough, no new Blade/JS needed. --}}
@props(['resource'])
@php
    $config = config("ecms.collections.{$resource}");
    $labelOverrides = [
        'icon' => 'Ikon', 'title' => 'Judul', 'description' => 'Deskripsi', 'content' => 'Isi',
        'name' => 'Nama', 'role' => 'Jabatan', 'photo' => 'Foto', 'caption' => 'Keterangan',
        'gallery_category_id' => 'Kategori', 'slug' => 'Slug (URL)', 'excerpt' => 'Ringkasan',
        'body' => 'Isi Artikel', 'published_at' => 'Tanggal Terbit', 'is_highlight' => 'Sorotan (Highlight)',
        'degree' => 'Jenjang / Gelar', 'institution' => 'Institusi', 'year' => 'Tahun',
        'photo_path' => 'Foto', 'image_path' => 'Foto', 'cover_image' => 'Gambar Sampul',
    ];
    $resolveField = function ($field) use ($labelOverrides, &$resolveField) {
        // A field can set its own 'label' in config/ecms.php; otherwise fall
        // back to the override dictionary, then a humanized column name.
        $field['label'] ??= $labelOverrides[$field['name']] ?? \Illuminate\Support\Str::headline($field['name']);
        if (($field['options'] ?? null) instanceof \Closure) {
            $field['options'] = $field['options']();
        }
        if (! empty($field['child_fields'])) {
            $field['child_fields'] = collect($field['child_fields'])->map($resolveField)->values()->all();
        }

        return $field;
    };
    $fields = collect($config['fields'])->map($resolveField)->values();

    // The upload control is derived from 'image_field' rather than needing a
    // second, matching entry in 'fields' — that duplication was silently
    // dropping the file input for collections where only one of the two was
    // set (gallery photos and article covers both shipped without one).
    if ($config['image_field'] && ! $fields->contains(fn ($field) => $field['type'] === 'image')) {
        $fields->push($resolveField([
            'name' => $config['image_field'],
            'type' => 'image',
            'rules' => [],
        ]));
    }
@endphp
<div
  data-collection-manager
  data-resource="{{ $resource }}"
  data-label-field="{{ $config['label_field'] }}"
  data-has-status="{{ $config['has_status'] ? '1' : '0' }}"
  data-has-sort="{{ $config['has_sort'] ? '1' : '0' }}"
  data-image-field="{{ $config['image_field'] }}"
  data-image-hint="{{ \App\Services\MediaService::hint() }}"
  data-fields='@json($fields)'
>
  <div class="flex items-center justify-between mb-5">
    <p class="text-sm text-ink/60">{{ $config['label'] }}</p>
    <button type="button" data-action="create" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-xs font-semibold uppercase tracking-wide px-4 py-2.5">
      Tambah Baru
    </button>
  </div>

  <div data-form-wrap class="hidden border border-sand-300 bg-white p-6 mb-6 space-y-4" data-fields-container></div>

  <div data-list class="space-y-3">
    <p class="text-sm text-ink/40" data-loading>Memuat data&hellip;</p>
  </div>
</div>
