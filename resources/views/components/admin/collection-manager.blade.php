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
  data-label="{{ $config['label'] }}"
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

  {{-- Add/edit form, shown as a modal over the dashboard --}}
  <div data-form-modal class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
    <div data-modal-backdrop class="absolute inset-0 bg-ink/50"></div>
    <div class="relative h-full overflow-y-auto flex items-start sm:items-center justify-center p-4 sm:p-6">
      <div class="relative w-full max-w-2xl bg-white border border-sand-300 shadow-xl my-4">
        <div class="flex items-center justify-between gap-4 border-b border-sand-200 px-6 py-4">
          <p data-modal-title class="text-[12px] font-semibold uppercase tracking-[0.18em] text-forest-800"></p>
          <button type="button" data-action="close" aria-label="Tutup"
            class="shrink-0 w-7 h-7 flex items-center justify-center text-ink/40 hover:text-forest-800 text-xl leading-none">&times;</button>
        </div>

        <div data-fields-container class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto"></div>

        <div class="flex items-center gap-3 border-t border-sand-200 px-6 py-4">
          <button type="button" data-action="submit"
            class="bg-forest-700 hover:bg-forest-800 text-sand-50 text-xs font-semibold uppercase tracking-wide px-5 py-2.5"></button>
          <button type="button" data-action="cancel"
            class="text-xs font-semibold uppercase tracking-wide text-ink/50 hover:text-ink/80">Batal</button>
        </div>
      </div>
    </div>
  </div>

  <div data-list class="space-y-3">
    <p class="text-sm text-ink/40" data-loading>Memuat data&hellip;</p>
  </div>
</div>
