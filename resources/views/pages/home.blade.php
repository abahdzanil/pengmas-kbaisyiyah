<x-layouts.app>

<!-- ============ HERO ============ -->
<section class="pt-40 pb-24 lg:pt-48 lg:pb-32 border-b border-sand-200">
  <div class="max-w-6xl mx-auto px-6 lg:px-8 grid lg:grid-cols-12 gap-14 items-center">

    <div class="lg:col-span-7">
      <x-ecms.text section="home" key="hero_eyebrow" tag="p" class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-5" />
      <x-ecms.text section="home" key="hero_title" tag="h1" class="font-extrabold text-[2.4rem] leading-[1.12] sm:text-5xl sm:leading-[1.1] text-forest-800 mb-6" />
      <x-ecms.richtext section="home" key="hero_subtitle" tag="p" class="text-[17px] leading-relaxed text-ink/80 max-w-xl mb-10" />

      <div class="flex flex-wrap items-center gap-4 mb-14">
        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
          {{ ecms('home.hero_button1_label', 'Hubungi Kami') }}
        </a>
        <a href="{{ route('about') }}" class="inline-flex items-center gap-2 border border-forest-700 text-forest-800 hover:bg-forest-700 hover:text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
          {{ ecms('home.hero_button2_label', 'Tentang Kami') }}
        </a>
      </div>

      <dl class="grid grid-cols-3 gap-6 max-w-lg border-t border-sand-200 pt-8">
        <div>
          <dt class="text-[11px] uppercase tracking-wide text-ink/50 mb-1">Usia Anak</dt>
          <dd class="font-bold text-forest-800">{{ ecms('home.stat_usia_value') }}</dd>
        </div>
        <div>
          <dt class="text-[11px] uppercase tracking-wide text-ink/50 mb-1">Naungan</dt>
          <dd class="font-bold text-forest-800">{{ ecms('home.stat_naungan_value') }}</dd>
        </div>
        <div>
          <dt class="text-[11px] uppercase tracking-wide text-ink/50 mb-1">Lokasi</dt>
          <dd class="font-bold text-forest-800">{{ ecms('home.stat_lokasi_value') }}</dd>
        </div>
      </dl>
    </div>

    <div class="lg:col-span-5">
      <div class="relative">
        <div class="border border-sand-300 bg-white p-5">
          <x-ecms.image section="home" key="hero_image" alt="Hero" class="aspect-[4/5] w-full object-cover">
            <div class="aspect-[4/5] bg-forest-100 border border-forest-200 flex flex-col items-center justify-center gap-3 text-center px-6">
              <x-icon name="logo" :size="30" stroke="#264a34" :stroke-width="'1.4'" />
              <p class="text-[11px] uppercase tracking-[0.14em] text-forest-700 font-semibold">Ruang Bermain &amp; Belajar</p>
              <p class="text-xs text-forest-600">{{ $settings->site_name ?? "KB 'Aisyiyah 2 Dau" }}</p>
            </div>
          </x-ecms.image>
        </div>
        <div class="hidden sm:block absolute -bottom-8 -left-8 w-40 border border-sand-300 bg-white p-3 shadow-sm">
          <div class="aspect-square bg-sand-200 border border-sand-300 flex flex-col items-center justify-center gap-2 text-center px-3">
            <x-icon name="camera" :size="20" stroke="#9c7838" />
            <p class="text-[10px] uppercase tracking-wide text-sand-700 font-semibold">Kegiatan Harian</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ============ KEUNGGULAN ============ -->
<section class="py-24 border-b border-sand-200">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="max-w-2xl mb-14">
      <x-ecms.text section="home" key="keunggulan_eyebrow" tag="p" class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4" />
      <x-ecms.text section="home" key="keunggulan_title" tag="h2" class="font-extrabold text-3xl sm:text-4xl text-forest-800" />
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-sand-200 border border-sand-200">
      @foreach ($features as $feature)
        <div class="bg-sand-50 p-8">
          <x-icon :name="$feature->icon ?? 'book'" :size="26" stroke="#2f5d40" :stroke-width="'1.5'" class="mb-5" />
          <h3 class="font-bold text-forest-800 mb-2">{{ $feature->title }}</h3>
          <p class="text-sm leading-relaxed text-ink/70">{{ $feature->description }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ============ ABOUT TEASER ============ -->
<section class="py-24 border-b border-sand-200 bg-forest-800 text-sand-50">
  <div class="max-w-6xl mx-auto px-6 lg:px-8 grid lg:grid-cols-12 gap-14 items-center">
    <div class="lg:col-span-7">
      <x-ecms.text section="home" key="about_teaser_eyebrow" tag="p" class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-300 mb-4" />
      <x-ecms.text section="home" key="about_teaser_title" tag="h2" class="font-extrabold text-3xl sm:text-4xl mb-6" />
      <x-ecms.richtext section="home" key="about_teaser_body" tag="p" class="text-[15px] leading-relaxed text-sand-100/90 mb-8 max-w-xl" />
      <a href="{{ route('about') }}" class="inline-flex items-center gap-2 border border-sand-50/40 hover:bg-sand-50 hover:text-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
        Selengkapnya Tentang Kami
      </a>
    </div>
    <div class="lg:col-span-5">
      <div class="border border-sand-50/20 p-8">
        <p class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-300 mb-3">Visi</p>
        <p class="text-lg leading-relaxed font-medium">
          &ldquo;{{ ecms('home.about_teaser_visi') }}&rdquo;
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============ GALLERY TEASER ============ -->
<section class="py-24 border-b border-sand-200">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-6 mb-14">
      <div class="max-w-xl">
        <x-ecms.text section="home" key="gallery_teaser_eyebrow" tag="p" class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4" />
        <x-ecms.text section="home" key="gallery_teaser_title" tag="h2" class="font-extrabold text-3xl sm:text-4xl text-forest-800" />
      </div>
      <a href="{{ route('gallery') }}" class="inline-flex items-center gap-2 border border-forest-700 text-forest-800 hover:bg-forest-700 hover:text-sand-50 text-sm font-semibold uppercase tracking-wide px-6 py-3 rounded-sm transition-colors shrink-0">
        Lihat Galeri Lengkap
      </a>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @foreach ($latestPhotos as $photo)
        <a href="{{ route('gallery') }}" class="group aspect-square overflow-hidden border border-sand-300 bg-sand-100 block">
          <img src="{{ Storage::url($photo->image_path) }}" alt="{{ $photo->caption }}"
            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
        </a>
      @endforeach
    </div>
  </div>
</section>

<!-- ============ CONTACT CTA ============ -->
<section class="py-24">
  <div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="border border-sand-300 bg-white p-10 sm:p-14 grid lg:grid-cols-12 gap-10 items-center">
      <div class="lg:col-span-8">
        <x-ecms.text section="home" key="contact_cta_eyebrow" tag="p" class="text-[12px] font-semibold uppercase tracking-[0.18em] text-sand-600 mb-4" />
        <x-ecms.text section="home" key="contact_cta_title" tag="h2" class="font-extrabold text-2xl sm:text-3xl text-forest-800 mb-3" />
        <x-ecms.text section="home" key="contact_cta_body" tag="p" class="text-[15px] text-ink/70 max-w-xl" />
      </div>
      <div class="lg:col-span-4 flex lg:justify-end">
        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-forest-700 hover:bg-forest-800 text-sand-50 text-sm font-semibold uppercase tracking-wide px-7 py-3.5 rounded-sm transition-colors">
          Hubungi Kami
        </a>
      </div>
    </div>
  </div>
</section>

</x-layouts.app>
