<x-layouts.app title="Gallery">

<section class="pt-40 pb-16 lg:pt-48 lg:pb-20 border-b border-sand-200">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <p class="text-xs text-ink/45 mb-5"><a href="{{ route('home') }}" class="hover:text-forest-700">Home</a> / <span class="text-forest-700">Gallery</span></p>
    <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4">Gallery</p>
    <x-ecms.text section="gallery" key="page_title" tag="h1" class="font-extrabold text-3xl sm:text-5xl text-forest-800 max-w-2xl mb-6" />
    <p class="text-[15px] text-ink/70 max-w-xl">
      {{ ecms('gallery.intro_text') }}
      @if ($settings->instagram_url)
        Kunjungi akun Instagram resmi kami
        <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="text-forest-700 underline underline-offset-2 font-semibold">{{ $settings->instagram_handle }}</a>.
      @endif
    </p>
  </div>
</section>

<section class="py-16">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">

    <div class="flex flex-wrap gap-3 mb-12">
      <button type="button" class="filter-btn bg-forest-700 text-sand-50 text-xs font-semibold uppercase tracking-wide px-5 py-2.5" data-filter="all">Semua</button>
      @foreach ($categories as $category)
        <button type="button" class="filter-btn border border-sand-300 text-ink/70 text-xs font-semibold uppercase tracking-wide px-5 py-2.5" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
      @endforeach
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach ($photos as $photo)
        <button type="button"
          class="gallery-card group relative aspect-square overflow-hidden border border-sand-300 bg-sand-100"
          data-category="{{ $photo->category?->slug }}"
          data-lightbox-src="{{ Storage::url($photo->image_path) }}"
          data-lightbox-caption="{{ $photo->caption }}"
          aria-label="Lihat foto: {{ $photo->caption }}">
          <img src="{{ Storage::url($photo->image_path) }}" alt="{{ $photo->caption }}"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
          <span class="absolute inset-x-0 bottom-0 bg-forest-900/75 text-sand-50 text-xs font-semibold px-3 py-2 text-left opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity">
            {{ $photo->caption }}
          </span>
        </button>
      @endforeach
    </div>

    @if ($photos->isEmpty())
      <p class="text-sm text-ink/50">Belum ada foto kegiatan yang ditambahkan.</p>
    @endif

    {{-- Lightbox: dibuka saat kartu galeri diklik (lihat public/js/app.js) --}}
    <div id="galleryLightbox" class="hidden fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Pratinjau foto">
      <div data-lightbox-backdrop class="absolute inset-0 bg-ink/80"></div>
      <div class="relative h-full flex items-center justify-center p-4 sm:p-8">
        <button type="button" data-lightbox-close aria-label="Tutup"
          class="absolute top-4 right-4 sm:top-6 sm:right-6 w-10 h-10 flex items-center justify-center text-sand-50/70 hover:text-sand-50 text-3xl leading-none">&times;</button>
        <figure class="max-w-4xl w-full">
          <img data-lightbox-image src="" alt="" class="w-full max-h-[75vh] object-contain bg-ink/40">
          <figcaption data-lightbox-text class="mt-4 text-center text-sm text-sand-50"></figcaption>
        </figure>
      </div>
    </div>
  </div>
</section>

</x-layouts.app>
